<form action="<?= $controller->notice($course) ?>" method="post" data-dialog class="default">
    <fieldset>
        <legend>
            <?= sprintf(_('Notiz für "%s"'), htmlReady($course->getFullName())) ?>
        </legend>

        <label>
            <?= _('Notiz') ?>
            <textarea name="notice"><?= htmlReady($notice) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            URLHelper::getLink("dispatch.php/admin/course/index#course-{$course->id}")
        ) ?>
    </footer>
</form>
