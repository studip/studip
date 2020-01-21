<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_header',
        [
            'action' => $this->controller->link_for('course/room_requests/request_select_properties/' . $request_id),
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
    <fieldset>
        <legend><?= _('Wünschbare Eigenschaften') ?></legend>
        <? foreach ($available_properties as $property) : ?>
            <?= $property->toHtmlInput(
                $selected_properties[$property->name],
                'selected_properties[' . htmlReady($property->name) . ']',
                true,
                '',
                false
            ) ?>
        <? endforeach ?>
    </fieldset>
<? endif ?>
<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_footer',
        [
            'room_search_button' => true,
            'save_buttons' => true
        ]
    ) ?>
<? endif ?>
