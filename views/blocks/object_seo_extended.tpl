[{$smarty.block.parent}]
[{elseif $sListType == "oetag"}]
   [{assign var="oTagLang" value=$otherlang.$iLang}]
   [{assign var="sLabel" value="GENERAL_SEO_TAG"|oxmultilangassign|cat:" "|cat:$oTagLang->sLangDesc}]
