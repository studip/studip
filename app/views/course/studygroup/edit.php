<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>

<form action="<?= $controller->update() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Studiengruppe bearbeiten') ?>
        </legend>

        <input type='submit' class="invisible" name="<?=_('Änderungen übernehmen') ?>" aria-hidden="true">
        <label>
            <span class="required"><?= _('Name') ?></span>
            <input type='text' name='groupname' value="<?= htmlReady($sem->getName()) ?>">
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="groupdescription"><?= htmlReady($sem->description) ?></textarea>
        </label>

        <? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id)) : ?>
            <?= $this->render_partial('course/studygroup/_replace_founder', compact('tutors')) ?>
        <? endif; ?>

        <label>
            <?= _('Zugang') ?>
            <select name="groupaccess">
                <option value="all" <? if (!$sem->admission_prelim) echo 'selected'; ?>>
                    <?= _('Offen für alle') ?>
                </option>
                <option value="invite" <? if ($sem->admission_prelim) echo 'selected'; ?>>
                    <?= _('Auf Anfrage') ?>
                </option>
            <? if (Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED || !$sem->visible): ?>
                <option value="invisible" <? if (!$sem->visible) echo 'selected'; ?> <? if (!Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED) echo 'disabled'; ?>>
                    <?= _('Unsichtbar') ?>
                </option>
            <? endif; ?>
            </select>
        </label>

    </fieldset>

    <footer>
        <?= Button::createAccept(_('Übernehmen'), ['title' => _("Änderungen übernehmen")]); ?>
        <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('seminar_main.php')); ?>
    </footer>
</form>
