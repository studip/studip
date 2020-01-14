<form class="default" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($property_groups): ?>
        <table class="default">
            <caption><?= _('Eigenschaftsgruppen')?></caption>
            <colgroup>
                <col class="checkbox">
                <col>
                <col>
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th colspan="4">
                        <p>
                            <?= _('Die ausgewählten Gruppen werden beim Speichern gelöscht bzw. die ausgewählten Eigenschaften aus der Gruppe entfernt.') ?>
                        </p>
                        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
                    </th>
                </tr>
                <tr>
                    <th>
                        <label>
                            <input type="checkbox"
                                   title="<?= _('Alle Gruppen löschen') ?>"
                                   data-proxyfor="input[name='selected_groups[]']">
                        </label>
                    </th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Eigenschaften') ?></th>
                    <th><?= _('Position') ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <p>
                            <?= _('Die ausgewählten Gruppen werden beim Speichern gelöscht bzw. die ausgewählten Eigenschaften aus der Gruppe entfernt.') ?>
                        </p>
                        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <? foreach ($property_groups as $group): ?>
                    <tr>
                        <td>
                            <input type="checkbox" title="<?= _('Gruppe löschen') ?>"
                                   name="selected_groups[]"
                                   data-proxyfor="input[name='selected_group_properties[<?= htmlReady($group->id) ?>][]']"
                                   value="<?= htmlReady($group->id) ?>">
                        </td>
                        <td>
                            <input type="text"
                                   name="edited_group_names[<?= htmlReady($group->id) ?>]"
                                   value="<?= htmlReady($group->name) ?>">
                        </td>
                        <td></td>
                        <td>
                            <input type="num" value="<?= htmlReady($group->position) ?>"
                                   name="group_position[<?= htmlReady($group->id) ?>]">
                        </td>
                    </tr>
                    <? foreach ($group->properties as $property): ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       title="<?= _('Aus der Gruppe entfernen') ?>"
                                       name="selected_group_properties[<?= htmlReady($group->id) ?>][]"
                                       value="<?= htmlReady($property->id) ?>">
                            </td>
                            <td></td>
                            <td>
                                <?= htmlReady($property->__toString())?>
                                (<?= htmlReady($property->type)?>)
                            </td>
                            <td>
                                <input type="num"
                                       value="<?= htmlReady($property->property_group_pos)?>"
                                       name="property_position[<?= htmlReady($property->id)?>]">
                            </td>
                        </tr>
                    <? endforeach ?>
                <? endforeach ?>
            </tbody>
        </table>
    <? else: ?>
        <?= MessageBox::info(
            _('Es sind keine Eigenschaftsgruppen vorhanden!')
        ) ?>
    <? endif ?>

    <? if ($ungrouped_properties): ?>
        <table class="default">
            <caption><?= _('Ungruppierte Eigenschaften')?></caption>
            <colgroup>
                <col class="checkbox">
                <col>
                <col>
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox"
                               title="<?= _('Alle Eigenschaften zu einer neuen Gruppe hinzufügen') ?>"
                               data-proxyfor="input[name='selected_properties[]']">
                    </th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Angezeigter Name') ?></th>
                    <th><?= _('Typ') ?></th>
                    <th><?= _('Gruppe zuweisen') ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <label class="undecorated">
                            <?= _('Neue Gruppe anlegen') ?>
                            <input type="text" name="new_group_name"
                                   value="<?= htmlReady($new_group_name)?>">
                        </label>
                        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <? foreach ($ungrouped_properties as $property): ?>
                    <tr>
                        <td>
                            <input type="checkbox"
                                   title="<?= _('Zu einer neuen Gruppe hinzufügen') ?>"
                                   name="selected_properties[]"
                                   value="<?= htmlReady($property->id) ?>">
                        </td>
                        <td><?= htmlReady($property->name) ?></td>
                        <td><?= htmlReady($property->__toString()) ?></td>
                        <td><?= htmlReady($property->type) ?></td>
                        <td>
                            <? if ($property_groups): ?>
                                <select name="property_move[<?= htmlReady($property->id)?>]">
                                    <option value=""
                                            <?= $property_move[$property->id] == ''
                                              ? 'selected="selected"'
                                              : '' ?>></option>
                                    <? foreach ($property_groups as $group): ?>
                                        <option value="<?= htmlReady($group->id) ?>"
                                                <?= $property_move[$property->id] == $group->id
                                                  ? 'selected="selected"'
                                                  : '' ?>>
                                            <?= htmlReady($group->name) ?>
                                        </option>
                                    <? endforeach ?>
                                </select>
                            <? endif ?>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
    <? endif ?>
</form>
