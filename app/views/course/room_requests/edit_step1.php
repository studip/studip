<h2><?= _('Raum suchen') ?></h2>
<label><?= _('Raumname') ?>
    <input type="text" name="room_name" value="<?= htmlReady($room_name) ?>">
</label>
<? if ($available_room_categories): ?>
    <label>
        <?= _('Raumkategorie') ?>
        <span class="flex-row">
            <select name="room_category_id">
                <option value=""><?= _('bitte auswählen') ?></option>
                    <? foreach ($available_room_categories as $rc): ?>
                        <option value="<?= htmlReady($rc->id) ?>"
                                <?= ($room_category_id == $rc->id)
                                  ? 'selected="selected"'
                                  : '' ?>>
                            <?= htmlReady($rc->name) ?>
                        </option>
                    <? endforeach ?>
            </select>
            <?= Icon::create('accept', 'clickable', ['title' => _('Raumtyp auswählen')])->asInput(
                '20px',
                [
                    'type' => 'image',
                    'class' => 'text-bottom',
                    'name' => 'select_category',
                    'value' => _('Raumtyp auswählen'),
                    'style' => 'margin-left: 0.2em; margin-top: 0.5em;'
                ]
            ) ?>
            <? if ($room_category): ?>
                <?= Icon::create('refresh', 'clickable', ['title' => _('alle Angaben zurücksetzen')])->asInput(
                    '20px',
                    [
                        'type' => 'image',
                        'class' => 'text-bottom',
                        'name' => 'reset_category',
                        'style' => 'margin-left: 0.2em; margin-top: 0.5em;'
                    ]
                ) ?>
            <? endif ?>
        </span>
    </label>
<? endif ?>
