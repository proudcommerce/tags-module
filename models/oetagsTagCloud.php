<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

if (!defined('OETAGCLOUD_MINFONT')) {
    define('OETAGCLOUD_MINFONT', 100);
    define('OETAGCLOUD_MAXFONT', 400);
    define('OETAGCLOUD_MINOCCURENCETOSHOW', 2);
    define('OETAGCLOUD_STARTPAGECOUNT', 20);
    define('OETAGCLOUD_EXTENDEDCOUNT', 200);
}

/**
 * Class dedicated to tag cloud handling
 *
 */
class oetagsTagCloud extends \oxSuperCfg
{

    /**
     * Cloud cache key
     *
     * @var string
     */
    protected $_sCacheKey = "tagcloud";

    /**
     * Max hit
     *
     * @var int
     */
    protected $_iMaxHit = null;

    /**
     * Cloud array
     *
     * @var array
     */
    protected $_aCloudArray = null;

    /**
     * Extended mode
     *
     * @var bool
     */
    protected $_blExtended = false;


    /**
     * Maximum tag's length
     * Maximum size of one tag in admin area and limits tag input field in front end
     *
     * @var int
     */
    protected $_iTagMaxLength = 60;

    /**
     * Object constructor. Initializes separator.
     */
    public function __construct()
    {
    }

    /**
     * Returns current maximum tag length
     *
     * @return int
     */
    public function getTagMaxLength()
    {
        return $this->_iTagMaxLength;
    }

    /**
     * Tag cloud mode setter (extended or not)
     *
     * @param bool $blExtended if true - extended cloud array will be returned
     */
    public function setExtendedMode($blExtended)
    {
        $this->_blExtended = $blExtended;
    }

    /**
     * Extended mode getter
     *
     * @return bool
     */
    public function isExtended()
    {
        return $this->_blExtended;
    }

    /**
     * Sets oetagsITagList object
     *
     * @param oetagsITagList $oTagList Tag cloud set object, which implements oetagsITagList
     */
    public function setTagList(oetagsITagList $oTagList)
    {
        $this->_oTagList = $oTagList;
    }

    /**
     * Returns oetagsITagList object
     *
     * @return oetagsITagList
     */
    public function getTagList()
    {
        return $this->_oTagList;
    }

    /**
     * Sets tag cloud array
     *
     * @param array $aTagCloudArray tag cloud array
     */
    public function setCloudArray($aTagCloudArray)
    {
        $sCacheIdent = $this->_formCacheKey();
        $this->_aCloudArray[$sCacheIdent] = $aTagCloudArray;
    }

    /**
     * Returns extended tag cloud array
     *
     * @return array
     */
    public function getCloudArray()
    {
        $sCacheIdent = $this->_formCacheKey();
        if (!isset($this->_aCloudArray[$sCacheIdent])) {
            $oTagList = $this->getTagList();

            $this->_aCloudArray[$sCacheIdent] = $this->formCloudArray($oTagList);
        }

        return $this->_aCloudArray[$sCacheIdent];
    }

    /**
     * Returns tag cloud array
     *
     * @param oetagsITagList $oTagList Tag List
     *
     * @return array
     */
    public function formCloudArray(oetagsITagList $oTagList)
    {
        // checking if current data is already loaded
        if ($oTagList->getCacheId()) {
            $sCacheIdent = $this->_formCacheKey($oTagList->getCacheId());
            $myUtils = oxRegistry::getUtils();
            // checking cache
            $aCloudArray = $myUtils->fromFileCache($sCacheIdent);
        }

        // loading cloud info
        if ($aCloudArray === null) {
            $oTagList->loadList();
            $oTagSet = $oTagList->get();
            if (count($oTagSet->get()) > $this->getMaxAmount()) {
                $oTagSet->sortByHitCount();
                $oTagSet->slice(0, $this->getMaxAmount());
            }
            $oTagSet->sort();
            $aCloudArray = $oTagSet->get();
            // updating cache
            if ($sCacheIdent) {
                $myUtils->toFileCache($sCacheIdent, $aCloudArray);
            }
        }

        return $aCloudArray;
    }

    /**
     * Returns tag size
     *
     * @param string $sTag tag title
     *
     * @return int
     */
    public function getTagSize($sTag)
    {
        $aCloudArray = $this->getCloudArray();
        if (is_null($aCloudArray[$sTag])) {
            return 1;
        }
        $iCurrSize = $this->_getFontSize($aCloudArray[$sTag]->getHitCount(), $this->_getMaxHit());

        // calculating min size
        return floor($iCurrSize / OETAGCLOUD_MINFONT) * OETAGCLOUD_MINFONT;
    }

    /**
     * Returns maximum amount of tags, that should be shown in list
     *
     * @return int
     */
    public function getMaxAmount()
    {
        if ($this->isExtended()) {
            return OETAGCLOUD_EXTENDEDCOUNT;
        } else {
            return OETAGCLOUD_STARTPAGECOUNT;
        }
    }

    /**
     * Resets tag cache
     *
     * @param int $iLang preferred language [optional]
     */
    public function resetTagCache($iLang = null)
    {
        if ($iLang) {
            $this->setLanguageId($iLang);
        }
        $this->resetCache();
    }

    /**
     * Resets tag cache
     */
    public function resetCache()
    {
        $myUtils = oxRegistry::getUtils();

        $sCacheId = null;
        if (($oTagList = $this->getTagList()) !== null) {
            $sCacheId = $oTagList->getCacheId();
        }

        $myUtils->toFileCache($this->_formCacheKey($sCacheId), null);

        $this->_aCloudArray = null;
    }

    /**
     * Returns tag cache key name.
     *
     * @param string $sTagListCacheId Whether to display full list
     *
     * @return string formed cache key
     */
    protected function _formCacheKey($sTagListCacheId = null)
    {
        $sExtended = $this->isExtended() ? '1' : '0';

        return $this->_sCacheKey . "_" . $this->getConfig()->getShopId() . "_" . $sExtended . "_" . $sTagListCacheId;
    }

    /**
     * Returns max hit
     *
     * @return int
     */
    protected function _getMaxHit()
    {
        if ($this->_iMaxHit === null) {
            $aHits = array_map(array($this, '_getTagHitCount'), $this->getCloudArray());
            $this->_iMaxHit = max($aHits);
        }

        return $this->_iMaxHit;
    }

    /**
     * Returns tag hit count. Used for _getMaxHit array mapping
     *
     * @param oeTagsTag $oTag tag object
     *
     * @return int
     */
    protected function _getTagHitCount($oTag)
    {
        return $oTag->getHitCount();
    }

    /**
     * Returns font size value for current occurrence depending on max occurrence.
     *
     * @param int $iHit    hit count
     * @param int $iMaxHit max hits count
     *
     * @return int
     */
    protected function _getFontSize($iHit, $iMaxHit)
    {
        //handling special case
        if ($iMaxHit <= OETAGCLOUD_MINOCCURENCETOSHOW || !$iMaxHit) {
            return OETAGCLOUD_MINFONT;
        }

        $iFontDiff = OETAGCLOUD_MAXFONT - OETAGCLOUD_MINFONT;
        $iMaxHitDiff = $iMaxHit - OETAGCLOUD_MINOCCURENCETOSHOW;
        $iHitDiff = $iHit - OETAGCLOUD_MINOCCURENCETOSHOW;

        if ($iHitDiff < 0) {
            $iHitDiff = 0;
        }

        $iSize = round($iHitDiff * $iFontDiff / $iMaxHitDiff) + OETAGCLOUD_MINFONT;

        return $iSize;
    }
}
