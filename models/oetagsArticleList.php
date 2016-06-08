<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

use OxidEsales\Eshop\Core\Registry;

/**
 * Article list manager.
 * Collects list of article according to collection rules (categories, etc.).
 *
 */
class oetagsArticleList extends oetagsArticleList_parent
{
    /**
     * Loads a list of articles having
     *
     * @param string $sTag  Searched tag
     * @param int    $iLang Active language
     *
     * @return int
     */
    public function loadTagArticles($sTag, $iLang)
    {
        $oListObject = $this->getBaseObject();
        $sArticleTable = $oListObject->getViewName();
        $sArticleFields = $oListObject->getSelectFields();
        $sViewName = getViewName('oxartextends', $iLang);

        $oTag = oxNew('oetagstag', $sTag);
        $oTag->addUnderscores();
        $sTag = $oTag->get();

        $sQ = "select {$sArticleFields} from {$sViewName} inner join {$sArticleTable} on " .
              "{$sArticleTable}.oxid = {$sViewName}.oxid where {$sArticleTable}.oxparentid = '' and " .
              "(   {$sViewName}.oetags like " . oxDb::getDb()->quote($sTag) .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote("%," . $sTag . ",%") .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote("%," . $sTag) .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote($sTag . ",%") . ")";

        // checking stock etc
        if (($sActiveSnippet = $oListObject->getSqlActiveSnippet())) {
            $sQ .= " and {$sActiveSnippet}";
        }

        if ($this->_sCustomSorting) {
            $sSort = $this->_sCustomSorting;
            if (strpos($sSort, '.') === false) {
                $sSort = $sArticleTable . '.' . $sSort;
            }
            $sQ .= " order by $sSort ";
        }

        $this->selectString($sQ);

        // calc count - we can not use count($this) here as we might have paging enabled
        return Registry::get("oxUtilsCount")->getTagArticleCount($sTag, $iLang);
    }

    /**
     * Returns array of article ids belonging to current tags
     *
     * @param string $sTag  current tag
     * @param int    $iLang active language
     *
     * @return array
     */
    public function getTagArticleIds($sTag, $iLang)
    {
        $oListObject = $this->getBaseObject();
        $sArticleTable = $oListObject->getViewName();
        $sViewName = getViewName('oxartextends', $iLang);

        $oTag = oxNew('oetagstag', $sTag);
        $oTag->addUnderscores();
        $sTag = $oTag->get();

        $sQ = "select {$sViewName}.oxid from {$sViewName} inner join {$sArticleTable} on " .
              "{$sArticleTable}.oxid = {$sViewName}.oxid where {$sArticleTable}.oxparentid = '' and {$sArticleTable}.oxissearch = 1 and " .
              "(   {$sViewName}.oetags like " . oxDb::getDb()->quote($sTag) .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote("%," . $sTag . ",%") .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote("%," . $sTag) .
              " or {$sViewName}.oetags like " . oxDb::getDb()->quote($sTag . ",%") . ")";

        // checking stock etc
        if (($sActiveSnippet = $oListObject->getSqlActiveSnippet())) {
            $sQ .= " and {$sActiveSnippet}";
        }

        if ($this->_sCustomSorting) {
            $sSort = $this->_sCustomSorting;
            if (strpos($sSort, '.') === false) {
                $sSort = $sArticleTable . '.' . $sSort;
            }
            $sQ .= " order by $sSort ";
        }

        return $this->_createIdListFromSql($sQ);
    }

    /**
     * Get description join.
     * Needed in case of searching for data in table oxartextends or its views.
     *
     * @return string
     */
    protected function getDescriptionJoin()
    {
        $table = Registry::get('oxTableViewNameGenerator')->getViewName('oxarticles');
        $descriptionJoin = '';
        $searchColumns = $this->getConfig()->getConfigParam('aSearchCols');

        if (is_array($searchColumns) && (in_array('oxlongdesc', $searchColumns) || in_array('oetags', $searchColumns))) {
            $viewName = getViewName('oxartextends');
            $descriptionJoin = " LEFT JOIN $viewName ON {$viewName}.oxid={$table}.oxid ";
        }
        return $descriptionJoin;
    }

    /**
     * Get search table name.
     * Needed in case of searching for data in table oxartextends or its views.
     *
     * @param string $table
     * @param string $field Chose table depending on field.
     *
     * @return string
     */
    protected function getSearchTableName($table, $field)
    {
        $searchTable = $table;

        if ($field == 'oxlongdesc' || $field == 'oetags') {
            $searchTable = Registry::get('oxTableViewNameGenerator')->getViewName('oxartextends');
        }

        return $searchTable;
    }
}
