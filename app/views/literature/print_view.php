<h1>
<?=htmlReady($title)?>
</h1>
<? if ( $list ) : ?>
    <?=$list?>
<? else : ?>
    <?=_("Es wurde noch keine Literatur erfasst")?>
<? endif ?>
<table width=100% border=0 cellpadding=2 cellspacing=0>
    <tr>
        <td>
            <i><font size=-1><?=_("Stand:") . " ".date("d.m.y, H:i",time())?></font></i>
        </td>
        <td align="right">
            <font size=-2>
                <?= Assets::img('logos/logo2b.png') ?>
            <br>&copy; <?=date("Y", time())." v.".$GLOBALS['SOFTWARE_VERSION']?>&nbsp; &nbsp; 
            </font>
        </td>
    </tr>
</table>