<form action="<?= $controller->url_for('settings/statusgruppen/assign') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Person einer Gruppe zuordnen') ?></legend>

        <label>
            <?= _('Einrichtung und Funktion auswählen') ?>:
            <select required name="role_id" class="role-selector">
                <option value="">-- <?= _('Bitte auswählen') ?> --</option>
                <?= $this->render_partial('settings/statusgruppen/_optgroup', ['data' => $admin_insts]) ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Zuweisen'), 'assign') ?>
    </footer>
</form>
