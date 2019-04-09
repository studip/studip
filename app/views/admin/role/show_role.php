<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/role/show_role') ?>" method="get" class="default inline">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Rollenzuweisungen anzeigen') ?>
        </legend>

        <label>
            <?= _('Rolle wählen') ?>

            <select name="role">
            <? foreach ($roles as $one_role): ?>
                <option value="<?= $one_role->getRoleid() ?>" <? if ($one_role->getRoleid() == $roleid) echo 'selected'; ?>>
                    <?= htmlReady($one_role->getRolename()) ?>
                <? if ($one_role->getSystemtype()): ?>
                    [<?= _('Systemrolle') ?>]
                <? endif; ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Auswählen'), 'selectrole', ['title' => _('Rolle auswählen')])?>
    </footer>
</form>

<? if (isset($role)): ?>
<br>
<form action="<?= $controller->url_for('admin/role/remove_user/' . $role->getRoleId() . '/bulk') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default" id="role-users">
        <colgroup>
            <col width="20px">
            <col width="3%">
            <col width="33%">
            <col width="5%">
            <col>
            <col width="24px">
        </colgroup>
        <caption>
            <?= sprintf(
                _('Liste der Personen mit der Rolle "%s"'),
                htmlReady($role->getRolename())
            ) ?>
        <? if (!$role->getSystemtype()): ?>
            <div class="actions">
                <?= $mps->render() ?>
            </div>
        <? endif; ?>
        </caption>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#role-users tbody :checkbox"
                           data-activates="#role-users tfoot button">
                </th>
                <th>&nbsp;</th>
                <th><?= _('Name') ?></th>
                <th><?= _('Status') ?></th>
                <th><?= _('Einrichtungszuordnung') ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
    <? if (count($users) === 0): ?>
            <tr>
                <td colspan="6" style="text-align: center;">
                    <?= _('Es wurden keine Benutzer gefunden.') ?>
                </td>
            </tr>
    <? else: ?>
        <? foreach (array_values($users) as $index => $user): ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= $user['user_id'] ?>">
                </td>
                <td style="text-align: right;">
                    <?= $index + 1 ?>.
                </td>
                <td>
                    <a href="<?= $controller->url_for('admin/role/assign_role', $user['user_id']) ?>">
                        <?= htmlReady(sprintf('%s %s (%s)', $user['Vorname'], $user['Nachname'], $user['username'])) ?>
                    </a>
                </td>
                <td><?= $user['perms'] ?></td>
                <td>
                <? $institutes = join(', ', $user['institutes']); ?>
                    <?= htmlReady(mb_substr($institutes, 0, 60)) ?>
                    <? if (mb_strlen($institutes) > 60): ?>
                    ...<?= tooltipIcon(join("\n", $user['institutes']))?>
                    <? endif ?>
                </td>
                <td class="actions">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Rolle entziehen')])
                            ->asInput([
                                "data-confirm" => _('Soll dieser Person wirklich die Rolle entzogen werden?'),
                                "formaction" => $controller->url_for('admin/role/remove_user/'.$roleid.'/'.$user['user_id'])]) ?>
                </td>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
        <? if ($implicit_count > 0): ?>
            <tr>
                <td></td>
                <td colspan="5">
                    <?= sprintf(
                        _('+%u weitere, implizit zugewiesene Person(en)'),
                        $implicit_count
                    ) ?>
                </td>
            </tr>
        <? endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <?= _('Alle markierten Einträge') ?>
                    <?= Studip\Button::create(_('Löschen'), 'delete', [
                            'data-confirm' => _('Sollen den markierten Personen wirklich die Rolle entzogen werden?'),
                    ]) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

<br>

<form action="<?= $controller->url_for('admin/role/remove_plugin/' . $role->getRoleId() . '/bulk') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default" id="role-plugins">
        <caption>
            <?= sprintf(_('Liste der Plugins mit der Rolle "%s"'),
                        htmlReady($role->getRolename())) ?>
            <div class="actions">
                <a href="<?= $controller->url_for('admin/role/add_plugin/' . $roleid) ?>" data-dialog="size=auto">
                    <?= Icon::create('plugin+add', 'clickable') ?>
                    <?= _('Plugins hinzufügen') ?>
                </a>
            </div>
        </caption>
        <colgroup>
            <col width="20px">
            <col width="3%">
            <col width="38%">
            <col>
            <col width="24px">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#role-plugins tbody :checkbox"
                           data-activates="#role-plugins tfoot button">
                </th>
                <th></th>
                <th><?= _('Name') ?></th>
                <th><?= _('Typ') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
    <? if (count($plugins) === 0): ?>
            <tr>
                <td colspan="5" style="text-align: center;">
                    <?= _('Es wurden keine Plugins gefunden.') ?>
                </td>
            </tr>
    <? else: ?>
        <? foreach (array_values($plugins) as $index => $plugin): ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= $plugin['id'] ?>">
                </td>
                <td style="text-align: right;">
                    <?= $index + 1 ?>.
                </td>
                <td>
                    <a href="<?= $controller->url_for('admin/role/assign_plugin_role', $plugin['id']) ?>">
                        <?= htmlReady($plugin['name']) ?>
                    </a>
                </td>
                <td><?= implode(', ', $plugin['type']) ?></td>
                <td class="actions">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Rolle entziehen')])
                            ->asInput([
                                "data-confirm" => _('Soll diesem Plugin wirklich die Rolle entzogen werden?'),
                                "formaction" => $controller->url_for('admin/role/remove_plugin/'.$roleid.'/'.$plugin['id'])]) ?>
                </td>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <?= _('Alle markierten Einträge') ?>
                    <?= Studip\Button::create(_('Löschen'), 'delete', [
                            'data-confirm' => _('Sollen den markierten Plugins wirklich die Rolle entzogen werden?'),
                    ]) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<? endif; ?>
