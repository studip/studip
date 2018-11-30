<form action="<?= $controller->link_for('wiki/store_pageperms', compact('keyword')) ?>" method="post" class="default" id="wiki-config">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset class="global-permissions">
        <label>
            <input type="checkbox" name="page_global_perms" value="1"
                   data-deactivates=".read-permissions :radio, .edit-permissions :radio"
                   <? if ($config->isDefault()) echo 'checked'; ?>>
            <?= _('Standard Wiki-Einstellungen verwenden') ?>
        </label>
    </fieldset>

    <fieldset class="read-permissions">
        <legend><?= _('Leseberechtigung') ?></legend>

        <label>
            <input type="radio" name="page_read_perms" id="autor_read" value="0"
                   <? if (!$config->read_restricted) echo 'checked'; ?>
                   title="<?= _('Wiki-Seite für alle Teilnehmende lesbar') ?>"
                   data-activates=".edit-permissions :radio">
            <?= _('Alle in der Veranstaltung') ?>
        </label>
        <label>
            <input type="radio" name="page_read_perms" id="tutor_read" value="1"
                   <? if ($config->read_restricted) echo 'checked'; ?>
                   title="<?= _('Wiki-Seite nur eingeschränkt lesbar') ?>"
                   data-deactivates="#autor_edit" data-activates="#tutor_edit">
            <?= _('Lehrende und Tutor/innen') ?>
        </label>
    </fieldset>

    <fieldset class="edit-permissions">
        <legend><?= _('Editierberechtigung') ?></legend>

        <label>
            <input type="radio" name="page_edit_perms" id="autor_edit" value="0"
                   <? if (!$config->edit_restricted) echo 'checked'; ?>
                   title="<?= _('Nur editierbar, wenn für alle Teilnehmenden lesbar') ?>">
            <?= _('Alle in der Veranstaltung') ?>
        </label>
        <label>
            <input type="radio" name="page_edit_perms" id="tutor_edit" value="1"
                   <? if ($config->edit_restricted) echo 'checked'; ?>
                   title="<?= _('Nur editierbar, wenn für diesen Personenkreis lesbar') ?>">
            <?= _('Lehrende und Tutor/innen') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            URLHelper::getURL('wiki.php', compact('keyword'))
        ) ?>
    </footer>
</form>
