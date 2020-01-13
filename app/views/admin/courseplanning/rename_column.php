<form class="default" method="post" action="<?= $controller->link_for('admin/courseplanning/rename_column/' . $column_id . '/' . $week_day) ?>" data-dialog="size=auto">
    <label>
        <?= _('Spaltenname') ?>
        <input name="column_name" type="text" value="<?= htmlReady($column_name) ?>">
    </label>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </div>
</form>
