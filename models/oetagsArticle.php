<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

define('OXARTICLE_LINKTYPE_TAG', 4);

/**
 * Article manager.
 * Creates fully detailed article object, with such information as VAT,
 * discounts, etc.
 *
 */
class oetagsArticle extends oetagsArticle_parent
{
    /**
     * Returns true if the field is multilanguage
     *
     * @param string $sFieldName Field name
     *
     * @return bool
     */
    public function isMultilingualField($sFieldName)
    {
        if ('oetags' == $sFieldName) {
            return true;
        }

        return parent::isMultilingualField($sFieldName);
    }

    /**
     * Execute cache dependencies
     *
     * @param int $dependencyEvent event name
     *
     * @return null
     */
    public function executeDependencyEvent($dependencyEvent = null)
    {
        parent::executeDependencyEvent($dependencyEvent);

        if (empty($dependencyEvent) ) {
            $this->_updateTagDependency();
        }
    }
    /**
     * Execute cache dependencies with tags
     */
    protected function _updateTagDependency()
    {
        $articleTagList = oxNew("oetagsArticleTagList");
        $articleTagList->load($this->getId());
        $articleTagList->executeDependencyEvent();
    }

}
