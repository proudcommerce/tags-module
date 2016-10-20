<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Transparent category manager class (executed automatically).
 *
 * @subpackage oxcmp
 */
class oetagsCategoriesComponent extends oetagsCategoriesComponent_parent
{
    /**
     * get active category id
     *
     * @return string
     */
    protected function _getActCat()
    {
        $sActManufacturer = oxRegistry::getConfig()->getRequestParameter('mnid');
        // @deprecated v5.3 (2016-05-04); Tags will be moved to own module.
        $sActTag = oxRegistry::getConfig()->getRequestParameter('searchtag');
        // END deprecated
        $sActCat = $sActManufacturer ? null : oxRegistry::getConfig()->getRequestParameter('cnid');

        // loaded article - then checking additional parameters
        $oProduct = $this->getProduct();
        if ($oProduct) {
            $myConfig = $this->getConfig();

            $sActManufacturer = $myConfig->getConfigParam('bl_perfLoadManufacturerTree') ? $sActManufacturer : null;

            $sActVendor = (getStr()->preg_match('/^v_.?/i', $sActCat)) ? $sActCat : null;

            $sActCat = $this->_addAdditionalParams($oProduct, $sActCat, $sActManufacturer, $sActTag, $sActVendor);
        }

        // @deprecated v5.3 (2016-05-04); Tags will be moved to own module.
        // Checking for the default category
        if ($sActCat === null && !$oProduct && !$sActManufacturer && !$sActTag) {
            // set remote cat
            $sActCat = $this->getConfig()->getActiveShop()->oxshops__oxdefcat->value;
            if ($sActCat == 'oxrootid') {
                // means none selected
                $sActCat = null;
            }
        }
        // END deprecated

        return $sActCat;
    }

    /**
     * Adds additional parameters: active category, list type and category id
     *
     * @param oxArticle $oProduct         loaded product
     * @param string    $sActCat          active category id
     * @param string    $sActManufacturer active manufacturer id
     * @param string    $sActTag          active tag
     * @param string    $sActVendor       active vendor
     *
     * @deprecated v5.3 (2016-05-04); Tags will be moved to own module. So the parameter sActTag will be removed.
     *
     * @return string $sActCat
     */
    protected function _addAdditionalParams($oProduct, $sActCat, $sActManufacturer, $sActTag, $sActVendor)
    {
        $sSearchPar = oxRegistry::getConfig()->getRequestParameter('searchparam');
        $sSearchCat = oxRegistry::getConfig()->getRequestParameter('searchcnid');
        $sSearchVnd = oxRegistry::getConfig()->getRequestParameter('searchvendor');
        $sSearchMan = oxRegistry::getConfig()->getRequestParameter('searchmanufacturer');
        $sListType = oxRegistry::getConfig()->getRequestParameter('listtype');

        // search ?
        if ((!$sListType || $sListType == 'search') && ($sSearchPar || $sSearchCat || $sSearchVnd || $sSearchMan)) {
            // setting list type directly
            $sListType = 'search';
        } else {

            // such Manufacturer is available ?
            if ($sActManufacturer && ($sActManufacturer == $oProduct->getManufacturerId())) {
                // setting list type directly
                $sListType = 'manufacturer';
                $sActCat = $sActManufacturer;
            } elseif ($sActVendor && (substr($sActVendor, 2) == $oProduct->getVendorId())) {
                // such vendor is available ?
                $sListType = 'vendor';
                $sActCat = $sActVendor;
                // @deprecated v5.3 (2016-05-04); Tags will be moved to own module.
            } elseif ($sActTag) {
                // tag ?
                $sListType = 'tag';
                // END deprecated
            } elseif ($sActCat && $oProduct->isAssignedToCategory($sActCat)) {
                // category ?
            } else {
                list($sListType, $sActCat) = $this->_getDefaultParams($oProduct);
            }
        }

        $oParentView = $this->getParent();
        //set list type and category id
        $oParentView->setListType($sListType);
        $oParentView->setCategoryId($sActCat);

        return $sActCat;
    }

}
