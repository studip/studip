<form class="default" id="resource-temporary-permissions" method="post"
      action="<?= ($single_user_mode
                 ? $resource->getActionLink(
                     'temporary_permissions',
                     [
                         'user_id' => $user->id
                     ]
                 )
                 : $resource->getActionLink('temporary_permissions')
              ) ?>"
      <?= (Request::isDialog()
         ? (
             $single_user_mode
             ? 'data-dialog="reload-on-close"'
             : 'data-dialog'
         )
         : '') ?>>
    <?= CSRFProtection::tokenTag() ?>
    <table class="default sortable-table temporary-permission-list" id="TemporaryPermissionList"
           data-sortlist="[[1, 0]]">
        <thead>
            <tr>
                <th>
                    <input type="checkbox" class="bulk-proxy"
                           data-proxyfor="input.selected-temporary-permission"
                           data-activates="table.temporary-permission-list button.bulk-action">
                </th>
                <th data-sort="text"><?= _('Name') ?></th>
                <th data-sort="htmldata"><?= _('Rechtestufe') ?></th>
                <th><?= _('Gültig von') ?></th>
                <th><?= _('Gültig bis') ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5">
                    <?
                    $button_attrs = ['class' => 'bulk-action'];
                    ?>
                    <?= \Studip\Button::create(_('Löschen'), 'bulk_delete', $button_attrs) ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <? if (count($temp_permissions)): ?>
                <? foreach ($temp_permissions as $permission): ?>
                    <?
                    $permission_sort_key = 10;
                    switch ($permission->perms) {
                        case 'autor': {
                            $permission_sort_key = 20;
                            break;
                        } case 'tutor': {
                            $permission_sort_key = 30;
                            break;
                        } case 'admin': {
                            $permission_sort_key = 40;
                            break;
                        }
                    }
                    ?>
                    <tr data-temp-perms="1" class="resource-temporary-permission-row">
                        <input type="hidden" name="permissions[permission_id][<?= htmlReady('existing_' . $permission->id) ?>]"
                               value="<?= htmlReady($permission->id) ?>">
                        <td>
                            <input type="checkbox" class="selected-temporary-permission"
                                   name="selected_permission_ids[<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= htmlReady($permission->id) ?>">
                        </td>
                        <td>
                            <?= htmlReady($permission->user->getFullName('full_rev_username')) ?>
                            (<?= htmlReady($permission->user->perms) ?>)
                            <input type="hidden" name="permissions[user_id][<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= htmlReady($permission->user_id)?>">
                        </td>
                        <td data-sort-value="<?= htmlReady($permission_sort_key) ?>">
                            <select name="permissions[level][<?= htmlReady('existing_' . $permission->id) ?>]">
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
                        <td class="DateTime">
                            <input type="text" class="has-date-picker"
                                   name="permissions[begin_date][<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= date('d.m.Y', $permission->begin)?>">
                            <input type="text" class="has-time-picker"
                                   name="permissions[begin_time][<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= date('H:i', $permission->begin)?>">
                        </td>
                        <td class="DateTime">
                            <input type="text" class="has-date-picker"
                                   name="permissions[end_date][<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= date('d.m.Y', $permission->end)?>">
                            <input type="text" class="has-time-picker"
                                   name="permissions[end_time][<?= htmlReady('existing_' . $permission->id) ?>]"
                                   value="<?= date('H:i', $permission->end)?>">
                        </td>
                    </tr>
                <? endforeach ?>
            <? endif ?>
            <tr id="ResourceEmptyPermissionListMessage"
                <?= count($temp_permissions) ? 'class="invisible"' : ''?>>
                <td colspan="3"></td>
            </tr>
            <tr class="invisible resource-temporary-permission-row resource-permission-list-template"
                data-temp-perms="1">
                <td>
                    <input type="checkbox"
                           name="selected_permission_ids[]"
                           disabled="disabled"
                           value="">
                </td>
                <td>
                    <input type="hidden" name="permissions[user_id][]"
                           value="USERID">
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
                <td class="DateTime">
                    <input type="text" name="permissions[begin_date][]">
                    <input type="text" name="permissions[begin_time][]">
                </td>
                <td class="DateTime">
                    <input type="text" name="permissions[end_date][]">
                    <input type="text" name="permissions[end_time][]">
                </td>
            </tr>
        </tbody>
    </table>

    <input type="checkbox" class="invisible bulk-datetime-enable">
    <fieldset class="bulk-datetime">
        <legend>
            <?= _('Neuen Zeitbereich für die ausgewählten Berechtigungen setzen') ?>
        </legend>

        <div class="col-2">
            <label>
                <?= _('Beginn') ?>
                <input type="text" name="bulk_begin_date"
                        class="has-date-picker"
                        value="<?= htmlReady(
                                $bulk_begin
                                ? $bulk_begin->format('d.m.Y')
                                : date('d.m.Y')
                                ) ?>">
                <input type="text" name="bulk_begin_time"
                        class="has-time-picker"
                        value="<?= htmlReady(
                                $bulk_begin
                                ? $bulk_begin->format('H:i')
                                : date('H:i')
                                ) ?>">
            </label>
        </div>
        <div class="col-2">
            <label>
                <?= _('Ende') ?>
                <input type="text" name="bulk_end_date"
                        class="has-date-picker"
                        value="<?= htmlReady(
                                $bulk_end
                                ? $bulk_end->format('d.m.Y')
                                : date('d.m.Y')
                                ) ?>">
                <input type="text" name="bulk_end_time"
                        class="has-time-picker"
                        value="<?= htmlReady(
                                $bulk_end
                                ? $bulk_end->format('H:i')
                                : date('H:i')
                              ) ?>">
            </label>
       </div>
        <!--<div>
       <?= \Studip\Button::create(
                _('Speichern'),
                'bulk_save'
            ) ?>
            </div>-->
   </fieldset>

<? if (!$single_user_mode): ?>
    <fieldset>
        <legend>
            <?= _('Person hinzufügen') ?>
        </legend>
        <label>
            <?= $user_search->render() ?>
        </label>
    </fieldset>

    <? if ($course_search): ?>
        <fieldset>
            <legend>
                <?= _('Teilnehmende aus Veranstaltung hinzufügen') ?>
            </legend>
            <label>
                <?= $course_search->render() ?>
            </label>
        </fieldset>
    <? endif ?>
<? endif ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
    </div>
</form>
