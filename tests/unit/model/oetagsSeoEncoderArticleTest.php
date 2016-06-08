<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';

use \modDB;
use \oxSeoEncoderArticle;
use \oxField;
use \oxDb;
use \oxRegistry;
use \oxTestModules;

/**
 *
 */
class modSeoEncoderArticle extends oxSeoEncoderArticle
{

    public function setProhibitedID($aProhibitedID)
    {
        $this->_aProhibitedID = $aProhibitedID;
    }

    public function getSeparator()
    {
        return $this->_sSeparator;
    }

    public function getSafePrefix()
    {
        return $this->_getSafePrefix();
    }

    public function setAltPrefix($sOXID)
    {
        $this->_sAltPrefix = $sOXID;
    }

    public function p_prepareTitle($a, $b = false)
    {
        return $this->_prepareTitle($a, $b);
    }

    /**
     * Only used for convenience in UNIT tests by doing so we avoid
     * writing extended classes for testing protected or private methods
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (defined('OXID_PHP_UNIT')) {
            if (substr($method, 0, 4) == "UNIT") {
                $method = str_replace("UNIT", "_", $method);
            }
            if (method_exists($this, $method)) {
                return call_user_func_array(array(& $this, $method), $args);
            }
        }

        throw new Exception("Function '$method' does not exist or is not accessable!" . PHP_EOL);
    }
}

/**
 * Testing oxseoencoder class
 */
class Unit_Model_oetagsSeoEncoderArticleTest extends \oeTagsTestCase
{

    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
        oxRegistry::get("oxSeoEncoder")->setPrefix('oxid');
        oxRegistry::get("oxSeoEncoder")->setSeparator();
        oxTestModules::cleanUp();

        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        modDB::getInstance()->cleanup();
        // deleting seo entries
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');
        oxDb::getDb()->execute('delete from oxobject2seodata');
        oxDb::getDb()->execute('delete from oxseohistory');

        $this->cleanUpTable('oxcategories');
        $this->cleanUpTable('oxarticles');
        $this->cleanUpTable('oxrecommlists');

        oxDb::getDb()->execute('delete from oxarticles where oxid = "testart"');
        oxDb::getDb()->execute('delete from oxobject2category where oxobjectid = "testart"');

        //$this->getConfig()->setActiveView( null );

        parent::tearDown();
    }

    public function __SaveToDbCreatesGoodMd5Callback($sSQL)
    {
        $this->aSQL[] = $sSQL;
        if ($this->aRET && isset($this->aRET[count($this->aSQL) - 1])) {
            return $this->aRET[count($this->aSQL) - 1];
        }
        return null;
    }

    /**
     * oxSeoEncoderArticle::_getAltUri() test case
     *
     * @return null
     */
    public function testGetAltUriVendor()
    {
        $oEncoder = $this->getMock("oxSeoEncoderArticle", array("_getListType", "getArticleVendorUri", "getArticleManufacturerUri", "getArticleTagUri", "getArticleUri"));
        $oEncoder->expects($this->any())->method('_getListType')->will($this->returnValue("vendor"));
        $oEncoder->expects($this->any())->method('getArticleVendorUri')->will($this->returnValue("vendorUri"));
        $oEncoder->expects($this->never())->method('getArticleManufacturerUri');
        $oEncoder->expects($this->never())->method('getArticleTagUri');
        $oEncoder->expects($this->never())->method('getArticleUri');

        $this->assertEquals("vendorUri", $oEncoder->UNITgetAltUri('1126', 0));
    }

    /**
     * oxSeoEncoderArticle::_getAltUri() test case
     *
     * @return null
     */
    public function testGetAltUriManufacturer()
    {
        $oEncoder = $this->getMock("oxSeoEncoderArticle", array("_getListType", "getArticleVendorUri", "getArticleManufacturerUri", "getArticleTagUri", "getArticleUri"));
        $oEncoder->expects($this->any())->method('_getListType')->will($this->returnValue("manufacturer"));
        $oEncoder->expects($this->never())->method('getArticleVendorUri');
        $oEncoder->expects($this->any())->method('getArticleManufacturerUri')->will($this->returnValue("manufacturerUri"));
        $oEncoder->expects($this->never())->method('getArticleTagUri');
        $oEncoder->expects($this->never())->method('getArticleUri');

        $this->assertEquals("manufacturerUri", $oEncoder->UNITgetAltUri('1126', 0));
    }

    /**
     * oxSeoEncoderArticle::_getAltUri() test case
     *
     * @return null
     */
    public function testGetAltUriTag()
    {
        $oEncoder = $this->getMock("oxSeoEncoderArticle", array("_getListType", "getArticleVendorUri", "getArticleManufacturerUri", "getArticleTagUri", "getArticleUri"));
        $oEncoder->expects($this->any())->method('_getListType')->will($this->returnValue("tag"));
        $oEncoder->expects($this->never())->method('getArticleVendorUri');
        $oEncoder->expects($this->never())->method('getArticleManufacturerUri');
        $oEncoder->expects($this->any())->method('getArticleTagUri')->will($this->returnValue("tagUri"));
        $oEncoder->expects($this->never())->method('getArticleUri');

        $this->assertEquals("tagUri", $oEncoder->UNITgetAltUri('1126', 0));
    }

