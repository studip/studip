<form action="<?= $controller->save($action) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= sprintf(_('Log-Aktion %s bearbeiten'), htmlReady($action->name)) ?>
        </legend>

        <label>
            <?= _('Beschreibung') ?>
            <input type="text" name="description"
                   value="<?= htmlReady(Request::get('description', $action->description)) ?>">
        </label>

        <label>
            <?= _('Template') ?>
            <input required type="text" name="info_template"
                   value="<?= htmlReady(Request::get('info_template', $action->info_template)) ?>">
        </label>

        <label>
            <input type="checkbox" name="active" value="1"
                   <? if (Request::int('active', $action->active)) echo 'checked'; ?>>
            <?= _('Aktiv') ?>
        </label>

        <label>
            <?= _('Ablaufzeit in Tagen') ?>
            (<?= _('optional') ?>, <?= _('0 = keine Ablaufzeit') ?>)
            <input type="number" class="size-s" name="expires" min="0"
                   value="<?= Request::int('expires', floor($action->expires / 86400)) ?>">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save', [
            'data-dialog' => 'reload-on-close',
        ]) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->adminURL()
        ) ?>
    </td>

</form>
