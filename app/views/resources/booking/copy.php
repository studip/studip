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
                      'resources/booking/copy/' . $booking->id
                      ) ?>" data-dialog>
            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <? if ($booking->resource->class_name == 'Room'): ?>
                    <legend><?= _('Zielraum auswählen') ?></legend>
                    <h2>
                        <?= _('In welchen Raum bzw. welche Räume soll die Buchung kopiert werden?') ?>
                    </h2>
                <? else: ?>
                    <legend><?= _('Zielressource auswählen') ?></legend>
                    <h2>
                        <?= _('In welche Ressource(n) soll die Buchung kopiert werden?') ?>
                    </h2>
                <? endif ?>
                <ul class="list-unstyled">
                    <? foreach ($available_resources as $resource): ?>
                        <li>
                            <input type="checkbox" name="selected_resource_ids[]"
                                   value="<?= htmlReady($resource->id)?>"
                                   <?= in_array($resource->id, $selected_resource_ids)
                                     ? 'checked="checked"'
                                     : '' ?>>
                        <?= htmlReady($resource->getFullName()) ?>
                        </li>
                    <? endforeach ?>
                </ul>
            </fieldset>
            <div data-dialog-button>
                <?= \Studip\Button::create(_('Kopieren'), 'save') ?>
            </div>
        </form>
    <? else: ?>
        <? if ($booking->resource->class_name == 'Room'): ?>
            <?= MessageBox::info(
                _('Es sind keine Räume verfügbar, in die die Buchung kopiert werden kann!')
            ) ?>
        <? else: ?>
            <?= MessageBox::info(
                _('Es sind keine Ressourcen verfügbar, in die die Buchung kopiert werden kann!')
            ) ?>
        <? endif ?>
    <? endif ?>
<? endif ?>
