<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxDb;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use \oxTestModules;

/**
 * Tests oxReverseProxyBackEnd
 */
class oetagsReverseProxyUrlGeneratorTest extends \oxUnitTestCase
{
    /**
     * Sets up oxReverseProxyAccess mock
     *
     * @return null|void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setConfigParam("blReverseProxyActive", true);
    }

    /**
     * Sets up oxReverseProxyAccess mock
     *
     * @return null|void
     */
    protected function tearDown()
    {
        $this->setConfigParam("blReverseProxyActive", false);
        $this->cleanUpTable("oxseo", "oxident");

        parent::tearDown();
    }

    /**
     *  Setting and getting widgets
     */
    public function testSetAndGetWidgets()
    {
        $oGenerator = oxNew('oxReverseProxyUrlGenerator');
        $oGenerator->setWidget('widget1');
        $oGenerator->setWidget('widget2');
        $oGenerator->setWidget('widget3');
        $oGenerator->setWidget('widget4', array('id41' => 'val41'));
        $oGenerator->setWidget('widget5', array('id51' => 'val51', 'id52' => 'val52'));

        $this->assertEquals(5, count($oGenerator->getWidgets()));
    }

    /**
     *  Setting and getting static page
     */
    public function testSetAndGetStaticPage()
    {
        $oGenerator = oxNew('oxReverseProxyUrlGenerator');
        $oGenerator->setStaticPage('page1');
        $oGenerator->setStaticPage('page2');
        $oGenerator->setStaticPage('', '_testIdent');

        $this->assertEquals(3, count($oGenerator->getStaticPages()));
    }

    /**
     *  Setting and getting dynamic page
     */
    public function testSetAndGetDynamicPage()
    {
        $oGenerator = oxNew('oxReverseProxyUrlGenerator');
        $oGenerator->setDynamicPage('page1');
        $oGenerator->setDynamicPage('page2');

        $this->assertEquals(2, count($oGenerator->getDynamicPages()));
    }

    /**
     *  Setting and getting business objects
     */
    public function testSetAndGetObjects()
    {
        $oGenerator = oxNew('oxReverseProxyUrlGenerator');
        $oGenerator->setObject('object1', 'id1');
        $oGenerator->setObject('object2', 'id2');

        $this->assertEquals(2, count($oGenerator->getObjects()));
    }

    /**
     *  Getting urls when seo is off
     */
    public function testGetUrlSeoOff()
    {
        $this->_prepareSeoTable();

        $aExpectedUrls = array(
            '/index.php?cl=page1.*',
            '/index.php?cl=page2.*',
            '/widget.php?.*cl=widget1.*',
            '/widget.php?.*cl=widget2.*',
            '/widget.php?.*cl=widget3.*id31=val31.*',
            '/widget.php?.*cl=widget4.*id41=val41.*id42=val42.*',
            '/widget.php?.*anid=val42.*cl=widget5.*xnid=val44.*',
            '/index.php?.*cl=details.*anid=oxarticleid.*',
            '/index.php?.*cl=content.*oxcid=oxcontentid.*',
            '/index.php?.*cl=manufacturerlist.*mnid=oxmanufacturerid.*',
            '/index.php?.*cl=alist.*cnid=oxcategoryid.*',
            '/index.php?.*cl=vendorlist.*cnid=oxvendorid.*',
            '/index.php?.*cl=details.*',
            '/index.php?.*cl=alist.*',
        );

        $this->setConfigParam('blSeoMode', false);
        $this->assertFalse(oxRegistry::getUtils()->seoIsActive(true));

        $oGenerator = oxNew('oxReverseProxyUrlGenerator');

        $oGenerator->setWidget('widGet1');
        $oGenerator->setWidget('widget2');
        $oGenerator->setWidget('widget3', array('id31' => 'val31'));
        $oGenerator->setWidget('widget4', array('id41' => 'val41', 'id42' => 'val42'));
        $oGenerator->setWidget('widget5', array('anid' => 'val42', 'xnid' => 'val44'));

        $oGenerator->setStaticPage('page1');
        $oGenerator->setStaticPage('page2');

        $oGenerator->setObject('oxobject', 'oxobjectid');
        $oGenerator->setObject('oxarticle', 'oxarticleid');
        $oGenerator->setObject('oxcontent', 'oxcontentid');
        $oGenerator->setObject('oxcategory', 'oxcategoryid');
        $oGenerator->setObject('oxmanufacturer', 'oxmanufacturerid');
        $oGenerator->setObject('oxvendor', 'oxvendorid');
        $oGenerator->setObject('oxarticle');
        $oGenerator->setObject('oxcategory');

        $aGeneratedUrls = $oGenerator->getUrls();
        $this->assertEquals(14, count($aGeneratedUrls));
        foreach ($aExpectedUrls as $sUrl) {
            $this->assertTrue(in_array($sUrl, $aGeneratedUrls), $sUrl);
        }
    }

