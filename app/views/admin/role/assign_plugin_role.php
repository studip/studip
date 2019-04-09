<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/role/assign_plugin_role') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Rollenverwaltung für Plugins') ?>
        </legend>

        <label>
            <select name="pluginid" style="min-width: 300px;">
                <? foreach ($plugins as $plugin): ?>
                    <option value="<?= $plugin['id'] ?>" <?= $plugin['id'] == $pluginid ? 'selected' : '' ?>>
                        <?= htmlReady($plugin['name']) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Auswählen'), 'select', ['title' => _('Plugin auswählen')]) ?>
    </footer>
</form>

<? if ($pluginid): ?>
    <br>
    <form action="<?= $controller->url_for('admin/role/save_plugin_role', $pluginid) ?>" method="POST" class="default">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
        <table class="default nohover">
            <tr>
                <th style="text-align: center;"><?= _('Gegenwärtig zugewiesene Rollen') ?></th>
                <th></th>
                <th><?= _('Verfügbare Rollen') ?></th>
            </tr>
            <tr class="table_row_even">
                <td style="text-align: right;">
                    <select multiple name="assignedroles[]" size="10" style="width: 300px;">
                        <? foreach ($assigned as $assignedrole): ?>
                            <option value="<?= $assignedrole->getRoleid() ?>">
                                <?= htmlReady($assignedrole->getRolename()) ?>
                                <? if ($assignedrole->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </td>
                <td style="text-align: center;">
                    <?= Icon::create('arr_2left', 'sort', ['title' => _('Markierte Rollen dem Plugin zuweisen')])->asInput(["type" => "image", "class" => "middle", "name" => "assign_role"]) ?>
                    <br>
                    <br>
                    <?= Icon::create('arr_2right', 'sort', ['title' => _('Markierte Rollen entfernen')])->asInput(["type" => "image", "class" => "middle", "name" => "remove_role"]) ?>
                </td>
                <td>
                    <select multiple name="rolesel[]" size="10" style="width: 300px;">
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
<? endif ?>
