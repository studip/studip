<? $month = $calendar->view; ?>
<table class="calendar-month">
    <thead>
        <tr>
            <td colspan="8" style="text-align: center; vertical-align: middle;">
                <div style="text-align: left; display: inline-block; width: 33%; white-space: nowrap;">
                    <a style="padding-right: 2em;" href="<?= $controller->url_for('calendar/group/month', ['atime' => mktime(12, 0, 0, date('n', $calendars[0][15]->getStart()), 15, date('Y', $calendars[0][15]->getStart()) - 1)]) ?>">
                        <?= Icon::create('arr_2left', 'clickable', ['title' => _('Ein Jahr zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                        <?= strftime('%B %Y', strtotime('-1 year', $calendars[0][15]->getStart())) ?>
                    </a>
                    <a href="<?= $controller->url_for('calendar/group/month', ['atime' => $calendars[0][0]->getStart() - 1]) ?>">
                        <?= Icon::create('arr_1left', 'clickable', ['title' => _('Einen Monat zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                        <?= strftime('%B %Y', strtotime('-1 month', $calendars[0][15]->getStart())) ?>
                    </a>
                </div>
                <div class="calhead" style="text-align: center; display: inline-block; width: 33%;">
                    <?= htmlReady(strftime("%B ", $calendars[0][15]->getStart())) .' '. date('Y', $calendars[0][15]->getStart()); ?>
                </div>
                <div style="text-align: right; display: inline-block; width: 33%; white-space: nowrap;">
                    <a style="padding-right: 2em;" href="<?= $controller->url_for('calendar/group/month', ['atime' => strtotime('+1 month', $calendars[0][15]->getStart())]) ?>">
                        <?= strftime('%B %Y', strtotime('+1 month', $calendars[0][15]->getStart())) ?>
                        <?= Icon::create('arr_1right', 'clickable', ['title' => _('Einen Monat vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                    </a>
                    <a href="<?= $controller->url_for('calendar/group/month', ['atime' => mktime(12, 0, 0, date('n', $calendars[0][15]->getStart()), 15, date('Y', $calendars[0][15]->getEnd()) + 1)]) ?>">
                        <?= strftime('%B %Y', strtotime('+1 year', $calendars[0][15]->getStart())) ?>
                        <?= Icon::create('arr_2right', 'clickable', ['title' => _('Ein Jahr vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                    </a>
                </div>
            </td>
        </tr>
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
                <a class="<?= $class_day . 'sday' ?>" href="<?= $controller->url_for('calendar/group/day/' . $this->range_id, ['atime' => $i]) ?>">
                    <?= $aday ?>
                </a>
                <? if ($hday["name"] != "") : ?>
                    <div style="color: #aaaaaa;" class="inday"><?= $hday['name'] ?></div>
                <? endif; ?>
                <? foreach($calendars as $user_calendars) : ?>
                    <? $count = sizeof($user_calendars[$j]->events) ?>
                    <? if ($count) : ?>
                    <div data-tooltip="">
                        <a class="inday calendar-event-text" href="<?= $controller->url_for('calendar/single/day/' . $user_calendars[$j]->getRangeId(), ['atime' => $user_calendars[$j]->getStart()]) ?>"><?= htmlReady($user_calendars[$j]->range_object->getFullname('no_title')) ?></a>
                        <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $user_calendars[$j]]) ?>
                    </div>
                    <? endif; ?>
                <? endforeach; ?>
                </td>
                    <td class="lightmonth calendar-month-week">
                    <a style="font-weight: bold;" class="calhead" href="<?= $controller->url_for('calendar/group/week/' . $this->range_id, ['atime' => $i]) ?>"><?= strftime("%V", $i) ?></a>
                    </td>
                </tr>
            <? else : ?>
                <? $hday_class = ['day', 'day', 'shday', 'hday'] ?>
                <? if ($hday['col']) : ?>
                    <a class="<?= $class_day . $hday_class[$hday['col']] ?>" href="<?= $controller->url_for('calendar/group/day', ['atime' => $i]) ?>">
                        <?= $aday ?>
                    </a>
                    <div style="color: #aaaaaa;" class="inday"><?= $hday['name'] ?></div>
                <? else : ?>
                    <a class="<?= $class_day . 'day' ?>" href="<?= $controller->url_for('calendar/single/day', ['atime' => $i]) ?>">
                        <?= $aday ?>
                    </a>
                <? endif; ?>
                <? foreach($calendars as $user_calendars) : ?>
                    <? $count = sizeof($user_calendars[$j]->events) ?>
                    <? if ($count) : ?>
                    <div data-tooltip>
                        <a class="inday calendar-event-text" href="<?= $controller->url_for('calendar/single/day/' . $user_calendars[$j]->getRangeId(), ['atime' => $user_calendars[$j]->getStart()]) ?>"><?= htmlReady($user_calendars[$j]->range_object->getFullname('no_title')) ?></a>
                        <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $user_calendars[$j]]) ?>
                    </div>
                    <? endif; ?>
                <? endforeach; ?>
                </td>
            <? endif; ?>
        <? endfor; ?>
        </tr>
    </tbody>
</table>
