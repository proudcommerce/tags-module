<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Testing details class
 */
class Unit_Controller_oetagsArticleDetailsControllerTest extends \oeTagsTestCase
{

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $this->cleanUpTable('oxrecommlists');
        $this->cleanUpTable('oxobject2list');
        $this->cleanUpTable('oxmediaurls');
        $this->cleanUpTable('oxarticles');
        $this->cleanUpTable('oxartextends');

        oxDb::getDB()->execute('delete from oxreviews where oxobjectid = "test"');
        oxDb::getDB()->execute('delete from oxratings');
        parent::tearDown();
    }

    /**
     * Test get active tag.
     *
     * @return null
     */
    public function testGetTag()
    {
        $oDetails = oxNew('Details');

        $this->setRequestParameter('searchtag', null);
        $this->assertNull($oDetails->getTag());

        $this->setRequestParameter('searchtag', 'sometag');
        $this->assertEquals('sometag', $oDetails->getTag());
    }

    /**
     * Tests tags with special chars
     */
    public function testGetTagSpecialChars()
    {
        $oDetails = oxNew('Details');
        $this->setRequestParameter('searchtag', 'sometag<">');
        $this->assertEquals('sometag&lt;&quot;&gt;', $oDetails->getTag());
    }

    /**
     * Test get link type.
     *
     * @return null
     */
    public function testGetLinkType()
    {
        $this->setRequestParameter('listtype', 'vendor');
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_VENDOR, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', 'manufacturer');
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_MANUFACTURER, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', 'tag');
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory');
        $this->assertEquals(OXARTICLE_LINKTYPE_TAG, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', null);
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue(null));
        $this->assertEquals(OXARTICLE_LINKTYPE_CATEGORY, $oDetailsView->getLinkType());

        $oCategory = $this->getMock("oxcategory", array('isPriceCategory'));
        $oCategory->expects($this->once())->method('isPriceCategory')->will($this->returnValue(true));

        $this->setRequestParameter('listtype', "recommlist");
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->never())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_RECOMM, $oDetailsView->getLinkType());

        $this->setRequestParameter('listtype', null);
        $oDetailsView = $this->getMock("details", array('getActiveCategory'));
        $oDetailsView->expects($this->once())->method('getActiveCategory')->will($this->returnValue($oCategory));
        $this->assertEquals(OXARTICLE_LINKTYPE_PRICECATEGORY, $oDetailsView->getLinkType());
    }

    /**
     * Test get tags.
     *
     * @return null
     */
    public function testGetTags()
    {
        $oArt = oxNew('oxArticle');
        $oArt->load('2000');

        /** @var Details|PHPUnit_Framework_MockObject_MockObject $oDetails */
        $oDetails = $this->getMock('details', array('getUser', 'getProduct'));
        $oDetails->expects($this->once())->method('getUser')->will($this->returnValue(true));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArt));
        $oDetails->editTags();

        $aTags = $oDetails->getTags();
        $this->assertTrue(isset($aTags['coolen']));

        // Demo data is different in EE and CE
        $expectedCount = $this->getTestConfig()->getShopEdition() == 'EE' ? 6 : 5;
        $this->assertEquals($expectedCount, count($aTags));
    }

    /**
     * Test get tags for editing.
     *
     * @return null
     */
    public function testGetEditTags()
    {
        $oArt = oxNew('oxArticle');
        $oArt->load('2000');

        /** @var Details|PHPUnit_Framework_MockObject_MockObject $oDetails */
        $oDetails = $this->getMock('details', array('getUser', 'getProduct'));
        $oDetails->expects($this->once())->method('getUser')->will($this->returnValue(true));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oArt));

        $oDetails->editTags();
        $this->assertTrue($oDetails->getEditTags());
    }

    /**
     * Test get tag cloud after adding new tags.
     *
     * @return null
     */
    public function testGetTagCloudManagerAfterAddTags()
    {
        oxTestModules::addFunction('oetagsSeoEncoderTag', '_saveToDb', '{return null;}');
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");

        $this->setRequestParameter('newTags', "newTag");

        /** @var oxSession|PHPUnit_Framework_MockObject_MockObject $oSession */
        $oSession = $this->getMock('oxSession', array('checkSessionChallenge'));
        $oSession->expects($this->once())->method('checkSessionChallenge')->will($this->returnValue(true));
        oxRegistry::set('oxSession', $oSession);

        $oArt = oxNew('oxArticle');
        $oArt->load('2000');
        $oArt->setId('_testArt');
        $oArt->save();

        $oArticle = oxNew('oxArticle');
        $oArticle->load('_testArt');

        $oDetails = $this->getProxyClass('details');
        $oDetails->setNonPublicVar("_oProduct", $oArticle);
        $oDetails->addTags();

        $this->assertTrue($oDetails->getTagCloudManager() instanceof oetagsTagCloud);
    }

    /**
     * Test adding of tags
     *
     * @return null
     */
    public function testAddTags()
    {
        $this->setRequestParameter('newTags', "tag1,tag2,tag3,tag3,tag3");

        /** @var oxSession|PHPUnit_Framework_MockObject_MockObject $oSession */
        $oSession = $this->getMock('oxSession', array('checkSessionChallenge'));
        $oSession->expects($this->once())->method('checkSessionChallenge')->will($this->returnValue(true));
        oxRegistry::set('oxSession', $oSession);

        $oArticle = oxNew('oxArticle');
        $oArticle->setId("_testArt");

        /** @var Details|PHPUnit_Framework_MockObject_MockObject $oDetails */
        $oDetails = $this->getMock('details', array('getProduct'));
        $oDetails->expects($this->any())->method('getProduct')->will($this->returnValue($oArticle));
        $oDetails->addTags();

        $oArticleTagList = oxNew('oetagsArticleTagList');
        $oArticleTagList->load('_testArt');

        $aTags = array(
            'tag1' => oxNew('oetagsTag', 'tag1'),
            'tag2' => oxNew('oetagsTag', 'tag2'),
            'tag3' => oxNew('oetagsTag', 'tag3'),
        );

        $this->assertEquals($aTags, $oArticleTagList->getArray());
    }

    /**
     * Test adding of tags and getting error with ajax enabled
     *
     * @return null
     */
    public function testAddTagsErrorAjax()
    {
        $this->setRequestParameter('blAjax', true);
        $this->setRequestParameter('newTags', "admin,tag1,tag2,tag3,tag3,tag3");

        /** @var oxSession|PHPUnit_Framework_MockObject_MockObject $oSession */
        $oSession = $this->getMock('oxSession', array('checkSessionChallenge'));
        $oSession->expects($this->once())->method('checkSessionChallenge')->will($this->returnValue(true));
        oxRegistry::set('oxSession', $oSession);

        $oArticle = oxNew('oxArticle');
        $oArticle->setId("_testArt");

        /** @var Details|PHPUnit_Framework_MockObject_MockObject $oDetails */
        $oDetails = $this->getMock('details', array('getProduct'));
        $oDetails->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($oArticle));

        $sResult = '{"tags":["tag1","tag2","tag3"],"invalid":["admin"],"inlist":[]}';

        /** @var oxUtils|PHPUnit_Framework_MockObject_MockObject $oUtils */
        $oUtils = $this->getMock('oxUtils', array('showMessageAndExit'));
        $oUtils->expects($this->any())
            ->method('showMessageAndExit')
            ->with($this->equalTo($sResult));

        oxRegistry::set("oxUtils", $oUtils);

        $oDetails->addTags();
    }

    /**
     * Test highlighting tags.
     * If tag does not exists, it should be created.
     *
     * @return null
     */
    public function testAddTagsHighlight()
    {
        /** @var oxSession|PHPUnit_Framework_MockObject_MockObject $oSession */
        $oSession = $this->getMock('oxSession', array('checkSessionChallenge'));
        $oSession->expects($this->once())->method('checkSessionChallenge')->will($this->returnValue(true));
        oxRegistry::set('oxSession', $oSession);

        $oArticleTagList = oxNew('oetagsArticleTagList');
        $oArticleTagList->load('_testArt');
        $oArticleTagList->addTag('tag1');
        $oArticleTagList->save();

        $this->setRequestParameter('highTags', "tag1,tag1,tag2,tag2");

        $oArticle = oxNew('oxArticle');
        $oArticle->setId("_testArt");

        /** @var Details|PHPUnit_Framework_MockObject_MockObject $oDetails */
        $oDetails = $this->getMock('details', array('getProduct'));
        $oDetails->expects($this->any())->method('getProduct')->will($this->returnValue($oArticle));
        $oDetails->addTags();

        $oArticleTagList->load('_testArt');

        $oTag = oxNew('oetagsTag', 'tag1');
        $oTag->setHitCount(2);

        $aTags = array('tag1' => $oTag, 'tag2' => oxNew('oetagsTag', 'tag2'));

        $this->assertEquals($aTags, $oArticleTagList->getArray());
    }

    /**
     * Test base view class title getter with searchtag.
     *
     * @return null
     */
    public function testGetTitleWithTag()
    {
        $this->setRequestParameter('searchtag', 'someTag');

        $oProduct = oxNew('oxArticle');
        $oProduct->oxarticles__oxtitle = new oxField('product title');
        $oProduct->oxarticles__oxvarselect = new oxField('and varselect');

        $oDetails = $this->getMock('details', array('getProduct'));
        $oDetails->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));

        $this->assertEquals('product title and varselect - someTag', $oDetails->getTitle());
    }

    /**
     * Test base view class title getter - no product.
     *
     * @return null
     */
    public function testGetTitle_noproduct()
    {
        $oView = $this->getMock('Details', array('getProduct'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue(null));
        $this->assertNull($oView->getTitle());
    }

    /**
     * Test cannonical URL getter - no product.
     *
     * @return null
     */
    public function testGetCanonicalUrl_noproduct()
    {
        $oView = $this->getMock('Details', array('getProduct'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue(null));
        $this->assertNull($oView->getCanonicalUrl());
    }

    /**
     * Testing Details::getBreadCrumb()
     *
     * @return null
     */
    public function testGetBreadCrumb()
    {

        $details = oxNew('Details');

        $this->setRequestParameter('listtype', 'search');
        $this->assertTrue(count($details->getBreadCrumb()) >= 1);

        $details = oxNew('details');
        $this->setRequestParameter('listtype', 'tag');
        $this->assertTrue(count($details->getBreadCrumb()) >= 1);

        $details = oxNew('details');
        $this->setRequestParameter('listtype', 'recommlist');
        $this->assertTrue(count($details->getBreadCrumb()) >= 1);

        $details = oxNew('details');
        $this->setRequestParameter('listtype', 'vendor');
        $this->assertTrue(count($details->getBreadCrumb()) >= 1);

        $this->setRequestParameter('listtype', 'aaa');

        $oCat1 = $this->getMock('oxcategory', array('getLink'));
        $oCat1->expects($this->once())->method('getLink')->will($this->returnValue('linkas1'));
        $oCat1->oxcategories__oxtitle = new oxField('title1');

        $oCat2 = $this->getMock('oxcategory', array('getLink'));
        $oCat2->expects($this->once())->method('getLink')->will($this->returnValue('linkas2'));
        $oCat2->oxcategories__oxtitle = new oxField('title2');

        $oView = $this->getMock("details", array("getCatTreePath"));
        $oView->expects($this->once())->method('getCatTreePath')->will($this->returnValue(array($oCat1, $oCat2)));

        $this->assertTrue(count($oView->getBreadCrumb()) >= 1);
    }

    public function testCanChangeTags_nouser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser');

        $this->assertFalse($oView->canChangeTags());
    }

    public function testCanChangeTags_withuser()
    {
        $oView = $this->getMock('Details', array('getUser'));
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(true));

        $this->assertTrue($oView->canChangeTags());
    }

    public function testCancelTags()
    {
        $this->setRequestParameter('blAjax', false);

        $oArticleTagList = $this->getMock('oetagsArticleTagList', array('load'));
        $oArticleTagList->expects($this->any())->method('load')->with($this->equalTo('test_artid'))->will($this->returnValue(true));
        $oArticleTagList->set('testtags');
        oxTestModules::addModuleObject('oetagsArticleTagList', $oArticleTagList);

        $oProduct = $this->getMock('oxArticle', array('getId'));
        $oProduct->expects($this->once())->method('getId')->will($this->returnValue('test_artid'));


        $oView = $this->getMock($this->getProxyClassName('Details'), array('getProduct'));
        $oView->expects($this->once())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->cancelTags();

        $this->assertEquals(array('testtags' => oxNew('oetagsTag', 'testtags')), $oView->getNonPublicVar('_aTags'));
        $this->assertSame(false, $oView->getNonPublicVar('_blEditTags'));
    }

    public function testCancelTags_ajaxcall()
    {
        $this->setRequestParameter('blAjax', true);

        $oArticleTagList = $this->getMock('oetagsArticleTagList', array('load'));
        $oArticleTagList->expects($this->any())->method('load')->with($this->equalTo('test_artid'))->will($this->returnValue(true));
        $oArticleTagList->set('testtags');
        oxTestModules::addModuleObject('oetagsArticleTagList', $oArticleTagList);

        $oProduct = $this->getMock('oxArticle', array('getId'));
        $oProduct->expects($this->any())->method('getId')->will($this->returnValue('test_artid'));

        $oUtils = $this->getMock('oxUtils', array('setHeader', 'showMessageAndExit'));
        $oUtils->expects($this->once())->method('setHeader');
        $oUtils->expects($this->once())->method('showMessageAndExit');
        oxTestModules::addModuleObject('oxUtils', $oUtils);

        $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();
        $oUtilsView = $this->getMock('oxUtilsView', array('getSmarty'));
        $oUtilsView->expects($this->once())->method('getSmarty')->will($this->returnValue($oSmarty));
        oxRegistry::set('oxUtilsView', $oUtilsView);

        $oView = $this->getMock($this->getProxyClassName('Details'), array('getProduct', 'getViewId'));
        $oView->expects($this->any())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->expects($this->any())->method('getViewId')->will($this->returnValue('test_viewId'));
        $oView->cancelTags();

        $result = $oView->getNonPublicVar('_aTags');
        $this->assertTrue(is_a($result['testtags'], 'oetagsTag'));
        $this->assertSame(false, $oView->getNonPublicVar('_blEditTags'));
    }

    public function testEditTags_nouser()
    {
        $oView = $this->getMock($this->getProxyClassName('Details'), array('getConfig', 'getProduct', 'getViewConfig', 'getViewId', 'getUser'));
        $oView->expects($this->never())->method('getConfig');
        $oView->expects($this->never())->method('getProduct');
        $oView->expects($this->never())->method('getViewConfig');
        $oView->expects($this->never())->method('getViewId');
        $oView->expects($this->once())->method('getUser')->will($this->returnValue(false));
        $oView->editTags();
    }

    public function testEditTags_ajaxcall()
    {
        $this->setRequestParameter('blAjax', true);

        $oArticleTagList = $this->getMock('oetagsArticleTagList', array('load'));
        $oArticleTagList->expects($this->any())->method('load')->with($this->equalTo('test_artid'))->will($this->returnValue(true));
        $oArticleTagList->set('testtags');
        oxTestModules::addModuleObject('oetagsArticleTagList', $oArticleTagList);

        $oProduct = $this->getMock('oxArticle', array('getId'));
        $oProduct->expects($this->any())->method('getId')->will($this->returnValue('test_artid'));

        $oUtils = $this->getMock('oxUtils', array('setHeader', 'showMessageAndExit'));
        $oUtils->expects($this->once())->method('setHeader');
        $oUtils->expects($this->once())->method('showMessageAndExit');
        oxTestModules::addModuleObject('oxUtils', $oUtils);

        $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();
        $oUtilsView = $this->getMock('oxUtilsView', array('getSmarty'));
        $oUtilsView->expects($this->once())->method('getSmarty')->will($this->returnValue($oSmarty));
        oxRegistry::set('oxUtilsView', $oUtilsView);

        $oView = $this->getMock($this->getProxyClassName('Details'), array('getProduct', 'getViewId', 'getUser'));
        $oView->expects($this->any())->method('getProduct')->will($this->returnValue($oProduct));
        $oView->expects($this->any())->method('getViewId')->will($this->returnValue('test_viewId'));
        $oView->expects($this->any())->method('getUser')->will($this->returnValue(true));
        $oView->editTags();

        $result = $oView->getNonPublicVar('_aTags');
        $this->assertTrue(is_a($result['testtags'], 'oetagsTag'));
        $this->assertSame(true, $oView->getNonPublicVar('_blEditTags'));
    }

    public function testGetViewId_testHash()
    {
        $oView = $this->getMock($this->getProxyClassName('Details'), array('getTags'));
        $oView->expects($this->any())->method('getTags')->will($this->returnValue('test_tags'));

        $oBaseView = oxNew('oxUBase');
        $sBaseViewId = $oBaseView->getViewId();

        $this->setRequestParameter('anid', 'test_anid');
        $this->setRequestParameter('cnid', 'test_cnid');
        $this->setRequestParameter('listtype', 'search');
        $this->setRequestParameter('searchparam', 'test_sparam');
        $this->setRequestParameter('renderPartial', 'test_render');
        $this->setRequestParameter('varselid', 'test_varselid');
        $aFilters = array('test_cnid' => array(0 => 'test_filters'));
        $this->setSessionParam('session_attrfilter', $aFilters);

        $sExpected = $sBaseViewId . '|test_anid|';
        if ('EE' == $this->getTestConfig()->getShopEdition()) {
            $sExpected .= 'search-test_sparam|test_cnid' . serialize('test_filters') . '|test_render|' . serialize('test_varselid') . '|' . serialize('test_tags');

        }

        $sResp = $oView->getViewId();
        $this->assertSame($sExpected, $sResp);
        $this->assertSame($sExpected, $oView->getNonPublicVar('_sViewId'));
    }

}


