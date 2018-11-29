<form action="<?= $controller->link_for('wiki/store_courseperms', compact('keyword')) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <label><?= _('Editierberechtigung') ?></label>

        <label>
            <input type="radio" name="courseperms" value="autor"
                   <? if ($status === 'autor') echo 'checked'; ?>>
            <?= _('Alle in der Veranstaltung') ?>
        </label>
        <label>
            <input type="radio" name="courseperms" value="tutor"
                   <? if ($status === 'tutor') echo 'checked'; ?>>
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
