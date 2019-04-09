<? $month = $calendar->view; ?>

<nav class="calendar-nav" style="vertical-align: middle">
    <span style="white-space: nowrap;">
        <a class="hidden-medium-down" style="padding-right: 2em;" href="<?= $controller->url_for('calendar/single/month', ['atime' => strtotime('-1 year', $atime)]) ?>">
            <?= Icon::create('arr_2left', 'clickable', ['title' => _('Ein Jahr zurück')])->asImg(['style' => 'vertical-align: text-top;']) ?>
            <?= strftime('%B %Y', strtotime('-1 year', $atime)) ?>
        </a>
        <a class="hidden-tiny-down" href="<?= $controller->url_for('calendar/single/month', ['atime' => strtotime('-1 month', $atime)]) ?>">
            <?= Icon::create('arr_1left', 'clickable', ['title' => _('Einen Monat zurück')])->asImg(['style' => 'vertical-align: text-top;']) ?>
            <?= strftime('%B %Y', strtotime('-1 month', $atime)) ?>
        </a>
    </span>

    <?
    $calType = 'month';
    $calLabel = htmlReady(strftime("%B ", $calendars[15]->getStart())) .' '. date('Y', $calendars[15]->getStart());
    ?>

    <?= $this->render_partial('calendar/single/_calhead', compact('calendar', 'atime', 'calType', 'calLabel')) ?>

    <span style="text-align: right; white-space: nowrap;">
        <a class="hidden-tiny-down" style="padding-right: 2em;" href="<?= $controller->url_for('calendar/single/month', ['atime' => strtotime('+1 month', $atime)]) ?>">
            <?= strftime('%B %Y', strtotime('+1 month', $atime)) ?>
            <?= Icon::create('arr_1right', 'clickable', ['title' => _('Einen Monat vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
        </a>
        <a class="hidden-medium-down" href="<?= $controller->url_for('calendar/single/month', ['atime' => strtotime('+1 year', $atime)]) ?>">
            <?= strftime('%B %Y', strtotime('+1 year', $atime)) ?>
            <?= Icon::create('arr_2right', 'clickable', ['title' => _('Ein Jahr vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
        </a>
    </span>
</nav>

<div class="table-scrollbox-horizontal">
<table class="calendar-month">
    <thead>
        <tr class="calendar-month-weekdays">
            <? $week_days = [39092400, 39178800, 39265200, 39351600, 39438000, 39524400, 39610800]; ?>
            <? foreach ($week_days as $week_day) : ?>
                <td class="precol1w">
                    <?= strftime('%a', $week_day) ?>
                </td>
            <? endforeach; ?>
            <td align="center" class="precol1w">
                <?= _('Woche') ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <? for ($i = $first_day, $j = 0; $i <= $last_day; $i += 86400, $j++) : ?>
            <? $aday = date('j', $i); ?>
            <?
            $class_day = '';
            if (($aday - $j - 1 > 0) || ($j - $aday > 6)) {
                $class_cell = 'lightmonth';
                $class_day = 'light';
            } elseif (date('Ymd', $i) == date('Ymd')) {
                $class_cell = 'celltoday';
            } else {
                $class_cell = 'month';
            }
            $hday = holiday($i);

            if ($j % 7 == 0) {
                ?><tr><?
            }
            ?>
            <td class="<?= $class_cell ?>">
            <? if (($j + 1) % 7 == 0) : ?>
                <a class="<?= $class_day . 'sday' ?>" href="<?= $controller->url_for('calendar/single/day', ['atime' => $i]) ?>">
                    <?= $aday ?>
                </a>
                <? if ($hday["name"] != "") : ?>
                    <div style="color: #aaaaaa;" class="inday"><?= $hday['name'] ?></div>
                <? endif; ?>
                <? foreach ($calendars[$j]->events as $event) : ?>
                    <div data-tooltip>
                        <a data-dialog="size=auto" title="<?= _('Termin bearbeiten') ?>" class="inday <?= $event instanceof CourseEvent ? 'calendar-course-event-text' : 'calendar-event-text' ?><?= $event->getCategory() ?>" href="<?= $controller->url_for('calendar/single/edit/' . $event->range_id . '/' . $event->event_id, ['atime' => $event->getStart()]) ?>"><?= htmlReady($event->getTitle()) ?></a>
                        <?= $this->render_partial('calendar/single/_tooltip', ['event' => $event, 'calendar' => $calendars[$j]]) ?>
                    </div>
                <? endforeach; ?>
                </td>
                    <td class="lightmonth calendar-month-week">
                    <a style="font-weight: bold;" class="calhead" href="<?= $controller->url_for('calendar/single/week', ['atime' => $i]) ?>"><?= strftime("%V", $i) ?></a>
                    </td>
                </tr>
            <? else : ?>
                <? $hday_class = ['day', 'day', 'shday', 'hday'] ?>
                <? if ($hday['col']) : ?>
                    <a class="<?= $class_day . $hday_class[$hday['col']] ?>" href="<?= $controller->url_for('calendar/single/day', ['atime' => $i]) ?>">
                        <?= $aday ?>
                    </a>
                    <div style="color: #aaaaaa;" class="inday"><?= $hday['name'] ?></div>
                <? else : ?>
                    <a class="<?= $class_day . 'day' ?>" href="<?= $controller->url_for('calendar/single/day', ['atime' => $i]) ?>">
                        <?= $aday ?>
                    </a>
                <? endif; ?>
                <? foreach ($calendars[$j]->events as $event) : ?>
                    <div data-tooltip>
                        <a data-dialog="size=auto" title="<?= _('Termin bearbeiten') ?>" class="inday <?= $event instanceof CourseEvent ? 'calendar-course-event-text' : 'calendar-event-text' ?><?= $event->getCategory() ?>" href="<?= $controller->url_for('calendar/single/edit/' . $event->range_id . '/' . $event->event_id, ['atime' => $event->getStart()]) ?>"><?= htmlReady($event->getTitle()) ?></a>
                        <?= $this->render_partial('calendar/single/_tooltip', ['event' => $event, 'calendar' => $calendars[$j]]) ?>
                    </div>
                <? endforeach; ?>
                </td>
            <? endif; ?>
        <? endfor; ?>
        </tr>
    </tbody>
</table>
</div>
