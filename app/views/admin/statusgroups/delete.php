<form method="post" action="<?= $controller->url_for("admin/statusgroups/delete/{$group->id}/{$user->user_id}") ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Nutzer aus Gruppe austragen') ?>
        </legend>

        <section>
            <?= sprintf(_('%s wirklich aus %s austragen?'), htmlReady($user->getFullname()), htmlReady($group->name)) ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Entfernen'), 'confirm') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups/index')) ?>
    </footer>
</form>
