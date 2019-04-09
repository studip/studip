<?
if (count($mvv_pathes)) {
?>
<font size="-1">
    <b><?= _('Modulzuordnung:') ?></b><br>
    <ul style="margin:0; padding-left:2em;">
    <?
    foreach ($mvv_pathes as $mvv_path) {
        $out = [];
        foreach ($mvv_path as $mvv_object) {
            if ($mvv_object instanceof StgteilabschnittModul) {
                $modul_id = $mvv_object->getId();
            }
            $out[] = $mvv_object->getDisplayName();
        }
    ?>
        <li><a data-dialog href="<?= $controller->url_for('search/module/show/' . $modul_id . '/1') ?>"><?= htmlReady(implode(' > ', $out)) ?></a></li>
    <? } ?>
    </ul>
    <br>
</font>
<? } else { ?>
<?= _('Keine Modulzuordnungen verfügbar') ?>
<? } ?>