    /**
     * oxSeoEncoderArticle::_getAltUri() test case
     *
     * @return null
     */
    public function testGetAltUriDefault()
    {
        $oEncoder = $this->getMock("oxSeoEncoderArticle", array("_getListType", "getArticleVendorUri", "getArticleManufacturerUri", "getArticleTagUri", "getArticleUri"));
        $oEncoder->expects($this->any())->method('_getListType');
        $oEncoder->expects($this->never())->method('getArticleVendorUri');
        $oEncoder->expects($this->never())->method('getArticleManufacturerUri');
        $oEncoder->expects($this->never())->method('getArticleTagUri');
        $oEncoder->expects($this->any())->method('getArticleUri')->will($this->returnValue("defaultUri"));


        $this->assertEquals("defaultUri", $oEncoder->UNITgetAltUri('1126', 0));
    }


    public function testGetArticleUrlRecommType()
    {
        $oEncoder = $this->getMock(
            "oxSeoEncoderArticle", array("getArticleVendorUri", "getArticleManufacturerUri",
                                         "getArticleTagUri", "getArticleRecommUri",
                                         "getArticleUri", "getArticleMainUri", "_getFullUrl")
        );

        $oEncoder->expects($this->never())->method('getArticleVendorUri');
        $oEncoder->expects($this->never())->method('getArticleManufacturerUri');
        $oEncoder->expects($this->never())->method('getArticleTagUri');
        $oEncoder->expects($this->never())->method('getArticleUri');
        $oEncoder->expects($this->never())->method('getArticleMainUri');

        $oEncoder->expects($this->once())->method('getArticleRecommUri')->will($this->returnValue("testRecommUrl"));
        $oEncoder->expects($this->once())->method('_getFullUrl')->will($this->returnValue("testFullRecommUrl"));

        $this->assertEquals("testFullRecommUrl", $oEncoder->getArticleUrl(oxNew('oxArticle'), 0, OXARTICLE_LINKTYPE_RECOMM));
    }

    /**
     * Testing if tag is taken from view
     */
    public function testGetTag()
    {
        $oView = $this->getMock("oxUBase", array("getTag"));
        $oView->expects($this->once())->method('getTag')->will($this->returnValue("testTag"));

        $this->getConfig()->setActiveView($oView);

        $oEncoder = oxNew('oxSeoEncoderArticle');
        $this->assertEquals("testTag", $oEncoder->UNITgetTag(oxNew('oxarticle'), 0));
    }

    /**
     * article has no vendor defined
     *
     * @return null
     */
    public function testGetVendorArticleHasNoManufacturerDefined()
    {
        $oEncoder = oxNew('oxSeoEncoderArticle');
        $this->assertNull($oEncoder->UNITgetVendor(oxNew('oxArticle'), 0));
    }

    public function testGetArticleTagUri()
    {
        $oArticle = oxNew('oxArticle');
        $oArticle->load('1126');

        $oSeoEncoderArticle = $this->getMock("oxSeoEncoderArticle", array("_getTag"));

        if ($this->getConfig()->getEdition() === 'EE') {
            $sTag = 'intellektuelle';
            $sTagUrl = "oetagstagcontroller/{$sTag}/Bar-Set-ABSINTH.html";
        } else {
            $sTag = 'absinth';
            $sTagUrl = "oetagstagcontroller/{$sTag}/Bar-Set-ABSINTH.html";
        }

        $oSeoEncoderArticle->expects($this->any())->method('_getTag')->will($this->returnValue($sTag));

        $this->assertEquals($sTagUrl, $oSeoEncoderArticle->getArticleTagUri($oArticle, 0));

        // chache works ?
        $this->assertEquals($sTagUrl, $oSeoEncoderArticle->getArticleTagUri($oArticle, 0));
    }

    public function testGetArticleUrlForTag()
    {
        $oArticle = $this->getMock('oxarticle', array('getLanguage'));
        $oArticle->expects($this->once())->method('getLanguage')->will($this->returnValue(0));

        $oEncoder = $this->getMock('oxSeoEncoderArticle', array('_getFullUrl', 'getArticleTagUri', '_getArticlePriceCategoryUri', 'getArticleUri', 'getArticleVendorUri', 'getArticleManufacturerUri', 'getArticleMainUri'));
        $oEncoder->expects($this->once())->method('_getFullUrl')->will($this->returnValue('seoarturl'));
        $oEncoder->expects($this->once())->method('getArticleTagUri')->will($this->returnValue('seoarturl'));
        $oEncoder->expects($this->never())->method('_getArticlePriceCategoryUri');
        $oEncoder->expects($this->never())->method('getArticleUri');
        $oEncoder->expects($this->never())->method('getArticleVendorUri');
        $oEncoder->expects($this->never())->method('getArticleManufacturerUri');
        $oEncoder->expects($this->never())->method('getArticleMainUri');

        $this->assertEquals('seoarturl', $oEncoder->getArticleUrl($oArticle, null, 4));
    }

