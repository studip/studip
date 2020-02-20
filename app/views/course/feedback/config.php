<form method="post" class="default" action="<?= $controller->url_for('course/feedback/config') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <span><?= _('Wer darf neue Feedback-Elemente anlegen?')?></span>
        <select name="create_perm_level">
            <option value="autor" <?= $create_perm_level == "autor" ? 'selected' : '' ?>>
                <?= _('Studierende') ?>
            </option>
            <option value="tutor" <?= $create_perm_level == "tutor" ? 'selected' : '' ?>>
                <?= _('Tutoren') ?></option>
            <option value="dozent" <?= $create_perm_level == "dozent" ? 'selected' : '' ?>>
                <?= _('Dozierende') ?>
            </option>
        </select>
    </label>
    <label>
        <span><?= _('Wer darf Feedback-Elemente und Einträge verwalten?')?></span>
        <select name="admin_perm_level">
            <option value="autor" <?= $admin_perm_level == "autor" ? 'selected' : '' ?>>
                <?= _('Studierende') ?>
            </option>
            <option value="tutor" <?= $admin_perm_level == "tutor" ? 'selected' : '' ?>>
                <?= _('Tutoren') ?>
            </option>
            <option value="dozent" <?= $admin_perm_level == "dozent" ? 'selected' : '' ?>>
                <?= _('Dozierende') ?>
            </option>
        </select>
    </label>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    </div>
</form>
