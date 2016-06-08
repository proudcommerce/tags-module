<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../oeTagsTestCase.php';

use \modDB;
use \oxTestModules;

/**
 * Testing oxArticleList class
 */
class oetagsArticlelistTest extends \oeTagsTestCase
{

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $myDB = $this->getDb();
        $myDB->execute('update oxactions set oxactive="1"');
        $myDB->execute('delete from oxaccessoire2article where oxarticlenid="_testArt" ');
        $myDB->execute('delete from oxorderarticles where oxid="_testId" or oxid="_testId2"');
        $myDB->execute('delete from oxrecommlists where oxid like "testlist%" ');
        $myDB->execute('delete from oxobject2list where oxlistid like "testlist%" ');

        $myDB->execute('delete from oxconfig where oxvarname="iTimeToUpdatePrices"');
        $myDB->execute('update oxarticles set oxupdatepricetime="0000-00-00 00:00:00"');

        $this->cleanUpTable('oxorder');
        $this->cleanUpTable('oxarticles');

        parent::tearDown();
    }

    /**
     * Get article table.
     *
     * @return string
     */
    protected function _getArticleTable()
    {
        return getViewName("oxarticles");
    }

    /**
     * Get object to category table.
     *
     * @return string
     */
    protected function _getO2CTable()
    {
        $sO2CTable = $this->getTestConfig()->getShopEdition() == 'EE' ? 'oxv_oxobject2category_1' : 'oxobject2category';

        return $sO2CTable;
    }

    /**
     * Test get search select use or.
     *
     * @return null
     */
    public function testGetSearchSelectUseOr()
    {
        $oTest = $this->getProxyClass('oxArticleList');

        $sArticleTable = $this->_getArticleTable();
        $sAEV = getViewName('oxartextends');

        $sExpt = "and ( ( $sArticleTable.oxtitle like '%test%' or $sArticleTable.oxshortdesc like '%test%' ";
        $sExpt .= "or $sArticleTable.oxsearchkeys like '%test%' or $sArticleTable.oxartnum like '%test%' ";
        $sExpt .= "or $sAEV.oetags like '%test%' ) or ";
        $sExpt .= " ( $sArticleTable.oxtitle like '%Search%' or $sArticleTable.oxshortdesc like '%Search%' ";
        $sExpt .= "or $sArticleTable.oxsearchkeys like '%Search%' or $sArticleTable.oxartnum like '%Search%' ";
        $sExpt .= "or $sAEV.oetags like '%Search%' )  ) ";
        $sRes = $oTest->UNITgetSearchSelect('test Search');

        $sExpt = str_replace(array("\n", "\r", " "), "", $sExpt);
        $sRes = str_replace(array("\n", "\r", " "), "", $sRes);

        $this->assertEquals($sExpt, $sRes);
    }

    /**
     * Test get search select no select string.
     *
     * @return null
     */
    public function testGetSearchSelectNoSelectString()
    {
        $oTest = $this->getProxyClass('oxArticleList');
        $sRes = $oTest->UNITgetSearchSelect(null);

        $this->assertEquals('', $sRes);
    }

    /**
     * Test get search select use and.
     *
     * @return null
     */
    public function testGetSearchSelectUseAND()
    {
        $this->setConfigParam('blSearchUseAND', 1);
        $oTest = $this->getProxyClass('oxArticleList');

        $sArticleTable = $this->_getArticleTable();
        $sAEV = getViewName('oxartextends');

        $sExpt = "and ( ( $sArticleTable.oxtitle like '%test%' or $sArticleTable.oxshortdesc like '%test%' ";
        $sExpt .= "or $sArticleTable.oxsearchkeys like '%test%' or $sArticleTable.oxartnum like '%test%' ";
        $sExpt .= "or $sAEV.oetags like '%test%' ) and ";
        $sExpt .= " ( $sArticleTable.oxtitle like '%Search%' or $sArticleTable.oxshortdesc like '%Search%' ";
        $sExpt .= "or $sArticleTable.oxsearchkeys like '%Search%' or $sArticleTable.oxartnum like '%Search%' ";
        $sExpt .= "or $sAEV.oetags like '%Search%' )  ) ";

        $sRes = $oTest->UNITgetSearchSelect('test Search');

        $sExpt = str_replace(array("\n", "\r", " "), "", $sExpt);
        $sRes = str_replace(array("\n", "\r", " "), "", $sRes);

        $this->assertEquals($sExpt, $sRes);
    }

    /**
     * Test get search select with german chars.
     *
     * @return null
     */
    public function testGetSearchSelectWithGermanChars()
    {
        $this->setConfigParam('blSearchUseAND', 1);
        $oTest = $this->getProxyClass('oxArticleList');

        $sArticleTable = $this->_getArticleTable();
        $sAEV = getViewName('oxartextends');

        $sExpt = "and ( ( $sArticleTable.oxtitle like '%würfel%' or $sArticleTable.oxtitle like '%w&uuml;rfel%' ";
        $sExpt .= "or $sArticleTable.oxshortdesc like '%würfel%' or $sArticleTable.oxshortdesc like '%w&uuml;rfel%' ";
        $sExpt .= "or $sArticleTable.oxsearchkeys like '%würfel%' or $sArticleTable.oxsearchkeys like '%w&uuml;rfel%' ";
        $sExpt .= "or $sArticleTable.oxartnum like '%würfel%' or $sArticleTable.oxartnum like '%w&uuml;rfel%' ";
        $sExpt .= "or $sAEV.oetags like '%würfel%' or $sAEV.oetags like '%w&uuml;rfel%' ) )";

        $sRes = $oTest->UNITgetSearchSelect('würfel ');

        $sExpt = str_replace(array("\n", "\r", " "), "", $sExpt);
        $sRes = str_replace(array("\n", "\r", " "), "", $sRes);

        $this->assertEquals($sExpt, $sRes);
    }

    /**
     * Test load search ids.
     *
     * @return null
     */
    public function testLoadSearchIds()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);

        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and ( ( $sArticleTable.oxtitle like";
        $sExpt .= " '%testSearch%'  or $sArticleTable.oxshortdesc like '%testSearch%'  or";
        $sExpt .= " $sArticleTable.oxsearchkeys like '%testSearch%'  or $sArticleTable.oxartnum";
        $sExpt .= " like '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($this->equalTo($sExpt))
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch');
    }

    /**
     * Test load search ids in eng lang with sort.
     *
     * @return null
     */
    public function testLoadSearchIdsInEngLangWithSort()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);
        $this->setLanguage(1);
        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and ( ( $sArticleTable.oxtitle like";
        $sExpt .= " '%testSearch%'  or $sArticleTable.oxshortdesc like '%testSearch%'  or";
        $sExpt .= " $sArticleTable.oxsearchkeys like '%testSearch%'  or $sArticleTable.oxartnum";
        $sExpt .= " like '%testSearch%'  )  )  order by oxtitle desc ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($this->equalTo($sExpt))
            ->will($this->returnValue(true));
        $oTest->setCustomSorting('oxtitle desc');
        $oTest->loadSearchIds('testSearch');
    }

    /**
     * Test load search ids category.
     *
     * @return null
     */
    public function testLoadSearchIdsCategory()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);

        $sArticleTable = $this->_getArticleTable();
        $sO2CTable = $this->_getO2CTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid from $sO2CTable as oxobject2category, $sArticleTable ";
        $sExpt .= " where oxobject2category.oxcatnid='cat1' and oxobject2category.oxobjectid=$sArticleTable.oxid";
        $sExpt .= " and " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = '' and";
        $sExpt .= " $sArticleTable.oxissearch = 1  and ( ( $sArticleTable.oxtitle like '%testSearch%' ";
        $sExpt .= " or $sArticleTable.oxshortdesc like '%testSearch%'  or $sArticleTable.oxsearchkeys";
        $sExpt .= " like '%testSearch%'  or $sArticleTable.oxartnum like '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($sExpt)
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch', 'cat1');
    }

    /**
     * Test load search vendor ids.
     *
     * @return null
     */
    public function testLoadSearchIdsVendor()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);

        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and $sArticleTable.oxvendorid = 'vendor1' ";
        $sExpt .= " and ( ( $sArticleTable.oxtitle like '%testSearch%'  or $sArticleTable.oxshortdesc";
        $sExpt .= " like '%testSearch%'  or $sArticleTable.oxsearchkeys like '%testSearch%'  or $sArticleTable.oxartnum like '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($sExpt)
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch', '', 'vendor1');
    }

    /**
     * Test load search  manufacturer ids.
     *
     * @return null
     */
    public function testLoadSearchIdsManufacturer()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);

        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and $sArticleTable.oxmanufacturerid = 'manufacturer1' ";
        $sExpt .= " and ( ( $sArticleTable.oxtitle like '%testSearch%'  or $sArticleTable.oxshortdesc";
        $sExpt .= " like '%testSearch%'  or $sArticleTable.oxsearchkeys like '%testSearch%'  or $sArticleTable.oxartnum like '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($sExpt)
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch', '', '', 'manufacturer1');
    }

    /**
     * Test load search ids category vendor manufacturer.
     *
     * @return null
     */
    public function testLoadSearchIdsCategoryVendorManufacturer()
    {

        $this->setConfigParam('aSearchCols', array('oxtitle', 'oxshortdesc', 'oxsearchkeys', 'oxartnum'));
        $this->setTime(100);

        $sArticleTable = $this->_getArticleTable();
        $sO2CTable = $this->_getO2CTable();
        $oArticle = oxNew('oxArticle');

        $sExpt = "select $sArticleTable.oxid from $sO2CTable as oxobject2category, $sArticleTable ";
        $sExpt .= " where oxobject2category.oxcatnid='cat1' and oxobject2category.oxobjectid=$sArticleTable.oxid";
        $sExpt .= " and " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = '' and";
        $sExpt .= " $sArticleTable.oxissearch = 1  and $sArticleTable.oxvendorid = 'vendor1'  and $sArticleTable.oxmanufacturerid = 'manufacturer1'  and";
        $sExpt .= " ( ( $sArticleTable.oxtitle like '%testSearch%'  or $sArticleTable.oxshortdesc";
        $sExpt .= " like '%testSearch%'  or $sArticleTable.oxsearchkeys like '%testSearch%'  or";
        $sExpt .= " $sArticleTable.oxartnum like '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($sExpt)
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch', 'cat1', 'vendor1', 'manufacturer1');
    }

    /**
     * Test load search ids with search in tags.
     *
     * @return null
     */
    public function testLoadSearchIdsWithSearchInTags()
    {

        $this->setTime(100);
        $this->setConfigParam('aSearchCols', array('oetags'));

        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sAEV = getViewName('oxartextends');

        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  LEFT JOIN $sAEV ON $sAEV.oxid=$sArticleTable.oxid  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and ( ( $sAEV.oetags like";
        $sExpt .= " '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($this->equalTo($sExpt))
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch');
    }

    /**
     * Test load search ids with search in long desc.
     *
     * @return null
     */
    public function testLoadSearchIdsWithSearchInLongDesc()
    {

        $this->setTime(100);
        $this->setConfigParam('aSearchCols', array('oxlongdesc'));

        $sArticleTable = $this->_getArticleTable();
        $oArticle = oxNew('oxArticle');

        $sAEV = getViewName('oxartextends');
        $sExpt = "select $sArticleTable.oxid, $sArticleTable.oxtimestamp from $sArticleTable  LEFT JOIN $sAEV ON $sAEV.oxid=$sArticleTable.oxid  where";
        $sExpt .= " " . $oArticle->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''";
        $sExpt .= " and $sArticleTable.oxissearch = 1  and ( ( $sAEV.oxlongdesc like";
        $sExpt .= " '%testSearch%'  )  ) ";

        $oTest = $this->getMock('oxArticleList', array("_createIdListFromSql"));
        $oTest->expects($this->once())->method("_createIdListFromSql")
            ->with($this->equalTo($sExpt))
            ->will($this->returnValue(true));
        $oTest->loadSearchIds('testSearch');
    }



    /**
     * Test article loading for chosen tags and how methods are called
     *
     * @return null
     */
    public function testLoadTagArticlesMock()
    {
        oxTestModules::addFunction('oxUtilsCount', 'getTagArticleCount', '{ return 999; }');

        $sView = getViewName('oxartextends', 5);
        $sQ = "select yyy from $sView inner join xxx on " .
              "xxx.oxid = $sView.oxid where xxx.oxparentid = '' " .
              "and (   oxv_oxartextends_5.oetags like 'zzz_' or oxv_oxartextends_5.oetags like '%,zzz_,%' or " .
              "oxv_oxartextends_5.oetags like '%,zzz_' or oxv_oxartextends_5.oetags like 'zzz_,%') and 1";

        $oBaseObject = $this->getMock('oxArticle', array('getViewName', 'getSelectFields', 'getSqlActiveSnippet'));
        $oBaseObject->expects($this->once())->method('getViewName')->will($this->returnValue('xxx'));
        $oBaseObject->expects($this->once())->method('getSelectFields')->will($this->returnValue('yyy'));
        $oBaseObject->expects($this->once())->method('getSqlActiveSnippet')->will($this->returnValue('1'));

        $oArtList = $this->getMock('oxArticleList', array('getBaseObject', 'selectString'));
        $oArtList->expects($this->once())->method('getBaseObject')->will($this->returnValue($oBaseObject));
        $oArtList->expects($this->once())->method('selectString')->with($sQ);

        $this->assertEquals(999, $oArtList->loadTagArticles('zzz', 5));
    }

    /**
     * Test article loading for chosen tags
     *
     * @return null
     */
    public function testLoadTagArticles()
    {
        $sTag = "wanduhr";
        $oTest = oxNew('oxArticleList');
        $oTest->loadTagArticles($sTag, 0);

        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $iCount = 4;
            $this->assertTrue(isset($oTest[1672]));
        } else {
            $iCount = 3;
        }

        $this->assertEquals($iCount, count($oTest));
        $this->assertTrue(isset($oTest[2000]));
        $this->assertTrue(isset($oTest[1771]));
        $this->assertTrue(isset($oTest[1354]));
    }

    /**
     * Test load tag articles lang 0.
     *
     * @return null
     */
    public function testLoadTagArticlesLang0()
    {
        $sTag = "wanduhr";
        $oTest = oxNew('oxArticleList');
        $oTest->loadTagArticles($sTag, 0);
        $this->assertEquals($oTest[2000]->oxarticles__oxtitle->value, 'Wanduhr ROBOT');
    }

    /**
     * Test load tag articles lang 1.
     *
     * @return null
     */
    public function testLoadTagArticlesLang1()
    {
        $sTag = "wanduhr";
        $oTest = oxNew('oxArticleList');
        $oTest->loadTagArticles($sTag, 1);
        $this->assertEquals(0, count($oTest));
    }

    /**
     * Test load tag articles with sorting.
     *
     * @return null
     */
    public function testLoadTagArticlesWithSorting()
    {
        $sTag = "wanduhr";
        $oTest = oxNew('oxArticleList');
        $oTest->setCustomSorting('oxtitle desc');
        $oTest->loadTagArticles($sTag, 0);

        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $aExpArrayKeys = array(1354, 2000, 1672, 1771);
            $iCount = 4;
        } else {
            $aExpArrayKeys = array(1354, 2000, 1771);
            $iCount = 3;
        }

        $this->assertEquals($iCount, count($oTest));
        $this->assertEquals($aExpArrayKeys, $oTest->ArrayKeys());
    }

    /**
     * Test load tag articles with sorting with table.
     *
     * @return null
     */
    public function testLoadTagArticlesWithSortingWithTable()
    {
        $sTag = "wanduhr";
        $oTest = oxNew('oxArticleList');

        // echo "mano: ". $oTest->getBaseObject()->getViewName().'.oxtitle desc';

        $oTest->setCustomSorting($oTest->getBaseObject()->getViewName() . '.oxtitle desc');


        $oTest->loadTagArticles($sTag, 0);

        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $aExpArrayKeys = array(1354, 2000, 1672, 1771);
            $iCount = 4;
        } else {
            $aExpArrayKeys = array(1354, 2000, 1771);
            $iCount = 3;
        }

        $this->assertEquals($iCount, count($oTest));
        $this->assertEquals($aExpArrayKeys, $oTest->ArrayKeys());
    }

    /**
     * Test get tag article ids with mock how code is executed.
     *
     * @return null
     */
    public function testGetTagArticleIdsMocking()
    {
        $aReturn = array('aaa' => 'bbb');
        $sView = getViewName('oxartextends', 5);
        $sQ = "select $sView.oxid from $sView inner join xxx on " .
              "xxx.oxid = $sView.oxid where xxx.oxparentid = '' and xxx.oxissearch = 1 and " .
              "(   oxv_oxartextends_5.oetags like 'zzz_' or oxv_oxartextends_5.oetags like '%,zzz_,%' or " .
              "oxv_oxartextends_5.oetags like '%,zzz_' or oxv_oxartextends_5.oetags like 'zzz_,%') " .
              "and 1 order by xxx.yyy ";

        $oBaseObject = $this->getMock('oxArticle', array('getViewName', 'getSqlActiveSnippet'));
        $oBaseObject->expects($this->once())->method('getViewName')->will($this->returnValue('xxx'));
        $oBaseObject->expects($this->once())->method('getSqlActiveSnippet')->will($this->returnValue('1'));

        $oArtList = $this->getMock('oxArticleList', array('getBaseObject', '_createIdListFromSql'));
        $oArtList->expects($this->exactly(1))->method('getBaseObject')->will($this->returnValue($oBaseObject));
        $oArtList->expects($this->once())->method('_createIdListFromSql')->with($sQ)->will($this->returnValue($aReturn));

        $oArtList->setCustomSorting('yyy');
        $this->assertEquals($aReturn, $oArtList->getTagArticleIds('zzz', 5));
    }

    /**
     * Test get tag article ids.
     *
     * @return null
     */
    public function testGetTagArticleIds()
    {
        $sTag = "wanduhr";

        if ($this->getTestConfig()->getShopEdition() == 'EE') {
            $aExpIds = array(1354, 2000, 1672, 1771);
        } else {
            $aExpIds = array(1354, 2000, 1771);
        }

        $oArtList = oxNew('oxArticleList');
        $oArtList->setCustomSorting('oxtitle desc');
        $oArtList->getTagArticleIds($sTag, 0);
        $this->assertEquals($aExpIds, $oArtList->ArrayKeys());
    }

    /**
     * Test enable select lists.
     *
     * @return null
     */
    public function testEnableSelectLists()
    {
        $oTest = $this->getProxyClass('oxArticleList');
        $this->assertFalse($oTest->getNonPublicVar("_blLoadSelectLists"));
        $oTest->enableSelectLists();
        $this->assertTrue($oTest->getNonPublicVar("_blLoadSelectLists"));
    }

}
