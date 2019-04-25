<form class="default" action="<?= $controller->url_for('admin/statusgroups/editGroup/' . $group->id) ?>#group-<?= $group->id ?>" method="post">
    <fieldset>
        <legend>
            <?= _('Gruppe bearbeiten') ?>
        </legend>

        <label>
            <span class="required"><?= _('Gruppenname') ?></span>
            <?= I18N::input('name', $group->name, [
                'required'    => '',
                'class'       => 'groupname',
                'size'        => 50,
                'placeholder' => _('Mitarbeiterinnen und Mitarbeiter'),
            ]) ?>
        </label>
        <label>
            <?= _('Weibliche Bezeichnung') ?>
            <?= I18N::input('name_w', $group->name_w, [
                'size'        => 50,
                'placeholder' => _('Mitarbeiterin'),
            ]) ?>
        </label>
        <label>
            <?= _('Männliche Bezeichnung') ?>
            <?= I18N::input('name_m', $group->name_m, [
                'size'        => 50,
                'placeholder' => _('Mitarbeiter'),
            ]) ?>
        </label>

    <? if ($type['needs_size']): ?>
        <label>
            <?= _('Größe') ?>
            <input name="size" type="text" size="10"
                   value="<?= htmlReady($group->size) ?>"
                   placeholder="<?= _('Unbegrenzt') ?>">
        </label>
    <? endif; ?>

    <? foreach ($group->getDatafields() as $field): ?>
        <?= $field->getHTML('datafields') ?>
    <? endforeach; ?>

    <? if ($type['needs_self_assign']): ?>
        <label>
            <?= _('Selbsteintrag') ?>
            <input name="selfassign" type="checkbox" value="1"
                   <? if ($group->selfassign) echo 'checked'; ?>>
        </label>
    <? endif; ?>

        <noscript>
            <label>
                <?= _('Position') ?>
                <input name="size" type="text" size="10"
                       value="<?= htmlReady($group->position) ?>"
                       placeholder="0">
            </label>
        </noscript>

        <label>
            <?= _('Einordnen unter') ?>
            <select name="range_id" class="nested-select">
                <option value="<?= htmlReady(Context::getId()) ?>">
                    - <?= _('Hauptebene') ?> -
                </option>
                <?= $this->render_partial("admin/statusgroups/_edit_subgroupselect.php", ['groups' => $groups, 'selected' => $group, 'level' => 0]) ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups')) ?>
    </footer>
</form>
