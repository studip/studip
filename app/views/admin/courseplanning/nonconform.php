<table class="default">
    <caption>
        <?= _('Folgende regelmäßige Termine von Veranstaltungen passen nicht in das vorgegebene Zeitraster:') ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Nr.') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Start') ?></th>
            <th><?= _('Ende') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($non_conform_dates as $ncd): ?>
        <tr>
            <td><?= htmlReady($ncd['nr']) ?></td>
            <td>
                <?= htmlReady($ncd['name']) ?>
                <a href="<?= $controller->link_for('course/details/index/' . $ncd['cid']) ?>" data-dialog="size=auto">
                    <?= Icon::create('info-circle')->asImg(['class' => 'text-bottom']) ?>
                </a>
            </td>
            <td><?= htmlReady($ncd['start']) ?></td>
            <td><?= htmlReady($ncd['end']) ?></td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
