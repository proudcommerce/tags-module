<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Article details information page.
 * Collects detailed article information, possible variants, such information
 * as crosselling, similarlist, picture gallery list, etc.
 * OXID eShop -> (Any chosen product).
 */
class oetagsArticleDetailsController extends oetagsArticleDetailsController_parent
{
    /**
     * If tags will be changed
    *
     * @var bool
     */
    protected $_blEditTags = null;

    /**
     * All tags
     *
     * @var array
     */
    protected $_aTags = null;

    /**
     * Adds tags from parameter
     *
     * @return null
     */
    public function addTags()
    {
        if (!oxRegistry::getSession()->checkSessionChallenge()) {
            return;
        }

        $tags = $this->getConfig()->getRequestParameter('newTags', true);
        $highTag = $this->getConfig()->getRequestParameter('highTags', true);
        if (!$tags && !$highTag) {
            return;
        }
        if ($highTag) {
            $tags = getStr()->html_entity_decode($highTag);
        }
        $article = $this->getProduct();

        // set current user added tags for this article for later checking
        $taggedArticles = oxRegistry::getSession()->getVariable("aTaggedProducts");
        $addedTags = $taggedArticles ? $taggedArticles[$article->getId()] : array();

        $articleTagList = oxNew("oetagsArticleTagList");
        $articleTagList->load($article->getId());
        $separator = $articleTagList->get()->getSeparator();
        $uniqueTags = array_unique(explode($separator, $tags));

        $result = $this->_addTagsToList($articleTagList, $uniqueTags, $addedTags);

        if (!empty($result['tags'])) {
            $articleTagList->save();
            foreach ($result['tags'] as $tag) {
                $addedTags[$tag] = 1;
            }
            $taggedArticles[$article->getId()] = $addedTags;
            oxRegistry::getSession()->setVariable('aTaggedProducts', $taggedArticles);
        }
        // for ajax call
        if ($this->getConfig()->getRequestParameter('blAjax', true)) {
            oxRegistry::getUtils()->showMessageAndExit(json_encode($result));
        }
    }

    /**
     * Adds tags to passed oetagsArticleTagList object
     *
     * @param oetagsArticleTagList $articleTagList Article tags list object
     * @param array            $tags           Tags array to add to list
     * @param array            $addedTags      Tags, which are already added to list
     *
     * @return array
     */
    protected function _addTagsToList($articleTagList, $tags, $addedTags)
    {
        $result = array('tags' => array(), 'invalid' => array(), 'inlist' => array());

        foreach ($tags as $tagName) {
            $tag = oxNew("oetagsTag", $tagName);
            if ($addedTags[$tag->get()] != 1) {
                if ($tag->isValid()) {
                    $articleTagList->addTag($tag);
                    $result['tags'][] = $tag->get();
                } else {
                    $result['invalid'][] = $tag->get();
                }
            } else {
                $result['inlist'][] = $tag->get();
            }
        }

        return $result;
    }

    /**
     * Sets tags editing mode
    *
     * @return null
     */
    public function editTags()
    {
        if (!$this->getUser()) {
            return;
        }
        $articleTagList = oxNew("oetagsArticleTagList");
        $articleTagList->load($this->getProduct()->getId());
        $tagSet = $articleTagList->get();
        $this->_aTags = $tagSet->get();
        $this->_blEditTags = true;

        // for ajax call
        if ($this->getConfig()->getRequestParameter('blAjax', true)) {
            $charset = oxRegistry::getLang()->translateString('charset');
            oxRegistry::getUtils()->setHeader("Content-Type: text/html; charset=" . $charset);
            $smarty = oxRegistry::get("oxUtilsView")->getSmarty();
            $smarty->assign('oView', $this);
            $viewConfig = $this->getViewConfig();
            $smarty->assign('oViewConf', $viewConfig);

            $theme = strtolower($viewConfig->getActiveTheme());
            $file = $viewConfig->getModulePath('oetags',"/views/{$theme}/tpl/page/details/inc/editTags.tpl");

            if(file_exists($file)) {
                oxRegistry::getUtils()->showMessageAndExit(
                    $smarty->fetch($file, $this->getViewId())
                );
            }
        }
    }

