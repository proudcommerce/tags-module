<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Seo encoder for articles
 *
 */
class oetagsSeoEncoderArticle extends oetagsSeoEncoderArticle_parent
{

    /**
     * Returns SEO uri for passed article and active tag
     *
     * @param oxArticle $oArticle     article object
     * @param int       $iLang        language id
     * @param bool      $blRegenerate if TRUE forces seo url regeneration
     *
     * @return string
     */
    public function getArticleTagUri($oArticle, $iLang, $blRegenerate = false)
    {
        $sSeoUri = null;
        if ($sTag = $this->_getTag()) {
            $iShopId = $this->getConfig()->getShopId();

            $oArticleTags = oxNew('oetagsArticleTagList');
            $oArticleTags->setArticleId($oArticle->getId());
            $oArticleTags->getStdTagLink($sTag);

            $sStdUrl = $oArticleTags->getStdTagLink($sTag);
            if ($blRegenerate || !($sSeoUri = $this->_loadFromDb('dynamic', $this->getDynamicObjectId($iShopId, $sStdUrl), $iLang))) {
                // generating new if not found
                if ($sSeoUri = oxRegistry::get("oetagsSeoEncoderTag")->getTagUri($sTag, $iLang, $oArticle->getId())) {
                    $sSeoUri .= $this->_prepareArticleTitle($oArticle);
                    $sSeoUri = $this->_processSeoUrl($sSeoUri, $this->_getStaticObjectId($iShopId, $sStdUrl), $iLang);
                    $sSeoUri = $this->_getDynamicUri($sStdUrl, $sSeoUri, $iLang);
                }
            }
        }

        return $sSeoUri;
    }

    /**
     * Returns active tag if available
     *
     * @return string | null
     */
    protected function _getTag()
    {
        $sTag = null;
        $oView = $this->getConfig()->getTopActiveView();
        if ( is_a($oView, 'oxView') && method_exists($oView, 'getTag')) {
            $sTag = $oView->getTag();
        }

        return $sTag;
    }

    /**
     * Encodes article URLs into SEO format
     *
     * @param oxArticle $oArticle Article object
     * @param int       $iLang    language
     * @param int       $iType    type
     *
     * @return string
     */
    public function getArticleUrl($oArticle, $iLang = null, $iType = 0)
    {
        if (!isset($iLang)) {
            $iLang = $oArticle->getLanguage();
        }

        $sUri = null;
        if (OXARTICLE_LINKTYPE_TAG == $iType) {
            $sUri = $this->getArticleTagUri($oArticle, $iLang);
            $sUri =$this->_getFullUrl($sUri, $iLang);
        } else {
            $sUri = parent::getArticleUrl($oArticle, $iLang, $iType);
        }

        return $sUri;
    }

    /**
     * Returns alternative uri used while updating seo
     *
     * @param string $sObjectId object id
     * @param int    $iLang     language id
     *
     * @return string
     */
    protected function _getAltUri($sObjectId, $iLang)
    {
        $sSeoUrl = null;
        $oArticle = oxNew("oxArticle");
        $oArticle->setSkipAssign(true);
        if ($oArticle->loadInLang($iLang, $sObjectId)) {
            if ('tag' == $this->_getListType()) {
                $sSeoUrl = $this->getArticleTagUri($oArticle, $iLang, true);
            } else {
                $sSeoUrl = parent::_getAltUri($sObjectId, $iLang);
            }
        }

        return $sSeoUrl;
    }
}
