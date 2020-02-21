<form class="default" method="POST" action="<?= $controller->url_for('admin/courseplanning/viewcolumns/' . $week_day) ?>" data-dialog="size=auto">

    <table class="default">
        <caption><?= _('Sichtbarkeit') ?></caption>
        <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th ></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($columns as $col): ?>
            <? if($col['id'] == '0') continue; ?>
            <tr>
                <td>
                    <input id="column_view_<?= $col['id'] ?>" name="column_view[]"
                           class="studip-checkbox" type="checkbox"
                           value="<?= $col['id'] ?>"
                           <?= $col['visible'] ? 'checked' : '' ?>>
                    <label for="column_view_<?= $col['id'] ?>"><?= htmlReady($col['title']) ?></label>
                </td>
                <td class="actions">
                    <?= ActionMenu::get()->addLink(
                        $controller->url_for('admin/courseplanning/remove_column', $institute_id, $col['id'], $week_day),
                        _('Spalte löschen'),
                        Icon::create('trash'),
                        [
                            'data-confirm' => _('Wollen Sie die Spalte wirklich entfernen?'),
                            'data-dialog'  => 'size=auto',
                        ]
                    ) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>

    <label>
        <?= _('Zusätzliche Spalte') ?>
        <input name="column_name" type="text" value="">
    </label>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </div>
</form>
