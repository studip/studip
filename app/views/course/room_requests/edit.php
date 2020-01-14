<form method="post" name="room_request"
      action="<?= $this->controller->link_for(
              'course/room_requests/edit/' . $request_id,
              $params
              ) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?> class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="range" value="<?= htmlReady($range) ?>">
    <input type="hidden" name="range_id" value="<?= htmlReady($range_id) ?>">
    <? foreach ($range_ids as $rid): ?>
        <input type="hidden" name="range_ids[]" value="<?= htmlReady($rid) ?>">
    <? endforeach ?>
    <input type="hidden" name="resource_id" value="<?= htmlReady($resource_id) ?>">
    <? if ($reset_selected_room): ?>
        <input type="hidden" name="reset_selected_room" value="1">
    <? endif ?>
    <? if (!$search_by_name && $room_category_id): ?>
        <input type="hidden" name="room_category_id"
               value="<?= htmlReady($room_category_id) ?>">
    <? endif ?>
    <? if ($selected_properties && ($step > 3)): ?>
        <? foreach ($selected_properties as $name => $state): ?>
            <input type="hidden" value="<?= htmlReady($state) ?>"
                   name="selected_properties[<?= htmlReady($name) ?>]">
        <? endforeach ?>
    <? endif ?>

    <fieldset>
        <legend><?= _('Raumanfragen bearbeiten / erstellen') ?></legend>
        <p>
            <? if (!$search_by_name): ?>
                <?= _('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.') ?>
                <div>
                    <strong><?= _('Achtung') ?>:</strong>
                    <?= _('Um später einen passenden Raum für Ihre Veranstaltung zu bekommen, geben Sie bitte immer die gewünschten Eigenschaften mit an!') ?>
                </div>
            <? else: ?>
                <?= _('Geben Sie bitte den gewünschten Raum an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.') ?>
                <div>
                    <strong><?= _('Achtung') ?>:</strong>
                    <?= _('Geben Sie bitte immer die notwendige Sitzplatzanzahl mit an!') ?>
                </div>
            <? endif ?>
        </p>
        <div>
            <h2><?= _('Anfrage') ?></h2>
            <article>
                <?= htmlready($request->getTypeString(), 1, 1); ?>
            </article>
            <h2><?= _('Bearbeitungsstatus') ?></h2>
            <article>
                <?= htmlReady($request->getStatusText()); ?>
            </article>
        </div>

        <section>
            <? if ($step < 4) : ?>
                <?= $this->render_partial('course/room_requests/edit_step1') ?>
                <?= $this->render_partial('course/room_requests/edit_step2') ?>
                <?= $this->render_partial('course/room_requests/edit_step3') ?>
                <?= \Studip\Button::create(
                    _('Passende Räume suchen'),
                    'search_rooms',
                    [
                        'title' => _('Startet die Suche von Räumen anhand der gewählten Eigenschaften.')
                    ]
                ) ?>
            <? elseif ($step >= 4) : ?>
                <?= $this->render_partial('course/room_requests/edit_step4') ?>
            <? endif ?>
            <? if ($saving_allowed) : ?>
                <? if ($user_is_global_resource_admin) : ?>
                    <section>
                        <h2><?= _('Benachrichtigungen') ?></h2>
                        <label>
                            <input type="checkbox" name="reply_lecturers" value="1"
                                   <?= $reply_lecturers
                                     ? 'checked="checked"'
                                     : ''
                                   ?>>
                        <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
                        </label>
                    </section>
                <? endif ?>
                <section>
                    <h2><?= _('Nachricht an die Raumvergabe') ?></h2>
                    <textarea name="comment" cols="58" rows="4"
                              placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur angefragten Raumbelegung') ?>"><?= htmlReady($comment) ?></textarea>
                </section>
            <? endif ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <? if ($saving_allowed) : ?>
            <?= \Studip\Button::createAccept(
                _('Speichern und zurück zur Übersicht'),
                'save_and_close_form',
                [
                    'title' => _('Speichern und zurück zur Übersicht')
                ]
            ) ?>
            <?= \Studip\Button::create(
                _('Übernehmen'),
                'save',
                [
                    'title' => _('Änderungen speichern')
                ]
            ) ?>
        <? endif ?>
        <?= \Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->link_for('course/room_requests/index/' . $course_id),
            [
                'title' => _('Abbrechen')
            ]
        ) ?>
    </footer>
</form>
