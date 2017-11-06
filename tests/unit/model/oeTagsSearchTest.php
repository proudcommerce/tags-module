<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * Class oeTagsSearchTest
 *
 * @package Unit\Application\Model
 */
class oeTagsSearchTest extends oeTagsTestCase
{
    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_oSearchHandler = oxNew('oxSearch');
        $this->tableViewNameGenerator = new TableViewNameGenerator();
        $this->getConfig()->setConfigParam('blUseTimeCheck', true);
        $this->cleanUpTable('oxarticles');
        $this->cleanUpTable('oxobject2category');
        $this->cleanUpTable('oxcategories');
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $myDB = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $myDB->execute('delete from oxselectlist where oxid = "oxsellisttest" ');
        $myDB->execute('delete from oxobject2selectlist where oxselnid = "oxsellisttest" ');
        $this->cleanUpTable('oxcategories');
        $this->cleanUpTable('oxarticles');
        $this->cleanUpTable('oxobject2category');
        parent::tearDown();
    }

    public function testGetSearchSelectWithSearchInTags()
    {
        // forcing config
        $this->getConfig()->setConfigParam('aSearchCols', array('oetags'));
        $this->getConfig()->setConfigParam('blUseRightsRoles', 0);

        oxAddClassModule('modOxUtilsDate', 'oxUtilsDate');
        Registry::get("oxUtilsDate")->UNITSetTime(0);

        $iCurrTime = 0;
        oxTestModules::addFunction("oxUtilsDate", "getRequestTime", "{ return $iCurrTime; }");

        $sSearchDate = date('Y-m-d H:i:s', $iCurrTime);
        $sArticleTable = $sTable = getViewName('oxarticles');
        $sAETable = getViewName('oxartextends');

        $sQ = "select `$sArticleTable`.`oxid`, $sArticleTable.oxtimestamp from $sArticleTable LEFT JOIN $sAETable ON $sArticleTable.oxid=$sAETable.oxid where (  ( $sArticleTable.oxactive = 1  and $sArticleTable.oxhidden = 0 or ( $sArticleTable.oxactivefrom < '$sSearchDate' and
               $sArticleTable.oxactiveto > '$sSearchDate' ) )  and ( $sArticleTable.oxstockflag != 2 or ( $sArticleTable.oxstock +
               $sArticleTable.oxvarstock ) > 0  )";
        if (!$this->getConfig()->getConfigParam('blVariantParentBuyable')) {
            $sTimeCheckQ = " or ( art.oxactivefrom < '$sSearchDate' and art.oxactiveto > '$sSearchDate' )";
            $sQ .= " and IF( $sTable.oxvarcount = 0, 1, ( select 1 from $sTable as art where art.oxparentid=$sTable.oxid and ( art.oxactive = 1 $sTimeCheckQ ) and ( art.oxstockflag != 2 or art.oxstock > 0 ) limit 1 ) ) ";
        }
        $sQ .= ")  and $sArticleTable.oxparentid = '' and $sArticleTable.oxissearch = 1  and
                ( (  $sAETable.oetags like '%xxx%' )  ) ";

        /** @var Search $oSearch */
        $oSearch = oxNew('oxSearch');
        $sFix = $oSearch->UNITgetSearchSelect('xxx');

        $aSearch = array("/\s+/", "/\t+/", "/\r+/", "/\n+/");
        $sQ = trim(strtolower(preg_replace($aSearch, " ", $sQ)));
        $sFix = trim(strtolower(preg_replace($aSearch, " ", $sFix)));

        $this->assertEquals($sQ, $sFix);
    }
}
