<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * Tests for Shop_Cache class.
 */
class oetagsShopCacheTest extends UnitTestCase
{

    /**
     * Shop_Cache::flushReverseProxyBackend() test case.
     */
    public function testFlushReverseProxyBackend()
    {
        $reverseProxyUrlGenerator = $this->getMock('oxReverseProxyUrlGenerator', array('setDynamicPage'));
        $reverseProxyUrlGenerator->expects($this->at(0))->method('setDynamicPage')->with($this->equalTo('oetagstagcontroller'));
        $reverseProxyUrlGenerator->expects($this->at(1))->method('setDynamicPage')->with($this->equalTo('rss'));

        oxTestModules::addModuleObject('oxReverseProxyUrlGenerator', $reverseProxyUrlGenerator);

        $view = oxNew('shop_cache');
        $view->flushReverseProxyBackend('lists');

    }
}
