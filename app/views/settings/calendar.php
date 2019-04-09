<?
use Studip\Button, Studip\LinkButton;

$cal_views = [
    'day'   => _('Tagesansicht'),
    'week'  => _('Wochenansicht'),
    'month' => _('Monatsansicht'),
    'year'  => _('Jahresansicht'),
];
$cal_deletes = [
    12 => _('12 Monate nach Ablauf'),
     6 => _('6 Monate nach Ablauf'),
     3 => _('3 Monate nach Ablauf'),
     0 => _('nie'),
];
$cal_step_days = [
     600 => _('10 Minuten'),
     900 => _('15 Minuten'),
    1800 => _('30 Minuten'),
    3600 => _('1 Stunde'),
    7200 => _('2 Stunden'),
];
$cal_step_weeks = [
    1800 => _('30 Minuten'),
    3600 => _('1 Stunde'),
    7200 => _('2 Stunden'),
];
?>

<form method="post" action="<?= $controller->url_for('settings/calendar/store') ?>" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Einstellungen des Terminkalenders') ?>
        </legend>

        <label>
            <?= _('Startansicht') ?>
            <select name="cal_view" id="cal_view" size="1">
                <? foreach ($cal_views as $index => $label): ?>
                    <option value="<?= $index ?>" <? if ($view == $index) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Wochenansicht') ?>
            <select name="cal_type_week">
                <option value="LONG"<?= $type_week == 'LONG' ? ' selected' : "" ?>>
                    <?= _('7 Tage-Woche') ?>
                </option>
                <option value="SHORT"<?= $type_week == 'SHORT' ? ' selected' : "" ?>>
                    <?= _('5 Tage-Woche') ?>
                </option>
            </select>
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _('Einzelterminkalender') ?>
        </legend>

        <div>
            <?= _('Zeitraum der Tages- und Wochenansicht') ?>
            <section class="hgroup">
                <label>
                    <?= _("Von") ?>
                    <select name="cal_start" aria-label="<?= _('Startzeit der Tages- und Wochenansicht') ?>" class="size-s">
                        <? for ($i = 0; $i < 24; $i += 1): ?>
                            <option value="<?= $i ?>" <? if ($start == $i) echo 'selected'; ?>>
                                <?= sprintf('%02u:00', $i) ?>
                            </option>
                        <? endfor; ?>
                    </select>
                    <?= _("Uhr") ?>
                </label>

                <label>
                    <?= _("Bis") ?>
                    <select name="cal_end" aria-label="<?= _('Endzeit der Tages- und Wochenansicht') ?>" class="size-s">
                        <? for ($i = 0; $i < 24; $i += 1): ?>
                            <option value="<?= $i ?>" <? if ($end == $i) echo 'selected'; ?>>
                                <?= sprintf('%02u:00', $i) ?>
                            </option>
                        <? endfor; ?>
                    </select>
                    <?= _("Uhr") ?>.
                </label>
            </section>
        </div>

        <label>
            <?= _('Zeitintervall der Tagesansicht') ?>
            <select name="cal_step_day" for="cal_step_day">
                <? foreach ($cal_step_days as $index => $label): ?>
                    <option value="<?= $index ?>" <? if ($step_day == $index) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Zeitintervall der Wochenansicht') ?>
            <select name="cal_step_week" id="cal_step_week">
                <? foreach ($cal_step_weeks as $index => $label): ?>
                    <option value="<?= $index ?>" <? if ($step_week == $index) echo 'selected'; ?>>
                        <?= $label ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

    </fieldset>

    <? if (get_config('CALENDAR_GROUP_ENABLE')): ?>
        <fieldset>
            <legend>
                <?= _('Gruppenterminkalender') ?>
            </legend>

            <label>
                <?= _("Zeitintervall der Tagesansicht") ?>
                <select name="cal_step_day_group" id="cal_step_day_group">
                    <? foreach ($cal_step_days as $index => $label): ?>
                        <option value="<?= $index ?>" <? if ($step_day_group == $index) echo 'selected'; ?>>
                            <?= $label ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </label>

            <label>
                <?= _('Zeitintervall der Wochenansicht') ?>
                <select name="cal_step_week_group" id="cal_step_week_group">
                    <? foreach ($cal_step_weeks as $index => $label): ?>
                        <option value="<?= $index ?>" <? if ($step_week_group == $index) echo 'selected'; ?>>
                            <?= $label ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </label>

        </fieldset>
    <? endif ?>

    <footer>
        <? if (Request::option('atime')): ?>
            <input type="hidden" name="atime" value="<?= Request::option('atime') ?>">
        <? endif ?>
        <input type="hidden" name="view" value="calendar">
        <?= Button::createAccept(_('Übernehmen'), ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
</form>
