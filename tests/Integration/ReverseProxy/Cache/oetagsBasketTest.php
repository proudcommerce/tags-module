<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/oetagsPageWidgets.php';
require_once __DIR__ . '/../oetagsCacheTestCase.php';

/**
 * Tests if reverse proxy is working
 *
 */
class oetagsBasketTest extends oetagsCacheTestCase
{
    /**
     * @var string Test name.
     */
    protected $testName = 'testAddToBasket';

    /**
     * Check if shop has reverse proxy active.
     */
    public function testIsReverseProxyActive()
    {
        $rpBackend = oxRegistry::get('oxReverseProxyBackend');
        $rpBackend->setFlush();
        $rpBackend->execute();

        $page = $this->createReverseProxyPage($this->getShopUrl());
        // Delete cookie file in this case as data providers are executed before all tests.
        $page->deleteCookies();

        // Add to basket in this place. As we will need basket in all test cases.
        $this->addArticleToBasket();

        $page->execute();
        $this->assertTrue($page->isTextPresent('Reverse proxy is active.'), 'Reverse proxy marked as inactive.');
    }

    /**
     * AddToBasket test data provider.
     *
     * @return array
     */
    public function providerAddToBasket()
    {
        $pageWidgets = new oetagsPageWidgets();

        $baseUrl = $this->getConfigParam('sShopURL');
        $baseSeparator = (substr($baseUrl, -1) != '/') ? "/" : "";
        $baseUrl = $baseUrl . $baseSeparator;

        $categoryWithSubCatUrl1    = $baseUrl . "Eco-Fashion/Woman/";
        $categoryWithoutSubCatUrl1 = $baseUrl . "Eco-Fashion/Woman/Jeans/";
        $detailsUrl1               = $baseUrl . "Eco-Fashion/Woman/Jeans/Kuyichi-Jeans-Anna.html";
        $categoryWithSubCatUrl2    = $baseUrl . "Fuer-Sie/";
        $categoryWithoutSubCatUrl2 = $baseUrl . "Fuer-Sie/Sport/";
        $detailsUrl2               = $baseUrl . "Fuer-Sie/Sport/Badetuch-GAME-BACKGAMMON.html";

        // cached widgets
        $cachedStartElements  = $pageWidgets->startWidgets;
        $cachedListElements   = $pageWidgets->listWidgets;
        $cachedDetailElements = $pageWidgets->detailsWidgets;

        // additional cached elements
        $cachedStartElements  = array_merge($cachedStartElements, array("start"));
        $cachedListElements   = array_merge($cachedListElements, array("alist"));
        $cachedDetailElements = array_merge($cachedDetailElements, array("details"));

        // non-cached elements
        $nonCachedElements       = array("oxwminibasket");
        $nonCachedStartElements  = $nonCachedElements;
        $nonCachedListElements   = $nonCachedElements;
        $nonCachedDetailElements = $nonCachedElements;

        // exceptions, these urls don't have some of the widgets
        $cachedListElements1   = array_diff($cachedListElements, array("oxwarticlebox"));
        $cachedDetailElements1 = array_diff($cachedDetailElements, array("oxwarticlebox"));

        // exclude non-cached elements
        $cachedStartElements   = array_diff($cachedStartElements, $nonCachedStartElements);
        $cachedListElements    = array_diff($cachedListElements, $nonCachedListElements);
        $cachedListElements1   = array_diff($cachedListElements1, $nonCachedListElements);
        $cachedDetailElements  = array_diff($cachedDetailElements, $nonCachedDetailElements);
        $cachedDetailElements1 = array_diff($cachedDetailElements1, $nonCachedDetailElements);

        return array(
            array($baseUrl,                   $cachedStartElements,   $nonCachedStartElements),
            array($categoryWithSubCatUrl1,    $cachedListElements1,   $nonCachedListElements),
            array($categoryWithoutSubCatUrl1, $cachedListElements,    $nonCachedListElements),
            array($detailsUrl1,               $cachedDetailElements1, $nonCachedDetailElements),
            array($categoryWithSubCatUrl2,    $cachedListElements,    $nonCachedListElements),
            array($categoryWithoutSubCatUrl2, $cachedListElements,    $nonCachedListElements),
            array($detailsUrl2,               $cachedDetailElements,  $nonCachedDetailElements),
        );
    }

    /**
     * Check if elements cached after adding to basket.
     *
     * @TODO check why only third call taken from cache. Maybe because language changes.
     *
     * @param string $pageUrl           Url off page to call.
     * @param array  $cachedElements    Elements to check if cached id's.
     * @param array  $notCachedElements Elements to check if not cached id's.
     *
     * @dataProvider providerAddToBasket
     */
    public function testAddToBasket($pageUrl, $cachedElements, $notCachedElements)
    {
        $page = $this->createReverseProxyPage($this->getShopUrl());
        $page->execute();

        $page = $this->createReverseProxyPage($pageUrl);
        $page->execute();
        $page->execute();

        $page1 = $this->createReverseProxyPage($pageUrl);
        $page1->execute();

        $page2 = $this->createReverseProxyPage($pageUrl);
        $page2->execute();

        $this->checkIfCached($cachedElements, $page1, $page2);
        $this->checkIfNotCached($notCachedElements, $page1, $page2);
    }

    /**
     * Add some article to basket.
     */
    protected function addArticleToBasket()
    {
        $pageUrl = $this->getShopUrl();

        $params = array(
            "cl"   => "alist",
            "fnc"  => "tobasket",
            "lang" => 0,
            "cnid" => "30e44ab8338d7bf06.79655612",
            "aid"  => 1436,
            "anid" => 1436,
            "am"   => 1,
        );

        $page = $this->createReverseProxyPage($pageUrl, $params);
        $page->execute();

        $page->saveToFile($this->formPageFileName($pageUrl, 'addArticleToBasket'), $this->getTestName());
    }
}
