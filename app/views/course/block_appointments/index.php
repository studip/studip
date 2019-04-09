<? if (!Request::isXhr()) : ?>
    <h1><?= _('Neuen Blocktermin anlegen') ?></h1>
<? endif ?>

<form <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>
    class="default collapsable"
    action="<?= $controller->url_for('course/block_appointments/save/' . $course_id, $editParams) ?>"
    method="post">

<? if ($confirm_many): ?>
    <?= MessageBox::info(sprintf(
        _('Sie legen %s%u%s Termine an. Bitte kontrollieren Sie Ihre Eingaben '
        . 'oder bestätigen Sie, dass die Termine angelegt werden sollen.'),
        '<strong>',
        $confirm_many,
        '</strong>'
    ), [
        sprintf(
            '<label><input type="checkbox" name="confirmed" value="1">%s</label>',
            sprintf(_('Ja, ich möchte wirklich %u Termine anlegen.'), $confirm_many)
        ),
    ]) ?>
<? endif; ?>

    <fieldset>
        <legend><?= _('Zeitraum') ?></legend>
        <label for="block_appointments_start_day" class="col-3">
            <?= _('Startdatum') ?>
            <input type="text" class="size-s has-date-picker" data-date-picker id="block_appointments_start_day"
                   name="block_appointments_start_day" value="<?= $request['block_appointments_start_day'] ?>">
        </label>
        <label for="block_appointments_end_day" class="col-3">
            <?= _('Enddatum') ?>
            <input type="text" class="size-s has-date-picker" data-date-picker='{">=":"#block_appointments_start_day"}' id="block_appointments_end_day"
                   name="block_appointments_end_day" value="<?= $request['block_appointments_end_day'] ?>">
        </label>
        <label for="block_appointments_start_time" class="col-3">
            <?= _('Startzeit') ?>
            <input type="text" class="size-s studip-timepicker" id="block_appointments_start_time"
                   name="block_appointments_start_time" value="<?= $request['block_appointments_start_time'] ?>"
                   placeholder="HH:mm">
        </label>

        <label for="block_appointments_end_time" class="col-3">
            <?= _('Endzeit') ?>
            <input type="text" class="size-s studip-timepicker" id="block_appointments_end_time"
                   name="block_appointments_end_time" value="<?= $request['block_appointments_end_time'] ?>"
                   placeholder="HH:mm">
        </label>

        <div id="block_appointments_days">
            <label><?= _('Die Veranstaltung findet an folgenden Tagen statt') ?></label>
            <label for="block_appointments_days_0" class="col-2">
                <input <?= empty($request['block_appointments_days']) || in_array('everyday', $request['block_appointments_days']) ? 'checked' : '' ?>
                    class="block_appointments_days"
                    name="block_appointments_days[]" id="block_appointments_days_0" type="checkbox" value="everyday">
                <?= _('Jeden Tag') ?>
            </label>

            <label for="block_appointments_days_1" class="col-2">
                <input <?= in_array('weekdays', (array) $request['block_appointments_days']) ? 'checked ' : '' ?>
                    class="block_appointments_days"
                    name="block_appointments_days[]" id="block_appointments_days_1" type="checkbox" value="weekdays">
                <?= _('Mo-Fr') ?>
            </label>
            <? foreach (range(0, 6) as $d) : ?>
                <? $id = 2 + $d ?>
                <label for="block_appointments_days_<?= $id ?>" class="col-2">
                    <input <?= in_array($d+1, (array) $request['block_appointments_days']) ? 'checked ' : '' ?>
                        class="block_appointments_days"
                        name="block_appointments_days[]" id="block_appointments_days_<?= $id ?>" type="checkbox"
                        value="<?= $d + 1 ?>">
                    <?= strftime('%A', strtotime("+$d day", $start_ts)) ?>
                </label>
            <? endforeach ?>
        </div>

    </fieldset>

    <fieldset class="collapsed">
        <legend><?= _('Weitere Daten') ?></legend>
        <label for="block_appointments_termin_typ">
            <?= _('Art der Termine') ?>
            <select clas="size-l" name="block_appointments_termin_typ" id="block_appointments_termin_typ">
                <? foreach ($GLOBALS['TERMIN_TYP'] as $key => $value) : ?>
                    <option
                        value="<?= $key ?>" <?= $request['block_appointments_termin_typ'] == $key ? 'selected' : '' ?>>
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label for="block_appointments_room_text">
            <?= _('Freie Ortsangabe') ?>
            <input type="text" name="block_appointments_room_text" id="block_appointments_room_text"
                   value="<?= $request['block_appointments_room_text'] ?>">
        </label>

        <label for="block_appointments_date_count">
            <?= _('Anzahl') ?>
            <input type="text" name="block_appointments_date_count" id="block_appointments_date_count" class="size-s" value="<?= $request['block_appointments_date_count'] ?: 1 ?>">
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), ['data-dialog' => 'size=big']) ?>
    </footer>
</form>
