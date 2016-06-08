<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Admin article main manager.
 * Collects and updates (on user submit) article base parameters data ( such as
 * title, article No., short Description and etc.).
 * Admin Menu: Manage Products -> Articles -> Main.
 */
class oetagsArticleMain extends oetagsArticleMain_parent
{
    /**
     * Customize article data for redering, like here: add tags.
     *
     * @param Article $article
     *
     * @return Article
     */
    protected function customizeArticleInformation($article)
    {
        //loading tags
        $articleTagList = oxNew("oetagsArticleTagList");
        $articleTagList->loadInLang($this->_iEditLang, $article->getId());
        $article->tags = $articleTagList->get();

        return $article;
    }

    /**
     * Save tags.
     *
     * @param Article $article
     * @param array   $parameters
     *
     * @return Article
     */
    protected function saveAdditionalArticleData($article, $parameters)
    {
        //saving tags
        if (isset($parameters['tags'])) {
            $tags = $parameters['tags'];
            if (!trim($tags)) {
                $tags = $article->oxarticles__oxsearchkeys->value;
            }
            $invalidTags = $this->_setTags($tags, $article->getId());
            if (!empty($aInvalidTags)) {
                $this->_aViewData["invalid_tags"] = implode(', ', $invalidTags);
            }
        }
        return $article;
    }

    /**
     * Sets tags to article. Returns invalid tags array
     *
     * @param string $sTags      Tags string to set for article
     * @param string $sArticleId Article id
     *
     * @return array of oetagsTag objects
     */
    protected function _setTags($sTags, $sArticleId)
    {
        $oArticleTagList = oxNew('oetagsArticleTagList');
        $oArticleTagList->loadInLang($this->_iEditLang, $sArticleId);
        $oArticleTagList->set($sTags);
        $oArticleTagList->save();

        return $oArticleTagList->get()->getInvalidTags();
    }

}
