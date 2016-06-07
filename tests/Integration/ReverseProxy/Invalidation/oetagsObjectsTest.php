<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxDb;
use \oxField;
use OxidEsales\EshopEnterprise\Core\Cache\ReverseProxy\ReverseProxyBackend;
use \oxRegistry;

require_once __DIR__ . '/../oetagsCacheTestCase.php';

/**
 * Testing invalidation url generation on main objects SAVE and DELETE events.
 */
class oetagsObjectsTest extends oetagsCacheTestCase
{
    /**
     * Initialize the fixture.
     */
    protected function setUp()
    {
        if (!$this->getTestConfig()->shouldEnableVarnish()) {
            $this->markTestSkipped("Varnish must be turned on to run these tests.");
        }
        parent::setUp();
        $this->setConfigParam('blReverseProxyActive', true);
        $this->prepareSeoTable();
        $this->prepareArticle();
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $this->cleanUpTable("oxseo", "oxident");
        oxDb::getDb()->execute('TRUNCATE TABLE `oxobject2category` ');
        oxDb::getDb()->execute('TRUNCATE TABLE `oxcategories` ');

        parent::tearDown();
    }

    /**
     * TODO: Add review, media url and discount.
     *
     * @return array
     */
    public function providerObjects()
    {
        return array(
            // Article
            array(
                'oxArticle',
                'oxarticleid',
                array(
                    '/',
                    '/widget.php?.*cl=oetagstagcloudwidget.*',
                    '/widget.php?.*cl=oxwrecommendation.*',
                    '/widget.php?.*cl=oxwcategorytree.*',
                    '/index.php?.*cl=search.*',
                    '/widget.php?.*anid=oxarticleid.*cl=oxwarticlebox.*',
                    '/widget.php?.*anid=oxarticleid.*cl=oxwarticledetails.*',
                    '/seo/home.*',
                    '/seo/category.*'
                )
            ),
            // Category
            array(
                'oxCategory',
                'oxcategoryid',
                array(
                    '/',
                    '/widget.php?.*cl=oxwcategorytree.*',
                    '/seo/category.*',
                    '/seo/home.*'
                )
            ),
            // Manufacturer
            array(
                'oxManufacturer',
                'oxmanufacturerid',
                array(
                    '/',
                    '/seo/home.*',
                    '/seo/manufacturer.*',
                    '/widget.php?.*cl=oxwmanufacturerlist.*'
                )
            ),
            // Vendor
            array(
                'oxVendor',
                'oxvendorid',
                array(
                    '/seo/vendor.*',
                    '/widget.php?.*cl=oxwvendorlist.*'
                )
            ),
            // Action
            array(
                'oxActions',
                'oxactionid',
                array(
                    '/',
                    '/seo/home.*',
                    '/widget.php?.*action=oxactionid.*cl=oxwactions.*'
                )
            ),
            // Content
            array(
                'oxContent',
                'oxcontentid',
                array(
                    '/seo/content.*',
                    '/widget.php?.*cl=oxwinformationlist.*',
                    '/widget.php?.*cl=oxwcategorytree.*'
                )
            ),
            // Country
            array(
                'oxCountry',
                'oxcountryid',
                array(
                    '/seo/register.*'
                )
            ),
            // Links
            array(
                'oxLinks',
                'oxlinkid',
                array(
                    '/seo/links.*'
                )
            ),
            // News
            array(
                'oxNews',
                'oxnewid',
                array(
                    '/',
                    '/seo/new.*',
                    '/seo/home.*'
                )
            ),
            // Discount
            array(
                'oxDiscount',
                'oxdiscountid',
                array(
                    '/',
                    '/index.php?.*cl=search.*',
                    '/seo/category.*',
                    '/seo/home.*',
                    '/seo/manufacturer.*',
                    '/seo/vendor.*',
                )
            ),
            // SubShop
            array(
                'oxShop',
                '2',
                array(
                    '/',
                    '/.*shp=2.*',
                    '/index.php?.*cl=mallstart.*',
                    '/seo/home.*'
                )
            ),
        );
    }

