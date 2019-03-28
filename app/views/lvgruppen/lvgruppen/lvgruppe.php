<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($lvgruppe) ?>
<form data-dialog="size=auto" class="default" action="<?= $submit_url ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Lehrveranstaltungsgruppe') ?></legend>
        <label>
            <?= _('Name') ?>
            <input <?= $perm->disable('name') ?> id="name" type="text" name="name" value="<?= htmlReady($lvgruppe->name) ?>" size="50" maxlength="250">
        </label>
        <label>
            <?= _('Alternativtext') ?>
            <?= MvvI18N::textarea('alttext', $lvgruppe->alttext, ['class' => 'add_toolbar ui-resizable wysiwyg'])->checkPermission($lvgruppe) ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($lvgruppe->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
                <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Lehrveranstaltungsgruppe anlegen')]) ?>
            <? endif; ?>
        <? elseif ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
