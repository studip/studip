<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_cancel_members') ?>" method="post">
    <section>
        <?= sprintf(ngettext('Soll %u Person wirklich aus %s ausgetragen werden?',
            'Sollen %u Personen wirklich aus %s ausgetragen werden?',
            count($members)), count($members), htmlReady($course_title)) ?>
        <?php foreach ($members as $m) : ?>
            <input type="hidden" name="members[]" value="<?= $m ?>"/>
        <?php endforeach ?>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Austragen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
