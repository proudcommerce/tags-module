<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

require_once __DIR__ . '/../../unit/oeTagsTestCase.php';

use \oxArticleList;
use \oxAttribute;
use \oxBase;
use \oxBasketItem;
use \oxCategory;
use \oxContent;
use \oxDb;
use \oxEmail;
use \oxField;
use \oxGroups;
use \oxLinks;
use \oxList;
use \oxObject2Category;
use \oxOrderArticle;
use \oxRegistry;
use \oxRssFeed;
use \oxSearch;
use \oxSelectlist;
use \oxSeoEncoder;
use \oxTestModules;
use \oxUBase;
use \oxUser;
use \oxUserBasketItem;
use \oxUserPayment;
use \oxUtils;
use \oxUtilsString;

/**
 * Class Unit_utf8Test
 */
class oetagsUtfTest extends \oeTagsTestCase
{
    /**
     * Test getter for title.
     */
    public function testTagGetTitle()
    {
        $value = 'литов';
        $result = 'Литов';

        $view = $this->getProxyClass('oetagstagcontroller');
        $view->setNonPublicVar("_sTag", $value);
        $this->assertEquals($result, $view->getTitle());
    }

    /**
     * Test getBreadCrumb.
     */
    public function testTagGetBreadCrumb()
    {
        $value = 'Литов';
        $result = 'Литов';

        $view = $this->getProxyClass('oetagstagcontroller');
        $view->setNonPublicVar("_sTag", $value);

        $paths = array(
            array('title' => 'Tags', 'link' => oxRegistry::get("oxSeoEncoder")->getStaticUrl($view->getViewConfig()->getSelfLink() . 'cl=oetagstagscontroller')),
            array('title' => $result, 'link' => $view->getCanonicalUrl())
        );

        $this->assertEquals($paths, $view->getBreadCrumb());
    }

}
