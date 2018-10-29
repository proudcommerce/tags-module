<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Seo encoder for tags.
 *
 */
class oetagsSeoEncoderTag extends \oxSeoEncoder
{
    /** @var oetagsTagCloud Tag preparation util object. */
    protected $_oTagPrepareUtil = null;

    /**
     * Returns SEO uri for tag.
     *
     * @param string $tag        Tag name
     * @param int    $languageId Language id
     *
     * @return string
     */
    public function getTagUri($tag, $languageId = null)
    {
        $uri = $this->getConfig()->getConfigParam('oetagsUri');
        return $this->_getDynamicTagUri($this->getStdTagUri($tag), "{$uri}/{$tag}/", $languageId);
    }

    /**
     * Returns dynamic object SEO URI.
     *
     * @param string $stdUrl     Standard url
     * @param string $seoUrl     Seo uri
     * @param int    $languageId Active language
     *
     * @return string
     */
    protected function _getDynamicTagUri($stdUrl, $seoUrl, $languageId)
    {
        $shopId = $this->getConfig()->getShopId();

        $stdUrl = $this->_trimUrl($stdUrl);
        $objectId = $this->getDynamicObjectId($shopId, $stdUrl);
        $seoUrl = $this->_prepareUri($this->addLanguageParam($seoUrl, $languageId), $languageId);

        //load details link from DB
        $seoUrlFromDbOld = $this->_loadFromDb('dynamic', $objectId, $languageId);
        $processedSeoUrl= $this->_processSeoUrl($seoUrl, $objectId, $languageId);
        if(!$seoUrlFromDbOld) {
            $this->_saveToDb('dynamic', $objectId, $stdUrl, $processedSeoUrl, $languageId, $shopId);
        }
        else {
            if($seoUrlFromDb !== $processedSeoUrl) {
                // old must be transferred to history
                $this->_copyToHistory($objectId, $shopId, $languageId, 'dynamic');
                $this->_saveToDb('dynamic', $objectId, $stdUrl, $processedSeoUrl, $languageId, $shopId);
            }
        }

        return $processedSeoUrl;
    }

    /**
     * Prepares tag for search in db
     *
     * @param string $tag tag to prepare
     *
     * @return string
     */
    protected function _prepareTag($tag)
    {
        if ($this->_oTagPrepareUtil == null) {
            $this->_oTagPrepareUtil = oxNew('oetagsTag');
        }

        return $tag = $this->_oTagPrepareUtil->prepare($tag);
    }

    /**
     * Returns standard tag url.
     * While tags are just strings, standard ulrs formatted stays here.
     *
     * @param string $tag                Tag name
     * @param bool   $shouldIncludeIndex If you need only parameters, set this to false (optional)
     *
     * @return string
     */
    public function getStdTagUri($tag, $shouldIncludeIndex = true)
    {
        $uri = "cl=oetagstagcontroller&amp;searchtag=" . rawurlencode($tag);
        if ($shouldIncludeIndex) {
            $uri = "index.php?" . $uri;
        }

        return $uri;
    }

    /**
     * Returns full url for passed tag
     *
     * @param string $tag        Tag name
     * @param int    $languageId Language id
     *
     * @return string
     */
    public function getTagUrl($tag, $languageId = null)
    {
        if (!isset($languageId)) {
            $languageId = oxRegistry::getLang()->getBaseLanguage();
        }

        return $this->_getFullUrl($this->getTagUri($tag, $languageId), $languageId);
    }

    /**
     * Returns tag SEO url for specified page.
     *
     * @param string $tag        Tag name
     * @param int    $pageNumber Page to prepare number
     * @param int    $languageId Language id
     *
     * @return string
     */
    public function getTagPageUrl($tag, $pageNumber, $languageId = null)
    {
        if (!isset($languageId)) {
            $languageId = oxRegistry::getLang()->getBaseLanguage();
        }
        $stdUrl = $this->getStdTagUri($tag);
        $parameters = null;

        $stdUrl = $this->_trimUrl($stdUrl, $languageId);
        $seoUrl = $this->getTagUri($tag, $languageId);

        $postfix = (int) $pageNumber > 0 ? 'pgNr=' . (int) $pageNumber : '';
        $fullUrl = $this->_getFullUrl($this->_getDynamicTagUri($stdUrl, $seoUrl, $languageId), $languageId);
        $fullUrl = (!empty($postfix)) ? \OxidEsales\Eshop\Core\Registry::getUtilsUrl()->appendParamSeparator($fullUrl) . $postfix : $fullUrl;

        return $fullUrl;
    }
}
