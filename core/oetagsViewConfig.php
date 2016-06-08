<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

class oetagsViewConfig extends oetagsViewConfig_parent
{

    /**
     * @var bool Show tags cloud.
     *
     */
    protected $_blShowTagCloud = true;

    /**
     * @var stdClass Active tag.
     *
     */
    protected $_oActTag = null;

    /**
     * Returns true if tags are ON. Depends also on controller class.
     *
     * @param oxubase $viewObject
     *
     * @return boolean
     */
    public function showTags($viewObject)
    {
        $return = (bool) $this->_blShowTagCloud && $this->getConfig()->getConfigParam("oetagsShowTags");

        if ( is_a($viewObject, 'OxidEsales\Eshop\Application\Controller\AccountController')
            || is_a($viewObject, 'OxidEsales\Eshop\Application\Controller\CompareController')
        ) {
            $return = false;
        }

        return $return;
    }

    /**
     * Active tag info object getter. Object properties:
     *  - sTag current tag
     *  - link link leading to tag article list
     *
     * @return stdClass
     */
    public function getActTag()
    {
        if ($this->_oActTag === null) {
            $this->_oActTag = new stdClass();
            $this->_oActTag->sTag = $tag = $this->getConfig()->getRequestParameter("searchtag", 1);
            $seoEncoderTag = oxRegistry::get("oetagsSeoEncoderTag");

            $link = false;
            if (oxRegistry::getUtils()->seoIsActive()) {
                $link = $seoEncoderTag->getTagUrl($tag, oxRegistry::getLang()->getBaseLanguage());
            }

            $constructedUrl = $this->getConfig()->getShopHomeURL() . $seoEncoderTag->getStdTagUri($tag, false);
            $this->_oActTag->link = $link ? $link : $constructedUrl;
        }

        return $this->_oActTag;
    }

    /**
     * Template variable getter. Returns additional params for url
     *
     * @return string
     */
    public function getAdditionalParameters()
    {
        $additionalParameters = parent::getAdditionalParameters();
        if (($value = oxRegistry::getConfig()->getRequestParameter('searchtag'))) {
            $additionalParameters = '&amp;searchtag=' . rawurlencode(rawurldecode($value));
        }

        return $additionalParameters;
    }

    /**
     * Returns array of params => values which are used in hidden forms and as additional url params.
     * NOTICE: this method SHOULD return raw (non encoded into entities) parameters, because values
     * are processed by htmlentities() to avoid security and broken templates problems
     *
     * @return array
     */
    public function getAdditionalNavigationParameters()
    {
        $config = $this->getConfig();

        $parameters = parent::getAdditionalNavigationParameters();
        $parameters['searchtag'] = $config->getRequestParameter('searchtag', true);

        return $parameters;
    }

    /**
     * Collects additional _GET parameters used by eShop
     *
     * @return string
     */
    public function addRequestParameters()
    {
        $result = parent::addRequestParameters();
        if ($value = oxRegistry::getConfig()->getRequestParameter('searchtag')) {
            $result = "&amp;searchtag={$value}";
        }

        return $result;
    }

    /**
     * returns additional url params for dynamic url building
     *
     * @param string $listType
     *
     * @return string
     */
    public function getDynUrlParameters($listType)
    {
        $result = '';
        $config = $this->getConfig();

        if ('tag' == $listType) {
            $result .= "&amp;listtype={$listType}";
            if ($param = rawurlencode($config->getRequestParameter('searchtag', true))) {
                $result .= "&amp;searchtag={$param}";
            }
        } else {
            $result = parent::getDynUrlParameters($listType);
        }

        return $result;
    }

    /**
     * Returns shop logout link
     *
     * @return string
     */
    public function getLogoutLink()
    {
        $return = parent::getLogoutLink();
        $searchTag = $this->getActSearchTag();
        $return .= ($searchTag ? "&amp;searchtag={$searchTag}" : '');
        return $return;

    }

    /**
     * Returns active search tag parameter
     *
     * @return string
     */
    public function getActSearchTag()
    {
        return oxRegistry::getConfig()->getRequestParameter('searchtag');
    }

}
