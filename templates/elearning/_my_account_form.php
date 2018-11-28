<form method="POST" action="<?= URLHelper::getLink() ?>#anker">
<?= CSRFProtection::tokenTag() ?>
<table class="default">
    <tbody>
        <tr>
            <td>
            <? if ($message): ?>
                <?= $message ?>
            <? else : ?>
                <strong><?= _('Loginname:') ?></strong>
                <?= htmlReady($login) ?>
            <? endif ?>
            </td>
            <td class="actions">
                <input type="hidden" name="new_account_step" value="1">
                <input type="hidden" name="new_account_cms" value="<?= htmlReady($my_account_cms) ?>">
            <? if ($is_connected) : ?>
                <?= Studip\Button::create(_('Bearbeiten'), 'change') ?>
            <? else : ?>
                <?= Studip\Button::create(_('Erstellen'), 'create') ?>
            <? endif?>
            </td>
        </tr>
    </tbody>
</table>
</form>