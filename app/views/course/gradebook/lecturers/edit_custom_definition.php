<form class="default" action="<?= $controller->link_for('course/gradebook/lecturers/update_custom_definition', $definition->id) ?>" method="POST">
    <?= CSRFProtection::tokenTag()?>
    <fieldset>
        <label>
            <?= _('Name der Leistung') ?>
            <input type="text" name="name" value="<?= htmlReady($definition->name) ?>" required>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers/custom_definitions')) ?>
    </footer>
</form>
