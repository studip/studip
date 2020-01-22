<? if (!$embedded) : ?>
    <?= $this->render_partial(
        'course/room_requests/_request_form_header',
        [
            'action' => $this->controller->link_for('course/room_requests/request_start/' . $request_id),
            'request_id' => $request_id
        ]
    ) ?>
    <?= $this->render_partial(
        'course/room_requests/_request_edit_header',
        ['request' => $request]
    ) ?>
<? endif ?>
<fieldset>
    <legend><?= _('Raum suchen') ?></legend>
    <label>
        <?= _('Raumname') ?>
        <span class="flex-row">
            <input type="text" name="room_name" value="<?= htmlReady($room_name) ?>">
            <?= Icon::create('search', Icon::ROLE_CLICKABLE)->asInput(
                [
                    'name' => 'search_by_name',
                    'class' => 'text-bottom',
                    'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                ]
            ) ?>
        </span>
    </label>
    <? if ($available_room_categories): ?>
        <label>
            <?= _('Raumkategorie') ?>
            <span class="flex-row">
                <select name="category_id" <?= $category ? 'readonly' : '' ?>>
                    <option value=""><?= _('bitte auswählen') ?></option>
                    <? foreach ($available_room_categories as $rc): ?>
                        <option value="<?= htmlReady($rc->id) ?>"
                                <?= ($category_id == $rc->id)
                                  ? 'selected="selected"'
                                  : '' ?>>
                            <?= htmlReady($rc->name) ?>
                        </option>
                    <? endforeach ?>
                </select>
                <? if ($category) : ?>
                    <?= Icon::create('refresh', Icon::ROLE_CLICKABLE, ['title' => _('alle Angaben zurücksetzen')])->asInput(
                        [
                            'type'  => 'image',
                            'class' => 'text-bottom',
                            'name'  => 'reset_category',
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                        ]
                    ) ?>
                <? else : ?>
                    <?= Icon::create('accept', Icon::ROLE_CLICKABLE, ['title' => _('Raumtyp auswählen')])->asInput(
                        [
                            'type'  => 'image',
                            'class' => 'text-bottom',
                            'name'  => 'select_properties',
                            'value' => _('Raumtyp auswählen'),
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;'
                        ]
                    ) ?>
                <? endif ?>
            </span>
        </label>
    <? endif ?>
</fieldset>
<? if (!$embedded) : ?>
    <?= $this->render_partial('course/room_requests/_request_form_footer') ?>
<? endif ?>
