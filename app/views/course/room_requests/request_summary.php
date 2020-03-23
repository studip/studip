<form method="post" name="room_request" class="default"
      action="<?= $this->controller->link_for('course/room_requests/request_summary/' . $request_id) ?>">
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= $this->render_partial(
        'course/room_requests/_request_edit_header',
        ['request' => $request]
    ) ?>
    <?= CSRFProtection::tokenTag() ?>
    <section class="resources-grid">
        <div>
            <fieldset>
                <legend><?= _('Ausgewählter Raum') ?></legend>
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
                        _('Anderen Raum wählen'),
                        'select_other_room'
                    ) ?>
                <? else: ?>
                    <?= MessageBox::info(
                        _('Es wurde kein konkreter Raum ausgewählt!')
                    ) ?>
                    <?= Studip\Button::create(
                        _('Eigenschaften neu wählen'),
                        'select_properties'
                    ) ?>
                <? endif ?>
                <? if ($request->properties): ?>
                    <? foreach ($request->properties as $property): ?>
                        <? if (!in_array($property->name, ['seats'])): ?>
                            <dt><?= htmlReady($property->display_name) ?></dt>
                            <dd><?= htmlReady($property->__toString()) ?></dd>
                        <? endif ?>
                    <? endforeach ?>
                <? endif ?>
                <? if ($request->category): ?>
                    <dt><?= _('Gewünschter Raumtyp') ?>:</dt>
                    <dd><?= htmlReady($request->category->name) ?></dd>
                <? endif ?>
                <label>
                    <?= _('Erwartete Anzahl an Teilnehmenden') ?>:
                    <input type="number" name="seats"
                           value="<?= htmlReady($seats) ?>"
                           min="1">
                </label>
                <label>
                    <?= _('Rüstzeit (in Minuten)') ?>
                    <input type="number" name="preparation_time"
                           value="<?= htmlReady($preparation_time) ?>"
                           min="0" max="<?= htmlReady($max_preparation_time) ?>">
                </label>
                <? if ($user_is_global_resource_admin) : ?>
                    <label>
                        <input type="checkbox" name="reply_lecturers" value="1"
                               <?= $reply_lecturers
                                   ? 'checked="checked"'
                                   : ''
                               ?>>
                        <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
                    </label>
                <? endif ?>
            </fieldset>
        </div>
        <div>
            <fieldset>
                <legend><?= _('Nachricht an die Raumvergabe') ?></legend>
                <textarea name="comment" cols="58" rows="4"
                          placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur angefragten Raumbelegung') ?>"><?= htmlReady($comment) ?></textarea>
            </fieldset>
        </div>
    </section>
    <footer data-dialog-button>
        <?= \Studip\Button::create(
            _('Speichern'),
            'save'
        ) ?>
        <?= \Studip\Button::create(
            _('Speichern und zurück zur Übersicht'),
            'save_and_close'
        ) ?>
        <?= \Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->link_for('course/room_requests/index/' . $course_id),
            [
                'title' => _('Abbrechen')
            ]
        ) ?>
    </footer>
</form>
