<div class="resource-position-property-fields"
    id="ResourcePositionProperty_<?= htmlReady($property_name) ?>_manual">
    <label>
        <?= _('Breitengrad: ') ?>
        <input type="text" maxlength="13"
            name="<?= htmlReady($property_name) ?>_latitude"
            value="<?= number_format(floatval($latitude), 7) ?>"
            class="resource-position-property-number-field">

    </label>
    <label>
        <?= _('Längengrad: ') ?>
        <input type="text" maxlength="13"
            name="<?= htmlReady($property_name) ?>_longitude"
            value="<?= number_format(floatval($longitude), 7) ?>"
            class="resource-position-property-number-field">
    </label>
    <label>
        <?= _('Höhenangabe (Meter): ') ?>
        <input type="text" maxlength="5"
            name="<?= htmlReady($property_name) ?>_altitude"
            value="<?= number_format(floatval($altitude), 1) ?>"
            class="resource-position-property-number-field">
    </label>
</div>
