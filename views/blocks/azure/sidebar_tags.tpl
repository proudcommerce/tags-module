[{if $oViewConf->showTags($oView) && $oView->getClassName() ne "details" && $oView->getClassName() ne "alist" && $oView->getClassName() ne "suggest" && $oView->getClassName() ne "tags"}]
    [{oxid_include_widget nocookie=1 cl="oetagsTagCloudWidget" blShowBox="1" noscript=1}]
[{/if}]
