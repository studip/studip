<? if ($room): ?>
<h2><?= _('Verantwortliche Person') ?></h2>
<? if ($room->owner): ?>
<a href="<?= URLHelper::getLink(
    'dispatch.php/profile',
    ['username' => $room->owner->username]
    ) ?>">
    <?= htmlReady($room->owner->getFullName()) ?>
</a>
<? else: ?>
<?= _('unbekannt') ?>
<? endif ?>

<h2><?= _('Berechtigungen') ?></h2>
<? if (empty($room->permissions)): ?>
<?= MessageBox::info(_('Für diesen Raum sind keine besonderen Berechtigungen eingerichtet!')) ?>
<? endif ?>
<table class="default">
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Rechtestufe') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($room->permissions as $permission): ?>
        <tr>
            <td><?= htmlReady($permission->user->getFullName()) ?></td>
            <td><?= htmlReady($permission->perms) ?></td>
            <td>
                <a href="<?= URLHelper::getLink(
                    'dispatch.php/resources/room/revoke_permission/' . $room->id,
                    ['permission_id' => $permission->id]
                    ) ?>" data-dialog>
                    <?= Icon::create('trash')->asImg('20px') ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>

        <tr>
            <td colspan="3">
                <?= \Studip\LinkButton::create(
                    _('Berechtigung hinzufügen'),
                    'javascript:void(window.alert(\'to be implemented\');'
                    ) ?>
            </td>
        </tr>
    </tbody>
</table>
<? endif ?>
