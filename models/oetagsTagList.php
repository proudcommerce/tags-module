<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

if (!defined('OETAGCLOUD_MINFONT')) {
    define('OETAGCLOUD_MINTAGLENGTH', 4);
    define('OETAGCLOUD_STARTPAGECOUNT', 20);
    define('OETAGCLOUD_EXTENDEDCOUNT', 200);
}

/**
 * Class dedicated to article tags handling.
 * Is responsible for saving, returning and adding tags for given article.
 */
class oeTagsTagList extends \oxI18n implements oetagsITagList
{

    /**
     * Tags array.
     *
     * @var array
     */
    protected $_oTagSet = null;

    /**
     * Extended mode
     *
     * @var bool
     */
    protected $_blExtended = false;

    /**
     * Instantiates oetagstagset object
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oTagSet = oxNew('oetagstagset');
    }

    /**
     * Returns cache id
     *
     * @return string
     */
    public function getCacheId()
    {
        return 'tag_list_' . $this->getLanguage();
    }

    /**
     * Loads all articles tags list.
     *
     * @return bool
     */
    public function loadList()
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $iLang = $this->getLanguage();

        $sArtView = getViewName('oxarticles', $iLang);
        $sViewName = getViewName('oxartextends', $iLang);

        // check if article is still active
        $oArticle = oxNew('oxArticle');
        $oArticle->setLanguage($iLang);
        $sArtActive = $oArticle->getActiveCheckQuery(true);

        $sQ = "SELECT {$sViewName}.`oetags` AS `oetags`
            FROM {$sArtView} AS `oxarticles`
                LEFT JOIN {$sViewName} ON `oxarticles`.`oxid` = {$sViewName}.`oxid`
            WHERE `oxarticles`.`oxactive` = 1 AND $sArtActive";

        $oDb->setFetchMode(oxDb::FETCH_MODE_ASSOC);
        $oRs = $oDb->select($sQ);

        $this->get()->clear();
        //Fetch the results row by row
        if ($oRs != false && $oRs->count() > 0) {
            while (!$oRs->EOF) {
                $row = $oRs->getFields();
                $this->_addTagsFromDb($row['oetags']);
                $oRs->fetchRow();
            }
        }

        return $this->_isLoaded = true;
    }

    /**
     * Returns oeTagsTagSet list
     *
     * @return oeTagsTagSet
     */
    public function get()
    {
        return $this->_oTagSet;
    }

    /**
     * Adds tag to list
     *
     * @param string $mTag tag as string or as oeTagsTag object
     */
    public function addTag($mTag)
    {
        $this->_oTagSet->addTag($mTag);
    }

    /**
     * Adds record from database to tagset
     *
     * @param string $sTags tags string to add
     *
     * @return void
     */
    protected function _addTagsFromDb($sTags)
    {
        if (empty($sTags)) {
            return;
        }
        $sSeparator = $this->get()->getSeparator();
        $aTags = explode($sSeparator, $sTags);
        foreach ($aTags as $sTag) {
            $oTag = oxNew("oetagstag");
            $oTag->set($sTag, false);
            $oTag->removeUnderscores();
            $this->addTag($oTag);
        }
    }
}
