<?
# Lifter010: TODO
use Studip\Button;
?>
<form action="<?= $controller->url_for('admin/user/delete') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
<? if ($users) : ?>
    <? $details = [] ?>
    <? foreach ($users as $user) : ?>
        <? $details[] = htmlReady(sprintf('%s (%s)', $user->getFullName(), $user->username)) ?>
        <input type="hidden" name="user_ids[]" value="<?= $user['user_id'] ?>">
    <? endforeach ?>
<? endif ?>
    <?= MessageBox::warning(_('Wollen Sie die folgenden Nutzer wirklich löschen?'), $details) ?>
    <label>
        <input id="documents" name="documents" value="1" checked type="checkbox">
        <?= _('Dokumente löschen?') ?>
    </label>
    <label>
        <input id="mail" name="mail" value="1" checked type="checkbox">
        <?= _('E-Mail-Benachrichtigung verschicken?') ?>
    </label>
    <footer data-dialog-button>
        <?= Button::createAccept(_('JA!'), 'delete', ['title' => _('Benutzer löschen')]) ?>
        <?= Button::createCancel(_('NEIN!'), 'back', ['title' => _('Abbrechen   ')]) ?>
    </footer>
</form>
