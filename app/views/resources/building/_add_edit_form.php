<? if ($show_form): ?>
    <? $url = ($mode == 'add')
        ? $controller->link_for('resources/building/add', ['category_id' => $category_id])
        : $controller->link_for('resources/building/edit/' . $building->id) ?>
    <form class="default" method="post" action="<?= $url ?>"
          data-dialog="reload-on-close">

        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Grunddaten') ?></legend>
            <label>
                <?= _('Name des Gebäudes') ?>
                <input type="text" name="name" value="<?= htmlReady($name) ?>">
            </label>
            <label>
                <?= _('Beschreibungstext') ?>
                <input type="text" name="description" value="<?= htmlReady($description) ?>">
            </label>
            <label>
                <?= _('Standort / Hierarchie') ?>
                <select name="parent_id">
                    <option value=""><?= _('Bitte wählen') ?></option>
                    <? foreach ($possible_parents as $resource): ?>
                        <option value="<?= htmlReady($resource->id) ?>"
                            <?= $parent_id == $resource->id ? 'selected="selected"' : '' ?>>
                            <?= htmlReady('/' . implode('/', ResourceManager::getHierarchyNames($resource))) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
            <label>
                <?= _('Gebäudenummer') ?>
                <input type="text" name="number" value="<?= htmlReady($number) ?>">
            </label>
            <label>
                <?= _('Adresse') ?>
                <input type="text" name="address" value="<?= htmlReady($address) ?>">
            </label>
            <? if ($GLOBALS['perm']->have_perm('root')): ?>
                <label>
                    <?= _('Sortierposition') ?>
                    <input type="text" name="sort_position" value="<?= htmlReady($sort_position) ?>">
                </label>
            <? endif ?>
            <?= $this->render_partial(
                '../../templates/resources/position_attribute_form_part.php',
                [
                    'property_name' => 'geo_coordinates',
                    'latitude'      => $latitude,
                    'longitude'     => $longitude,
                    'altitude'      => $altitude
                ]
            ) ?>
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
