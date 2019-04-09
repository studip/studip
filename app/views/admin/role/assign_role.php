<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/role/assign_role') ?>" class="default" method="POST">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Rollen für Benutzer verwalten') ?>
        </legend>

    <? if (empty($users)): ?>
        <label>
            <?= _('Benutzer') ?>
            <input type="text" name="username" value="<?= htmlReady($username) ?>" placeholder="<?= _('Name der Person') ?>">
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Suchen'), 'search', ['title' => _('Benutzer suchen')])?>
    </footer>
    <? else: ?>
        <label>
            <?= _('Benutzer') ?>
            <select name="usersel" style="min-width: 300px;">
            <? foreach ($users as $user): ?>
                <option value="<?= $user->id ?>" <? if ($currentuser && $currentuser->id === $user->id) echo 'selected'; ?>>
                    <?= htmlReady(sprintf('%s %s (%s)', $user->vorname, $user->nachname, $user->username)) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Auswählen'), 'select', ['title' => _('Benutzer auswählen')])?>
        <?= LinkButton::create(_('Zurücksetzen'), $controller->url_for('admin/role/assign_role'), ['title' => _('Suche zurücksetzen')])?>
    </footer>
    <? endif ?>
</form>

<? if (isset($currentuser)): ?>
    <br>
    <form action="<?= $controller->url_for('admin/role/save_role', $currentuser->id) ?>" method="POST">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
        <table class="default nohover">
            <tr>
                <th style="text-align: center;">
                    <? printf(_('Rollen für %s'), htmlReady($currentuser->vorname . ' ' . $currentuser->nachname)) ?>
                </th>
                <th></th>
                <th><?= _('Verfügbare Rollen') ?></th>
            </tr>
            <tr class="table_row_even">
                <td style="text-align: right;">
                    <select multiple name="assignedroles[]" size="10" style="width: 300px;">
                        <? foreach ($assignedroles as $assignedrole): ?>
                            <option value="<?= $assignedrole->getRoleid() ?>">
                                <?= htmlReady($assignedrole->getRolename()) ?>
                                <? if ($assignedrole->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </td>
                <td style="text-align: center;">
                    <?= Icon::create('arr_2left', 'sort', ['title' => _('Markierte Rollen dem Benutzer zuweisen')])->asInput(["type" => "image", "class" => "middle", "name" => "assign_role"]) ?>
                    <br>
                    <br>
                    <?= Icon::create('arr_2right', 'sort', ['title' => _('Markierte Rollen entfernen')])->asInput(["type" => "image", "class" => "middle", "name" => "remove_role"]) ?>
                </td>
                <td>
                    <select size="10" name="rolesel[]" multiple style="width: 300px;">
                        <? foreach ($roles as $role): ?>
                            <option value="<?= $role->getRoleid() ?>">
                                <?= htmlReady($role->getRolename()) ?>
                                <? if ($role->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>

    <h3>
        <?= _('Einrichtungszuordnungen') ?>
    </h3>

    <table class="default">
        <colgroup>
            <col width="50%">
            <col>
            <col width="24px">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Rolle')?> </th>
                <th><?= _('Einrichtungen')?> </th>
                <th class="actions"><?= _('Aktionen')?> </th>
            </tr>
        </thead>
        <tbody>
    <? foreach ($assignedroles as $assignedrole): ?>
        <? if (!$assignedrole->getSystemtype()): ?>
            <tr>
                <td>
                    <?= htmlReady($assignedrole->getRolename()) ?>
                </td>
                <td>
                    <?= htmlReady(implode(",\n", $assignedroles_institutes[$assignedrole->getRoleid()]))?>
                </td>
                <td class="actions">
                    <a href="<?= $controller->link_for('/assign_role_institutes/' . $assignedrole->getRoleid() . '/' . $currentuser->id) ?>" data-dialog="size=auto;reload-on-close">
                        <?= Icon::create('edit', 'clickable')->asImg(['title' => _('Einrichtungszuordnung bearbeiten')]) ?>
                    </a>
                </td>
            </tr>
        <? endif; ?>
    <? endforeach; ?>
        </tbody>
    </table>

    <h3>
        <?= _('Implizit zugewiesene Systemrollen') ?>
    </h3>

    <? foreach ($all_userroles as $role): ?>
        <? if (!in_array($role, $assignedroles)): ?>
            <?= htmlReady($role->getRolename()) ?><br>
        <? endif ?>
    <? endforeach ?>
<? endif ?>
