<table class="blank"  align="center" valign="top" width="100%" border="0" cellpadding="1" cellspacing="0">
    <tr valign="top">
        <td class="table_row_odd" align="left" width="40%">
            <font size="-1"><b>
            <?=$title?>
            </b></font>
        </td>
        <td class="table_row_odd" align="left" width="40%">
        <? if ($all_open) : ?>
            <a href="<?=URLHelper::getURL('?close_all=1&view='.$view.'&cms_select='.$cms_select.'&search_key='.$search_key)?>">
                <?= Assets::img('close_all.png', tooltip2(_('Alle Module schließen'))) ?>
            </a>
        <? else : ?>
            <a href="<?=URLHelper::getLink('?open_all=1&view='.$view.'&cms_select='.$cms_select.'&search_key='.$search_key)?>">
                <?= Assets::img('open_all.png', tooltip2(_('Alle Module öffnen'))) ?>
            </a>
        <? endif?>
        </td>
    </tr>
</table>
