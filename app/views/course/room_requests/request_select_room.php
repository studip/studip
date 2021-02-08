<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_header',
        [
            'action'     => $this->controller->link_for('course/room_requests/request_select_room/' . $request_id),
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
    <? if (($category instanceof ResourceCategory) && !$direct_room_requests_only): ?>
        <?= $this->render_partial(
            'course/room_requests/request_select_properties',
            ['embedded' => true]
        ) ?>
    <? endif ?>
<? endif ?>
</div>

<div>
<? if ($available_rooms) : ?>
    <section class="contentbox">
        <header><h1><?= _('Passende Räume') ?></h1></header>
        <section class="selectbox">
            <fieldset>
                <? foreach ($available_rooms as $room): ?>
                    <div class="flex-row">
                        <label class="horizontal">
                            <? if ($overlaps[$room->id] <= 0.0): ?>
                                <?= Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(
                                    ['class' => 'text-bottom']
                                ) ?>
                            <? elseif ($overlaps[$room->id] >= 1.0): ?>
                                <?= Icon::create('decline-circle', Icon::ROLE_STATUS_RED)->asImg(
                                    ['class' => 'text-bottom']
                                ) ?>
                            <? else: ?>
                                <?= Icon::create('exclaim-circle', Icon::ROLE_STATUS_YELLOW)->asImg(
                                    ['class' => 'text-bottom']
                                ) ?>
                            <? endif ?>
                            <input type="radio" name="selected_room_id"
                                   data-activates="button[type='submit'][name='select_room']"
                                   value="<?= htmlReady($room->id) ?>">
                            <?= htmlReady(mb_substr($room->name, 0, 50)); ?>
                            <? if ($room->properties): ?>
                                <? $property_names = $room->getInfolabelPrperties()
                                    ->pluck('fullname') ?>
                                <?= tooltipIcon(implode("\n", $property_names)) ?>
                            <? endif ?>
                        </label>
                    </div>
                <? endforeach ?>
            </fieldset>
        </section>
    </section>
    <? else : ?>
        <?= MessageBox::info(_('Es wurden keine passenden Räume gefunden!')) ?>
    <? endif ?>
    </div>
</section>
<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_footer',
        [
            'room_search_button' => true,
            'room_select_button' => true,
            'save_buttons' => true,
            'select_properties_button' => false
        ]
    ) ?>
<? endif ?>
