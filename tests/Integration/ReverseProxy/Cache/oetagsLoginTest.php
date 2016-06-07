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
class LoginTest extends oetagsCacheTestCase
{
    /** @var string Test name. */
    protected $testName = 'testLogin';

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

        // Login in this place. As we will need to be login in all test cases.
        $this->login();

        $page = $this->createReverseProxyPage($this->getShopUrl());
        $page->execute();

        $this->assertTrue($page->isTextPresent('Reverse proxy is active.'), 'Reverse proxy marked as inactive.');
    }

    /**
     * Module data provider.
     *
     * @return array
     */
    public function providerLogin()
    {
        $pageWidgets = new oetagsPageWidgets();

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

        // cached widgets
        $cachedStartElements   = $pageWidgets->startWidgets;
        $cachedListElements    = $pageWidgets->listWidgets;
        $cachedDetailElements  = $pageWidgets->detailsWidgets;
        $cachedCompareElements = $pageWidgets->compareWidgets;
        $cachedAccountElements = $pageWidgets->accountWidgets;

        // additional cached elements
        $cachedListElements           = array_merge($cachedListElements, array());
        $cachedDetailElements         = array_merge($cachedDetailElements, array());

        // non-cached elements
        $unCachedElements             = array("oxwservicemenu");
        $unCachedStartElements        = array_merge($unCachedElements, array( "start", "oxwarticlebox" ));
        $unCachedListElements         = array_merge($unCachedElements, array( "alist", "oxwarticlebox" ));
        $unCachedDetailElements       = array_merge($unCachedElements, array( "details", "oxwarticledetails", "oxwarticlebox", "oxwreview", "oxwrating" ));
        $unCachedCompareElements      = array_merge($unCachedElements, array( "compare" ));
        $unCachedAccountElements      = array_merge($unCachedElements, array( "account" ));
        $unCachedOrderHistoryElements = array_merge($unCachedElements, array( "account_order" ));
        $unCachedMyPasswordElements   = array_merge($unCachedElements, array( "account_password" ));
        $unCachedNewsLetterElements   = array_merge($unCachedElements, array( "account_newsletter" ));
        $unCachedMyAddressElements    = array_merge($unCachedElements, array( "account_user" ));
        $unCachedComparisonElements   = array_merge($unCachedElements, array( "compare" ));
        $unCachedWishListElements    = array_merge($unCachedElements, array( "account_noticelist" ));
        $unCachedGiftRegistryElements = array_merge($unCachedElements, array( "account_wishlist" ));
        $unCachedListManiaElements    = array_merge($unCachedElements, array( "account_recommlist" ));

        // exceptions, these urls don't have some of the widgets
        $cachedListElements1          = array_diff($cachedListElements, array( "oxwarticlebox" ));
        $cachedDetailElements1        = array_diff($cachedDetailElements, array( "oxwarticlebox" ));
        $unCachedListElements1        = array_diff($unCachedListElements, array( "oxwarticlebox" ));
        $unCachedDetailElements1      = array_diff($unCachedDetailElements, array( "oxwarticlebox" ));

        // exclude non-cached elements
        $cachedStartElements        = array_diff($cachedStartElements, $unCachedStartElements);
        $cachedListElements         = array_diff($cachedListElements, $unCachedListElements);
        $cachedListElements1        = array_diff($cachedListElements1, $unCachedListElements1);
        $cachedDetailElements       = array_diff($cachedDetailElements, $unCachedDetailElements);
        $cachedDetailElements1      = array_diff($cachedDetailElements1, $unCachedDetailElements1);
        $cachedCompareElements      = array_diff($cachedCompareElements, $unCachedCompareElements);
        $cachedAccountElements      = array_diff($cachedAccountElements, $unCachedAccountElements);
        $cachedOrderHistoryElements = array_diff($cachedAccountElements, $unCachedOrderHistoryElements);
        $cachedMyPasswordElements   = array_diff($cachedAccountElements, $unCachedMyPasswordElements);
        $cachedNewsLetterElements   = array_diff($cachedAccountElements, $unCachedNewsLetterElements);
        $cachedMyAddressElements    = array_diff($cachedAccountElements, $unCachedMyAddressElements);
        $cachedComparisonElements   = array_diff($cachedAccountElements, $unCachedComparisonElements);
        $cachedWishListElements    = array_diff($cachedAccountElements, $unCachedWishListElements);
        $cachedGiftRegistryElements = array_diff($cachedAccountElements, $unCachedGiftRegistryElements);
        $cachedListManiaElements    = array_diff($cachedAccountElements, $unCachedListManiaElements);

        return array(
                    array($baseUrl,                         $cachedStartElements,        $unCachedStartElements),
                    array($categoryWithSubCatUrl1,          $cachedListElements1,        $unCachedListElements1),
                    array($categoryWithoutSubCatUrl1,       $cachedListElements,         $unCachedListElements),
                    array($detailsUrl1,                     $cachedDetailElements1,      $unCachedDetailElements1),
                    array($categoryWithSubCatUrl2,          $cachedListElements,         $unCachedListElements),
                    array($categoryWithoutSubCatUrl2,       $cachedListElements,         $unCachedListElements),
                    array($detailsUrl2,                     $cachedDetailElements,       $unCachedDetailElements),
                    array($compareUrl,                      $cachedCompareElements,      $unCachedCompareElements),
                    array($accountUrl,                      $cachedAccountElements,      $unCachedAccountElements),
                    array($orderHistoryUrl,                 $cachedOrderHistoryElements, $unCachedOrderHistoryElements),
                    array($myPasswordUrl,                   $cachedMyPasswordElements,   $unCachedMyPasswordElements),
                    array($newsLetterUrl,                   $cachedNewsLetterElements,   $unCachedNewsLetterElements),
                    array($myAddressUrl,                    $cachedMyAddressElements,    $unCachedMyAddressElements),
                    array($comparisonUrl,                   $cachedComparisonElements,   $unCachedComparisonElements),
                    array($wishListUrl,                     $cachedWishListElements,    $unCachedWishListElements),
                    array($giftRegistryUrl,                 $cachedGiftRegistryElements, $unCachedGiftRegistryElements),
                    array($listManiaUrl,                    $cachedListManiaElements,    $unCachedListManiaElements),
        );
    }

    /**
     * Check if widgets cached after login.
     *
     * @param string $pageUrl           Url off page to call.
     * @param array  $cachedElements    Elements to check if cached id's.
     * @param array  $notCachedElements Elements to check if not cached id's.
     *
     * @dataProvider providerLogin
     */
    public function testLogin($pageUrl, $cachedElements, $notCachedElements)
    {
        $page = $this->createReverseProxyPage($this->getShopUrl());
        $page->execute();

        // Worm up cache to generate environment key.
        $page = $this->createReverseProxyPage($pageUrl);
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
     * Logs in to page. Creates screen shot of login page.
     */
    protected function login()
    {
        $pageUrl = $this->getShopUrl();
        $params["cl"]   = "start";
        $params["fnc"]  = "login_noredirect";
        $params["lang"] = 0;
        $params["lgn_usr"]   = "admin";
        $params["lgn_pwd"]   = "admin";

        $page = $this->createReverseProxyPage($pageUrl, $params);
        $page->execute();

        $page->saveToFile($this->formPageFileName($pageUrl, 'afterLogin'), $this->getTestName());

        $this->assertTrue($page->isTextPresent('Set-Cookie: oxenv_key'), 'Page must set environment key');
    }
}
