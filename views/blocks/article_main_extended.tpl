[{$smarty.block.parent}]
<tr>
    <td class="edittext">
        <label for="tags">[{oxmultilang ident="OETAGS_ARTICLE_MAIN_TAGS"}]&nbsp;</label>
    </td>
    <td class="edittext">
        <input type="text" id="tags" class="editinput" size="32" maxlength="255" name="editval[tags]" value="[{$edit->tags}]">
        [{oxinputhelp ident="OETAGS_HELP_ARTICLE_MAIN_TAGS"}]
    </td>
</tr>
