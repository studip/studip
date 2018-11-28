<form method="post" action="<?= $controller->url_for("admin/statusgroups/sortAlphabetic/{$group->id}") ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Gruppe alphabetisch sortieren') ?>
        </legend>

        <section>
            <?= sprintf(_('Gruppe %s wirklich alphabetisch sortieren? Die vorherige Sortierung kann nicht wiederhergestellt werden.'), htmlReady($group->name)) ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Sortieren'), 'confirm') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups')) ?>
    </footer>
</form>
