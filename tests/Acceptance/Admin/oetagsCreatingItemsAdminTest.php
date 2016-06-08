<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\Eshop\Tests\Acceptance\AdminTestCase;

/** Creating and deleting items. */
class oetagsCreatingItemsAdminTest extends AdminTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $dbMetaDataHandler = oxNew('OxidEsales\Eshop\Core\DbMetaDataHandler');
        $dbMetaDataHandler->updateViews();

        $this->installModule();
    }

    /**
     * Activates oetags module
     *
     * @param string $sTestSuitePath
     *
     * @throws Exception
     */
    public function addTestData($sTestSuitePath)
    {
        parent::addTestData($sTestSuitePath);

        $path = $this->getTestConfig()->getShopEdition() == 'EE' ? 'EE' : 'CE_PE';
        $this->importSql(__DIR__ . "/../../testdata/{$path}/demodata_{$path}.sql");
    }

    /**
     * creating Product. Main, Extending
     *
     * @group creatingitems
     */
    public function testCreateProductMainExtended()
    {
        $this->loginAdmin("Administer Products", "Products");
        $this->changeAdminListLanguage('Deutsch');
        $this->type("where[oxarticles][oxartnum]", "100");
        $this->clickAndWait("submitit");
        $this->openListItem("link=[DE 4] Test product 0 šÄßüл");
        $this->assertEquals("[DE 4] Test product 0 šÄßüл", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("Deutsch", $this->getSelectedLabel("test_editlanguage"));
        $this->frame("list");
        $this->changeAdminListLanguage('English');
        $this->frame("edit");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("English", $this->getSelectedLabel("test_editlanguage"));
        //Product tab
        $this->clickCreateNewItem();
        $this->assertEquals("", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("0", $this->getValue("editval[oxarticles__oxactive]"));
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->type("editval[oxarticles__oxtitle]", "create_delete product [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxartnum]", "10000");
        $this->type("editval[oxarticles__oxshortdesc]", "create_delete short desc [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxsearchkeys]", "search [EN]_šÄßüл");
        $this->select("editval[oxarticles__oxvendorid]", "label=Distributor [EN] šÄßüл");
        $this->select("editval[oxarticles__oxmanufacturerid]", "label=Manufacturer [EN] šÄßüл");
        $this->type("editval[oxarticles__oxprice]", "5.9");
        $this->type("editval[oxarticles__oxpricea]", "1.1");
        $this->type("editval[oxarticles__oxpriceb]", "1.2");
        $this->type("editval[oxarticles__oxpricec]", "1.3");
        $this->type("editval[oxarticles__oxvat]", "4");
        $this->select("art_category", "label=Test category 0 [EN] šÄßüл");
        $this->assertEquals("", $this->getValue("editval[tags]"));
        $this->assertEquals("", $this->getValue("editval[oxarticles__oxean]"));
        $this->assertEquals("", $this->getValue("editval[oxarticles__oxdistean]"));
        $this->type("editval[tags]", "create_delete_tag_[EN]_šäßüл+-><()~*\\',[]{};:./|!@#$%^&?=`");
        $this->type("editval[oxarticles__oxean]", "EAN_šÄßüл");
        $this->type("editval[oxarticles__oxdistean]", "vendor EAN_ßÄ");
        $this->assertEquals("", $this->getEditorValue("oxarticles__oxlongdesc"));
        $this->typeToEditor("oxarticles__oxlongdesc", "long desc [EN]_šÄßüл");
        $this->clickAndWaitFrame("saveArticle", "list");
        $this->assertEquals("0", $this->getValue("editval[oxarticles__oxactive]"));
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->assertEquals("create_delete product [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("10000", $this->getValue("editval[oxarticles__oxartnum]"));
        $this->assertEquals("create_delete short desc [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->assertEquals("search [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxsearchkeys]"));
        $this->assertEquals("Distributor [EN] šÄßüл", $this->getSelectedLabel("editval[oxarticles__oxvendorid]"));
        $this->assertEquals("Manufacturer [EN] šÄßüл", $this->getSelectedLabel("editval[oxarticles__oxmanufacturerid]"));
        $this->assertEquals("5.9", $this->getValue("editval[oxarticles__oxprice]"));
        $this->assertEquals("1.1", $this->getValue("editval[oxarticles__oxpricea]"));
        $this->assertEquals("1.2", $this->getValue("editval[oxarticles__oxpriceb]"));
        $this->assertEquals("1.3", $this->getValue("editval[oxarticles__oxpricec]"));
        $this->assertEquals("4", $this->getValue("editval[oxarticles__oxvat]"));
        $this->assertEquals("create_delete_tag_ en _šäßüл", $this->getValue("editval[tags]"));
        $this->assertEquals("EAN_šÄßüл", $this->getValue("editval[oxarticles__oxean]"));
        $this->assertEquals("vendor EAN_ßÄ", $this->getValue("editval[oxarticles__oxdistean]"));
        $this->assertEquals("long desc [EN]_šÄßüл", $this->getEditorValue("oxarticles__oxlongdesc"));
        $this->assertEquals("Save", $this->getValue("saveArticle"));
        $this->assertEquals("Copy Product", $this->getValue("save"));
        $this->assertElementPresent("//input[@name='save' and @value='Copy to']");
        $this->assertEquals("English", $this->getSelectedLabel("test_editlanguage"));
        $this->assertEquals("Deutsch", $this->getSelectedLabel("new_lang"));
        $this->clickAndWaitFrame("//input[@name='save' and @value='Copy to']", "list");
        $this->assertEquals("Deutsch", $this->getSelectedLabel("test_editlanguage"));
        $this->check("editval[oxarticles__oxactive]");
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->type("editval[oxarticles__oxtitle]", "create_delete product [DE]");
        $this->type("editval[oxarticles__oxartnum]", "10001");
        $this->type("editval[oxarticles__oxshortdesc]", "create_delete short desc [DE]");
        $this->type("editval[oxarticles__oxsearchkeys]", "search [DE]");
        $this->type("editval[oxarticles__oxprice]", "5.91");
        $this->type("editval[oxarticles__oxpricea]", "1.11");
        $this->type("editval[oxarticles__oxpriceb]", "1.21");
        $this->type("editval[oxarticles__oxpricec]", "1.31");
        $this->type("editval[oxarticles__oxvat]", "4.5");
        $this->type("editval[tags]", "create_delete_tag_[DE]");
        $this->typeToEditor("oxarticles__oxlongdesc", "long desc [DE]");
        $this->clickAndWaitFrame("//input[@value='Save']", "list");

        $this->selectAndWait("test_editlanguage", "label=English");
        $this->assertEquals("0", $this->getValue("editval[oxarticles__oxactive]"));
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->assertEquals("create_delete product [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("10001", $this->getValue("editval[oxarticles__oxartnum]"));
        $this->assertEquals("create_delete short desc [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->assertEquals("search [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxsearchkeys]"));
        $this->assertEquals("5.91", $this->getValue("editval[oxarticles__oxprice]"));
        $this->assertEquals("1.11", $this->getValue("editval[oxarticles__oxpricea]"));
        $this->assertEquals("1.21", $this->getValue("editval[oxarticles__oxpriceb]"));
        $this->assertEquals("1.31", $this->getValue("editval[oxarticles__oxpricec]"));
        $this->assertEquals("4.5", $this->getValue("editval[oxarticles__oxvat]"));
        $this->assertEquals("create_delete_tag_ en _šäßüл", $this->getValue("editval[tags]"));
        $this->assertEquals("EAN_šÄßüл", $this->getValue("editval[oxarticles__oxean]"));
        $this->assertEquals("vendor EAN_ßÄ", $this->getValue("editval[oxarticles__oxdistean]"));
        $this->assertEquals("long desc [EN]_šÄßüл", $this->getEditorValue("oxarticles__oxlongdesc"));
        $this->selectAndWait("test_editlanguage", "label=Deutsch");
        $this->assertEquals("0", $this->getValue("editval[oxarticles__oxactive]"));
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->assertEquals("create_delete product [DE]", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("10001", $this->getValue("editval[oxarticles__oxartnum]"));
        $this->assertEquals("create_delete short desc [DE]", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->assertEquals("search [DE]", $this->getValue("editval[oxarticles__oxsearchkeys]"));
        $this->assertEquals("Distributor [EN] šÄßüл", $this->getSelectedLabel("editval[oxarticles__oxvendorid]"));
        $this->assertEquals("Manufacturer [EN] šÄßüл", $this->getSelectedLabel("editval[oxarticles__oxmanufacturerid]"));
        $this->assertEquals("5.91", $this->getValue("editval[oxarticles__oxprice]"));
        $this->assertEquals("1.11", $this->getValue("editval[oxarticles__oxpricea]"));
        $this->assertEquals("1.21", $this->getValue("editval[oxarticles__oxpriceb]"));
        $this->assertEquals("1.31", $this->getValue("editval[oxarticles__oxpricec]"));
        $this->assertEquals("4.5", $this->getValue("editval[oxarticles__oxvat]"));
        $this->assertEquals("create_delete_tag_ de", $this->getValue("editval[tags]"));
        $this->assertEquals("EAN_šÄßüл", $this->getValue("editval[oxarticles__oxean]"));
        $this->assertEquals("vendor EAN_ßÄ", $this->getValue("editval[oxarticles__oxdistean]"));
        $this->assertEquals("long desc [DE]", $this->getEditorValue("oxarticles__oxlongdesc"));
        $this->selectAndWait("test_editlanguage", "label=English");
        //Extended tab
        $this->openTab("Extended");
        $this->assertElementNotPresent("editval[oxarticles__oxurlimg]", "#289 from Mantis");
        $this->type("editval[oxarticles__oxweight]", "1");
        $this->type("editval[oxarticles__oxlength]", "2");
        $this->type("editval[oxarticles__oxwidth]", "3");
        $this->type("editval[oxarticles__oxheight]", "4");
        $this->type("editval[oxarticles__oxunitquantity]", "5");
        $this->type("editval[oxarticles__oxunitname]", "6");
        $this->type("editval[oxarticles__oxexturl]", "http://url.lt");
        $this->type("editval[oxarticles__oxurldesc]", "url text [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxbprice]", "7");
        $this->type("editval[oxarticles__oxtprice]", "8");
        $this->type("editval[oxarticles__oxtemplate]", "template_šÄßüл");
        $this->type("editval[oxarticles__oxquestionemail]", "contact_šÄßüл");
        $this->assertEquals("on", $this->getValue("//input[@name='editval[oxarticles__oxissearch]' and @type='checkbox']"));
        $this->uncheck("//input[@name='editval[oxarticles__oxissearch]' and @type='checkbox']");
        $this->assertEquals("off", $this->getValue("//input[@name='editval[oxarticles__oxnonmaterial]' and @type='checkbox']"));
        $this->check("//input[@name='editval[oxarticles__oxnonmaterial]' and @type='checkbox']");
        $this->assertEquals("off", $this->getValue("//input[@name='editval[oxarticles__oxfreeshipping]' and @type='checkbox']"));
        $this->check("//input[@name='editval[oxarticles__oxfreeshipping]' and @type='checkbox']");
        $this->assertEquals("off", $this->getValue("//input[@name='editval[oxarticles__oxblfixedprice]' and @type='checkbox']"));
        $this->check("editval[oxarticles__oxblfixedprice]");
        $this->assertEquals("url text [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxurldesc]"));
        $this->assertElementNotPresent("editval[oxarticles__oxbundleid]");
        $this->assertElementPresent("//input[@value='Assign Products']");
        if ($this->isElementPresent("//input[@name='editval[oxarticles__oxskipdiscounts]' and @type='checkbox']")) {
            $this->assertEquals("off", $this->getValue("//input[@name='editval[oxarticles__oxskipdiscounts]' and @type='checkbox']"));
            $this->check("//input[@name='editval[oxarticles__oxskipdiscounts]' and @type='checkbox']");
        }
        $this->clickAndWait("save");
        $this->selectAndWait("test_editlanguage", "label=Deutsch");
        $this->assertEquals("1", $this->getValue("editval[oxarticles__oxweight]"));
        $this->assertEquals("2", $this->getValue("editval[oxarticles__oxlength]"));
        $this->assertEquals("3", $this->getValue("editval[oxarticles__oxwidth]"));
        $this->assertEquals("4", $this->getValue("editval[oxarticles__oxheight]"));
        $this->assertEquals("http://url.lt", $this->getValue("editval[oxarticles__oxexturl]"));
        $this->assertEquals("", $this->getValue("editval[oxarticles__oxurldesc]"));
        $this->type("editval[oxarticles__oxurldesc]", "url text [DE]");
        $this->assertEquals("7", $this->getValue("editval[oxarticles__oxbprice]"));
        $this->assertEquals("8", $this->getValue("editval[oxarticles__oxtprice]"));
        $this->assertEquals("template_šÄßüл", $this->getValue("editval[oxarticles__oxtemplate]"));
        $this->assertEquals("contact_šÄßüл", $this->getValue("editval[oxarticles__oxquestionemail]"));
        $this->assertEquals("off", $this->getValue("//input[@name='editval[oxarticles__oxissearch]' and @type='checkbox']"));
        $this->assertEquals("on", $this->getValue("//input[@name='editval[oxarticles__oxnonmaterial]' and @type='checkbox']"));
        $this->assertEquals("on", $this->getValue("//input[@name='editval[oxarticles__oxfreeshipping]' and @type='checkbox']"));
        $this->assertEquals("on", $this->getValue("//input[@name='editval[oxarticles__oxblfixedprice]' and @type='checkbox']"));
        $this->assertElementNotPresent("editval[oxarticles__oxbundleid]");
        if ($this->isElementPresent("//input[@name='editval[oxarticles__oxskipdiscounts]' and @type='checkbox']")) {
            $this->assertEquals("on", $this->getValue("//input[@name='editval[oxarticles__oxskipdiscounts]' and @type='checkbox']"));
        }
        $this->clickAndWait("save");
        $this->assertEquals("url text [DE]", $this->getValue("editval[oxarticles__oxurldesc]"));
        $this->selectAndWait("test_editlanguage", "label=English");
        $this->assertEquals("url text [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxurldesc]"));
        //testing if other tabs are working
        $this->checkTabs(array(
            'Stock',
            'Selection',
            'Crosssell.',
            'Variants',
            'Pictures',
            'Review',
            'Statistics',
            'SEO',
            'Rights',
            'Mall'
        ));
    }

    /**
     * creating Product. Copy product
     *
     * @group creatingitems
     */
    public function testCreateProductCopy()
    {
        $this->loginAdmin("Administer Products", "Products");
        $this->frame("edit");
        $this->assertEquals("0", $this->getValue("editval[oxarticles__oxactive]"));
        $this->assertElementNotPresent("editval[oxarticles__oxactivefrom]");
        $this->assertElementNotPresent("editval[oxarticles__oxactiveto]");
        $this->type("editval[oxarticles__oxtitle]", "create_delete product [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxartnum]", "10001_šÄßüл");
        $this->type("editval[oxarticles__oxshortdesc]", "create_delete short desc [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxsearchkeys]", "search [EN]_šÄßüл");
        $this->select("editval[oxarticles__oxvendorid]", "label=Distributor [EN] šÄßüл");
        $this->select("editval[oxarticles__oxmanufacturerid]", "label=Manufacturer [EN] šÄßüл");
        $this->type("editval[oxarticles__oxprice]", "5.91");
        $this->type("editval[oxarticles__oxpricea]", "1.11");
        $this->type("editval[oxarticles__oxpriceb]", "1.21");
        $this->type("editval[oxarticles__oxpricec]", "1.31");
        $this->type("editval[oxarticles__oxvat]", "4.5");
        $this->select("art_category", "label=Test category 0 [EN] šÄßüл");
        $this->type("editval[tags]", "create_delete_tag_[EN]_šäßüл");
        $this->type("editval[oxarticles__oxean]", "EAN_Äß");
        $this->type("editval[oxarticles__oxdistean]", "vendor EAN_Äß");
        $this->clickAndWaitFrame("saveArticle", "list");
        $this->clickAndWaitFrame("/descendant::input[@name='save'][2]", "list");
        $this->check("editval[oxarticles__oxactive]");
        $this->type("editval[oxarticles__oxtitle]", "create_delete product [DE]");
        $this->type("editval[oxarticles__oxshortdesc]", "create_delete short desc [DE]");
        $this->type("editval[oxarticles__oxsearchkeys]", "search [DE]");
        $this->type("editval[tags]", "create_delete_tag_[DE]");
        $this->clickAndWaitFrame("saveArticle", "list");
        $this->selectAndWait("test_editlanguage", "label=English", "editval[tags]");
        //Extended tab
        $this->frame("list");
        $this->openListItem("link=Extended", "editval[oxarticles__oxweight]");
        $this->type("editval[oxarticles__oxweight]", "1");
        $this->type("editval[oxarticles__oxlength]", "2");
        $this->type("editval[oxarticles__oxwidth]", "3");
        $this->type("editval[oxarticles__oxheight]", "4");
        $this->type("editval[oxarticles__oxunitquantity]", "5");
        $this->type("unitinput", "6");
        $this->type("editval[oxarticles__oxexturl]", "http://url.lt");
        $this->type("editval[oxarticles__oxurldesc]", "url text [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxbprice]", "7");
        $this->type("editval[oxarticles__oxtprice]", "8");
        $this->type("editval[oxarticles__oxtemplate]", "template_šÄßüл");
        $this->type("editval[oxarticles__oxquestionemail]", "contact_šÄßüл");
        $this->uncheck("/descendant::input[@name='editval[oxarticles__oxissearch]'][2]");
        $this->check("/descendant::input[@name='editval[oxarticles__oxnonmaterial]'][2]");
        $this->check("/descendant::input[@name='editval[oxarticles__oxfreeshipping]'][2]");
        $this->check("editval[oxarticles__oxblfixedprice]");
        $this->clickAndWait("save");
        $this->selectAndWait("test_editlanguage", "label=Deutsch");
        $this->type("editval[oxarticles__oxurldesc]", "url text [DE]");
        $this->clickAndWait("save");
        $this->selectAndWait("test_editlanguage", "label=English");
        // Inventory tab
        $this->openTab("Stock");
        $this->selectAndWait("test_editlanguage", "label=Deutsch");
        $this->type("editval[oxarticles__oxstock]", "10");
        $this->select("editval[oxarticles__oxstockflag]", "label=External Storehouse");
        $this->type("editval[oxarticles__oxdelivery]", "2008-01-01");
        $this->check("editval[oxarticles__oxremindactive]");
        $this->type("editval[oxarticles__oxremindamount]", "5");
        $this->type("editval[oxarticles__oxstocktext]", "in stock [DE]");
        $this->type("editval[oxarticles__oxnostocktext]", "out of stock [DE]");
        $this->clickAndWait("save");
        $this->selectAndWait("test_editlanguage", "label=English");
        $this->type("editval[oxarticles__oxstocktext]", "in stock [EN]_šÄßüл");
        $this->type("editval[oxarticles__oxnostocktext]", "out of stock [EN]_šÄßüл");
        $this->clickAndWait("save");
        $this->frame("list");
        //copying article
        $this->type("where[oxarticles][oxartnum]", "10001");
        $this->clickAndWait("submitit");
        $this->assertElementPresent("//tr[@id='row.1']/td[2]");
        $this->assertElementNotPresent("//tr[@id='row.2']/td[2]");
        $this->openTab("Main");
        $this->selectAndWait("test_editlanguage", "label=Deutsch");
        $this->assertEquals("create_delete short desc [DE]", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->clickAndWaitFrame("save", "list");
        $this->assertEquals("Deutsch", $this->getSelectedLabel("test_editlanguage"));
        $this->assertEquals("create_delete short desc [DE]", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->selectAndWait("test_editlanguage", "label=English", "editval[oxarticles__oxtitle]");
        $this->assertEquals("create_delete product [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxtitle]"));
        $this->assertEquals("10001_šÄßüл", $this->getValue("editval[oxarticles__oxartnum]"));
        $this->assertEquals("create_delete short desc [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxshortdesc]"));
        $this->assertEquals("search [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxsearchkeys]"));
        $this->assertEquals("5.91", $this->getValue("editval[oxarticles__oxprice]"));
        $this->assertEquals("1.11", $this->getValue("editval[oxarticles__oxpricea]"));
        $this->assertEquals("1.21", $this->getValue("editval[oxarticles__oxpriceb]"));
        $this->assertEquals("1.31", $this->getValue("editval[oxarticles__oxpricec]"));
        $this->assertEquals("4.5", $this->getValue("editval[oxarticles__oxvat]"));
        $this->openTab("Extended");
        $this->assertEquals("1", $this->getValue("editval[oxarticles__oxweight]"));
        $this->assertEquals("2", $this->getValue("editval[oxarticles__oxlength]"));
        $this->assertEquals("3", $this->getValue("editval[oxarticles__oxwidth]"));
        $this->assertEquals("4", $this->getValue("editval[oxarticles__oxheight]"));
        $this->assertEquals("5", $this->getValue("editval[oxarticles__oxunitquantity]"));
        $this->assertEquals("6", $this->getValue("unitinput"));
        $this->assertEquals("http://url.lt", $this->getValue("editval[oxarticles__oxexturl]"));
        $this->assertEquals("url text [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxurldesc]"));
        $this->assertEquals("7", $this->getValue("editval[oxarticles__oxbprice]"));
        $this->assertEquals("8", $this->getValue("editval[oxarticles__oxtprice]"));
        $this->assertEquals("template_šÄßüл", $this->getValue("editval[oxarticles__oxtemplate]"));
        $this->assertEquals("contact_šÄßüл", $this->getValue("editval[oxarticles__oxquestionemail]"));
        $this->assertTrue($this->isChecked("/descendant::input[@name='editval[oxarticles__oxnonmaterial]'][2]"));
        $this->assertTrue($this->isChecked("/descendant::input[@name='editval[oxarticles__oxfreeshipping]'][2]"));
        $this->assertFalse($this->isChecked("/descendant::input[@name='editval[oxarticles__oxissearch]'][2]"));
        $this->assertEquals("on", $this->getValue("editval[oxarticles__oxblfixedprice]"));
        $this->openTab("Stock");
        $this->assertEquals("10", $this->getValue("editval[oxarticles__oxstock]"));
        $this->assertEquals("2008-01-01", $this->getValue("editval[oxarticles__oxdelivery]"));
        $this->assertEquals("5", $this->getValue("editval[oxarticles__oxremindamount]"));
        $this->assertEquals("in stock [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxstocktext]"));
        $this->assertEquals("out of stock [EN]_šÄßüл", $this->getValue("editval[oxarticles__oxnostocktext]"));
        //testing if other tabs are working
        $this->checkTabs(array(
            'Selection',
            'Crosssell.',
            'Variants',
            'Pictures',
            'Review',
            'Statistics',
            'SEO',
            'Rights',
            'Mall'
        ));
        $this->frame("list");
        $this->assertEquals("create_delete product [EN]_šÄßüл", $this->getText("//tr[@id='row.1']/td[3]"));
        $this->assertEquals("create_delete product [EN]_šÄßüл", $this->getText("//tr[@id='row.2']/td[3]"));
        $this->assertElementNotPresent("//tr[@id='row.3']/td[3]");
    }

    /**
     * @param array $tabsToTest
     */
    protected function checkTabs($tabsToTest)
    {
        $this->frame('list');
        foreach ($tabsToTest as $tab) {
            if ($this->isElementPresent("//div[@class='tabs']//a[text()='$tab']")) {
                $this->openTab($tab);
            }
        }
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
