[{assign var="oDetailsProduct" value=$oView->getProduct()}]
[{if $oViewConf->showTags($oView) && ( $oView->getTagCloudManager() || ( $oxcmp_user && $oDetailsProduct ) )}]
    [{oxscript include='js/widgets/oxajax.min.js'}]
    [{oxscript include=$oViewConf->getModuleUrl('oetags','out/src/js/widgets/oetag.min.js')}]
    [{oxscript add="$('p.tagCloud a.tagText').click(oeTag.highTag);"}]
    [{oxscript add="$('#saveTag').click(oeTag.saveTag);"}]
    [{oxscript add="$('#cancelTag').click(oeTag.cancelTag);"}]
    [{oxscript add="$('#editTag').click(oeTag.editTag);"}]

    <p class="tagCloud">
        [{assign var="oCloudManager" value=$oView->getTagCloudManager()}]
        [{if $oCloudManager->getCloudArray()|count < 0}]
        [{oxmultilang ident="NO_TAGS"}]
        [{/if}]
        [{foreach from=$oCloudManager->getCloudArray() item="oTag" name="detailsTags"}]
        <a class="btn btn-info tagitem_[{$oCloudManager->getTagSize($oTag->getTitle())}]" href="[{$oTag->getLink()}]">[{$oTag->getTitle()}]</a>
        [{/foreach}]
    </p>

    [{if $oDetailsProduct && $oView->canChangeTags()}]
    <form action="[{$oViewConf->getSelfActionLink()}]#tags" method="post" id="tagsForm">
        <div class="hidden">
            [{$oViewConf->getHiddenSid()}]
            [{$oViewConf->getNavFormParams()}]
            <input type="hidden" name="cl" value="[{$oViewConf->getTopActiveClassName()}]">
            <input type="hidden" name="aid" value="[{$oDetailsProduct->oxarticles__oxid->value}]">
            <input type="hidden" name="anid" value="[{$oDetailsProduct->oxarticles__oxnid->value}]">
            <input type="hidden" name="fnc" value="editTags">
        </div>
        <div class="form-group">
            <button class="submitButton btn btn-primary" id="editTag" type="submit">
                <i class="fa fa-pencil"></i> [{oxmultilang ident="EDIT_TAGS"}]
            </button>
        </div>
    </form>
    [{/if}]
    [{/if}]
