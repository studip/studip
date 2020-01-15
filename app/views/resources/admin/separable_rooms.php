<form class="default" method="post"
      action="<?= URLHelper::getLink('dispatch.php/resources/admin/separable_rooms') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($building_id): ?>
        <input type="hidden" name="building_id"
               value="<?= htmlReady($building_id) ?>">
    <? else: ?>
        <select name="building_id">
            <? foreach ($buildings as $building): ?>
                <option value="<?= htmlReady($building->id) ?>"
                        <?= $building->id == $building_id
                          ? 'selected="selected"'
                          : '' ?>>
                <?= htmlReady($building->name) ?>
                </option>
            <? endforeach ?>
        </select>
        <?= \Studip\Button::create(_('Gebäude auswählen'), 'select_building') ?>
    <? endif ?>
    <? if ($building_id): ?>
        <? if ($separable_rooms): ?>
            <table class="default">
                <caption><?= sprintf(_('%s: Teilbare Räume'), htmlReady($building->name)) ?></caption>
                <colgroup>
                    <col class="checkbox">
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th style="width: 2em;">
                            <input type="checkbox"
                                   data-proxyfor="<?= (
                                                  $separable_rooms
                                                  ? "input[name='selected_separable_rooms[]'"
                                                  : "input[name='selected_single_rooms[]'"
                                                  ) ?>]">
                        </th>
                        <th><?= _('Raumname') ?></th>
                        <th><?= _('Raumteile') ?></th>
                        <th class="actions"><?= _('Aktionen') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <? if ($separable_rooms): ?>
                                <?= \Studip\Button::create(
                                    _('Teilbare Räume löschen'),
                                    'bulk_delete_separable_rooms'
                                ) ?>
                                <?= \Studip\Button::create(
                                    _('Raumteile löschen'),
                                    'bulk_delete_room_parts'
                                ) ?>
                            <? endif ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <? foreach ($separable_rooms as $separable_room): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_separable_rooms[]"
                                       value="<?= htmlReady($separable_room->id) ?>">
                            </td>
                            <td><?= htmlReady($separable_room->name) ?></td>
                            <td></td>
                            <td class="actions">
                                <?= Icon::create('trash')->asInput(
                                    [
                                        'name' => 'delete_separable_room['
                                              . $separable_room->id . ']',
                                        'class' => 'text-bottom'
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <? foreach ($separable_room->parts as $room_part): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_room_parts[]"
                                           value="<?= htmlReady($room_part->id) ?>">
                                </td>
                                <td></td>
                                <td><?= htmlReady($room_part->getRoomName()) ?></td>
                                <td class="actions">
                                    <?= Icon::create('trash')->asInput(
                                        [
                                            'name' => 'delete_room_part['
                                                  . $room_part->id
                                                  . ']',
                                            'data-confirm' => _('Wollen Sie den Raum wirklich entfernen?'),
                                            'class' => 'text-bottom'
                                        ]
                                    ) ?>
                                </td>
                            </tr>
                        <? endforeach ?>
                    <? endforeach ?>
                </tbody>
            </table>
        <? endif ?>
        <? if ($single_rooms) : ?>
            <table class="default">
                <caption><?= sprintf(_('%s: Einzelne Räume'), htmlReady($building->name)) ?></caption>
                <colgroup>
                    <col class="checkbox">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th style="width: 2em;">
                            <input type="checkbox"
                                   data-proxyfor="input[name='selected_single_rooms[]']">
                        </th>
                        <th><?= _('Raumname') ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <? if ($separable_rooms): ?>
                        <tr>
                            <td colspan="2">
                                <select name="separable_room_id">
                                    <? foreach ($separable_rooms as $separable_room): ?>
                                        <option value="<?= htmlReady($separable_room->id) ?>">
                                            <?= htmlReady($separable_room->name) ?>
                                        </option>
                                    <? endforeach ?>
                                </select>
                                <?= \Studip\Button::create(
                                    _('Raumteil(e) zu teilbarem Raum hinzufügen'),
                                    'add_room_part'
                                ) ?>
                            </td>
                        </tr>
                    <? endif ?>
                    <tr>
                        <td colspan="2">
                            <input type="text" name="separable_room_name"
                                   value="<?= htmlReady($separable_room_name) ?>"
                                   placeholder="<?= _('Name des neuen teilbaren Raumes') ?>">
                            <?= \Studip\Button::create(
                                _('Neuen teilbaren Raum erzeugen'),
                                'create_separable_room'
                            ) ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <? if (($single_rooms) && ($separable_rooms)): ?>
                        <tr>
                            <th colspan="3">
                                <input type="checkbox"
                                       data-proxyfor="input[name='selected_single_rooms[]']">
                            </th>
                        </tr>
                    <? endif ?>
                    <? foreach ($single_rooms as $room): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_single_rooms[]"
                                       value="<?= htmlReady($room->id) ?>">
                            </td>
                            <td><?= htmlReady($room->name) ?></td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        <? endif ?>
    <? endif ?>
</form>
