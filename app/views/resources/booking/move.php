<? if ($show_form): ?>
    <? if ($available_resources): ?>
        <form class="default" method="post"
              action="<?= $this->controller->link_for('resources/booking/move/' . $booking->id) ?>" data-dialog="size=auto">
            <?= CSRFProtection::tokenTag() ?>
                <label>
                    <? if ($booking->resource->class_name == 'Room'): ?>
                        <?= _('Zielraum auswählen') ?>
                        <?= tooltipIcon(_('In welchen Raum soll die Buchung verschoben werden?')) ?>
                    <? else: ?>
                        <?= _('Zielressource auswählen') ?>
                        <?= tooltipIcon(_('In welche Ressource soll die Buchung verschoben werden?')) ?>
                    <? endif ?>

                    <select name="selected_resource_id" class="nested-select">
                        <? foreach ($available_resources as $resource): ?>
                            <? if ($resource->id == $booking->resource->id) continue; ?>
                            <option value="<?= htmlReady($resource->id) ?>"
                                <?= in_array($resource->id, $selected_resource_ids) ? 'selected' : '' ?>>
                                <?= htmlReady($resource->getFullName()) ?>
                            </option>

                        <? endforeach ?>
                    </select>
                </label>
            <?= $this->render_partial(
                'resources/booking/index.php',
                [
                    'booking' => $booking,
                    'hide_buttons' => true
                ]
            ) ?>
            <div data-dialog-button>
                <?= \Studip\LinkButton::create(
                    _('Zurück'),
                    $controller->url_for('resources/booking/edit/' . $booking->id),
                    [
                        'data-dialog' => '1'
                    ]
                ) ?>
                <?= \Studip\Button::create(_('Verschieben'), 'save') ?>
            </div>
        </form>
    <? else: ?>
        <? if ($booking->resource->class_name == 'Room'): ?>
            <?= MessageBox::info(
                _('Es sind keine Räume verfügbar, in die die Buchung verschoben werden kann!')
            ) ?>
        <? else: ?>
            <?= MessageBox::info(
                _('Es sind keine Ressourcen verfügbar, in die die Buchung verschoben werden kann!')
            ) ?>
        <? endif ?>
    <? endif ?>
<? endif ?>
