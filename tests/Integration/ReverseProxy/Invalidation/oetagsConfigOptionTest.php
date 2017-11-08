<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\TestingLibrary\UnitTestCase;

require_once __DIR__ . '/../oetagsCacheTestCase.php';

/**
 *
 */
class oetagsConfigOptionTest extends oetagsCacheTestCase
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
     * Test case for config option which invalidates start page
     *
     */
    public function testInvalidation()
    {
        $configOptionName = 'oetagsShowTags';
        $expectedResult = array('/',
                                '/seo/home.*',
                                '/seo/article.*',
                                '/seo/category.*',
                                '/seo/manufacturer.*',
                                '/seo/vendor.*');

        $reverseProxyBackend = oxNew('oxReverseProxyBackend');
        oxRegistry::set('oxReverseProxyBackend', $reverseProxyBackend);

        // Pretend that there is reverse proxy header.
        $reverseProxyBackend->setReverseProxyCapableDoEsi(true);

        $config = oxNew('oxconfig');
        $config->executeDependencyEvent($configOptionName);

        $urls = $reverseProxyBackend->getUrlPool();

        sort($urls);
        sort($expectedResult);

        $this->assertFalse($reverseProxyBackend->isFlushSet());
        $this->assertEquals($urls, $expectedResult);
    }
    /**
     * Adds records needed for testing to seo table.
     */
    protected function prepareSeoTable()
    {
        $database = oxDb::getDb();
        $database->execute('TRUNCATE TABLE `oxseo`');
        $sqlTail = ", `oxlang`=0, `oxshopid`=1";
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/page1', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page1', `oxobjectid`='page1', `oxident`=1" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/page2', `oxtype` = 'static', `oxstdurl` = 'index.php?cl=page2', `oxobjectid`='page2', `oxident`=2" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid', `oxident`=3" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/content', `oxtype` = 'oxcontent', `oxobjectid`='oxcontentid', `oxident`=4" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid', `oxident`=5". $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/manufacturer', `oxtype` = 'oxmanufacturer', `oxobjectid`='oxmanufacturerid', `oxident`=6" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/vendor', `oxtype` = 'oxvendor', `oxobjectid`='oxvendorid', `oxident`=7" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/article', `oxtype` = 'oxarticle', `oxobjectid`='oxarticleid2', `oxident`=8" . $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/category', `oxtype` = 'oxcategory', `oxobjectid`='oxcategoryid2', `oxident`=9". $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/register', `oxstdurl` = 'index.php?cl=register', `oxtype` = 'static', `oxobjectid`='registerid', `oxident`=10". $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/new', `oxstdurl` = 'index.php?cl=news', `oxtype` = 'static', `oxobjectid`='newsid', `oxident`=11". $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/home', `oxstdurl` = 'index.php?cl=start', `oxtype` = 'static', `oxobjectid`='startid', `oxident`=12". $sqlTail;
        $database->execute($sql);
        $sql = "INSERT INTO `oxseo` SET `oxseourl` = 'seo/links', `oxstdurl` = 'index.php?cl=links', `oxtype` = 'static', `oxobjectid`='linksid', `oxident`=13". $sqlTail;
        $database->execute($sql);
    }
}
