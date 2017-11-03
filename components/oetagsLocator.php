<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Locator controller for: category, vendor, manufacturers and search lists.
 */
class oetagsLocator extends oetagsLocator_parent
{
    /**
     * Sets details locator data for articles that came from tag list.
     *
     * @param oxubase   $oLocatorTarget oxubase object
     * @param oxarticle $oCurrArticle   current article
     *
     */
    protected function _setTagLocatorData($oLocatorTarget, $oCurrArticle)
    {
        if (($oTag = $oLocatorTarget->getViewConfig()->getActTag())) {

            $myUtils = oxRegistry::getUtils();

            // loading data for article navigation
            $oIdList = oxNew('oxArticleList');
            $oLang = oxRegistry::getLang();

            if ($oLocatorTarget->showSorting()) {
                $oIdList->setCustomSorting($oLocatorTarget->getSortingSql($oLocatorTarget->getSortIdent()));
            }

            $oIdList->getTagArticleIds($oTag->sTag, $oLang->getBaseLanguage());

            //page number
            $iPage = $this->_findActPageNumber($oLocatorTarget->getActPage(), $oIdList, $oCurrArticle);

            // setting product position in list, amount of articles etc
            $oTag->iCntOfProd = $oIdList->count();
            $oTag->iProductPos = $this->_getProductPos($oCurrArticle, $oIdList, $oLocatorTarget);

            if (oxRegistry::getUtils()->seoIsActive()) {
                $oTag->toListLink = oxRegistry::get("oetagsSeoEncoderTag")->getTagPageUrl($oTag->sTag, $iPage);
            } else {
                $sPageNr = $this->_getPageNumber($iPage);
                $oTag->toListLink = $this->_makeLink($oTag->link, $sPageNr);
            }

            $sAddSearch = '';
            // setting parameters when seo is Off
            if (!$myUtils->seoIsActive()) {
                $sSearchTagParameter = oxRegistry::getConfig()->getRequestParameter('searchtag', true);
                $sAddSearch = 'searchtag=' . rawurlencode($sSearchTagParameter);
                $sAddSearch .= '&amp;listtype=tag';
            }

            $oNextProduct = $this->_oNextProduct;
            $oBackProduct = $this->_oBackProduct;
            $oTag->nextProductLink = $oNextProduct ? $this->_makeLink($oNextProduct->getLink(), $sAddSearch) : null;
            $oTag->prevProductLink = $oBackProduct ? $this->_makeLink($oBackProduct->getLink(), $sAddSearch) : null;
            $oStr = getStr();
            $sTitle = $oLang->translateString('TAGS') . ' / ' . $oStr->htmlspecialchars($oStr->ucfirst($oTag->sTag));
            $oLocatorTarget->setSearchTitle($sTitle);
            $oLocatorTarget->setActiveCategory($oTag);
        }
    }

}
