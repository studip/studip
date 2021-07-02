<form action="<?= $controller->link_for('wiki/store_page_config', compact('keyword')) ?>" method="post" class="default" id="wiki-config">
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
                   title="<?= _('Wiki-Seite für alle Teilnehmenden lesbar') ?>"
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

    <fieldset>
        <legend><?= _('Vorgängerseite') ?></legend>
        <label>
            <? if ($keyword === "WikiWikiWeb") : ?>
                <p><?= _("Diese Wikiseite darf keine Vorgängerseite haben.") ?></p>
            <? else : ?>
            <select name="ancestor_select" id="ancestor_select">
            <option value=""> <?= _('keine Vorgängerseite') ?> </option>
                <? foreach ($validKeywords as $validKeyword) : ?>
                    <option value="<?= htmlReady($validKeyword) ?>" <?= $page->ancestor === $validKeyword ? 'selected="selected"' : '' ?> >
                        <?= $validKeyword === 'WikiWikiWeb' ? _('Wiki-Startseite') : htmlReady($validKeyword) ?>
                    </option>
                <? endforeach ?>
            </select>
            <? endif ?>
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
