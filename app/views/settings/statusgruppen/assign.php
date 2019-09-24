<form action="<?= $controller->link_for('settings/statusgruppen/assign') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Person einer Gruppe zuordnen') ?></legend>

        <label>
            <?= _('Einrichtung und Funktion auswählen') ?>:
            <select required name="role_id" class="role-selector">
                <option value="">-- <?= _('Bitte auswählen') ?> --</option>
            <? if ($admin_insts && is_array($admin_insts)): ?>
                <?= $this->render_partial('settings/statusgruppen/_optgroup', ['data' => $admin_insts]) ?>
            <? endif; ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Zuweisen'), 'assign') ?>
    </footer>
</form>
