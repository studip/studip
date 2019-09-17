<?php
$days_of_the_week = [
    _('Montag')     => 1,
    _('Dienstag')   => 2,
    _('Mittwoch')   => 3,
    _('Donnerstag') => 4,
    _('Freitag')    => 5,
    _('Samstag')    => 6,
    _('Sonntag')    => 0
];
$intervals = [
    _('wöchentlich')     => 1,
    _('zweiwöchentlich') => 2,
    _('dreiwöchentlich') => 3,
    _('monatlich')       => 4,
];
?>

<form action="<?= $controller->store() ?>" method="post" class="default" data-dialog>
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Neue Sprechstundenblöcke anlegen') ?>
        </legend>

        <label>
            <span class="required"><?= _('Ort') ?></span>

            <input required type="text" name="room"
                   value="<?= htmlReady(Request::get('room', $room)) ?>"
                   placeholder="<?= _('Ort') ?>">
        </label>

        <label class="col-3">
            <span class="required"><?= _('Beginn') ?></span>

            <input required type="text" name="start-date" id="start-date"
                   value="<?= htmlReady(Request::get('start-date', strftime('%d.%m.%Y', strtotime('+7 days'))))  ?>"
                   placeholder="<?= _('tt.mm.jjjj') ?>"
                   data-date-picker>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Ende') ?></span>

            <input required type="text" name="end-date" id="end-date"
                   value="<?= htmlReady(Request::get('end-date', strftime('%d.%m.%Y', strtotime('+4 weeks'))))  ?>"
                   placeholder="<?= _('tt.mm.jjjj') ?>"
                   data-date-picker='{">":"#start-date"}'>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Am Wochentag') ?></span>

            <select required name="day-of-week">
            <? foreach ($days_of_the_week as $day => $value): ?>
                <option value="<?= $value ?>" <? if (Request::get('day-of-week', strftime('%w')) == $value) echo 'selected'; ?>>
                    <?= htmlReady($day) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Intervall') ?></span>
            <select required name="interval">
            <? foreach ($intervals as $interval => $value): ?>
                <option value="<?= $value ?>" <? if (Request::int('interval') == $value) echo 'selected'; ?>>
                    <?= htmlReady($interval) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label for="start-time" class="col-3">
            <span class="required"><?= _('Von') ?></span>

            <input required type="text" name="start-time" id="start-time"
                   value="<?= htmlReady(Request::get('start-time', '08:00')) ?>"
                   placeholder="<?= _('HH:mm') ?>"
                   data-time-picker='{"<":"#end-time"}'>
        </label>

        <label for="ende_hour" class="col-3">
            <span class="required"><?= _('Bis') ?></span>

            <input required type="text" name="end-time" id="end-time"
                   value="<?= htmlReady(Request::get('end-time', '09:00')) ?>"
                   placeholder="<?= _('HH:mm') ?>"
                   data-time-picker='{">":"#start-time"}'>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Dauer eines Termins in Minuten') ?></span>
            <input required type="text" name="duration"
                   value="<?= htmlReady(Request::int('duration', 15)) ?>"
                   maxlength="3" pattern="^\d+$">
        </label>

        <label class="col-3">
            <?= _('Maximale Teilnehmerzahl') ?>
            <?= tooltipIcon(_('Falls Sie mehrere Personen zulassen wollen (wie z.B. zu einer Klausureinsicht), so geben Sie hier die maximale Anzahl an Personen an, die sich anmelden dürfen.')) ?>
            <input required type="text" name="size" id="size"
                   min="1" max="50" value="<?= Request::int('size', 1) ?>">
        </label>

        <label>
            <?= _('Information zu den Terminen in diesem Block') ?>
            <textarea name="note"><?= htmlReady(Request::get('note')) ?></textarea>
        </label>

        <label>
            <input type="checkbox" name="calender-events" value="1"
                    <? if (Request::int('calender-events')) echo 'checked'; ?>>
            <?= _('Die freien Sprechstundentermine auch im Kalender markieren') ?>
        </label>

    <? if ($course_search): ?>
        <label>
            <?= _('Zugewiesene Veranstaltung') ?>
            <?= tooltipIcon(_('Wählen Sie hier eine Veranstaltung aus, damit die Sprechstundentermine nur für Teilnehmer der gewählten Veranstaltung sichtbar sind und auch nur von diesen belegt werden können.')) ?>
            <?= $course_search->render() ?>
        </label>
    <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL()
        ) ?>
    </footer>
</form>
