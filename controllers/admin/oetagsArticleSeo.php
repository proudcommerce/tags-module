<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Article seo config class
 */
class oetagsArticleSeo extends oetagsArticleSeo_parent
{
    /**
     * Processes parameter before writing to db
     *
     * @param string $sParam parameter to process
     *
     * @return string
     */
    public function processParam($sParam)
    {
        if ($this->getTag()) {
            return '';
        } else {
            return parent::processParam($sParam);
        }
    }

    /**
     * Returns active tag, used for seo url getter
     *
     * @return string | null
     */
    public function getTag()
    {
        if ($this->getActCatType() == 'oetag') {

            $iLang = $this->getActCatLang();
            $sTagId = $this->getActCatId();

            $oProduct = oxNew('oxArticle');
            $oProduct->loadInLang($iLang, $this->getEditObjectId());

            $aList = $this->_getTagList($oProduct, $iLang);
            foreach ($aList as $oTag) {
                if ($oTag->getId() == $sTagId) {
                    return $oTag->getTitle();
                }
            }
        }
        return null;
    }

    /**
     * Returns list type for current seo url
     *
     * @return string
     */
    public function getListType()
    {
        $result = ('oetag' == $this->getActCatType()) ? 'tag' : parent::getListType();

        return $result;
    }

    /**
     * Returns seo uri
     *
     * @return string
     */
    public function getEntryUri()
    {
        if ('oetag' == $this->getActCatType()) {
            $oProduct = oxNew('oxArticle');
            if ($oProduct->load($this->getEditObjectId())) {
                $oEncoder = $this->_getEncoder();
                $return = $oEncoder->getArticleTagUri($oProduct, $this->getActCatLang());
            }
        } else {
            $return = parent::getEntryUri();
        }

        return $return;
    }

    /**
     * Returns product selections array [type][language] (categories, vendors etc assigned)
     *
     * @return array
     */
    public function getSelectionList()
    {
        if ($this->_aSelectionList === null) {
            $this->_aSelectionList = parent::getSelectionList();

            $oProduct = oxNew('oxArticle');
            $oProduct->load($this->getEditObjectId());
            $aLangs = $oProduct->getAvailableInLangs();

            foreach ($aLangs as $iLang => $sLangTitle) {
                if ($oTagList = $this->_getTagList($oProduct, $iLang)) {
                    $this->_aSelectionList["oetag"][$iLang] = $oTagList;
                }
            }
        }

        return $this->_aSelectionList;
    }

    /**
     * Returns product tags array for given language
     *
     * @param oxArticle $oArticle Article object
     * @param int       $iLang    language id
     *
     * @return array
     */
    protected function _getTagList($oArticle, $iLang)
    {
        $oArticleTagList = oxNew("oetagsArticleTagList");
        $oArticleTagList->setLanguage($iLang);
        $oArticleTagList->load($oArticle->getId());
        $aTagsList = array();
        if (count($aTags = $oArticleTagList->getArray())) {
            $sShopId = $this->getConfig()->getShopId();
            $iProdId = $oArticle->getId();
            foreach ($aTags as $sTitle => $oTagObject) {
                // A. we do not have oetagsTag object yet, so reusing manufacturers for general interface
                $oTag = oxNew("oxManufacturer");
                $oTag->setLanguage($iLang);
                $oTag->setId(md5(strtolower($sShopId . $this->_getStdUrl($iProdId, "oetag", "tag", $iLang, $sTitle))));
                $oTag->oxmanufacturers__oxtitle = new oxField($sTitle);
                $aTagsList[] = $oTag;
            }
        }

        return $aTagsList;
    }

    /**
     * Returns seo entry type
     *
     * @return string
     */
    protected function _getSeoEntryType()
    {
        if ($this->getTag()) {
            return 'dynamic';
        } else {
            return parent::_getSeoEntryType();
        }
    }

    /**
     * Returns id of object which must be saved
     *
     * @return string
     */
    protected function _getSaveObjectId()
    {
        $sId = parent::_getSaveObjectId();

        if ($this->getActCatType() == 'oetag') {
            $sId = $this->_getEncoder()->getDynamicObjectId($this->getConfig()->getShopId(), $this->_getStdUrl($sId));
        }

        return $sId;
    }

    /**
     * Returns objects standard url
     *
     * @param string $sOxid     object id
     * @param string $sCatType  preferred type - oxvendor/oxmanufacturer/oetag.. [default is NULL]
     * @param string $sListType preferred list type tag/vendor/manufacturer.. [default is NULL]
     * @param string $iLang     preferred language id [default is NULL]
     * @param string $sTag      preferred tag [default is NULL]
     *
     * @return string
     */
    protected function _getStdUrl($sOxid, $sCatType = null, $sListType = null, $iLang = null, $sTag = null)
    {
        $iLang = $iLang !== null ? $iLang : $this->getEditLang();
        $sCatType = $sCatType !== null ? $sCatType : $this->getActCatType();
        $sListType = $sListType !== null ? $sListType : $this->getListType();

        $aParams = array();
        if ($sListType) {
            $aParams["listtype"] = $sListType;
        }

        $oProduct = oxNew('oxArticle');
        $oProduct->loadInLang($iLang, $sOxid);

        // adding vendor or manufacturer id
        switch ($sCatType) {
            case 'oxvendor':
                $aParams["cnid"] = "v_" . $this->getActCatId();
                break;
            case 'oxmanufacturer':
                $aParams["mnid"] = $this->getActCatId();
                break;
            case 'oetag':
                $aParams["searchtag"] = $sTag !== null ? $sTag : $this->getTag();
                break;
            default:
                $aParams["cnid"] = $this->getActCatId();
                break;
        }

        $oUtilsUrl = oxRegistry::get("oxUtilsUrl");

        return trim($oUtilsUrl->appendUrl($oProduct->getBaseStdLink($iLang, true, false), $aParams), '&amp;');
    }
}