    /**
     * Test case on SAVE event
     *
     * @dataProvider providerObjects
     *
     * @param string $objectName
     * @param string $objectId
     * @param array  $expectedResult
     */
    public function testOnSave($objectName, $objectId, $expectedResult)
    {
        $reverseProxyBackend = $this->getReverseProxyCacheBackend();

        $object = oxNew($objectName);
        $object->setId($objectId);
        $object->save();

        $urls = $reverseProxyBackend->getUrlPool();
        sort($urls);
        sort($expectedResult);

        $this->assertEquals($expectedResult, $urls);
        $this->assertFalse($reverseProxyBackend->isFlushSet());
    }

    /**
     * Test case on DELETE event.
     *
     * @dataProvider providerObjects
     *
     * @param string $objectName
     * @param string $objectId
     * @param array  $expectedResult
     */
    public function testOnDelete($objectName, $objectId, $expectedResult)
    {
        $reverseProxyBackend = $this->getReverseProxyCacheBackend();

        $object = oxNew($objectName);
        $object->setId($objectId);
        $object->delete();

        $urls = $reverseProxyBackend->getUrlPool();
        sort($urls);
        sort($expectedResult);

        $this->assertEquals($expectedResult, $urls);
        $this->assertFalse($reverseProxyBackend->isFlushSet());
    }

    /**
     * Adds records needed for testing to seo table.
     */
    protected function prepareSeoTable()
    {
        $database = oxDb::getDb();
        $database->execute('TRUNCATE TABLE `oxseo`');
        $sqlTail = ", `oxlang`=0, `oxshopid`=1";
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/page1', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page1', `oxobjectid`='page1', `oxident`='_test1'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/page2', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page2', `oxobjectid`='page2', `oxident`='_test2'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid', `oxident`='_test3'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/content', `oxtype` = 'oxcontent', `oxobjectid`='oxcontentid', `oxident`='_test4'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid', `oxident`='_test5'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/manufacturer', `oxtype` = 'oxmanufacturer', `oxobjectid`='oxmanufacturerid', `oxident`='_test6'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/vendor', `oxtype` = 'oxvendor', `oxobjectid`='oxvendorid', `oxident`='_test7'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid2', `oxident`='_test8'" . $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid2', `oxident`='_test9'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/register', `oxstdurl` = 'index.php?cl=register', `oxtype` = 'static', `oxobjectid`='registerid', `oxident`='_test10'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/new', `oxstdurl` = 'index.php?cl=news', `oxtype` = 'static', `oxobjectid`='newsid', `oxident`='_test11'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/home', `oxstdurl` = 'index.php?cl=start', `oxtype` = 'static', `oxobjectid`='startid', `oxident`='_test12'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/links', `oxstdurl` = 'index.php?cl=links', `oxtype` = 'static', `oxobjectid`='linksid', `oxident`='_test13'". $sqlTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/recommlist', `oxstdurl` = 'index.php?cl=recommlist', `oxtype` = 'dynamic', `oxobjectid`='_testId', `oxident`='_test15', `oxlang`=0, `oxshopid`=1";
        $database->execute($query);
    }

    /**
     * Adds articles to database.
     */
    protected function prepareArticle()
    {
        $priceCategory = oxNew('oxCategory');
        $priceCategory->setId('oxcategoryid2');
        $priceCategory->oxcategories__oxparentid = new oxField('oxrootid');
        $priceCategory->oxcategories__oxleft = new oxField('1');
        $priceCategory->oxcategories__oxright = new oxField('2');
        $priceCategory->oxcategories__oxrootid = new oxField('_testCat');
        $priceCategory->oxcategories__oxactive = new oxField(1);
        $priceCategory->oxcategories__oxshopid = new oxField(1);
        $priceCategory->save();

        $new = oxNew("oxBase");
        $new->init("oxobject2category");
        $new->oxobject2category__oxtime     = new oxField(0);
        $new->oxobject2category__oxobjectid = new oxField('oxarticleid');
        $new->oxobject2category__oxcatnid   = new oxField('oxcategoryid2');
        $new->oxobject2category__oxshopid   = new oxField(1);
        $new->save();
    }

    /**
     * @return ReverseProxyBackend
     */
    protected function getReverseProxyCacheBackend()
    {
        $reverseProxyBackend = $this->getMock('oxReverseProxyBackend', array('isActive', 'execute'));
        $reverseProxyBackend->expects($this->any())->method("isActive")->will($this->returnValue(true));
        $reverseProxyBackend->expects($this->any())->method("execute")->will($this->returnValue(true));

        oxRegistry::set('oxReverseProxyBackend', $reverseProxyBackend);

        return $reverseProxyBackend;
    }
}
