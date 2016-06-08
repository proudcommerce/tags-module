<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use \oxRegistry;
use \oxCategory;

/**
 * Article detailed information widget.
 *
 * @mixin oxwArticleDetails
 */
class oetagsArticleDetailsWidget extends oetagsArticleDetailsWidget_parent
{

    /**
     * If tags can be changed
     *
     * @var bool
     */
    protected $_blCanEditTags = null;

    /**
     * Checks if rating functionality is on and allowed to user
     *
     * @return bool
     */
    public function canChangeTags()
    {
        if ($oUser = $this->getUser()) {

            return true;
        }

        return false;
    }

    /**
     * Returns tag cloud manager class
     *
     * @return oetagsTagCloud
     */
    public function getTagCloudManager()
    {
        $oTagList = oxNew("oetagsArticleTagList");
        //$oTagList->load($this->getProduct()->getId());
        $oTagList->setArticleId($this->getProduct()->getId());
        $oTagCloud = oxNew("oetagsTagCloud");
        $oTagCloud->setTagList($oTagList);
        $oTagCloud->setExtendedMode(true);

        return $oTagCloud;
    }

    /**
     * Returns if tags can be changed, if user is logged in and
     * product exists.
     **
     * @return bool
     */
    public function isEditableTags()
    {
        if ($this->_blCanEditTags === null) {
            $this->_blCanEditTags = false;
            if ($this->getProduct() && $this->getUser()) {
                $this->_blCanEditTags = true;
            }
        }

        return $this->_blCanEditTags;
    }

    /**
     * Returns current view link type
     *
     * @return int
     */
    public function getLinkType()
    {
        if ($this->_iLinkType === null) {
            $sListType = oxRegistry::getConfig()->getRequestParameter('listtype');

            if ('tag' == $sListType) {
                $this->_iLinkType = OXARTICLE_LINKTYPE_TAG;
            } else {
                $this->_iLinkType = parent::getLinkType();
            }
        }

        return $this->_iLinkType;
    }

    /**
     * Returns tag separator
     *
     * @return string
     */
    public function getTagSeparator()
    {
        $sSeparator = $this->getConfig()->getConfigParam("oetagsSeparator");

        return $sSeparator;
    }

}
