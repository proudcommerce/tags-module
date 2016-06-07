<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxDb;
use OxidEsales\Eshop\Core\Edition\EditionPathProvider;
use OxidEsales\Eshop\Core\Edition\EditionRootPathProvider;
use OxidEsales\Eshop\Core\Edition\EditionSelector;
use OxidEsales\Eshop\Core\FileSystem\FileSystem;
use OxidEsales\EshopEnterprise\Core\Cache\ReverseProxy\ReverseProxyBackend;
use \oxRegistry;
use \oxShop;

/**
 * Main shop configuration class.
 */
class oetagsConfigEE extends oetagsConfigEE_parent
{
    /**
     * Execute dependencies
     *
     * @param string $variableName - config names
     *
     * @return bool
     */
    protected function _efectsAllDetails($variableName)
    {
        if ('oetagsShowTags' == $variableName) {
            return true;
        }

        return parent::_efectsAllDetails($variableName);
    }

    /**
     * Execute dependencies
     *
     * @param string $variableName - config names
     *
     * @return bool
     */
    protected function _effectsAllList($variableName)
    {
        if ('oetagsShowTags' == $variableName) {
            return true;
        }

        return parent::_effectsAllList($variableName);
    }

    /**
     * Execute dependencies
     *
     * @param string $variableName - config names
     *
     * @return bool
     */
    protected function _effectsStartPage($variableName)
    {
        if ('oetagsShowTags' == $variableName) {
            return true;
        }

        return parent::_effectsStartPage($variableName);
    }

}
