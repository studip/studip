<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_delete_members',
        $source_group->id) ?>" method="post">
    <section>
        <?= sprintf(ngettext('Soll %u Person wirklich aus %s entfernt werden?',
            'Sollen %u Personen wirklich aus %s entfernt werden?',
            count($members)), count($members), htmlReady($source_group->name)) ?>
        <?php foreach ($members as $m) : ?>
            <input type="hidden" name="members[]" value="<?= $m ?>"/>
        <?php endforeach ?>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Entfernen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
