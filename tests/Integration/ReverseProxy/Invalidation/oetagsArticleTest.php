<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxDb;
use \oxField;
use OxidEsales\EshopEnterprise\Core\Cache\ReverseProxy\ReverseProxyBackend;
use OxidEsales\TestingLibrary\UnitTestCase;
use \oxRegistry;

require_once __DIR__ . '/../oetagsCacheTestCase.php';

/**
 * Testing invalidation url generation on additional article events.
 */
class oetagsArticleTest extends oetagsCacheTestCase
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
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown()
    {
        $this->cleanUpTable("oxseo", "oxident");
        $this->cleanUpTable("oxarticles", "oxid");

        oxDb::getDb()->execute('TRUNCATE TABLE `oxobject2category` ');
        oxDb::getDb()->execute('TRUNCATE TABLE `oxcategories` ');

        parent::tearDown();
    }

    /**
     * Data provider for testOnArticleStockChanges.
     *
     * @return array
     */
    public function providerOnArticleStockChanges()
    {
        $urlPoolNoFlush = array();

        $urlPoolFlush = array(
            '/widget.php?.*anid=_testArticle1.*cl=oxwarticlebox.*',
            '/widget.php?.*anid=_testArticle1.*cl=oxwarticledetails.*',
        );

        $urlPoolFlushVisibilityChanged = array(
            '/widget.php?.*anid=_testArticle1.*cl=oxwarticlebox.*',
            '/widget.php?.*anid=_testArticle1.*cl=oxwarticledetails.*',
            '/widget.php?.*cl=oetagstagcloudwidget.*',
            '/widget.php?.*cl=oxwrecommendation.*',
            '/',
            '/seo/home.*',
            '/index.php?.*cl=search.*',
            '/seo/recommlist.*',
            '/index.php?.*cl=wishlist.*',
        );

        // blUseStock, oxArticle.oxactive, oxArticle.oxstock, oxArticle.oxstockflag, variable reduce stock amount, expected url pool
        return array(
            array( false, 0, 100, 2, 1, $urlPoolNoFlush),
            array(false, 0, 100, 2, 99, $urlPoolNoFlush),
            array(false, 0, 100, 2, 100, $urlPoolNoFlush),
            array(false, 1, 100, 2, 1, $urlPoolNoFlush),
            array(false, 1, 100, 2, 99, $urlPoolNoFlush),
            array(false, 1, 100, 2, 100, $urlPoolNoFlush),
            array(true, 0, 100, 2, 1, $urlPoolNoFlush),
            array(true, 0, 100, 2, 99, $urlPoolFlushVisibilityChanged),
            array(true, 0, 100, 2, 100, $urlPoolFlushVisibilityChanged),
            array(true, 1, 100, 2, 1, $urlPoolNoFlush),
            array(true, 1, 100, 2, 99, $urlPoolFlush),
            array(true, 1, 100, 2, 100, $urlPoolFlushVisibilityChanged),
        );
    }

    /**
     * Test case on Stock changes (for articles) event.
     *
     * @dataProvider providerOnArticleStockChanges
     *
     * @param bool   $shouldUseStock
     * @param string $isActive
     * @param int    $amountInStock
     * @param int    $stockFlag
     * @param int    $reduceStock
     * @param array  $expectedUrlPool
     */
    public function testOnArticleStockChanges($shouldUseStock, $isActive, $amountInStock, $stockFlag, $reduceStock, $expectedUrlPool)
    {
        $this->getConfig()->setConfigParam('blUseStock', $shouldUseStock);

        $this->prepareArticle($isActive, $amountInStock, $stockFlag);

        $reverseProxyBackend = $this->getReverseProxyCacheBackend();

        $object = oxNew('oxArticle');
        $object->load('_testArticle1');

        $object->reduceStock($reduceStock);

        $urls = $reverseProxyBackend->getUrlPool();
        sort($urls);
        sort($expectedUrlPool);

        $this->assertEquals($expectedUrlPool, $urls);
        $this->assertFalse($reverseProxyBackend->isFlushSet());
    }

    public function testOnArticleChangesSortableAttribute()
    {
        $this->prepareArticle(1, 1, 2);
        $this->addArticleToCategory('_testArticle1', 'oxcategoryid2');

        $reverseProxyBackend = $this->getReverseProxyCacheBackend();

        $object = oxNew('oxArticle');
        $object->load('_testArticle1');
        $object->oxarticles__oxtitle = new oxField('newTitle');
        $object->save();

        $urls = $reverseProxyBackend->getUrlPool();

        $this->assertTrue(in_array('/seo/category.*', $urls), 'Category should be flushed');
        $this->assertFalse($reverseProxyBackend->isFlushSet());
    }

    public function testOnArticleChangesNotSortableAttribute()
    {
        $this->prepareArticle(1, 1, 2);
        $this->addArticleToCategory('_testArticle1', 'oxcategoryid2');

        $this->getConfig()->setConfigParam('aSortCols', '');
        $reverseProxyBackend = $this->getReverseProxyCacheBackend();

        $object = oxNew('oxArticle');
        $object->load('_testArticle1');
        $object->oxarticles__oxtitle = new oxField('newTitle');
        $object->save();

        $urls = $reverseProxyBackend->getUrlPool();

        $this->assertFalse(in_array('/seo/category.*', $urls), 'Category should not be flushed');
        $this->assertFalse($reverseProxyBackend->isFlushSet());
    }

    protected function addArticleToCategory($articleId, $categoryId)
    {
        $priceCategory = oxNew('oxCategory');
        $priceCategory->setId($categoryId);
        $priceCategory->oxcategories__oxparentid = new oxField('oxrootid');
        $priceCategory->oxcategories__oxleft = new oxField('1');
        $priceCategory->oxcategories__oxright = new oxField('2');
        $priceCategory->oxcategories__oxrootid = new oxField('_testCat');
        $priceCategory->oxcategories__oxactive = new oxField(1);
        $priceCategory->oxcategories__oxshopid = new oxField(1);
        $priceCategory->save();

        $new = oxNew("oxbase");
        $new->init("oxobject2category");
        $new->oxobject2category__oxtime     = new oxField(0);
        $new->oxobject2category__oxobjectid = new oxField($articleId);
        $new->oxobject2category__oxcatnid   = new oxField($categoryId);
        $new->oxobject2category__oxshopid   = new oxField(1);
        $new->save();
    }

    /**
     * @return ReverseProxyBackend
     */
    protected function getReverseProxyCacheBackend()
    {
        $reverseProxyBackend = $this->getMock('oxReverseProxyBackend', array('isActive'));
        $reverseProxyBackend->expects($this->any())->method("isActive")->will($this->returnValue(true));
        oxRegistry::set('oxReverseProxyBackend', $reverseProxyBackend);

        return $reverseProxyBackend;
    }

    /**
     * Adds records needed for testing to seo table.
     */
    protected function prepareSeoTable()
    {
        $database = oxDb::getDb();
        $database->execute('TRUNCATE TABLE `oxseo`');
        $queryTail = ", `oxlang`=0, `oxshopid`=1";
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid', `oxident`='_test3'" . $queryTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid', `oxident`='_test5'". $queryTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid2', `oxident`='_test8'" . $queryTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/home', `oxstdurl` = 'index.php?cl=start', `oxtype` = 'static', `oxobjectid`='startid', `oxident`='_test12'". $queryTail;
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/recommlist', `oxstdurl` = 'index.php?cl=recommlist', `oxtype` = 'dynamic', `oxobjectid`='_testId', `oxident`='_test15', `oxlang`=0, `oxshopid`=1";
        $database->execute($query);
        $query = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid2', `oxident`='_test9'". $queryTail;
        $database->execute($query);
    }

    /**
     * Adds article to database.
     *
     * @param string $isActive
     * @param int    $amountInStock
     * @param int    $stockFlag
     */
    protected function prepareArticle($isActive, $amountInStock, $stockFlag)
    {
        $shopId = $this->getShopId();
        $this->addToDatabase(
            "INSERT INTO oxarticles (oxid, oxshopid, oxprice, oxactive, oxstock, oxstockflag)
             VALUES ('_testArticle1', '{$shopId}', 10.00, '{$isActive}', '{$amountInStock}', '{$stockFlag}')",
            'oxarticles',
            array($shopId)
        );
    }
}
