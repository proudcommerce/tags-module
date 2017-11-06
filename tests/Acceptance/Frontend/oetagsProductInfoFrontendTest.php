<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/** Frontend: product information/ details related tests */
class ProductInfoFrontendTest extends \OxidEsales\EshopCommunity\Tests\Acceptance\AdminTestCase
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
     * Test, that on the product details page is the tags tab is present.
     *
     * @group product
     */
    public function testFrontendDetailsTagsTab()
    {
        $this->openShop();
        $this->searchFor("100");
        $this->clickAndWait("//ul[@id='searchList']/li[2]//a");

        //navigation between products (in details page)
        $this->assertEquals("%YOU_ARE_HERE%: / Search result for \"100\"", $this->getText("breadCrumb"));
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->getText("//h1"));
        $this->assertEquals("%PRODUCT% 2 %OF% 4", $this->getText("//div[@id='detailsItemsPager']/span"));

        $this->assertEquals("%TAGS%", $this->clearString($this->getText("//ul[@id='itemTabs']/li[4]")));
        $this->click("//ul[@id='itemTabs']/li[4]/a");
        $this->waitForItemAppear("tags");
        $this->assertEquals("šÄßüл tag [EN] 1", $this->clearString($this->getText("tags")));
    }

    /**
     * Product details. test for checking main product details as info, prices, buying etc
     *
     * @group product
     */
    public function testFrontendDetailsNavigationAndInfo()
    {
        $this->openShop();
        $this->searchFor("100");
        $this->clickAndWait("//ul[@id='searchList']/li[2]//a");

        //navigation between products (in details page)
        $this->assertEquals("%YOU_ARE_HERE%: / Search result for \"100\"", $this->getText("breadCrumb"));
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->getText("//h1"));
        $this->assertEquals("%PRODUCT% 2 %OF% 4", $this->getText("//div[@id='detailsItemsPager']/span"));
        $this->clickAndWait("//div[@id='detailsItemsPager']/a[text()='%NEXT_PRODUCT%']");
        $this->assertEquals("%PRODUCT% 3 %OF% 4", $this->getText("//div[@id='detailsItemsPager']/span"));
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));
        $this->clickAndWait("//div[@id='detailsItemsPager']/a[text()='%PREVIOUS_PRODUCT%']");
        $this->assertEquals("%YOU_ARE_HERE%: / Search result for \"100\"", $this->getText("breadCrumb"));
        $this->assertEquals("%PRODUCT% 2 %OF% 4", $this->getText("//div[@id='detailsItemsPager']/span"));

        //product info
        $this->_assertArticle('Test product 1 [EN] šÄßüл', 'Test product 1 short desc [EN] šÄßüл', '1001', '100,00 € *');
        $this->assertTextPresent("%MESSAGE_NOT_ON_STOCK%");

        $this->assertTextPresent("%AVAILABLE_ON% 2008-01-01");
        $this->assertElementPresent("productSelections");
        $this->assertElementPresent("//div[@id='productSelections']//ul");
        $this->assertEquals("selvar1 [EN] šÄßüл +1,00 € selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл -2,00 € selvar4 [EN] šÄßüл +2%", $this->getText("//div[@id='productSelections']//ul"));
        $this->assertTextPresent("%REDUCED_FROM_2% 150,00 €");
        $this->assertEquals("Test product 1 long description [EN] šÄßüл", $this->getText("//*[@id='description']"));

        $this->assertEquals("%SPECIFICATION%", $this->clearString($this->getText("//ul[@id='itemTabs']/li[2]")));
        $this->click("//ul[@id='itemTabs']/li[2]/a");
        $this->waitForItemAppear("attributes");
        $this->assertEquals("Test attribute 1 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[1]/th"));
        $this->assertEquals("attr value 11 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[1]/td"));
        $this->assertEquals("Test attribute 3 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[2]/th"));
        $this->assertEquals("attr value 3 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[2]/td"));
        $this->assertEquals("Test attribute 2 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[3]/th"));
        $this->assertEquals("attr value 12 [EN] šÄßüл", $this->getText("//div[@id='attributes']//tr[3]/td"));

        //buying product
        //TODO: Selenium refactor with basket construct
        $this->click("//div[@id='productSelections']//ul/li[2]/a");
        $this->type("amountToBasket", "2");
        $this->clickAndWait("toBasket");

        $this->assertEquals("2", $this->getText("//div[@id='miniBasket']/span"));

        $this->clickAndWait("//div[@id='overviewLink']/a");
        $this->assertEquals("%YOU_ARE_HERE%: / %SEARCH%", $this->getText("breadCrumb"));
        $this->assertEquals("100", $this->getValue("searchParam"));
    }

    /**
     * Product details. test for checking main product details as info, prices, buying etc
     *
     * @group product
     */
    public function testFrontendDetailsAdditionalInfo()
    {
        if (isSUBSHOP) {
            $this->executeSql("UPDATE `oxrecommlists` SET `OXSHOPID` = ".oxSHOPID."  WHERE 1");
            $this->executeSql("UPDATE `oxratings` SET `OXSHOPID` = ".oxSHOPID."  WHERE 1");
        }
        $this->clearCache();
        $this->openShop();
        $this->searchFor("1003");
        $this->clickAndWait("//ul[@id='searchList']//a");
        //staffelpreis
        $this->assertEquals("Test product 3 [EN] šÄßüл", $this->getText("//h1"));
        $this->assertEquals("75,00 € *", $this->getText("productPrice"));

        if ($this->getTestConfig()->getShopEdition() === 'EE' && !isSUBSHOP) {  //staffepreis is not inherited to subshp
            $this->assertElementPresent("amountPrice");
            $this->click("amountPrice");
            $this->waitForItemAppear("priceinfo");
            $this->assertEquals("2", $this->getText("//ul[@id='priceinfo']/li[3]/label"));
            $this->assertEquals("75,00 €", $this->getText("//ul[@id='priceinfo']/li[3]/span"));
            $this->assertEquals("6", $this->getText("//ul[@id='priceinfo']/li[4]/label"));
            $this->assertEquals("20 % %DISCOUNT%", $this->getText("//ul[@id='priceinfo']/li[4]/span"));
            $this->click("amountPrice");
            $this->waitForItemDisappear("priceinfo");
        }

        //review when user not logged in
        $this->assertElementPresent("//h4[text()='%WRITE_PRODUCT_REVIEW%']");
        $this->assertTextPresent("%NO_REVIEW_AVAILABLE%");
        $this->assertEquals("%MESSAGE_LOGIN_TO_WRITE_REVIEW%", $this->getText("reviewsLogin"));

        $this->click("productLinks");
        $this->waitForItemAppear("suggest");
        $this->assertEquals("%LOGIN_TO_ACCESS_WISH_LIST%", $this->getText("loginToNotice"));
        $this->assertEquals("%LOGIN_TO_ACCESS_GIFT_REGISTRY%", $this->getText("loginToWish"));

        //compare link
        $this->assertElementNotPresent("//p[@id='servicesTrigger']/span");
        $this->clickAndWait("addToCompare");
        $this->assertEquals("1", $this->clearString($this->getText("//p[@id='servicesTrigger']/span")));
        $this->click("productLinks");
        $this->waitForItemAppear("suggest");
        $this->assertEquals("%REMOVE_FROM_COMPARE_LIST%", $this->getText("removeFromCompare"));
        $this->clickAndWait("removeFromCompare");
        $this->assertElementNotPresent("//p[@id='servicesTrigger']/span");
        $this->click("productLinks");
        $this->waitForItemAppear("suggest");
        $this->assertEquals("%COMPARE%", $this->getText("addToCompare"));
        $this->clickAndWait("addToCompare");
        //check if compare products are not gone after you login
        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%ACCOUNT%']");
        $this->loginInFrontend("example_test@oxid-esales.dev", "useruser");
        $this->assertEquals("2", $this->clearString($this->getText("//p[@id='servicesTrigger']/span")));
        $this->assertEquals("%PRODUCT%: 1", $this->clearString($this->getText("//section[@id='content']//div[2]/dl[3]/dd")));
    }

    /**
     * checking if after md variants selection in details page all other js are still working correctly
     *
     * @group product
     */
    public function testMdVariantsAndJs()
    {
        $this->openShop();
        $this->searchFor("3571");
        $this->clickAndWait("searchList_1");
        $this->assertEquals("Kuyichi Jeans CANDY", $this->getText("//h1"));
        $this->selectVariant("variants", 1, "W 31/L 34", "W 31/L 34");
        $this->assertFalse($this->isEditable("toBasket"));
        $this->selectVariant("variants", 2, "Dark Blue", "W 31/L 34, Dark Blue");
        $this->assertFalse($this->isVisible("priceAlarmLink"));
        $this->click("productLinks");
        $this->waitForItemAppear("priceAlarmLink");
        $this->assertFalse($this->isVisible("pa[price]"));
        $this->click("priceAlarmLink");
        $this->waitForItemAppear("pa[price]");
        $this->assertFalse($this->isVisible("tags"));
        $this->click("//ul[@id='itemTabs']/li[4]/a");
        $this->waitForItemAppear("tags");
        $this->assertFalse($this->isVisible("attributes"));
        $this->click("//ul[@id='itemTabs']/li[2]/a");
        $this->waitForItemAppear("attributes");
        $this->assertFalse($this->isVisible("description"));
        $this->click("//ul[@id='itemTabs']/li[1]/a");
        $this->waitForItemAppear("//*[@id='description']");
        $this->assertFalse($this->isVisible("loginBox"));
        $this->click("loginBoxOpener");
        $this->waitForItemAppear("loginBox");
        $this->assertElementNotPresent("basketFlyout");
        $this->clickAndWait("toBasket");
        $this->assertFalse($this->isVisible("loginBox"));
        $this->click("minibasketIcon");
        $this->waitForItemAppear("basketFlyout");
        $this->assertTextPresent("%SELECTED_COMBINATION%: W 31/L 34, Dark Blue");
        $this->click("link=%RESET_SELECTION%");
        $this->waitForTextDisappear("%SELECTED_COMBINATION%: W 31/L 34, Dark Blue");
    }

    /**
     * Asserts that opened article information is correct
     *
     * @param string $sTitle article title
     * @param string $sDescription article short description
     * @param string $sArtNr article number
     * @param string $sPrice price, including currency and other characters belonging to it, like "15,00 € *"
     * @param bool $blToBasketActive whether toBasket button is active
     */
    private function _assertArticle($sTitle, $sDescription = '', $sArtNr = '', $sPrice = '', $blToBasketActive = true)
    {
        $sTitle ? $this->assertEquals($sTitle, $this->getText("productTitle")) : '';
        $sDescription ? $this->assertEquals($sDescription, $this->getText("productShortdesc")) : '';
        $sArtNr ? $this->assertEquals("%PRODUCT_NO%: $sArtNr", $this->getText("productArtnum")) : '';
        $sPrice ? $this->assertEquals($sPrice, $this->getText("productPrice")) : '';

        $sAssert = $blToBasketActive ? "assertTrue" : "assertFalse";
        $this->$sAssert($this->isEditable("toBasket"));
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

