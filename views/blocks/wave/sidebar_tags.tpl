[{if $oViewConf->showTags($oView) && $oView->getClassName() != 'start' && $oView->getClassName() != 'tags'}]
    <div class="box well well-sm hidden-xs hidden-sm">
        <section>
            <div class="page-header h3">[{oxmultilang ident="DD_SIDEBAR_TAGCLOUD"}]</div>
            [{oxid_include_widget cl="oetagsTagCloudWidget" nocookie=1 noscript=1}]
        </section>
    </div>
[{/if}]
