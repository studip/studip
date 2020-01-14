<h2><?= _('Ausgewählter Raum')?></h2>
<section>
    <? if ($selected_room): ?>
        <input type="hidden" name="selected_room_id"
               value="<?= htmlReady($selected_room->id) ?>">
        <input type="hidden" name="confirmed_selected_room_id"
               value="<?= htmlReady($selected_room->id) ?>">
        <?= htmlReady($selected_room->name) ?>
        <? if ($selected_room->properties): ?>
            <? $property_names = $selected_room->properties
                ->findBy('info_label', 1)
                ->findBy('state', '', '!=')
                ->pluck('fullname') ?>
            <?= tooltipIcon(
                implode("\n", $property_names)
            ) ?>
        <? endif ?>
        <?= Studip\Button::create(
            _("Anderen Raum wählen"),
            'reset_selected_room'
        ) ?>
    <? else: ?>
        <?= MessageBox::info(
            _('Es wurde kein konkreter Raum ausgewählt!')
        )?>
    <? endif ?>
</section>
<label>
    <?= _('Erwartete Anzahl an Teilnehmenden') ?>:
    <input type="number" name="seats"
           value="<?= htmlReady($selected_properties['seats']) ?>"
           min="1">
</label>
<label>
    <?= _('Rüstzeit') ?>
    <input type="number" name="preparation_time"
           value="<?= htmlReady($preparation_time) ?>"
           min="0" max="<?= htmlReady($max_preparation_time) ?>">
</label>
