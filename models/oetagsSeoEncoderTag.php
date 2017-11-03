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
        return $this->_getDynamicTagUri($this->getStdTagUri($tag), "oetagstagcontroller/{$tag}/", $languageId);
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
        $oldSeoUrl = $this->_loadFromDb('dynamic', $objectId, $languageId);
        if ($oldSeoUrl === $seoUrl) {
            $seoUrl = $oldSeoUrl;
        } else {
            if ($oldSeoUrl) {
                // old must be transferred to history
                $this->_copyToHistory($objectId, $shopId, $languageId, 'dynamic');
            }
            // creating unique
            $seoUrl = $this->_processSeoUrl($seoUrl, $objectId, $languageId);

            // inserting
            $this->_saveToDb('dynamic', $objectId, $stdUrl, $seoUrl, $languageId, $shopId);
        }

        return $seoUrl;
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
        $stdUrl = $this->getStdTagUri($tag) . '&amp;pgNr=' . $pageNumber;
        $parameters = (int) ($pageNumber + 1);

        $stdUrl = $this->_trimUrl($stdUrl, $languageId);
        $seoUrl = $this->getTagUri($tag, $languageId) . $parameters . "/";

        return $this->_getFullUrl($this->_getDynamicTagUri($stdUrl, $seoUrl, $languageId), $languageId);
    }
}
