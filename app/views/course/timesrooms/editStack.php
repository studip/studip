<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id, $linkAttributes) ?>"
      class="default collapsable" data-dialog="size=big">
    <?= CSRFProtection::tokenTag()?>
    <input type="hidden" name="method" value="edit">
    <input type="hidden" name="checked_dates" value="<?= implode(',', $checked_dates) ?>">

    <fieldset>
        <legend><?= _('Raumangaben') ?></legend>
        <? if (Config::get()->RESOURCES_ENABLE && (!empty($room_search) || !empty($selectable_rooms))): ?>
            <section>
                <input type="radio" name="action" value="room"
                        id="room">
                <label style="display: inline-block; width: 50%; vertical-align: middle">
                    <? if (!empty($room_search)) : ?>
                        <?= $room_search
                            ->setAttributes(['onFocus' => "jQuery('input[type=radio][name=action][value=room]').prop('checked', 'checked')"])
                            ->render() ?>
                    <? else : ?>
                        <select name="room_id" style="display: inline-block; width: 50%;" onFocus="jQuery('input[type=radio][name=action][value=room]').prop('checked', 'checked')">
                            <option value="0">-- <?= _('Raum auswählen') ?> --</option>
                            <? foreach ($selectable_rooms as $room): ?>
                                <option value="<?= htmlReady($room->id)?>">
                                    <?= htmlReady($room->name) ?>
                                </option>
                            <? endforeach ?>
                        </select>
                    <? endif ?>
                    <? if (!$only_bookable_rooms) : ?>
                        <?
                        $input_attr = [
                            'class' => 'text-bottom',
                            'style' => 'margin-left: 0.2em; margin-top: 0.6em;',
                            'name' => 'only_bookable_rooms',
                            'value' => '1',
                            'title' => _('Nur buchbare Räume anzeigen')
                        ];
                        if (Request::isDialog()) {
                            $input_attr['data-dialog'] = 'size=big';
                        }
                        ?>
                        <?= Icon::create('room-request')->asInput(20, $input_attr) ?>
                    <? endif ?>
                    <div>
                        <?= _('Rüstzeit (in Minuten)') ?>
                        <input type="number" name="preparation_time"
                               class="preparation-time"
                               value="<?= htmlReady($preparation_time) ?>"
                               min="0" max="<?= htmlReady($max_preparation_time) ?>">
                    </div>
                </label>
            </section>

            <? $placerholder = _('Freie Ortsangabe (keine Raumbuchung):') ?>
        <? else : ?>
            <? $placerholder = _('Freie Ortsangabe:') ?>
        <? endif ?>
        <section>
        <input type="radio" name="action" value="freetext">
        <label style="display: inline;">
            <input type="text" name="freeRoomText" style="display: inline-block; width: 50%;" value="<?= $tpl['freeRoomText'] ?>"
                   placeholder="<?= $placerholder ?>"
                   onFocus="jQuery('input[type=radio][name=action][value=freetext]').prop('checked', 'checked')">
        </label>
        </section>
        <? if (Config::get()->RESOURCES_ENABLE) : ?>
            <label>
                <input type="radio" name="action" value="noroom" style="display:inline">
                <?= _('Kein Raum') ?>
            </label>
        <? endif ?>

        <label>
            <input type="radio" name="action" value="nochange" checked="checked">
            <?= _('Keine Änderungen an den Raumangaben vornehmen') ?>
        </label>
    </fieldset>

    <fieldset class="collapsed">
        <legend><?= _('Terminangaben') ?></legend>
        <label>
            <?= _('Art') ?>
            <select name="course_type" id="course_type">
                <option value=""><?= _('-- Keine Änderung --') ?></option>
                <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                    <option value="<?= $id ?>"><?= htmlReady($value['name']) ?></option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <fieldset class="collapsed">
        <legend><?= _('Durchführende Lehrende') ?></legend>
        <label>
            <select name="related_persons_action" id="related_persons_action">
                <option value="">-- <?= _('Aktion auswählen') ?> --</option>
                <option value="add">...<?= _('hinzufügen') ?></option>
                <option value="delete">...<?= _('entfernen') ?></option>
            </select>
        </label>

        <select name="related_persons[]" id="related_persons" multiple>
            <? foreach ($teachers as $teacher) : ?>
                <option value="<?= htmlReady($teacher['user_id']) ?>"><?= htmlReady($teacher['fullname']) ?></option>
            <? endforeach ?>
        </select>
    </fieldset>

    <? if (count($gruppen)) : ?>
        <fieldset class="collapsed">
            <legend><?= _('Beteiligte Gruppen') ?></legend>
            <label>
                <select name="related_groups_action" id="related_groups_action">
                    <option value="">-- <?= _('Aktion auswählen') ?> --</option>
                    <option value="add">...<?= _('hinzufügen') ?></option>
                    <option value="delete">...<?= _('entfernen') ?></option>
                </select>
            </label>

            <select id="related_groups" name="related_groups[]" multiple>
                <? foreach ($gruppen as $gruppe) : ?>
                    <option value="<?= htmlReady($gruppe->statusgruppe_id) ?>"><?= htmlReady($gruppe->name) ?></option>
                <? endforeach ?>
            </select>
        </fieldset>
    <? endif ?>


    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Änderungen speichern'), 'save') ?>
        <? if (Request::int('fromDialog')) : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), ['data-dialog' => 'size=big']) ?>
        <? endif ?>
    </footer>
</form>
