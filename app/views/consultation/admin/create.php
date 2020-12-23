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

<? if ($flash['confirm-many']): ?>
    <?= MessageBox::info(
        _('Sie erstellen eine sehr große Anzahl an Terminen.') . ' ' .
        _('Bitte bestätigen Sie diese Aktion.'),
        [
            '<label><input type="checkbox" name="confirmed" value="1">' .
            sprintf(
                _('Ja, ich möchte wirklich %s Termine erstellen.'),
                number_format($flash['confirm-many'], 0, ',', '.')
            ) .
            '</label>'
        ]
    )->hideClose() ?>
<? endif; ?>

    <fieldset>
        <legend>
            <?= _('Neue Terminblöcke anlegen') ?>
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
                   data-date-picker='{">=":"today"}'>
        </label>

        <label class="col-3">
            <span class="required"><?= _('Ende') ?></span>

            <input required type="text" name="end-date" id="end-date"
                   value="<?= htmlReady(Request::get('end-date', strftime('%d.%m.%Y', strtotime('+4 weeks'))))  ?>"
                   placeholder="<?= _('tt.mm.jjjj') ?>"
                   data-date-picker='{">=":"#start-date"}'>
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

    <? if ($responsible): ?>
        <label>
            <?= _('Durchführende Person') ?>
            <select name="teacher_id">
                <option value=""></option>
            <? foreach ($responsible as $user): ?>
                <option value="<?= htmlReady($user->id) ?>">
                    <?= htmlReady($user->getFullName()) ?>
                </option>
            <? endforeach; ?>
            </select>
    <? endif; ?>

        <label>
            <?= _('Information zu den Terminen in diesem Block') ?>
            <textarea name="note"><?= htmlReady(Request::get('note')) ?></textarea>
        </label>

        <label>
            <input type="checkbox" name="calender-events" value="1"
                    <? if (Request::bool('calender-events')) echo 'checked'; ?>>
            <?= _('Die freien Termine auch im Kalender markieren') ?>
        </label>

        <label>
            <input type="checkbox" name="show-participants" value="1"
                    <? if (Request::bool('show-participants')) echo 'checked'; ?>>
            <?= _('Namen der buchenden Personen sind öffentlich sichtbar') ?>
        </label>

        <label>
            <?= _('Grund der Buchung abfragen') ?>
        </label>
        <div class="hgroup">
            <label>
                <input type="radio" name="require-reason" value="yes"
                       <? if (Request::get('require-reason') === 'yes') echo 'checked'; ?>>
                <?= _('Ja, zwingend erforderlich') ?>
            </label>

            <label>
                <input type="radio" name="require-reason" value="optional"
                       <? if (Request::get('require-reason', 'optional') === 'optional') echo 'checked'; ?>>
                <?= _('Ja, optional') ?>
            </label>

            <label>
                <input type="radio" name="require-reason" value="no"
                       <? if (Request::get('require-reason') === 'no') echo 'checked'; ?>>
                <?= _('Nein') ?>
            </label>
        </div>

        <label>
            <?= _('Bestätigung für folgenden Text einholen') ?>
            (<?= _('optional') ?>)
            <?= tooltipIcon(_('Wird hier ein Text eingegeben, so müssen Buchende bestätigen, dass sie diesen Text gelesen haben.')) ?>
            <textarea name="confirmation-text"><?= htmlReady(Request::get('confirmation-text')) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL()
        ) ?>
    </footer>
</form>
