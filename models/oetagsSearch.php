<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

/**
 * Implements search
 *
 */
class oetagsSearch extends oetagsSearch_parent
{
    /**
     * Get description join. Needed in case of searching for data in table oxartextends or its views.
     *
     * @param string $table
     *
     * @return string
     */
    protected function getDescriptionJoin($table)
    {
        $descriptionJoin = '';
        $searchColumns = $this->getConfig()->getConfigParam('aSearchCols');

        if (is_array($searchColumns) && (in_array('oxlongdesc', $searchColumns) || in_array('oetags', $searchColumns))) {
            $viewName = getViewName('oxartextends', $this->_iLanguage);
            $descriptionJoin = " LEFT JOIN {$viewName } ON {$table}.oxid={$viewName }.oxid ";
        }
        return $descriptionJoin;
    }

    /**
     * Get search field name.
     * Needed in case of searching for data in table oxartextends or its views.
     *
     * @param string $table
     * @param string $field Chose table depending on field.
     *
     * @return string
     */
    protected function getSearchField($table, $field)
    {
        if ($field == 'oxlongdesc' || $field == 'oetags') {
            $searchField = getViewName('oxartextends', $this->_iLanguage) . ".{$field}";
        } else {
            $searchField = "{$table}.{$field}";
        }
        return $searchField;
    }
}
