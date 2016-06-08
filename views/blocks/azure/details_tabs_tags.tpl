[{if $oViewConf->showTags($oView) && ( $oView->getTagCloudManager() || ( ( $oView->getTagCloudManager() || $oxcmp_user) && $oDetailsProduct ) )}]
    [{capture append="tabs"}]<a href="#tags">[{oxmultilang ident="TAGS"}]</a>[{/capture}]
    [{capture append="tabsContent"}]
        <div id="tags">
            [{oxid_include_dynamic file=$oViewConf->getModulePath('oetags','views/azure/tpl/page/details/inc/tags.tpl')}]
        </div>
    [{/capture}]
[{/if}]
