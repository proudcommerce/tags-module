<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */


/**
 * Admin shop TERMS manager.
 * Collects shop TERMS information, updates it on user submit, etc.
 * Admin Menu: Main Menu -> Core Settings -> Terms.
 */
class oetagsShopCache extends oetagsShopCache_parent
{
    /**
     * Flushes (Invalidates) all reverse proxy cache, or given cache section.
     *
     * @param string $section Section name.
     */
    public function flushReverseProxyBackend($section = null)
    {
        $reverseProxyBackend = oxRegistry::get('oxReverseProxyBackend');
        $reverseProxyUrlGenerator = oxNew('oxReverseProxyUrlGenerator');

        if (is_null($section)) {
            $reverseProxySection = oxRegistry::getConfig()->getRequestParameter("reverseProxySection");
        } else {
            $reverseProxySection = $section;
        }

        if ('lists' == $reverseProxySection) {
            // Flush all tag lists.
            $reverseProxyUrlGenerator->setDynamicPage('oetagstagcontroller');
        }

        $reverseProxyBackend->set($reverseProxyUrlGenerator->getUrls());
        $reverseProxyBackend->execute();

        parent::flushReverseProxyBackend($section);
    }
}
