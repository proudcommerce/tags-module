<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Class dedicated to article tags handling.
 * Is responsible for saving, returning and adding tags for given article.
 *
 */
class oetagsArticleTagList extends \oxI18n implements oetagsITagList
{

    /**
     * Tags.
     *
     * @var string
     */
    protected $_oTagSet = null;

    /**
     * Instantiates oetagsTagSet object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oTagSet = oxNew('oetagsTagSet');
    }

    /**
     * Sets article id.
     *
     * @param string $articleId Article id
     */
    public function setArticleId($articleId)
    {
        $this->setId($articleId);
    }

    /**
     * Returns current article id.
     *
     * @return string
     */
    public function getArticleId()
    {
        return $this->getId();
    }

    /**
     * Returns cache id.
     *
     * @return string
     */
    public function getCacheId()
    {
        return null;
    }

    /**
     * Loads article tags from DB. Returns true on success.
     *
     * @param string $articleId article id
     *
     * @return bool
     */
    public function load($articleId)
    {
        $this->setArticleId($articleId);
        $database = oxDb::getDb();
        $viewName = getViewName('oxartextends', $this->getLanguage());
        $query = "select oetags from {$viewName} where oxid = " . $database->quote($articleId);

        $this->set('');
        // adding tags to list. Tags does not need to be checked again, but dashes needs to be removed
        $tags = explode($this->get()->getSeparator(), $database->getOne($query));
        foreach ($tags as $value) {
            $tag = oxNew('oetagstag');
            $tag->set($value, false);
            $tag->removeUnderscores();
            $this->addTag($tag);
        }

        return $this->_isLoaded = true;
    }

    /**
     * Loads article tags list.
     *
     * @param string $articleId article id
     *
     * @return bool
     */
    public function loadList($articleId = null)
    {
        if ($articleId === null && ($articleId = $this->getArticleId()) === null) {
            return false;
        }

        return $this->load($articleId);
    }

    /**
     * Saves article tags to DB. Returns true on success.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->canSave()) {
            return false;
        }

        if (!$this->getArticleId()) {
            return false;
        }
        $tagSet = $this->get();
        foreach ($tagSet as $tag) {
            $tag->addUnderscores();
        }
        $tags = oxDb::getInstance()->escapeString($tagSet);
        $database = oxDb::getDb();

        $table = getLangTableName('oxartextends', $this->getLanguage());
        $languageSuffix = oxRegistry::getLang()->getLanguageTag($this->getLanguage());

        $query = "insert into {$table} (oxid, oetags$languageSuffix) value (" . $database->quote($this->getArticleId()) . ", '{$tags}')
               on duplicate key update oetags$languageSuffix = '{$tags}'";

        if ($database->execute($query)) {
            $this->executeDependencyEvent();

            return true;
        }

        return false;
    }


    /**
     * Saves article tags.
     *
     * @param string $tag article tag
     *
     * @return bool
     */
    public function set($tag)
    {
        return $this->_oTagSet->set($tag);
    }

    /**
     * Returns article tags set object.
     *
     * @return object;
     */
    public function get()
    {
        return $this->_oTagSet;
    }

    /**
     * Returns article tags array.
     *
     * @return object;
     */
    public function getArray()
    {
        return $this->_oTagSet->get();
    }

    /**
     * Adds tag to list.
     *
     * @param string $tag tag as string or as oetagsTag object
     *
     * @return bool
     */
    public function addTag($tag)
    {
        return $this->_oTagSet->addTag($tag);
    }

    /**
     * Returns standard product Tag URL.
     *
     * @param string $tag tag
     *
     * @return string
     */
    public function getStdTagLink($tag)
    {
        $stdTagLink = $this->getConfig()->getShopHomeURL($this->getLanguage(), false);

        return $stdTagLink . "cl=details&amp;anid=" . $this->getId() . "&amp;listtype=tag&amp;searchtag=" . rawurlencode($tag);
    }

    /**
     * Checks if tags was already tagged for the same product.
     *
     * @param string $tagTitle given tag
     *
     * @return bool
     */
    public function canBeTagged($tagTitle)
    {
        $products = oxRegistry::getSession()->getVariable("aTaggedProducts");
        if (isset($products) && $tags = $products[$this->getArticleId()]) {
            if ($tags[$tagTitle] == 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute cache dependencies.
     */
    public function executeDependencyEvent()
    {
        $this->_updateTagDependency();
    }

    /**
     * Execute cache dependencies.
     *
     */
    protected function _updateTagDependency()
    {
        // reset tags cloud cache
        $tagList = oxNew("oetagsTagList");
        $tagList->setLanguage($this->getLanguage());
        $tagCloud = oxNew("oetagsTagCloud");
        $tagCloud->setTagList($tagList);
        $tagCloud->resetCache();
    }

    /**
     * Should article tags be saved.
     * Method is used to overwrite.
     *
     * @return bool
     */
    protected function canSave()
    {
        $canSave = true;
        if ( method_exists($this, 'canUpdate') && !($this->canUpdate() && $this->canUpdateField('oetags'))) {
            $canSave = false;
        }
        return $canSave;
    }
}
