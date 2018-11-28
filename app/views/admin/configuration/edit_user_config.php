<form action="<?= $controller->url_for('admin/configuration/edit_user_config/' . $user_id . '?id=' . $field) ?>" method="post" data-dialog class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Konfigurationsparameter bearbeiten') ?>
        </legend>

        <h1><?= htmlReady(User::find($user_id)->getFullname()) ?> - <?= htmlReady($field) ?></h1>

        <?= $this->render_partial('admin/configuration/type-edit.php', $config) ?>
        <label>
            <?= _('Beschreibung:') ?> (<em>description</em>)
            <textarea name="descriptio" readonly><?= htmlReady($config['description']) ?></textarea>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('admin/configuration/user_configuration', compact('user_id'))) ?>
    </footer>
</form>
