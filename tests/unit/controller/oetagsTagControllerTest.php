<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';

/**
 * Testing tag class
 */
class  Unit_Controller_oetagsTagControllerTest extends \oeTagsTestCase
{

    public function testSetItemSorting()
    {
        $oView = oxNew('oetagsTagController');
        $oView->setItemSorting('alist', "testSortBy", "testSortOrder");

        $aSorting = $this->getSession()->getVariable("aSorting");

        $this->assertNotNull($aSorting);
        $this->assertTrue(isset($aSorting["alist"]));
        $this->assertEquals("testSortBy", $aSorting["alist"]["sortby"]);
        $this->assertEquals("testSortOrder", $aSorting["alist"]["sortdir"]);
    }

    public function testRender()
    {
        $this->setRequestParameter("searchtag", "kuyichi");
        $oView = oxNew('oetagsTagController');

        $this->assertEquals("page/list/list.tpl", $oView->render());
    }

    /**
     * Testing if render method calls empty category should be outputted
     */
    public function testRender_noArticlesForTag()
    {
        $this->setRequestParameter("pgNr", 999);
        $this->setRequestParameter("searchtag", "notexistingtag");

        $oView = oxNew('oetagsTagController');
        $this->assertEquals("page/list/list.tpl", $oView->render());
    }

    public function testGetAddUrlParams()
    {
        $this->setRequestParameter("searchtag", "testSearchTag");

        $oListView = oxNew('aList');
        $sListViewParams = $oListView->getAddUrlParams();
        $sListViewParams .= "listtype=tag&amp;searchtag=testSearchTag";

        $oView = oxNew('oetagsTagController');
        $this->assertEquals($sListViewParams, $oView->getAddUrlParams());
    }

    public function testGetTitlePageSuffix()
    {
        $oView = $this->getMock("oetagsTagController", array("getActPage"));
        $oView->expects($this->once())->method('getActPage')->will($this->returnValue(0));

        $this->assertNull($oView->getTitlePageSuffix());

        $oView = $this->getMock("oetagsTagController", array("getActPage"));
        $oView->expects($this->once())->method('getActPage')->will($this->returnValue(1));

        $this->assertEquals(oxRegistry::getLang()->translateString('PAGE') . " " . 2, $oView->getTitlePageSuffix());
    }

    public function testGetTreePath()
    {
        $sTag = "testTag";

        $oStr = getStr();

        $aPath[0] = oxNew("oxCategory");
        $aPath[0]->setLink(false);
        $aPath[0]->oxcategories__oxtitle = new oxField(oxRegistry::getLang()->translateString('TAGS'));

        $aPath[1] = oxNew("oxCategory");
        $aPath[1]->setLink(false);
        $aPath[1]->oxcategories__oxtitle = new oxField($oStr->htmlspecialchars($oStr->ucfirst($sTag)));

        $oView = $this->getMock('oetagsTagController', array("getTag"));
        $oView->expects($this->once())->method('getTag')->will($this->returnValue($sTag));

        $this->assertEquals($aPath, $oView->getTreePath());
    }

    public function testNoIndex()
    {
        $tagView = oxNew('oetagsTagController');
        $this->assertTrue(0 === $tagView->noIndex());
    }

    public function testGetCanonicalUrlForPageNumberTwo()
    {
        $tagView = $this->getMock("oetagsTagController", array("getActPage", "_addPageNrParam", "generatePageNavigationUrl", "getTag"));
        $tagView->expects($this->once())->method('getActPage')->will($this->returnValue(1));
        $tagView->expects($this->once())->method('generatePageNavigationUrl')->will($this->returnValue("testUrl"));
        $tagView->expects($this->once())->method('_addPageNrParam')->with($this->equalTo("testUrl"), $this->equalTo(1))->will($this->returnValue("testUrlWithPagePAram"));
        $tagView->expects($this->never())->method('getTag');

        $this->assertEquals("testUrlWithPagePAram", $tagView->getCanonicalUrl());
    }

    public function testGetCanonicalUrlForPageNumberOne()
    {
        oxTestModules::addFunction('oxUtilsServer', 'getServerVar', '{ if ( $aA[0] == "HTTP_HOST") { return "shop.com/"; } else { return "test.php";} }');

        $tagView = $this->getMock("oetagsTagController", array("getActPage", "_addPageNrParam", "generatePageNavigationUrl", "getTag"));
        $tagView->expects($this->never())->method('generatePageNavigationUrl');
        $tagView->expects($this->never())->method('_addPageNrParam');
        $tagView->expects($this->once())->method('getActPage')->will($this->returnValue(0));
        $tagView->expects($this->once())->method('getTag')->will($this->returnValue('testTag'));

        $this->assertEquals(oxRegistry::get("oetagsSeoEncoderTag")->getTagUrl('testTag'), $tagView->getCanonicalUrl());
    }

    public function testGeneratePageNavigationUrlSeo()
    {
        oxTestModules::addFunction("oxUtils", "seoIsActive", "{ return true; }");
        oxTestModules::addFunction("oetagsSeoEncoderTag", "getTagUrl", "{ return 'sTagUrl'; }");

        $tag = $this->getMock('oetagsTagController', array('getTag'));
        $tag->expects($this->once())->method('getTag')->will($this->returnValue('sTag'));

        $this->assertEquals('sTagUrl', $tag->generatePageNavigationUrl());
    }

