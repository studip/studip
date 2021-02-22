<? if ($show_form): ?>
    <form class="default" method="post" action="<?= ($mode == 'add')
        ? $controller->url_for('resources/room/add')
        : $controller->url_for('resources/room/edit/' . $room->id) ?>"
          data-dialog="reload-on-close">

        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="category_id" value="<?= htmlReady($category_id) ?>">
        <fieldset>
            <legend><?= _('Grunddaten') ?></legend>
            <label>
                <?= _('Der Raum befindet sich in folgender Hierarchie:') ?>
                <select name="parent_id">
                    <? foreach ($building_hierarchies as $building_id => $hierarchy): ?>
                        <option value="<?= htmlReady($building_id) ?>"
                            <?= ($building_id == $parent_id) ? 'selected="selected"' : '' ?>>
                            <?= htmlReady($hierarchy) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
            <label>
                <?= _('Name des Raumes') ?>
                <input type="text" name="name" value="<?= htmlReady($room->name) ?>">
            </label>
            <label>
                <?= _('Beschreibungstext') ?>
                <input type="text" name="description" value="<?= htmlReady($room->description) ?>">
            </label>
            <label>
                <input type="checkbox" name="requestable" value="1"
                       <?= $room->requestable ? 'checked="checked"' : '' ?>>
                <?= _('Raum ist wünschbar') ?>
            </label>
            <label>
                <?= _('Raumtyp') ?>
                <input type="text" name="room_type" value="<?= htmlReady($room_type) ?>">
            </label>
            <label>
                <?= _('Sitzplätze') ?>
                <input type="number" name="seats" value="<?= htmlReady($seats) ?>">
            </label>
            <label>
                <input type="checkbox" name="booking_plan_is_public" value="1"
                       <?= $booking_plan_is_public ? 'checked="checked"' : '' ?>>
                <?= _('Raumplan ist öffentlich zugänglich') ?>
            </label>
            <? if ($GLOBALS['perm']->have_perm('root')): ?>
                <label>
                    <?= _('Sortierposition') ?>
                    <input type="text" name="sort_position"
                           value="<?= htmlReady($sort_position) ?>">
                </label>
            <? endif ?>
        </fieldset>
        <? if ($grouped_defined_properties): ?>
            <?= $this->render_partial(
                'resources/resource/_standard_properties_form_part.php',
                [
                    'grouped_defined_properties' => $grouped_defined_properties,
                    'property_data'              => $property_data
                ]
            ) ?>
        <? endif ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'confirmed') ?>
        </div>
    </form>
<? endif ?>
