<?php
/**
 * #PHPHEADER_OETAGS_LICENSE_INFORMATION#
 */

class oetagsUtilsCount extends oetagsUtilsCount_parent
{
    /**
     * Returns specified Tag article count
     *
     * @param string $sTag  tag to search article count
     * @param int    $iLang language
     *
     * @return int
     */
    public function getTagArticleCount($sTag, $iLang)
    {
        $oDb = oxDb::getDb();

        $oArticle = oxNew("oxArticle");
        $sArticleTable = $oArticle->getViewName();
        $sActiveSnippet = $oArticle->getSqlActiveSnippet();
        $sViewName = getViewName('oxartextends', $iLang);

        $sQ = "select count(*) from {$sViewName} inner join {$sArticleTable} on " .
              "{$sArticleTable}.oxid = {$sViewName}.oxid where {$sArticleTable}.oxparentid = '' and" .
              " {$sArticleTable}.oxissearch = 1 AND {$sViewName}.oetags " .
              " like " . $oDb->quote("%" . $sTag . "%") . " and {$sActiveSnippet}";

        return $oDb->getOne($sQ);
    }

}