    public function testGeneratePageNavigationUrl()
    {
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction("oxUtils", "seoIsActive", "{ return false; }");

        $tag = $this->getMock('oetagsTagController', array('getTag'));
        $tag->expects($this->never())->method('getTag');

        $sUrl = $this->getConfig()->getShopHomeURL() . $tag->UNITgetRequestParams(false);
        $this->assertEquals($sUrl, $tag->generatePageNavigationUrl());
    }

    public function testAddPageNrParamSeo()
    {
        oxTestModules::addFunction("oxUtils", "seoIsActive", "{ return true; }");
        oxTestModules::addFunction("oetagsSeoEncoderTag", "getTagPageUrl", "{ return 'sTagPageUrl'; }");

        $tag = $this->getMock('oetagsTagController', array('getTag'));
        $tag->expects($this->once())->method('getTag')->will($this->returnValue('sTag'));

        $this->assertEquals('sTagPageUrl', $tag->UNITaddPageNrParam('sUrl', 10));
    }

    public function testAddPageNrParam()
    {
        $sUrl = 'sUrl?pgNr=10';

        oxTestModules::addFunction("oxUtils", "seoIsActive", "{ return false; }");

        $tag = $this->getMock('oetagsTagController', array('getTag'));
        $tag->expects($this->never())->method('getTag');

        $this->assertEquals($sUrl, $tag->UNITaddPageNrParam('sUrl', 10));
    }

    public function testGetProductLinkType()
    {
        $tagView = oxNew('oetagsTagController');
        $this->assertEquals(OXARTICLE_LINKTYPE_TAG, $tagView->UNITgetProductLinkType());
    }

    public function testPrepareMetaKeyword()
    {
        $article1 = oxNew('oxArticle');
        $article1->setId('oArticle1');
        $article1->oxarticles__oxtitle = new oxField('testoxtitle1');

        $article2 = oxNew('oxArticle');
        $article2->setId('oArticle2');
        $article2->oxarticles__oxtitle = new oxField('testoxtitle2');

        $articleList = oxNew('oxlist');
        $articleList->offsetSet($article1->getId(), $article1);
        $articleList->offsetSet($article2->getId(), $article2);

        $tagView = $this->getMock('oetagsTagController', array('getArticleList'));
        $tagView->expects($this->any())->method('getArticleList')->will($this->returnValue($articleList));
        $this->assertEquals("testoxtitle1, testoxtitle2", $tagView->getMetaKeywords());
    }

    public function testPrepareMetaDescription()
    {
        $article1 = oxNew('oxArticle');
        $article1->setId('oArticle1');
        $article1->oxarticles__oxtitle = new oxField('testoxtitle1');

        $article2 = oxNew('oxArticle');
        $article2->setId('oArticle2');
        $article2->oxarticles__oxtitle = new oxField('testoxtitle2');

        $articleList = oxNew('oxlist');
        $articleList->offsetSet($article1->getId(), $article1);
        $articleList->offsetSet($article2->getId(), $article2);

        $tagView = $this->getMock('oetagsTagController', array('getArticleList'));
        $tagView->expects($this->any())->method('getArticleList')->will($this->returnValue($articleList));
        $this->assertEquals("testoxtitle1, testoxtitle2", $tagView->getMetaDescription());
    }


    public function testGetArticleList()
    {
        $sTag = 'wanduhr';
        $this->getConfig()->setConfigParam('iNrofCatArticles', 20);
        $tag = $this->getProxyClass('oetagsTagController');
        $tag->setNonPublicVar("_sTag", $sTag);
        $articleList = $tag->getArticleList();

        $expectedCount = $this->getTestConfig()->getShopEdition() == 'EE'? 4 : 3;
        $this->assertEquals($expectedCount, $articleList->count());
    }

    public function testGetTitle()
    {
        $sTag = "wanduhr";
        $tag = $this->getProxyClass('oetagsTagController');
        $tag->setNonPublicVar("_sTag", $sTag);
        $this->assertEquals('Wanduhr', $tag->getTitle());
    }

    /**
     * Testing tags::getBreadCrumb()
     *
     * @return null
     */
    public function testGetBreadCrumb()
    {
        $tag = oxNew('oetagsTagController');
        $this->assertEquals(2, count($tag->getBreadCrumb()));
    }

    /**
     * Test get active tag.
     *
     * @return null
     */
    public function testGetTag()
    {
        $tag = oxNew('oetagsTagController');

        $this->setRequestParameter('searchtag', null);
        $this->assertNull($tag->getTag());

        $this->setRequestParameter('searchtag', 'sometag');
        $this->assertEquals('sometag', $tag->getTag());
    }

    /**
     * Tests tags with special chars
     */
    public function testGetTagSpecialChars()
    {
        $tag = oxNew('oetagsTagController');

        $this->setRequestParameter('searchtag', 'sometag<">');
        $this->assertEquals('sometag&lt;&quot;&gt;', $tag->getTag());
    }

}
