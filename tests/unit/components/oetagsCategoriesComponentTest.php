<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

class CmpCategoriesTest extends \OxidTestCase
{

    public static $oCL = null;

    public function tearDown()
    {
        self::$oCL = null;
        parent::tearDown();
    }

    public function testGetActCatLoadDefault()
    {
        $oActShop = new stdClass;
        $oActShop->oxshops__oxdefcat = new oxField('default category');

        $oCfg = $this->getMock('stdClass', array('getActiveShop'));
        $oCfg->expects($this->once())->method('getActiveShop')->will($this->returnValue($oActShop));

        $o = $this->getMock('oxcmp_categories', array('getConfig', 'getProduct', '_addAdditionalParams'));
        $o->expects($this->once())->method('getConfig')->will($this->returnValue($oCfg));
        $o->expects($this->once())->method('getProduct')->will($this->returnValue(null));
        $o->expects($this->never())->method('_addAdditionalParams');

        $this->setRequestParameter('mnid', null);
        $this->setRequestParameter('searchtag', null);
        $this->setRequestParameter('cnid', null);

        $this->assertEquals('default category', $o->UNITgetActCat());
    }

    public function testGetActCatLoadDefaultoxroot()
    {
        $oActShop = new stdClass;
        $oActShop->oxshops__oxdefcat = new oxField('oxrootid');

        $oCfg = $this->getMock('stdClass', array('getActiveShop'));
        $oCfg->expects($this->once())->method('getActiveShop')->will($this->returnValue($oActShop));

        $o = $this->getMock('oxcmp_categories', array('getConfig', 'getProduct', '_addAdditionalParams'));
        $o->expects($this->once())->method('getConfig')->will($this->returnValue($oCfg));
        $o->expects($this->once())->method('getProduct')->will($this->returnValue(null));
        $o->expects($this->never())->method('_addAdditionalParams');

        $this->setRequestParameter('mnid', null);
        $this->setRequestParameter('searchtag', null);
        $this->setRequestParameter('cnid', null);

        $this->assertSame(null, $o->UNITgetActCat());
    }

    public function testGetActCatWithProduct()
    {
        $o = $this->getMock('oxcmp_categories', array('getProduct', '_addAdditionalParams'));
        $o->expects($this->once())->method('getProduct')->will($this->returnValue("product"));
        $o->expects($this->once())->method('_addAdditionalParams')->with(
            $this->equalTo("product"),
            $this->equalTo(null),
            $this->equalTo('mnid')
        );

        $this->setRequestParameter('mnid', 'mnid');
        $this->setRequestParameter('searchtag', 'searchtag');
        $this->setRequestParameter('cnid', 'cnid');

        $this->assertSame(null, $o->UNITgetActCat());
    }

    public function testGetActCatWithProductAltBranches()
    {
        $o = $this->getMock('oxcmp_categories', array('getProduct', '_addAdditionalParams'));
        $o->expects($this->once())->method('getProduct')->will($this->returnValue("product"));
        $o->expects($this->once())->method('_addAdditionalParams')->with(
            $this->equalTo("product"),
            $this->equalTo("cnid"),
            $this->equalTo('')
        );

        $this->setRequestParameter('mnid', '');
        $this->setRequestParameter('searchtag', 'searchtag');
        $this->setRequestParameter('cnid', 'cnid');

        $this->assertSame(null, $o->UNITgetActCat());
    }

    /**
     * Testing oxcmp_categories::_addAdditionalParams()
     *
     * @return null
     */
    public function testAddAdditionalParamsTag()
    {
        $this->setRequestParameter("searchparam", null);
        $this->setRequestParameter("searchcnid", null);
        $this->setRequestParameter("searchvendor", null);
        $this->setRequestParameter("searchmanufacturer", null);
        $this->setRequestParameter("searchtag", 'searchtag');
        $this->setRequestParameter("listtype", null);

        $parent = $this->getMock("oxView", array("setListType", "setCategoryId"));
        $parent->expects($this->once())->method("setListType")->with($this->equalTo('tag'));
        $parent->expects($this->once())->method("setCategoryId")->with($this->equalTo("testCatId"));

        $product = $this->getMock("oxArticle", array("getVendorId", "getManufacturerId"));
        $product->expects($this->any())->method("getVendorId")->will($this->returnValue("_testVendorId"));
        $product->expects($this->any())->method("getManufacturerId")->will($this->returnValue("_testManId"));

        $component = oxNew(\OxidEsales\Eshop\Application\Component\CategoriesComponent::class);
        $component->setParent($parent);
        $this->assertEquals("testCatId", $component->_addAdditionalParams($product, "testCatId", "testManId", "testVendorId"));
    }
}
