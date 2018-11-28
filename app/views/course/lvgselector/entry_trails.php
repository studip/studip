<?
if ($pathes) {
    $trails = $area->getTrails([
        'Modulteil',
        'StgteilabschnittModul',
        'StgteilAbschnitt',
        'StgteilVersion',
        'Studiengang']);
    $pathes = ModuleManagementModelTreeItem::getPathes($trails);
}
?>
<? if (count($pathes)) : ?>
<? foreach ($pathes as $path) : ?>
    <li style="background-color:inherit;padding-left:20px;color:#666666">
        <?= htmlReady($path) ?>
    </li>
<? endforeach; ?>
<? else : ?>
<li style="background-color:inherit;padding-left:20px;color:#666666">
    <?= _('Keine Module in den Semestern der Veranstaltung verfügbar.'); ?>
</li>
<? endif; ?>
