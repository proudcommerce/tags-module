[{$smarty.block.parent}]
<tr>
    <td class="edittext">
        [{oxmultilang ident="OETAGS_ARTICLE_MAIN_TAGS"}]&nbsp;
    </td>
    <td class="edittext">
        <input type="text" class="editinput" size="32" maxlength="255" name="editval[tags]" value="[{$edit->tags}]">
        [{oxinputhelp ident="OETAGS_HELP_ARTICLE_MAIN_TAGS"}]
    </td>
</tr>
