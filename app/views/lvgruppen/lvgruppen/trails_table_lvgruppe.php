<? if (count($trails)) : ?>
<table class="default" style="font-size: 0.8em">
    <tr>
        <? foreach (array_reverse($trail_classes) as $trail_class) : ?>
        <th><?= $trail_class::getClassDisplayName() ?></th>
        <? endforeach; ?>
    </tr>
<? foreach ($trails as $key => $trail) : ?>
    <tr>
        <? foreach (array_reverse($trail_classes) as $trail_class) : ?>
            <? if (isset($trail[$trail_class])) : ?>
            <td style="vertical-align: top;">
                <?= htmlReady($trail[$trail_class]->getDisplayName()) ?>
            </td>
            <? else : ?>
            <td style="text-align: center; vertical-align: top;">-</td>
            <? endif; ?>
        <? endforeach; ?>
    </tr>
<? endforeach; ?>
</table>
<? else : ?>
<span class="mvv-no-entry">
<?= _('Diese Lehrveranstaltungsgruppe wurde für das ausgewählte Semester keinen Modulteilen zugeordnet.') ?>
</span>
<? endif; ?>