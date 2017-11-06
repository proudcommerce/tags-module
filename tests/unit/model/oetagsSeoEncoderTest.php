<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version       OXID eShop CE
 */

class modSeoEncoder extends oxSeoEncoder
{

    public function setProhibitedID($aProhibitedID)
    {
        $this->_aProhibitedID = $aProhibitedID;
    }

    public function getSeparator()
    {
        return oxSeoEncoder::$_sSeparator;
    }

    public function getSafePrefix()
    {
        return $this->_getSafePrefix();
    }

    public function setAltPrefix($soxID)
    {
        $this->_sAltPrefix = $soxID;
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
class oetagsSeoEncoderTest extends oeTagsTestCase
{

    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');
        oxDb::getDb()->execute('delete from oxobject2seodata');
        oxDb::getDb()->execute('delete from oxseohistory');

        parent::setUp();

        oxRegistry::get("oxSeoEncoder")->setPrefix('oxid');
        oxRegistry::get("oxSeoEncoder")->setSeparator();
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
        //echo $this->getName()."\n";
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        // deleting seo entries
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');
        oxDb::getDb()->execute('delete from oxobject2seodata');
        oxDb::getDb()->execute('delete from oxseohistory');

        $this->cleanUpTable('oxcategories');
        $this->cleanUpTable('oxarticles');
        $this->cleanUpTable('oxarticles');

        // cleanup
        if ($this->getName() == 'testIfArticleMetaDataStoredInSeoTableIsKeptAfterArticleTitleWasChanged') {
            $oArticle = oxNew('oxArticle');
            $oArticle->delete('_testArticle');
        }

        $oConfig = $this->getConfig();

        // restore..
        $cl = oxTestModules::addFunction('oxSeoEncoder', 'clean_aReservedEntryKeys', '{oxSeoEncoder::$_aReservedEntryKeys = null;}');
        $oEncoder = new $cl();
        $oEncoder->setSeparator($oConfig->getConfigParam('sSEOSeparator'));
        $oEncoder->setPrefix($oConfig->getConfigParam('sSEOuprefix'));
        $oEncoder->setReservedWords($oConfig->getConfigParam('aSEOReservedWords'));
        $oEncoder->clean_aReservedEntryKeys();

        parent::tearDown();
    }

    public function __SaveToDbCreatesGoodMd5Callback($sSQL)
    {
        $this->aSQL[] = $sSQL;
        if ($this->aRET && isset($this->aRET[count($this->aSQL) - 1])) {
            return $this->aRET[count($this->aSQL) - 1];
        }
        return array();
    }

    public function testProcessSeoUrl()
    {
        $sSeoUrl = "seourl/";
        $oEncoder = oxNew('oxSeoEncoder');
        $this->assertEquals($sSeoUrl, $oEncoder->UNITprocessSeoUrl($sSeoUrl, null, 1, true));
        $this->assertEquals("en/" . $sSeoUrl, $oEncoder->UNITprocessSeoUrl($sSeoUrl, null, 1, false));
    }

    public function testLanguagePrefixForSeoUrlForDe()
    {
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        $oConfig = $this->getConfig();

        // inserting price category for test
        $oPriceCategory = oxNew('oxCategory');
        $oPriceCategory->setId("_testPriceCategoryId");
        $oPriceCategory->oxcategories__oxparentid = new oxField("oxrootid");
        $oPriceCategory->oxcategories__oxrootid = $oPriceCategory->getId();
        $oPriceCategory->oxcategories__oxactive = new oxField(1);
        $oPriceCategory->oxcategories__oxshopid = new oxField($oConfig->getBaseShopId());
        $oPriceCategory->oxcategories__oxtitle = new oxField("Test Price Category DE");
        $oPriceCategory->oxcategories__oxpricefrom = new oxField(0);
        $oPriceCategory->oxcategories__oxpriceto = new oxField(999);
        $oPriceCategory->save();

        $sShopUrl = $oConfig->getShopUrl(0);

        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $sArticleId = "1849";
            $sArticleSeoUrl = $sShopUrl . "Party/Bar-Equipment/Bar-Butler-6-BOTTLES.html";
            $sArticleVendorSeoUrl = $sShopUrl . "Nach-Lieferant/Hersteller-1/Bar-Butler-6-BOTTLES.html";
            $sArticleManufacturerSeoUrl = $sShopUrl . "Nach-Hersteller/Hersteller-1/Bar-Butler-6-BOTTLES.html";
            $sArticleTagSeoUrl = $sShopUrl . "oetagstagcontroller/cmmaterial/Bar-Butler-6-BOTTLES.html";
            $sCategoryId = "30e44ab82c03c3848.49471214";
            $sCategorySeoUrl = $sShopUrl . "Fuer-Sie/";
            $sManufacturerId = "88a996f859f94176da943f38ee067984";
            $sManufacturerSeoUrl = $sShopUrl . "Nach-Hersteller/Hersteller-1/";
            $sVendorId = "d2e44d9b31fcce448.08890330";
            $sVendorSeoUrl = $sShopUrl . "Nach-Lieferant/Hersteller-1/";
        } else {
            $sArticleId = "1964";
            $sArticleSeoUrl = $sShopUrl . "Geschenke/Original-BUSH-Beach-Radio.html";
            $sArticleVendorSeoUrl = $sShopUrl . "Nach-Lieferant/Bush/Original-BUSH-Beach-Radio.html";
            $sArticleManufacturerSeoUrl = $sShopUrl . "Nach-Hersteller/Bush/Original-BUSH-Beach-Radio.html";
            $sArticleTagSeoUrl = $sShopUrl . "oetagstagcontroller/seiner/Original-BUSH-Beach-Radio.html";
            $sCategoryId = "8a142c3e4143562a5.46426637";
            $sCategorySeoUrl = $sShopUrl . "Geschenke/";
            $sManufacturerId = "fe07958b49de225bd1dbc7594fb9a6b0";
            $sManufacturerSeoUrl = $sShopUrl . "Nach-Hersteller/Haller-Stahlwaren/";
            $sVendorId = "68342e2955d7401e6.18967838";
            $sVendorSeoUrl = $sShopUrl . "Nach-Lieferant/Haller-Stahlwaren/";
        }

        $sContentId = "f41427a099a603773.44301043";
        $sContentSeoUrl = $sShopUrl . "Datenschutz/";

        $oCategory = oxNew('oxCategory');
        $oCategory->load($sCategoryId);

        $oView = $this->getMock("details", array("getTag", "getActiveCategory"));
        $tag = $this->getTestConfig()->getShopEdition() == 'EE' ? "cmmaterial" : "seiner";
        $oView->expects($this->once())->method('getTag')->will($this->returnValue($tag));
        $oView->expects($this->at(0))->method('getActiveCategory')->will($this->returnValue($oCategory));
        $oView->expects($this->at(1))->method('getActiveCategory')->will($this->returnValue($oPriceCategory));

        $oConfig->dropLastActiveView();
        $oConfig->setActiveView($oView);

        $oArticle = oxNew('oxArticle');
        $oArticle->load($sArticleId);
        $oArticle->setLinkType(OXARTICLE_LINKTYPE_CATEGORY);
        $this->assertEquals($sArticleSeoUrl, $oArticle->getLink(0));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_VENDOR);
        $this->assertEquals($sArticleVendorSeoUrl, $oArticle->getLink(0));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_MANUFACTURER);
        $this->assertEquals($sArticleManufacturerSeoUrl, $oArticle->getLink(0));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_TAG);
        $this->assertEquals($sArticleTagSeoUrl, $oArticle->getLink(0));

        $oCategory = oxNew('oxCategory');
        $oCategory->load($sCategoryId);
        $this->assertEquals($sCategorySeoUrl, $oCategory->getLink(0));

        $oContent = oxNew('oxContent');
        $oContent->load($sContentId);
        $this->assertEquals($sContentSeoUrl, $oContent->getLink(0));

        $oManufacturer = oxNew('oxManufacturer');
        $oManufacturer->load($sManufacturerId);
        $this->assertEquals($sManufacturerSeoUrl, $oManufacturer->getLink(0));

        $oTagEncoder = oxNew('oetagsSeoEncoderTag');
        $sTag = $this->getTestConfig()->getShopEdition() == 'EE' ? "messerblock" : "flaschen";
        $sTagUrl = $this->getTestConfig()->getShopEdition() == 'EE' ? "oetagstagcontroller/messerblock/" : "oetagstagcontroller/flaschen/";

        $this->assertEquals($sShopUrl . "oetagstagcontroller/bar-equipment/", $oTagEncoder->getTagUrl("bar equipment", 0));
        $this->assertEquals($sShopUrl . $sTagUrl, $oTagEncoder->getTagUrl($sTag, 0));

        $oVendor = oxNew('oxVendor');
        $oVendor->load($sVendorId);
        $this->assertEquals($sVendorSeoUrl, $oVendor->getLink(0));
        // missing static urls..
    }

    public function testLanguagePrefixForSeoUrlForEn()
    {
        oxTestModules::addFunction("oxutilsserver", "getServerVar", "{ \$aArgs = func_get_args(); if ( \$aArgs[0] === 'HTTP_HOST' ) { return '" . $this->getConfig()->getShopUrl() . "'; } elseif ( \$aArgs[0] === 'SCRIPT_NAME' ) { return ''; } else { return \$_SERVER[\$aArgs[0]]; } }");
        $oConfig = $this->getConfig();

        // inserting price category for test
        $oPriceCategory = oxNew('oxCategory');
        $oPriceCategory->setId("_testPriceCategoryId");
        $oPriceCategory->oxcategories__oxparentid = new oxField("oxrootid");
        $oPriceCategory->oxcategories__oxrootid = $oPriceCategory->getId();
        $oPriceCategory->oxcategories__oxactive = new oxField(1);
        $oPriceCategory->oxcategories__oxshopid = new oxField($oConfig->getBaseShopId());
        $oPriceCategory->oxcategories__oxtitle = new oxField("Test Price Category DE");
        $oPriceCategory->oxcategories__oxpricefrom = new oxField(0);
        $oPriceCategory->oxcategories__oxpriceto = new oxField(999);
        $oPriceCategory->save();
        $oPriceCategory->setLanguage(1);
        $oPriceCategory->save();

        $sShopUrl = $oConfig->getShopUrl(0);
        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $sArticleId = "6b63f459c781fa42edeb889242304014";
            $sArticleSeoUrl = $sShopUrl . "en/Eco-Fashion/Woman/Shirts/Stewart-Brown-Organic-Pima-Edged-Lengthen.html";
            $sArticleVendorSeoUrl = $sShopUrl . "en/By-distributor/true-fashion-com/Stewart-Brown-Organic-Pima-Edged-Lengthen.html";
            $sArticleManufacturerSeoUrl = $sShopUrl . "en/By-Manufacturer/Stewart-Brown/Stewart-Brown-Organic-Pima-Edged-Lengthen.html";
            $sArticlePriceCatSeoUrl = $sShopUrl . "en/Test-Price-Category-DE/Stewart-Brown-Organic-Pima-Edged-Lengthen.html";
            $sArticleTagSeoUrl = $sShopUrl . "en/oetagstagcontroller/shirt/Stewart-Brown-Organic-Pima-Edged-Lengthen.html";
            $sTag = "shirt";
            $sCategoryId = "30e44ab82c03c3848.49471214";
            $sCategorySeoUrl = $sShopUrl . "en/For-Her/";
            $sManufacturerId = "88a996f859f94176da943f38ee067984";
            $sManufacturerSeoUrl = $sShopUrl . "en/By-Manufacturer/Manufacturer-1/";
            $sVendorId = "d2e44d9b31fcce448.08890330";
            $sVendorSeoUrl = $sShopUrl . "en/By-distributor/Manufacturer-1/";
        } else {
            $sArticleId = "1964";
            $sArticleSeoUrl = $sShopUrl . "en/Gifts/Original-BUSH-Beach-Radio.html";
            $sArticleVendorSeoUrl = $sShopUrl . "en/By-distributor/Bush/Original-BUSH-Beach-Radio.html";
            $sArticleManufacturerSeoUrl = $sShopUrl . "en/By-Manufacturer/Bush/Original-BUSH-Beach-Radio.html";
            $sArticlePriceCatSeoUrl = $sShopUrl . "en/Test-Price-Category-DE/Original-BUSH-Beach-Radio.html";
            $sArticleTagSeoUrl = $sShopUrl . "en/oetagstagcontroller/original/Original-BUSH-Beach-Radio.html";
            $sTag = "original";
            $sCategoryId = "8a142c3e4143562a5.46426637";
            $sCategorySeoUrl = $sShopUrl . "en/Gifts/";
            $sManufacturerId = "fe07958b49de225bd1dbc7594fb9a6b0";
            $sManufacturerSeoUrl = $sShopUrl . "en/By-Manufacturer/Haller-Stahlwaren/";
            $sVendorId = "68342e2955d7401e6.18967838";
            $sVendorSeoUrl = $sShopUrl . "en/By-distributor/Haller-Stahlwaren/";
        }

        $sContentId = "f41427a099a603773.44301043";
        $sContentSeoUrl = $sShopUrl . "en/Privacy-Policy/";

        $oCategory = oxNew('oxCategory');
        $oCategory->load($sCategoryId);

        $oView = $this->getMock("details", array("getTag", "getActiveCategory"));
        $oView->expects($this->once())->method('getTag')->will($this->returnValue($sTag));
        $oView->expects($this->at(0))->method('getActiveCategory')->will($this->returnValue($oCategory));
        $oView->expects($this->at(1))->method('getActiveCategory')->will($this->returnValue($oPriceCategory));

        $oConfig->dropLastActiveView();
        $oConfig->setActiveView($oView);

        $oArticle = oxNew('oxArticle');
        $oArticle->setLinkType(OXARTICLE_LINKTYPE_CATEGORY);
        $oArticle->load($sArticleId);
        $this->assertEquals($sArticleSeoUrl, $oArticle->getLink(1));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_VENDOR);
        $this->assertEquals($sArticleVendorSeoUrl, $oArticle->getLink(1));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_MANUFACTURER);
        $this->assertEquals($sArticleManufacturerSeoUrl, $oArticle->getLink(1));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_PRICECATEGORY);
        $this->assertEquals($sArticlePriceCatSeoUrl, $oArticle->getLink(1));

        $oArticle->setLinkType(OXARTICLE_LINKTYPE_TAG);
        $this->assertEquals($sArticleTagSeoUrl, $oArticle->getLink(1));

        $oCategory = oxNew('oxCategory');
        $oCategory->load($sCategoryId);
        $this->assertEquals($sCategorySeoUrl, $oCategory->getLink(1));

        $oContent = oxNew('oxContent');
        $oContent->load($sContentId);
        $this->assertEquals($sContentSeoUrl, $oContent->getLink(1));

        $oManufacturer = oxNew('oxManufacturer');
        $oManufacturer->load($sManufacturerId);
        $this->assertEquals($sManufacturerSeoUrl, $oManufacturer->getLink(1));

        $sTag = "kuyichi";
        $sTagSeoUrl = $sShopUrl . "en/oetagstagcontroller/kuyichi/";

        $oTagEncoder = oxNew('oetagsSeoEncoderTag');
        $this->assertEquals($sTagSeoUrl, $oTagEncoder->getTagUrl($sTag, 1));

        $oVendor = oxNew('oxVendor');
        $oVendor->load($sVendorId);
        $this->assertEquals($sVendorSeoUrl, $oVendor->getLink(1));
        // missing static urls..
    }

    public function testGetFullUrl()
    {
        oxTestModules::addFunction('oxUtilsUrl', 'processSeoUrl($url)', '{return "PROC".$url."CORP";}');

        $oConfig = $this->getMock('oxconfig', array('getShopUrl'));
        $oConfig->expects($this->once())->method('getShopUrl')->with($this->equalTo(1))->will($this->returnValue('url/'));

        $oEncoder = $this->getMock('oxseoencoder', array('getConfig'), array(), '', false);
        $oEncoder->expects($this->once())->method('getConfig')->will($this->returnValue($oConfig));

        $this->assertEquals('PROCurl/seouri/CORP', $oEncoder->UNITgetFullUrl('seouri/', 1));
    }

    public function testSettingEmptyMetaDataWhileUpdatingObjectSeoInfo()
    {
        $iShopId = $this->getConfig()->getBaseShopId();
        $oDb = oxDb::getDb(oxDB::FETCH_MODE_ASSOC);

        $oEncoder = oxNew('oxSeoEncoder');
        $oEncoder->addSeoEntry('testid', $iShopId, 0, 'index.php?cl=std', 'seo/url/', 'oxcategory', 0, 'oxkeywords', 'oxdescription', '');

        $sQ = "select oxkeywords from oxobject2seodata where oxobjectid = 'testid' and oxshopid = '{$iShopId}' and oxlang = 0 ";
        $this->assertEquals('oxkeywords', $oDb->getOne($sQ));

        $sQ = "select oxdescription from oxobject2seodata where oxobjectid = 'testid' and oxshopid = '{$iShopId}' and oxlang = 0 ";
        $this->assertEquals('oxdescription', $oDb->getOne($sQ));

        $oEncoder = oxNew('oxSeoEncoder');
        $oEncoder->addSeoEntry('testid', $iShopId, 0, 'index.php?cl=std', 'seo/url/', 'oxcategory', 0, '', '', '');

        $sQ = "select oxkeywords from oxobject2seodata where oxobjectid = 'testid' and oxshopid = '{$iShopId}' and oxlang = 0 ";
        $this->assertEquals('', $oDb->getOne($sQ));

        $sQ = "select oxdescription from oxobject2seodata where oxobjectid = 'testid' and oxshopid = '{$iShopId}' and oxlang = 0 ";
        $this->assertEquals('', $oDb->getOne($sQ));
    }


    public function testIfArticleMetaDataStoredInSeoTableIsKeptAfterArticleTitleWasChanged()
    {

        // creating some article
        $oArticle = $this->getMock('oxArticle', array('isAdmin', 'canDo', 'getRights'));
        $oArticle->expects($this->any())->method('isAdmin')->will($this->returnValue(true));
        $oArticle->expects($this->any())->method('canDo')->will($this->returnValue(true));
        $oArticle->expects($this->any())->method('getRights')->will($this->returnValue(false));
        $oArticle->setId('_testArticle');
        $oArticle->oxarticles__oxtitle = new oxField('testarticletitle');
        $oArticle->save();

        // saving its meta data
        $oEncoder = oxRegistry::get("oxSeoEncoder");
        $oEncoder->addSeoEntry(
            $oArticle->getId(), $oArticle->getShopId(), $oArticle->getLanguage(), 'http://stdlink',
            $oArticle->getLink(), 'oxarticle', 0, 'oxseo oxkeywords', 'oxseo oxdescription', ''
        );

        // now testing if meta data was stored..
        $this->assertEquals('oxseo oxdescription', $oEncoder->getMetaData($oArticle->getId(), 'oxdescription'));
        $this->assertEquals('oxseo oxkeywords', $oEncoder->getMetaData($oArticle->getId(), 'oxkeywords'));

        // setting new title for product
        $oArticle->oxarticles__oxtitle = new oxField('new testarticletitle');
        $oArticle->save();

        $oArticle = oxNew('oxArticle');
        $oArticle->load('_testArticle');
        $oArticle->getLink();

        // testing if meta data was kept the same..
        $this->assertEquals('oxseo oxdescription', $oEncoder->getMetaData($oArticle->getId(), 'oxdescription'));
        $this->assertEquals('oxseo oxkeywords', $oEncoder->getMetaData($oArticle->getId(), 'oxkeywords'));

        // resetting seo
        $oEncoder->markAsExpired(null, $oArticle->getShopId());

        $oArticle = oxNew('oxArticle');
        $oArticle->load('_testArticle');
        $oArticle->getLink();

        // testing if meta data was kept the same..
        $this->assertEquals('oxseo oxdescription', $oEncoder->getMetaData($oArticle->getId(), 'oxdescription'));
        $this->assertEquals('oxseo oxkeywords', $oEncoder->getMetaData($oArticle->getId(), 'oxkeywords'));
    }

    public function testFetchSeoUrl()
    {
        oxTestModules::addFunction("oxUtils", "seoIsActive", "{ return true;}");
        oxTestModules::addFunction("oxUtils", "isSearchEngine", "{return false;}");

        $oArticle = oxNew('oxArticle');
        $oArticle->load('1126');
        $oArticle->getLink();

        $oCat = $oArticle->getCategory();

        $sStdUrl = 'index.php?cl=details&amp;anid=1126&amp;cnid=' . ($oCat ? $oCat->getId() : '');

        $oEncoder = oxNew('oxSeoEncoder');

        $categoryUrl = $this->getTestConfig()->getShopEdition() == 'EE' ? "Party/Bar-Equipment" : "Geschenke/Bar-Equipment";
        $this->assertEquals("$categoryUrl/Bar-Set-ABSINTH.html", $oEncoder->fetchSeoUrl($sStdUrl));
    }

    public function testFetchSeoUrlNoAvailable()
    {
        $sStdUrl = 'index.php?cl=details&amp;anid=1126';
        $oEncoder = oxNew('oxseoencoder');
        $this->assertFalse($oEncoder->fetchSeoUrl($sStdUrl));
    }

    public function testPrepareSpecificTitle()
    {
        $sTitle = "Wie/bestellen?/" . str_repeat('a', 200) . ' ' . str_repeat('a', 200) . ' ' . str_repeat('a', 200);
        $sResult = "Wie-bestellen-" . str_repeat('a', 200) . '-' . str_repeat('a', 200);

        $oEncoder = oxNew('oxSeoEncoder');
        $this->assertEquals($sResult, $oEncoder->UNITprepareTitle($sTitle));
        $this->assertEquals($sResult, $oEncoder->UNITprepareTitle($sTitle, false));
        $this->assertEquals($sResult . '-' . str_repeat('a', 200), $oEncoder->UNITprepareTitle($sTitle, true));
    }

    /**
     * Test case:
     * cookies are cleaned up, object seo urls are not written
     */
    public function testIfSeoUrlsAreFine()
    {
        // preparing environment
        oxTestModules::addFunction("oxutils", "seoIsActive", "{return true;}");
        oxTestModules::addFunction("oxutils", "isSearchEngine", "{return false;}");
        $this->getConfig()->setConfigParam('blSessionUseCookies', false);

        // cleaning up
        oxDb::getDb()->execute('delete from oxseo where oxtype != "static"');

        $oArticle = oxNew('oxArticle');
        $articleId = $this->getTestConfig()->getShopEdition() == 'EE' ? '1889' : '1126';
        $oArticle->load($articleId);
        $oArticle->getLink();

        $oCat = $oArticle->getCategory();

        $this->assertEquals('index.php?cl=details&amp;anid=' . $oArticle->getId() . '&amp;cnid=' . ($oCat ? $oCat->getId() : ''), oxDb::getDb()->getOne('select oxstdurl from oxseo where oxobjectid = "' . $oArticle->getId() . '"'));
    }

}
