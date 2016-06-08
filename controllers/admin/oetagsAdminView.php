<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */


/**
 * @internal This class should not be directly extended, instead of it oxAdminView class should be used.
 */
class oetagsAdminView extends oetagsAdminView_parent
{
    /**
     * Marks seo entires as expired, cleans up tag clouds cache
     *
     * @param string $sShopId Shop id
     */
    public function resetSeoData($sShopId)
    {
        parent::resetSeoData($sShopId);

        // resetting tag cache
        $oTagCloud = oxNew('oetagstagcloud');
        $oTagCloud->resetCache();
    }
}
