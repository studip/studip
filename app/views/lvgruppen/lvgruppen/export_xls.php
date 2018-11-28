<table>
    <tr>
        <th><?= _('LV-Gruppe') ?></th>
        <th><?= _('Verantw. Einrichtung Modul') ?></th>
        <th><?= _('Abschluss') ?></th>
        <th><?= _('Studiengang') ?></th>
        <th><?= _('Version des Studiengangteils') ?></th>
        <th><?= _('Modul') ?></th>
        <th><?= _('Modulteil') ?></th>
    </tr>
    <? $trail_classes = words('Modulteil Modul StgteilAbschnitt StgteilVersion '
                . 'Studiengang Fachbereich'); ?>
    <? foreach ($lvgruppen as $lvgruppe) : ?>
        <? $trails = $lvgruppe->getTrails($trail_classes, MvvTreeItem::TRAIL_SHOW_INCOMPLETE); ?>
        <? if (count($trails)) : ?>
            <? foreach ($trails as $trail) : ?>
                <tr>
                    <td><?= htmlReady($lvgruppe->getDisplayName()) ?></td>
                <? foreach (array_reverse($trail_classes) as $trail_class) : ?>
                    <? if ($trail[$trail_class]) : ?>
                        <td><?= htmlReady($trail[$trail_class]->getDisplayName()) ?></td>
                    <? else : ?>
                        <td></td>
                    <? endif; ?>
                <? endforeach; ?>
                </tr>
            <? endforeach; ?>
        <? else : ?>
        <tr>
            <td><?= htmlReady($lvgruppe->getDisplayName()) ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <? endif; ?>
    <? endforeach; ?>
</table>
