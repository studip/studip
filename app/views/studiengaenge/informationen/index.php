<table class="default nohover collapsable">
    <colgroup>
        <col width="70%">
        <col width="29%">
        <col width="1%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Fach') ?></th>
            <th><?= _('Studierende') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($studycourses as $key => $studycourse) : ?>
        <? $count = UserStudyCourse::countBySql('fach_id = ?', [$studycourse->fach_id]); ?>
        <? if ($count > 0) : ?>
    <tbody class="collapsed">
        <tr class="table-header header-row">
            <td class="toggle-indicator">
                <a id="<?= $studycourse->fach_id?>" class="mvv-load-in-new-row"
                    href="<?= $controller->url_for('/showdegree', $studycourse->fach_id, $key+1)?>">
                    <?= htmlReady($studycourse->name) ?>
                </a>
            </td>
            <td>
                <?= $count ?>
            </td>
            <td class="dont-hide actions">
                <a href="<?= $controller->url_for('/messagehelper', ['fach_id' => $studycourse->fach_id]) ?>" data-dialog >
                    <?= Icon::create('mail', Icon::ROLE_CLICKABLE,
                        ['title' => htmlReady(sprintf(_('Alle Studierenden des Faches %s benachrichtigen.'),
                                $studycourse->name))]) ?>
                </a>
            </td>
        </tr>
    </tbody>
        <? endif; ?>
    <? endforeach; ?>
</table>