    protected function _prepareSeoTable()
    {
        $oDb = oxDb::getDb();
        $oDb->Execute('TRUNCATE TABLE `oxseo`');
        $sSqlTail = ", `oxlang`=0, `oxshopid`=1";
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/page1', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page1', `oxobjectid`='page1', `oxident`='_test1'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/page2', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page2', `oxobjectid`='page2', `oxident`='_test2'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid', `oxident`='_test3'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/content', `oxtype` = 'oxcontent', `oxobjectid`='oxcontentid', `oxident`='_test4'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid', `oxident`='_test5'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/manufacturer', `oxtype` = 'oxmanufacturer', `oxobjectid`='oxmanufacturerid', `oxident`='_test6'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/vendor', `oxtype` = 'oxvendor', `oxobjectid`='oxvendorid', `oxident`='_test7'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid2', `oxident`='_test8'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid2', `oxident`='_test9'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/rsslist', `oxtype` = 'dynamic', `oxstdurl` = '?cl=rss', `oxident`='_test10', `oxobjectid`='_test10'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/oetagstaglist', `oxtype` = 'dynamic', `oxstdurl` = 'index.php?cl=oetagstagcontroller&newtag', `oxident`='_test11', `oxobjectid`='_test11'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/recommlist', `oxtype` = 'dynamic', `oxstdurl` = 'index.php?cl=recommlist', `oxident`='_test12', `oxobjectid`='_test12'" . $sSqlTail;
        $oDb->Execute($sSql);
        $sSql = "REPLACE INTO `oxseo` SET `oxseourl` = 'seo/page3', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page3', `oxobjectid`='page3', `oxident`='_test13'" . $sSqlTail;
        $oDb->Execute($sSql);
    }

    /**
     *  Getting urls when seo is on
     */
    public function testGetUrlSeoOn()
    {
        $aExpectedUrls = array(
            '/seo/page1.*',
            '/seo/page2.*',
            '/seo/page3.*',
            '/widget.php?.*cl=widget1.*',
            '/widget.php?.*cl=widget2.*',
            '/widget.php?.*cl=widget3.*id31=val31.*',
            '/widget.php?.*cl=widget4.*id41=val41.*id42=val42.*',
            '/widget.php?.*anid=val41.*cl=widget5.*xnid=val42.*',
            '/seo/article.*',
            '/seo/content.*',
            '/seo/category.*',
            '/seo/manufacturer.*',
            '/seo/vendor.*',
            '/seo/oetagstaglist.*',
            '/seo/rsslist.*',
            '/seo/recommlist.*',
        );

        $this->getConfig()->setConfigParam('blSeoMode', true);

        $this->_prepareSeoTable();

        $oGenerator = oxNew('oxReverseProxyUrlGenerator');

        $oGenerator->setWidget('widget1');
        $oGenerator->setWidget('widget2');
        $oGenerator->setWidget('widget3', array('id31' => 'val31'));
        $oGenerator->setWidget('widget4', array('id41' => 'val41', 'id42' => 'val42'));
        $oGenerator->setWidget('widget5', array('anid' => 'val41', 'xnid' => 'val42'));

        $oGenerator->setStaticPage('page1');
        $oGenerator->setStaticPage('page2');
        //by objectId
        $oGenerator->setStaticPage('', 'page3');

        $oGenerator->setDynamicPage('rss');
        $oGenerator->setDynamicPage('recommlist');
        $oGenerator->setDynamicPage('oetagstagcontroller');

        $oGenerator->setObject('oxobject', 'oxobjectid');
        $oGenerator->setObject('oxarticle', 'oxarticleid');
        $oGenerator->setObject('oxcontent', 'oxcontentid');
        $oGenerator->setObject('oxcategory', 'oxcategoryid');
        $oGenerator->setObject('oxmanufacturer', 'oxmanufacturerid');
        $oGenerator->setObject('oxvendor', 'oxvendorid');
        $oGenerator->setObject('oxarticle');
        $oGenerator->setObject('oxcategory');

        $aGeneratedUrls = $oGenerator->getUrls();
        $this->assertEquals(18, count($aGeneratedUrls));

        foreach ($aExpectedUrls as $sUrl) {
            $this->assertTrue(in_array($sUrl, $aGeneratedUrls));
        }
    }

    /**
     * @return array
     */
    public function providerPrependsPathToUrlQuery()
    {
        return array(
            array('shopUrlPath', 'urlQuery', 'shopUrlPath.*urlQuery'),
            array('shopUrlPath/', '/urlQuery', 'shopUrlPath.*urlQuery'),
            array('', 'urlQuery', 'urlQuery'),
            array('/', 'urlQuery', 'urlQuery'),
            // Cases for home page.
            array('/shopUrlPath/', '/', '/shopUrlPath/'),
            array('shopUrlPath', '/', '/shopUrlPath/'),
            array('', '/', '/'),
            array('/', '/', '/'),
            // Cases when we want to flush all urls.
            array('shopUrlPath', '.*', 'shopUrlPath.*'),
            array('shopUrlPath/', '.*', 'shopUrlPath.*'),
            array('', '.*', '.*'),
            array('/', '.*', '.*'),
        );
}

    /**
     * @param string $shopUrlPath
     * @param string $urlQuery
     * @param string $result
     *
     * @dataProvider providerPrependsPathToUrlQuery
     */
    public function testPrependsPathToUrlQuery($shopUrlPath, $urlQuery, $result)
    {
        $urlGenerator = oxNew('oxReverseProxyUrlGenerator');

        $this->assertSame($result, $urlGenerator->prependPathToUrlQuery($shopUrlPath, $urlQuery));
    }
}
