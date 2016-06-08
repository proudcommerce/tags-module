<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Tag cloud.
 * Shop starter, manages starting visible articles, etc.
 *
 */
class oetagsTagCloudWidget extends \oxWidget
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'widget/sidebar/tags.tpl';

    /**
     * Checks if tags list should be displayed in separate box
     *
     * @return bool
     */
    public function displayInBox()
    {
        return (bool) $this->getViewParameter("blShowBox");
    }

    /**
     * Returns tag cloud manager class
     *
     * @return oetagsTagCloud
     */
    public function getTagCloudManager()
    {
        $oTagList = oxNew("oetagsTagList");
        //$oTagList->loadList();
        $oTagCloud = oxNew("oetagsTagCloud");
        $oTagCloud->setTagList($oTagList);

        return $oTagCloud;
    }

    /**
     * Template variable getter. Returns true
     *
     * @return bool
     */
    public function isMoreTagsVisible()
    {
        return true;
    }

}
