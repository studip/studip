<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_header',
        [
            'action'     => $this->controller->link_for('course/room_requests/request_select_properties/' . $request_id),
            'request_id' => $request_id
        ]
    ) ?>
    <?= $this->render_partial(
        'course/room_requests/_request_edit_header',
        ['request' => $request]
    ) ?>
    <?= $this->render_partial(
        'course/room_requests/request_start',
        ['embedded' => true]
    ) ?>
<? endif ?>

<? if ($available_properties) : ?>
        <? foreach ($available_properties as $property) : ?>
            <?= $property->toHtmlInput(
                $selected_properties[$property->name],
                'selected_properties[' . htmlReady($property->name) . ']',
                true,
                false
            ) ?>
        <? endforeach ?>
<? endif ?>

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


    <label>
        <?= _('Nachricht an die Raumvergabe') ?>
    <textarea name="comment" cols="58" rows="4"
              placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur angefragten Raumbelegung') ?>"><?= htmlReady($comment) ?></textarea>
    </label>

<? if (!$embedded) : ?>
    </div>
    </section>
    <?= $this->render_partial(
        'course/room_requests/_request_form_footer',
        [
            'room_search_button' => true,
            'save_buttons'       => true
        ]
    ) ?>
<? endif ?>
