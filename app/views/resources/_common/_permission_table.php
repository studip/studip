<?php
/**
 * Template variables:
 * - table_id: The ID of the table. This is required for the JS code which adds
 *   new users to the table to work.
 * - table caption: An optional table caption.
 * - permissions: An array of ResourcePermission objects.
 * - single_user: The User object in case the table is used in single
 *   user mode where the permissions of one specific user are displayed.
 * - custom_columns: An associative multi-dimensional array to display
 *   additional columns.
 *       Structure:
 *       The first layer has the column names as indexes.
 *       The second layer contains an associative array with the cells
 *       for the column. That array has the permission object IDs
 *       (see permissions above) as indexes.
 * - custom_actions: A multi-dimensional array for additional actions in the
 *   actions column. The array has the following structure:
 *       The first layer contains associative arrays where each array represents
 *       one action.
 *       The second layer consists of associative arrays with the following
 *       structure:
 *       [
 *           'icon' => The Icon object for the action.
 *           'title' => The descriptive title for the action.
 *           'link_classes' => Classes which shall be added to the link element
 *                             of the action.
 *           'url' => An optional URL that shall be attached to the action's
 *                         link element via the href attribute.
 *           'js_action' => An optional JavaScript action that shall be attached
 *                          to the link element using the onclick attribute.
 *       ]
 * - show_delete_action: If the delete action shall be shown (true) or not (false).
 *   Defaults to true, if not set.
 */

//Make sure the delete action is shown when the $show_delete_action
//template variable isn't set:
if (!isset($show_delete_action)) {
    $show_delete_action = true;
}
?>
<table class="default sortable-table resource-permissions-table"
       data-sortlist="[[1, 0]]"
       <?= $table_id ? 'id="' . htmlReady($table_id) . '"' : '' ?>>
    <? if ($table_caption): ?>
        <caption><?= htmlReady($table_caption) ?></caption>
    <? endif ?>
    <colgroup>
        <col class="checkbox">
        <col>
        <col>
        <? if ($custom_columns): ?>
            <? foreach ($custom_columns as $column_name): ?>
                <col>
            <? endforeach ?>
        <? endif ?>
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox"
                       data-proxyfor="input[name='selected_permissions[]']"
                       data-activates="table.resource-permissions-table button.bulk-action">
            </th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata"><?= _('Rechtestufe') ?></th>
            <? if ($custom_columns): ?>
                <? foreach (array_keys($custom_columns) as $column_name): ?>
                    <th><?= htmlReady($column_name) ?></th>
                <? endforeach ?>
            <? endif ?>
        </tr>
    </thead>
    <? if ($show_delete_action): ?>
    <tfoot>
        <tr>
            <td colspan="3">
                <?
                $button_attrs = [
                    'class'                    => 'bulk-action',
                    'data-activates-condition' => 'table.resource-permissions-table :checkbox:checked'
                ];
                ?>
                
                    <?= \Studip\Button::create(_('Löschen'), 'bulk_delete', $button_attrs) ?>
            </td>
        </tr>
    </tfoot>
    <? endif ?>
    <tbody>
        <? if (count($permissions)): ?>
            <? foreach ($permissions as $permission): ?>
                <?
                $permission_sort_key = 10;
                switch ($permission->perms) {
                    case 'autor':
                    {
                        $permission_sort_key = 20;
                        break;
                    }
                    case 'tutor':
                    {
                        $permission_sort_key = 30;
                        break;
                    }
                    case 'admin':
                    {
                        $permission_sort_key = 40;
                        break;
                    }
                }
                ?>
                <tr data-user_id="<?= htmlReady($permission->user_id) ?>">
                    <td>
                        <input type="checkbox" name="selected_permissions[]"
                               value="<?= htmlReady($permission->user_id) ?>">
                    </td>
                    <td>
                        <? if ($permission->user instanceof User): ?>
                            <?= htmlReady($permission->user->getFullName('full_rev_username')) ?>
                            (<?= htmlReady($permission->user->perms) ?>)
                        <? else: ?>
                            <?= _('unbekannt') ?>
                        <? endif ?>
                        <input type="hidden" name="permissions[user_id][]"
                               value="<?= htmlReady($permission->user_id) ?>">
                    </td>
                    <td data-sort-value="<?= htmlReady($permission_sort_key) ?>">
                        <select name="permissions[level][]">
                            <option value="user"
                                    <?=
                                    $permission->perms == 'user'
                                        ? 'selected="selected"'
                                        : '' ?>>
                                user
                            </option>
                            <option value="autor"
                                    <?=
                                    $permission->perms == 'autor'
                                        ? 'selected="selected"'
                                        : '' ?>>
                                autor
                            </option>
                            <option value="tutor"
                                    <?=
                                    $permission->perms == 'tutor'
                                        ? 'selected="selected"'
                                        : '' ?>>
                                tutor
                            </option>
                            <option value="admin"
                                    <?=
                                    $permission->perms == 'admin'
                                        ? 'selected="selected"'
                                        : '' ?>>
                                admin
                            </option>
                        </select>
                    </td>
                    <? if ($custom_columns): ?>
                        <? foreach ($custom_columns as $column_content): ?>
                            <td>
                                <?= htmlReady($column_content[$permission->id]) ?>
                            </td>
                        <? endforeach ?>
                    <? endif ?>
                </tr>
            <? endforeach ?>
        <? endif ?>
        <tr id="resource-empty-permission-list-message"
            <?= count($permissions) ? 'class="invisible"' : '' ?>>
            <td colspan="3" style="text-align: center">
                <? if ($single_user instanceof User): ?>
                    <?= $custom_empty_list_message ?: sprintf(
                        _('Es sind keine besonderen Rechte für %s vorhanden.'),
                        htmlReady($single_user->getFullName()
                        )) ?>
                <? else: ?>
                    <?= $custom_empty_list_message ?: _('Es sind keine besonderen Rechte vorhanden.') ?>
                <? endif ?>
            </td>
        </tr>
        <tr class="invisible resource-permission-list-template">
            <td>
                <input type="checkbox" name="selected_permissions[]"
                       disabled="disabled">
            </td>
            <td>
                <input type="hidden" name="permissions[user_id][]">
            </td>
            <td>
                <select name="permissions[level][]">
                    <option value="user">
                        user
                    </option>
                    <option value="autor" selected="selected">
                        autor
                    </option>
                    <option value="tutor">
                        tutor
                    </option>
                    <option value="admin">
                        admin
                    </option>
                </select>
            </td>
            <? if ($custom_columns): ?>
                <? foreach (array_keys($custom_columns) as $column_name): ?>
                    <td><?= htmlReady($custom_columns[$column_name]) ?></td>
                <? endforeach ?>
            <? endif ?>
        </tr>
    </tbody>
</table>
