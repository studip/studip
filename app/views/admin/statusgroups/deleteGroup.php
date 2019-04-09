<form method="post" action="<?= $controller->url_for("admin/statusgroups/deleteGroup/{$group->id}") ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Gruppe löschen') ?>
        </legend>

        <section>
            <?= sprintf(_('Gruppe %s wirklich löschen?'), htmlReady($group->name)) ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Löschen'), 'confirm', ['data-dialog-button' => '']) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups/index'), ['data-dialog-button' => '', 'data-dialog' => 'close']) ?>
    </footer>
</form>
