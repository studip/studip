<p>
    <?= _('Hier können Sie eine neue Wiki-Seite erstellen.') ?>
</p>

<form action="<?= URLHelper::getLink('wiki.php', ['view' => 'editnew', 'lastpage' => $keyword]) ?>" method="post" class="default">
    <label>
        <span class="required"><?= _('Titel') ?></span>
        <input required type="text" name="keyword"
               placeholder="<?= _('Name der Wiki-Seite') ?>">
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('wiki.php', compact('keyword'))) ?>
    </footer>
</form>
