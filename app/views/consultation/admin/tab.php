<form action="<?= $controller->tab($from_expired) ?>" method="post" class="default">
    <fieldset>
        <legend><?= _('Namen des Reiters ändern') ?></legend>

        <label>
            <?= _('Aktueller Name') ?>
            <?= I18N::input('tab_title', $current_title, ['required' => '']) ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createCancel(
            _('Abbrechen'),
            $from_expired ? $controller->indexURL() : $controller->expiredURL()
        ) ?>
</form>