    /**
     * Cancels tags editing mode
     *
     */
    public function cancelTags()
    {
        $articleTagList = oxNew("oetagsArticleTagList");
        $articleTagList->load($this->getProduct()->getId());
        $tagSet = $articleTagList->get();
        $this->_aTags = $tagSet->get();
        $this->_blEditTags = false;

        // for ajax call
        if (oxRegistry::getConfig()->getRequestParameter('blAjax', true)) {
            $charset = oxRegistry::getLang()->translateString('charset');
            oxRegistry::getUtils()->setHeader("Content-Type: text/html; charset=" . $charset);
            $smarty = oxRegistry::get("oxUtilsView")->getSmarty();
            $smarty->assign('oView', $this);
            $viewConfig = $this->getViewConfig();
            $smarty->assign('oViewConf', $viewConfig);

            $theme = strtolower($viewConfig->getActiveTheme());
            $file = $viewConfig->getModulePath('oetags',"/views/{$theme}/tpl/page/details/inc/tags.tpl");

            if(file_exists($file)) {
                oxRegistry::getUtils()->showMessageAndExit(
                    $smarty->fetch($file, $this->getViewId())
                );
            }
        }
    }

    /**
     * Returns if tags will be edit
     *
     * @return bool
     */
    public function getEditTags()
    {
        return $this->_blEditTags;
    }

    /**
     * Returns all tags
    *
     * @return array
     */
    public function getTags()
    {
        return $this->_aTags;
    }

    /**
     * Returns current view link type
     *
     * @return int
     */
    public function getLinkType()
    {
        if ($this->_iLinkType === null) {
            $listType = $this->getConfig()->getRequestParameter('listtype');
            if ('tag' == $listType) {
                $this->_iLinkType = OXARTICLE_LINKTYPE_TAG;
            }
            $this->_iLinkType = parent::getLinkType();
        }

        return $this->_iLinkType;
    }

    /**
     * Returns current view title. Default is null
     *
     * @return null
     */
    public function getTitle()
    {
        if ($article = $this->getProduct()) {
            $tag = $this->getTag();
            $articleTitle = $article->oxarticles__oxtitle->value;
            $variantSelectionId = $article->oxarticles__oxvarselect->value;

            $variantSelectionValue = $variantSelectionId ? ' ' . $variantSelectionId : '';
            $tagValue = !empty($tag) ? ' - ' . $tag : '';

            return $articleTitle . $variantSelectionValue . $tagValue;
        }
        return null;
    }

    /**
     * Template variable getter. Returns meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $meta = parent::getMetaDescription();

        if ($tag = $this->getTag()) {
            $meta = $tag . ' - ' . $meta;
        }

        return $meta;
    }

    /**
     * Template variable getter. Returns current tag
     *
     * @return string
     */
    public function getTag()
    {
        return oxRegistry::getConfig()->getRequestParameter("searchtag");
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        if ('tag' == $this->getListType()) {
            $paths = $this->_getTagBreadCrumb();
        } else {
            $paths = parent::getBreadCrumb();
        }

        return $paths;
    }

    /**
     * Checks if rating functionality is on and allowed to user
     *
     * @return bool
     */
    public function canChangeTags()
    {
        if ($this->getUser()) {
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
        $tagList = oxNew("oetagsArticleTagList");
        $tagList->setArticleId($this->getProduct()->getId());
        $tagCloud = oxNew("oetagsTagCloud");
        $tagCloud->setTagList($tagList);
        $tagCloud->setExtendedMode(true);

        return $tagCloud;
    }

    /**
     * Tag bread crumb
     *
     * @return array
     */
    protected function _getTagBreadCrumb()
    {
        $paths = array();

        $tagPath = array();

        $baseLanguageId = oxRegistry::getLang()->getBaseLanguage();
        $selfLink = $this->getViewConfig()->getSelfLink();

        $tagPath['title'] = oxRegistry::getLang()->translateString('TAGS', $baseLanguageId, false);
        $tagPath['link'] = oxRegistry::get("oxSeoEncoder")->getStaticUrl($selfLink . 'cl=oetagstagscontroller');
        $paths[] = $tagPath;

        $searchTagParameter = oxRegistry::getConfig()->getRequestParameter('searchtag');
        $stringModifier = getStr();
        $tagPath['title'] = $stringModifier->ucfirst($searchTagParameter);
        $tagPath['link'] = oxRegistry::get("oetagsSeoEncoderTag")->getTagUrl($searchTagParameter);
        $paths[] = $tagPath;

        return $paths;
    }

    /**
     * Generate the view id.
     *
     * @return string
     */
    protected function generateViewId()
    {
        $viewId = parent::generateViewId();

        if ('EE' == $this->getShopEdition()) {
            $viewId .= '|' . serialize($this->getTags());
        }
        return $viewId;
    }

}
