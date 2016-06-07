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
class CacheTest extends oetagsCacheTestCase
{
    /** @var string Test name. */
    protected $testName = 'testPageCache';

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

        $page->execute();
        $page->execute();
        $page->execute();

        $this->assertTrue($page->isTextPresent('Reverse proxy is active.'), 'Reverse proxy marked as inactive.');
    }

    /**
     * Module data provider.
     *
     * @return array
     */
    public function providerPageCacheGet()
    {
        $baseUrl = $this->getConfigParam('sShopURL');
        $baseSeparator = (substr($baseUrl, -1) != '/') ? "/" : "";
        $baseUrl = $baseUrl . $baseSeparator;

        $categoryWithSubCatUrl1          = $baseUrl . "Eco-Fashion/Woman/";
        $categoryWithoutSubCatUrl1       = $baseUrl . "Eco-Fashion/Woman/Jeans/";
        $detailsUrl1                     = $baseUrl . "Eco-Fashion/Woman/Jeans/Kuyichi-Jeans-Anna.html";
        $categoryWithSubCatUrl2          = $baseUrl . "Fuer-Sie/";
        $categoryWithoutSubCatUrl2       = $baseUrl . "Fuer-Sie/Sport/";
        $detailsUrl2                     = $baseUrl . "Fuer-Sie/Sport/Badetuch-GAME-BACKGAMMON.html";
        $compareUrl                      = $baseUrl . "en/my-product-comparison/";
        $accountUrl                      = $baseUrl . "en/my-account/";
        $orderHistoryUrl                 = $baseUrl . "en/order-history/";
        $myPasswordUrl                   = $baseUrl . "en/my-password/";
        $newsLetterUrl                   = $baseUrl . "index.php?lang=1&cl=account_newsletter";
        $myAddressUrl                    = $baseUrl . "en/my-address/";
        $comparisonUrl                   = $baseUrl . "en/my-product-comparison/";
        $wishListUrl                     = $baseUrl . "en/my-wish-list/";
        $giftRegistryUrl                 = $baseUrl . "en/my-gift-registry/";
        $listManiaUrl                    = $baseUrl . "en/my-listmania-list/";
        $notFoundPage                    = $baseUrl . "/zuzu/not_existing_page.html";

        // cached widgets
        $pageWidgets = new oetagsPageWidgets();
        $cachedStartElements   = $pageWidgets->startWidgets;
        $cachedListElements    = $pageWidgets->listWidgets;
        $cachedDetailElements  = $pageWidgets->detailsWidgets;
        $cachedCompareElements = $pageWidgets->compareWidgets;
        $cachedAccountElements = $pageWidgets->accountWidgets;
        $cached404Elements     = $pageWidgets->notFoundWidgets;

        // additional cached elements
        $cachedStartElements          = array_merge($cachedStartElements, array( "start" ));
        $cachedListElements           = array_merge($cachedListElements, array( "alist" ));
        $cachedDetailElements         = array_merge($cachedDetailElements, array( "details" ));

        // non-cached elements
        $unCachedStartElements        = array();
        $unCachedListElements         = array();
        $unCachedDetailElements       = array();
        $unCachedCompareElements      = array("compare");
        $unCachedAccountElements      = array("account");
        $unCachedOrderHistoryElements = array("account_order");
        $unCachedMyPasswordElements   = array("account_password");
        $unCachedNewsLetterElements   = array("account_newsletter");
        $unCachedMyAddressElements    = array("account_user");
        $unCachedComparisonElements   = array("compare");
        $unCachedWishListElements    = array("account_noticelist");
        $unCachedGiftRegistryElements = array("account_wishlist");
        $unCachedListManiaElements    = array("account_recommlist");
        $unCached404Elements          = array();

        // exceptions, these urls don't have some of the widgets
        $cachedListElements1          = array_diff($cachedListElements, array( "oxwarticlebox" ));
        $cachedDetailElements1        = array_diff($cachedDetailElements, array( "oxwarticlebox" ));

        // exclude non-cached elements
        $cachedStartElements          = array_diff($cachedStartElements, $unCachedStartElements);
        $cachedListElements           = array_diff($cachedListElements, $unCachedListElements);
        $cachedListElements1          = array_diff($cachedListElements1, $unCachedListElements);
        $cachedDetailElements         = array_diff($cachedDetailElements, $unCachedDetailElements);
        $cachedDetailElements1        = array_diff($cachedDetailElements1, $unCachedDetailElements);
        $cachedAccountElements        = array_diff($cachedAccountElements, $unCachedAccountElements);

        return array(
                    array($baseUrl,                         $cachedStartElements,   $unCachedStartElements),
                    array($categoryWithSubCatUrl1,          $cachedListElements1,   $unCachedListElements),
                    array($categoryWithoutSubCatUrl1,       $cachedListElements,    $unCachedListElements),
                    array($detailsUrl1,                     $cachedDetailElements1, $unCachedDetailElements),
                    array($categoryWithSubCatUrl2,          $cachedListElements,    $unCachedListElements),
                    array($categoryWithoutSubCatUrl2,       $cachedListElements,    $unCachedListElements),
                    array($detailsUrl2,                     $cachedDetailElements,  $unCachedDetailElements),
                    array($compareUrl,                      $cachedCompareElements, $unCachedCompareElements),
                    array($accountUrl,                      $cachedAccountElements, $unCachedAccountElements),
                    array($orderHistoryUrl,                 $cachedAccountElements, $unCachedOrderHistoryElements),
                    array($myPasswordUrl,                   $cachedAccountElements, $unCachedMyPasswordElements),
                    array($newsLetterUrl,                   $cachedAccountElements, $unCachedNewsLetterElements),
                    array($myAddressUrl,                    $cachedAccountElements, $unCachedMyAddressElements),
                    array($comparisonUrl,                   $cachedAccountElements, $unCachedComparisonElements),
                    array($wishListUrl,                     $cachedAccountElements, $unCachedWishListElements),
                    array($giftRegistryUrl,                 $cachedAccountElements, $unCachedGiftRegistryElements),
                    array($listManiaUrl,                    $cachedAccountElements, $unCachedListManiaElements),
                    array($notFoundPage,                    $cached404Elements,     $unCached404Elements),
        );
    }

    /**
     * Check if elements are cached when called with GET.
     *
     * @TODO investigate why need two clicks to cache page.
     *
     * @param string $pageUrl           Url off page to call.
     * @param array  $cachedElements    Elements to check if cached id's.
     * @param array  $notCachedElements Elements to check if not cached id's.
     *
     * @dataProvider providerPageCacheGet
     */
    public function testPageCacheGet($pageUrl, $cachedElements, $notCachedElements)
    {
        $page = $this->createReverseProxyPage($pageUrl);

        // Worm up cache to generate environment key.
        $page->execute();
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
     * Module data provider.
     *
     * @return array
     */
    public function providerPageCachePost()
    {
        $pageWidgets = new oetagsPageWidgets();

        $baseUrl = $this->getConfigParam('sShopURL');
        $baseSeparator = (substr($baseUrl, -1) != '/') ? "/" : "";
        $baseUrl = $baseUrl . $baseSeparator;

        $unCachedStartElements[]  = "start";
        $unCachedStartElements = array_merge($unCachedStartElements, $pageWidgets->startWidgets);

        return array(
            array($baseUrl, array(), $unCachedStartElements),
        );
    }

    /**
     * Check if elements are NOT cached when called with POST.
     *
     * @param string $pageUrl           Url off page to call.
     * @param array  $cachedElements    Elements to check if cached id's.
     * @param array  $notCachedElements Elements to check if not cached id's.
     *
     * @dataProvider providerPageCachePost
     */
    public function testPageCachePost($pageUrl, $cachedElements, $notCachedElements)
    {
        $pageGet = $this->createReverseProxyPage($pageUrl);

        // Worm up cache to generate environment key.
        $pageGet->execute();

        $pageGet->execute();

        $params = array("lang" => 1);
        $pagePost = $this->createReverseProxyPage($pageUrl, $params);
        $pagePost->execute();

        $this->checkIfCached($cachedElements, $pageGet, $pagePost);
        $this->checkIfNotCached($notCachedElements, $pageGet, $pagePost);

        // Cache time must be same as in first get. Lower than POST.
        $pageGet->execute();

        $this->checkIfCached($cachedElements, $pageGet, $pagePost);
        $this->checkIfNotCached($notCachedElements, $pageGet, $pagePost);
    }
}