    /**
     * Test case:
     * wrong article seo url preparation, must be
     *  Bierspiel-OANS-ZWOA-GSUFFA
     * but returns
     *  de/Spiele/Brettspiele/Bierspiel-OANS-ZWOA-GSUFFA-...
     */
    public function testGetArticleSeoLinkDe()
    {
        $oArticle = oxNew('oxArticle');

        if ($this->getConfig()->getEdition() === 'EE') {
            $oArticle->loadInLang(0, '1889');
            $sExp = "Bierspiel-OANS-ZWOA-GSUFFA";
        } else {
            $oArticle->loadInLang(0, '1127');
            $sExp = "Blinkende-Eiswuerfel-FLASH";
        }

        $oEncoder = oxRegistry::get("oxSeoEncoderArticle");
        $oEncoder->setSeparator();
        $this->assertEquals($sExp, $oEncoder->UNITprepareTitle($oArticle->oxarticles__oxtitle->value));
    }

    public function testCreateArticleCategoryUri()
    {
        oxTestModules::addFunction('oxSeoEncoderCategory', 'getCategoryUri($c, $l = NULL, $blRegenerate = false)', '{return "caturl".$c->getId().$l;}');
        $oA = $this->getMock('oxarticle', array('getLanguage', 'getId', 'getBaseStdLink'));
        $oA->expects($this->never())->method('getLanguage');
        $oA->expects($this->any())->method('getId')->will($this->returnValue('articleId'));
        $oA->expects($this->any())->method('getBaseStdLink')->with(
            $this->equalTo(1)
        )->will($this->returnValue('articleBaseStdLink'));

        $oUtilsUrl = $this->getMock('oxutilsurl', array('appendUrl'));
        $oUtilsUrl->expects($this->any())->method('appendUrl')->with(
            $this->equalTo('articleBaseStdLink'),
            $this->equalTo(array('cnid' => 'catId'))
        )->will($this->returnValue('articleStdLink'));
        oxTestModules::addModuleObject('oxUtilsUrl', $oUtilsUrl);

        $oC = $this->getMock('oxcategory', array('getId'));
        $oC->expects($this->any())->method('getId')->will($this->returnValue('catId'));

        $oSEA = $this->getMock('oxSeoEncoderArticle', array('_getProductForLang', '_prepareArticleTitle', '_processSeoUrl', '_saveToDb'));
        $oSEA->expects($this->once())->method('_getProductForLang')->with($this->equalTo($oA), $this->equalTo(1))->will($this->returnValue($oA));
        $oSEA->expects($this->once())->method('_prepareArticleTitle')->with($this->equalTo($oA))->will($this->returnValue('articleTitle'));
        $oSEA->expects($this->once())->method('_processSeoUrl')->with(
            $this->equalTo("caturlcatId1articleTitle"),
            $this->equalTo('articleId'),
            $this->equalTo(1)
        )->will($this->returnValue('articleUrlReturned'));
        $oSEA->expects($this->once())->method('_saveToDb')->with(
            $this->equalTo("oxarticle"),
            $this->equalTo('articleId'),
            $this->equalTo("articleStdLink"),
            $this->equalTo('articleUrlReturned'),
            $this->equalTo(1),
            $this->equalTo(null),
            $this->equalTo(0),
            $this->equalTo('catId')
        )->will($this->returnValue(null));

        $this->assertEquals('articleUrlReturned', $oSEA->UNITcreateArticleCategoryUri($oA, $oC, 1));
    }

    public function testGetArticleMainUrl()
    {
        $oA = $this->getMock('oxarticle', array('getLanguage'));
        $oA->expects($this->any())->method('getLanguage')->will($this->returnValue(1));

        $oSEA = $this->getMock('oxSeoEncoderArticle', array('_getFullUrl', 'getArticleMainUri'));
        $oSEA->expects($this->once())->method('getArticleMainUri')
            ->with(
                $this->equalTo($oA),
                $this->equalTo(1)
            )->will($this->returnValue('articleUri'));
        $oSEA->expects($this->once())->method('_getFullUrl')
            ->with(
                $this->equalTo('articleUri'),
                $this->equalTo(1)
            )->will($this->returnValue('articleUrlReturned'));

        $this->assertEquals('articleUrlReturned', $oSEA->getArticleMainUrl($oA));
    }

}
