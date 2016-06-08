<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxDb;
use OxidEsales\TestingLibrary\AcceptanceTestCase;

/** Selenium tests for new layout. */
class oetagsNavigationFrontendTest extends AcceptanceTestCase
{

    protected function setUp()
    {
        parent::setUp();

        $dbMetaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');
        $dbMetaDataHandler->updateViews();

        $path = $this->getTestConfig()->getShopEdition() == 'EE' ? 'EE' : 'CE_PE';
        $this->importSql(__DIR__ . "/../../testdata/{$path}/demodata_{$path}.sql");

        $this->installModule();
    }

    /**
     * Checking Tags functionality
     *
     * @group frontend
     */
    public function testFrontendTags()
    {
        $this->openShop();
        $this->loginInFrontend("example_test@oxid-esales.dev", "useruser");
        $this->assertElementPresent("tagBox");
        $this->assertEquals("%TAGS%", $this->getText("//nav[@id='tagBox']/h3"));
        $this->assertElementPresent("//nav[@id='tagBox']//a[text()='%MORE%%ELLIPSIS%']");
        $this->clickAndWait("//nav[@id='tagBox']//a[text()='%MORE%%ELLIPSIS%']");

        $this->assertEquals("%YOU_ARE_HERE%: / %TAGS%", $this->getText("breadCrumb"));
        $this->assertEquals("%TAGS%", $this->getText("//h1"));
        $this->assertElementPresent("link=[EN]");
        $this->assertElementPresent("link=šÄßüл");
        $this->assertElementPresent("link=tag");
        $this->assertElementPresent("link=1");
        $this->assertElementPresent("link=2");
        $this->assertElementPresent("link=3");
        $this->clickAndWait("link=%HOME%");

        $this->clickAndWait("//nav[@id='tagBox']//a[text()='tag']");
        $this->assertEquals("%YOU_ARE_HERE%: / %TAGS% / Tag", $this->getText("breadCrumb"));
        $this->assertEquals("Tag", $this->getText("//section[@id='content']/h1"));
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementPresent("//ul[@id='productList']/li[4]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[5]");

        // go to product 1002 details
        $this->clickAndWait("//ul[@id='productList']/li[3]//a");
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));
        $this->assertEquals("%YOU_ARE_HERE%: / %TAGS% / Tag", $this->getText("breadCrumb"));
        $this->assertFalse($this->isVisible("tags"));
        $this->assertEquals("%TAGS%", $this->getText("//ul[@id='itemTabs']/li[2]"));
        $this->click("//ul[@id='itemTabs']/li[2]/a");
        $this->waitForItemAppear("tags");
        $this->assertTrue($this->isVisible("link=tag"));

        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));

        //adding new tag
        $this->assertElementNotPresent("newTags");
        $this->click("//ul[@id='itemTabs']//a[text()='%TAGS%']");
        $this->waitForItemAppear("tags");
        $this->click("editTag");
        $this->waitForItemAppear("newTags");
        $this->assertTextPresent("%ADD_TAGS%:");
        $this->type("newTags", "new_tag");
        $this->click("saveTag");
        $this->waitForText("new_tag");
        $this->click("cancelTag");
        $this->waitForElement("link=new_tag");
        $this->assertTrue($this->isVisible("link=new_tag"));
        $this->clickAndWait("link=new_tag");
        $this->assertEquals("%YOU_ARE_HERE%: / %TAGS% / New_tag", $this->getText("breadCrumb"));
        $this->assertEquals("New_tag", $this->getText("//h1"));
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementNotPresent("productList_2");
    }

    /**
     * Checking Tags functionality
     *
     * @group knorke
     */
    public function testFrontendTagsNavigation()
    {
        $this->openShop();
        $this->assertElementPresent("tagBox");
        $this->assertEquals("%TAGS%", $this->getText("//nav[@id='tagBox']/h3"));
        $this->assertElementPresent("//nav[@id='tagBox']//a[text()='tag']");

        $this->clickAndWait("//nav[@id='tagBox']//a[text()='tag']");
        $this->assertEquals("%YOU_ARE_HERE%: / %TAGS% / Tag", $this->getText("breadCrumb"));
        $this->assertEquals("Tag", $this->getText("//h1"));
        //login just to check, if no errors occur (there were some)
        $this->loginInFrontend("example_test@oxid-esales.dev", "useruser");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementPresent("//ul[@id='productList']/li[4]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[5]");
        //sorting by title desc
        $this->assertElementPresent("sortItems");
        $this->selectDropDown("sortItems", "", "li[3]"); //Title desc
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));

        $this->assertElementPresent("//ul[@id='productList']/li[4]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[5]");

        //displaying 2 items per page
        $this->assertElementNotPresent("itemsPager");
        $this->selectDropDown("itemsPerPage", "2");
        $this->assertElementPresent("//div[@id='itemsPager']//a[text()='1']");
        $this->assertElementPresent("//div[@id='itemsPager']//a[text()='2']");
        $this->assertElementPresent("//div[@id='itemsPager']//a[text()='%NEXT%']");
        $this->assertElementNotPresent("//div[@id='itemsPager']//a[text()='%PREVIOUS%']");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementPresent("//ul[@id='productList']/li[2]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");

        //going to page 2
        $this->clickAndWait("//div[@id='itemsPager']//a[text()='%NEXT%']");
        $this->assertEquals("Test product 3 [EN] šÄßüл", $this->getText("productList_2"));
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");
        $this->assertElementNotPresent("//div[@id='itemsPager']//a[text()='%NEXT%']");
        $this->assertElementPresent("//div[@id='itemsPager']//a[text()='%PREVIOUS%']");
        //sorting by title asc
        $this->assertElementPresent("sortItems");
        $this->selectDropDown("sortItems", "", "li[2]");
        //after sorting wer are redirected to 1st page
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("productList_2"));
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");
        $this->assertElementPresent("//div[@id='itemsPager']//a[text()='%NEXT%']");
        $this->assertElementNotPresent("//div[@id='itemsPager']//a[text()='%PREVIOUS%']");
    }

    /**
     * Search in frontend. Checking option: Fields to be considered in Search
     *
     * @group frontend
     */
    public function testFrontendSearchConsideredFields()
    {
        //art num is not considered in search
        $this->callShopSC("oxConfig", null, null, array("aSearchCols" => array("type" => "arr", "value" => array ("oxtitle", "oxshortdesc", "oetags" ))));
        $this->clearCache();
        $this->openShop();
        $this->searchFor("100");
        $this->assertEquals("%YOU_ARE_HERE%: / %SEARCH%", $this->getText("breadCrumb"));
        $this->assertTextPresent("0 %HITS_FOR% \"100\"");

        //art num is considered in search
        $this->callShopSC("oxConfig", null, null, array("aSearchCols" => array("type" => "arr", "value" => array("oxtitle", "oxshortdesc", "oxsearchkeys", "oxartnum", "oetags"))));
        $this->clearTemp();
        $this->searchFor("100");
        $this->assertEquals("%YOU_ARE_HERE%: / %SEARCH%", $this->getText("breadCrumb"));
        $this->assertTextPresent("4 %HITS_FOR% \"100\"");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->clearString($this->getText("searchList_1")));
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->clearString($this->getText("searchList_2")));
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->clearString($this->getText("searchList_3")));
        $this->assertEquals("Test product 3 [EN] šÄßüл", $this->clearString($this->getText("searchList_4")));
        $this->assertElementNotPresent("searchList_5");

        $this->clickAndWait("searchList_3");
        $this->assertEquals("%YOU_ARE_HERE%: / Search result for \"100\"", $this->getText("breadCrumb"));
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));
        $this->selectVariant("variants", 1, "var2 [EN] šÄßüл", "var2 [EN] šÄßüл");
        $this->assertEquals("%YOU_ARE_HERE%: / Search result for \"100\"", $this->getText("breadCrumb"));
        $this->assertEquals("Test product 2 [EN] šÄßüл var2 [EN] šÄßüл", $this->getText("//h1"));

        $this->clickAndWait("//div[@id='overviewLink']/a");
        $this->assertEquals("%YOU_ARE_HERE%: / %SEARCH%", $this->getText("breadCrumb"));
        $this->assertTextPresent("4 %HITS_FOR% \"100\"");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->clearString($this->getText("searchList_1")));
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->clearString($this->getText("searchList_2")));
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->clearString($this->getText("searchList_3")));
        $this->assertEquals("Test product 3 [EN] šÄßüл", $this->clearString($this->getText("searchList_4")));
    }

    /**
     * @param $iStatus
     * @return array
     */
    protected function _userData($iStatus)
    {
        $aSubscribedUserData = array(
            'OXSAL' => 'MRS',
            'OXFNAME' => 'name_šÄßüл',
            'OXLNAME' => 'surname_šÄßüл',
            'OXEMAIL' => 'example01@oxid-esales.dev',
            'OXDBOPTIN' => (string)$iStatus
        );

        return $aSubscribedUserData;
    }

    /**
     * install given module in shop
     *
     * @param string $name
     */
    private function installModule($name = 'oetags')
    {
        $module = oxNew('oxModule');
        $module->load($name);
        $moduleCache = oxNew('oxModuleCache', $module);
        $moduleInstaller = oxNew('oxModuleInstaller', $moduleCache);

        $moduleInstaller->activate($module);
        $moduleInstaller->deactivate($module);
        $moduleInstaller->activate($module);

        $moduleList = oxNew("oxModuleList");
        $moduleList->getModulesFromDir(oxRegistry::getConfig()->getModulesDir());

        $modulePaths = oxRegistry::getConfig()->getConfigParam('aModulePaths');
        $moduePaths['oetags'] = 'oe/oetags';
        oxRegistry::getConfig()->saveShopConfVar('aarr', 'aModulePaths', $modulePaths);

    }

}
