<?php
/**
* #PHPHEADER_OETAGS_LICENSE_INFORMATION#
*/

/**
* Testing oetagsViewConfig class
*/
class Unit_Core_oetagsViewConfigTest extends \oeTagsTestCase
{

    /**
     * Test active tag seo.
     */
    public function testGetActTagSeo()
    {
        $sTag = $this->getTestConfig()->getShopEdition() == 'EE' ? 'ideale' : 'liebliche';

        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        $this->setRequestParameter('searchtag', $sTag);

        $tag = new stdclass();
        $tag->sTag = $sTag;
        $tag->link = $this->getConfig()->getShopUrl() . "oetagstagcontroller/{$sTag}/";

        $viewConfig = oxNew('oxviewconfig');
        $view = oxNew('oxubase');
        $this->assertEquals($tag, $viewConfig->getActTag($view));
    }

    /**
     * Test getting active tag.
     */
    public function testGetActTag()
    {
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return false;}");
        $this->setRequestParameter('searchtag', 'someTag');

        $tag = new stdclass();
        $tag->sTag = 'someTag';
        $tag->link = $this->getConfig()->getShopHomeURL() . "cl=oetagstagcontroller&amp;searchtag=someTag";

        $viewConfig = oxNew('oxviewconfig');
        $view = oxNew('oxubase');
        $this->assertEquals($tag, $viewConfig->getActTag($view));
    }

    /**
     * viewConfig::showTags() test case
     *
     * @return null
     */
    public function testShowTagsYes()
    {
        $this->setConfigParam("oetagsShowTags", true);

        $viewConfig = oxNew('oxviewconfig');
        $view = oxNew('oxubase');
        $this->assertTrue($viewConfig->showTags($view));
    }

    /**
     * viewConfig::showTags() test case
     *
     * @return null
     */
    public function testShowTagsNever()
    {
        $this->setConfigParam("oetagsShowTags", true);

        $viewConfig = oxNew('oxviewconfig');
        $view = oxNew('account');
        $this->assertFalse($viewConfig->showTags($view));
    }

    /**
     * Tests if navigation parameters getter collects all needed values.
     * Tested implicitly with view object using overwritten viewconfig.
     *
     * @return null
     */
    public function testGetNavigationParams()
    {
        $parameters = array("cnid"               => "testCategory",
                            "mnid"               => "testManufacturer",
                            "listtype"           => "testType",
                            "ldtype"             => "testDisplay",
                            "recommid"           => "paramValue",
                            "searchrecomm"       => "testRecommendation",
                            "searchparam"        => "testSearchParam",
                            "searchtag"          => "testTag",
                            "searchvendor"       => "testVendor",
                            "searchcnid"         => "testCategory",
                            "searchmanufacturer" => "testManufacturer",
        );
        foreach ($parameters as $sKey => $sValue) {
            $this->setRequestParameter($sKey, $sValue);
        }
        $parameters['actcontrol'] = "content";

        $view = oxNew('oxubase');
        $view->setClassName('content');
        $result = $view->getNavigationParams();

        ksort($parameters);
        ksort($result);
        $this->assertEquals($parameters, $result);
    }

    /*
     * Test adding additional data
     */
    public function testAdditionalParameters()
    {
        $this->setRequestParameter('cnid', 'testCnId');
        $this->setRequestParameter('lang', '1');
        $this->setRequestParameter('searchparam', 'aa');
        $this->setRequestParameter('searchtag', 'testtag');
        $this->setRequestParameter('searchcnid', 'testcat');
        $this->setRequestParameter('searchvendor', 'testvendor');
        $this->setRequestParameter('searchmanufacturer', 'testmanufact');
        $this->setRequestParameter('mnid', 'testid');
        $view = oxNew('oxubase');
        $view->setClassName('testClass');
        $myConfig = $this->getMock('oxconfig', array('getActiveView'));
        $myConfig->expects($this->once())
            ->method('getActiveView')
            ->will($this->returnValue($view));
        $view->setConfig($myConfig);
        $view->getAdditionalParams();

        $additionalParameters = "cl=testClass&amp;searchparam=aa&amp;searchcnid=testcat&amp;searchvendor=testvendor&amp;searchmanufacturer=testmanufact&amp;cnid=testCnId&amp;mnid=testid&amp;searchtag=testtag";
        $this->assertEquals($additionalParameters, $view->getAdditionalParams());
    }

    /**
     * Test getting request parameters
     */
    public function testGetRequestParams()
    {
        $view = $this->getMock('oxubase', array('getClassName', 'getFncName'));
        $view->expects($this->any())->method('getClassName')->will($this->returnValue('testclass'));
        $view->expects($this->any())->method('getFncName')->will($this->returnValue('testfunc'));
        $this->setRequestParameter('cnid', 'catid');
        $this->setRequestParameter('mnid', 'manId');
        $this->setRequestParameter('anid', 'artid');
        $this->setRequestParameter('page', '2');
        $this->setRequestParameter('tpl', 'test');
        $this->setRequestParameter('oxloadid', 'test');
        $this->setRequestParameter('pgNr', '2');
        $this->setRequestParameter('searchparam', 'test');
        $this->setRequestParameter('searchcnid', 'searchcat');
        $this->setRequestParameter('searchvendor', 'searchven');
        $this->setRequestParameter('searchmanufacturer', 'searchman');
        $this->setRequestParameter('searchrecomm', 'searchrec');
        $this->setRequestParameter('searchtag', 'searchtag');
        $this->setRequestParameter('recommid', 'recid');

        $sExpUrl = 'cl=testclass&amp;fnc=testfunc&amp;cnid=catid&amp;mnid=manId&amp;anid=artid&amp;page=2&amp;tpl=test&amp;oxloadid=test&amp;pgNr=2' .
                   '&amp;searchparam=test&amp;searchcnid=searchcat&amp;searchvendor=searchven' .
                   '&amp;searchmanufacturer=searchman&amp;searchrecomm=searchrec&amp;recommid=recid&amp;searchtag=searchtag';
        $this->assertEquals($sExpUrl, $view->UNITgetRequestParams());
    }

    /**
     * Test for dynamic url parameters.
     */
    public function testGetDynUrlParams()
    {
        $oV = oxNew('oxubase');
        $this->setRequestParameter('searchparam', 'sa"');
        $this->setRequestParameter('searchcnid', 'sa"%22');
        $this->setRequestParameter('searchvendor', 'sa%22"');
        $this->setRequestParameter('searchmanufacturer', 'ma%22"');

        $oV->setListType('lalala');
        $this->assertEquals('', $oV->getDynUrlParams());
        $oV->setListType('search');
        $sGot = $oV->getDynUrlParams();
        $this->assertEquals('&amp;listtype=search&amp;searchparam=sa%22&amp;searchcnid=sa%22%22&amp;searchvendor=sa%22%22&amp;searchmanufacturer=ma%22%22', $sGot);
    }

    /**
     * Test for dynamic url parameters.
     */
    public function testGetDynUrlParamsInTaglist()
    {
        $oV = oxNew('oxubase');
        $this->setRequestParameter('searchtag', 'testtag');
        $oV->setListType('tag');
        $sGot = $oV->getDynUrlParams();
        $this->assertEquals('&amp;listtype=tag&amp;searchtag=testtag', $sGot);
    }

    public function testGetLogoutLink()
    {
        $oCfg = $this->getMock('oxconfig', array('getShopHomeURL', 'isSsl'));
        $oCfg->expects($this->once())
            ->method('getShopHomeURL')
            ->will($this->returnValue('shopHomeUrl/'));
        $oCfg->expects($this->once())
            ->method('isSsl')
            ->will($this->returnValue(false));

        $oVC = $this->getMock(
            'oxviewconfig'
            , array('getConfig', 'getTopActionClassName', 'getActCatId', 'getActTplName', 'getActContentLoadId'
                    , 'getActArticleId', 'getActSearchParam', 'getActSearchTag', 'getActListType', 'getActRecommendationId')
        );

        $oVC->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($oCfg));
        $oVC->expects($this->once())
            ->method('getTopActionClassName')
            ->will($this->returnValue('actionclass'));
        $oVC->expects($this->once())
            ->method('getActCatId')
            ->will($this->returnValue('catid'));
        $oVC->expects($this->once())
            ->method('getActTplName')
            ->will($this->returnValue('tpl'));
        $oVC->expects($this->once())
            ->method('getActContentLoadId')
            ->will($this->returnValue('oxloadid'));
        $oVC->expects($this->once())
            ->method('getActArticleId')
            ->will($this->returnValue('anid'));
        $oVC->expects($this->once())
            ->method('getActSearchParam')
            ->will($this->returnValue('searchparam'));
        $oVC->expects($this->once())
            ->method('getActSearchTag')
            ->will($this->returnValue('searchtag'));
        $oVC->expects($this->once())
            ->method('getActRecommendationId')
            ->will($this->returnValue('testrecomm'));
        $oVC->expects($this->once())
            ->method('getActListType')
            ->will($this->returnValue('listtype'));

        $this->assertEquals('shopHomeUrl/cl=actionclass&amp;cnid=catid&amp;anid=anid&amp;searchparam=searchparam&amp;recommid=testrecomm&amp;listtype=listtype&amp;fnc=logout&amp;tpl=tpl&amp;oxloadid=oxloadid&amp;redirect=1&amp;searchtag=searchtag', $oVC->getLogoutLink());
    }

    /**
     * Tests forming of logout link when in ssl page
     *
     * @return null
     */
    public function testGetLogoutLinkSsl()
    {
        $oCfg = $this->getMock('oxconfig', array('getShopSecureHomeUrl', 'isSsl'));
        $oCfg->expects($this->once())
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('sslShopHomeUrl/'));
        $oCfg->expects($this->once())
            ->method('isSsl')
            ->will($this->returnValue(true));

        $oVC = $this->getMock(
            'oxviewconfig'
            , array('getConfig', 'getTopActionClassName', 'getActCatId', 'getActTplName', 'getActContentLoadId'
                    , 'getActArticleId', 'getActSearchParam', 'getActSearchTag', 'getActListType', 'getActRecommendationId')
        );

        $oVC->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($oCfg));
        $oVC->expects($this->once())
            ->method('getTopActionClassName')
            ->will($this->returnValue('actionclass'));
        $oVC->expects($this->once())
            ->method('getActCatId')
            ->will($this->returnValue('catid'));
        $oVC->expects($this->once())
            ->method('getActTplName')
            ->will($this->returnValue('tpl'));
        $oVC->expects($this->once())
            ->method('getActContentLoadId')
            ->will($this->returnValue('oxloadid'));
        $oVC->expects($this->once())
            ->method('getActArticleId')
            ->will($this->returnValue('anid'));
        $oVC->expects($this->once())
            ->method('getActSearchParam')
            ->will($this->returnValue('searchparam'));
        $oVC->expects($this->once())
            ->method('getActSearchTag')
            ->will($this->returnValue('searchtag'));
        $oVC->expects($this->once())
            ->method('getActRecommendationId')
            ->will($this->returnValue('testrecomm'));
        $oVC->expects($this->once())
            ->method('getActListType')
            ->will($this->returnValue('listtype'));

        $this->assertEquals('sslShopHomeUrl/cl=actionclass&amp;cnid=catid&amp;anid=anid&amp;searchparam=searchparam&amp;recommid=testrecomm&amp;listtype=listtype&amp;fnc=logout&amp;tpl=tpl&amp;oxloadid=oxloadid&amp;redirect=1&amp;searchtag=searchtag', $oVC->getLogoutLink());
    }
}
