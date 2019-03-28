<? use Studip\Button, Studip\LinkButton; ?>
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
<script>
    MVV.PARENT_ID = '<?= $dokument->getId() ?>';
</script>

<? $perm = MvvPerm::get($dokument) ?>
<form class="default" action="<?= $controller->url_for('/dokument/' . $dokument->id) ?>" method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name:') ?>
            <?= MvvI18N::input('name', $dokument->name, ['maxlength' => '255', 'required' => ''])->checkPermission($dokument) ?>
        </label>
        <label>
            <?= _('Linktext') ?>
            <?= MvvI18N::input('linktext', $dokument->linktext, ['maxlength' => '255', 'required' => ''])->checkPermission($dokument) ?>
        <label>
        <label>
            <?= _('URL des Dokuments') ?>
            <input <?= $perm->disable('url') ?> type="text" id="dokument_url" name="url" maxlength="4000" value="<?= htmlReady($dokument->url) ?>" required>
        <label>
            <?= _('Beschreibung') ?>
            <?= MvvI18N::textarea('beschreibung', $dokument->beschreibung, ['class' => 'add_toolbar ui-resizable wysiwyg'])->checkPermission($dokument) ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($dokument->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Dokument anlegen')]) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
                <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
