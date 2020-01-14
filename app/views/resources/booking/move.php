<? if ($show_form): ?>
    <? if ($available_resources): ?>
        <?= $this->render_partial(
            'resources/booking/index.php',
            [
                'booking' => $booking,
                'hide_buttons' => true
            ]
        ) ?>
        <form class="default" method="post"
              action="<?= $this->controller->link_for(
                      'resources/booking/move/' . $booking->id
                      ) ?>" data-dialog>
            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <? if ($booking->resource->class_name == 'Room'): ?>
                    <legend><?= _('Zielraum auswählen') ?></legend>
                    <h2>
                        <?= _('In welchen Raum soll die Buchung verschoben werden?') ?>
                    </h2>
                <? else: ?>
                    <legend><?= _('Zielressource auswählen') ?></legend>
                    <h2>
                        <?= _('In welche Ressource soll die Buchung verschoben werden?') ?>
                    </h2>
                <? endif ?>
                <ul class="list-unstyled">
                    <? foreach ($available_resources as $resource): ?>
                        <? if($resource->id == $booking->resource->id) continue; ?>
                        <li>
                            <label>
                                <input type="radio" name="selected_resource_id"
                                    value="<?= htmlReady($resource->id)?>"
                                    <?= in_array($resource->id, $selected_resource_ids)
                                        ? 'checked="checked"'
                                        : '' ?>>
                                <?= htmlReady($resource->getFullName()) ?>
                            </label>
                        </li>
                    <? endforeach ?>
                </ul>
            </fieldset>
            <div data-dialog-button>
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
