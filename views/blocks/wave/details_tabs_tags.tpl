[{if $oViewConf->showTags($oView) && ( $oView->getTagCloudManager() || ( ( $oView->getTagCloudManager() || $oxcmp_user) && $oDetailsProduct ) )}]
    [{capture append="tabs"}]<a href="#tags" class="nav-link" data-toggle="tab">[{oxmultilang ident="TAGS"}]</a>[{/capture}]
    [{capture append="tabsContent"}]
    <div id="tags" class="tab-pane[{if $blFirstTab}] active[{/if}]">
        [{oxid_include_dynamic file=$oViewConf->getModulePath('oetags','views/wave/tpl/page/details/inc/tags.tpl')}]
    </div>
    [{assign var="blFirstTab" value=false}]
    [{/capture}]
[{/if}]
