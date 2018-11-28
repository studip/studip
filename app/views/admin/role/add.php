<form action="<?= $controller->url_for('admin/role/add') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend>
            <?= _('Neue Rolle anlegen') ?>
        </legend>

        <label for="name">
            <?= _('Name') ?>:
            <input type="text" name="name" id="name">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'createrolebtn') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                                            $controller->url_for('admin/role')) ?>
    </footer>
</form>
