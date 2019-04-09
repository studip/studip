<div class="calendar-single-year">

    <nav class="calendar-nav" style="vertical-align: middle">
        <span style="white-space: nowrap;">
            <a href="<?= $controller->url_for('calendar/single/year', ['atime' => strtotime('-1 year', $atime)]) ?>">
                <?= Icon::create('arr_2left', 'clickable', ['title' => _('Ein Jahr zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                <?= strftime('%Y', strtotime('-1 year', $atime)) ?>
            </a>
        </span>

        <?
        $calType = 'year';
        $calLabel = date('Y', $calendar->getStart());
        ?>

        <?= $this->render_partial('calendar/single/_calhead', compact('calendar', 'atime', 'calType', 'calLabel')) ?>

        <span style="text-align: right; white-space: nowrap;">
            <a href="<?= $controller->url_for('calendar/single/year', ['atime' => strtotime('+1 year', $atime)]) ?>">
                <?= strftime('%Y', strtotime('+1 year', $atime)) ?>
                <?= Icon::create('arr_2right', 'clickable', ['title' => _('Ein Jahr vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
            </a>
        </span>
    </nav>

    <div class="table-scrollbox-horizontal">
        <table class="calendar-single-year--table" width="100%">
            <? $days_per_month = [31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (date('L', $calendar->getStart())) {
                $days_per_month[2]++;
            }
            ?>

            <thead>
                <tr>
                    <? for ($i = 1; $i < 13; $i++) : ?>
                        <?  $ts_month += ( $days_per_month[$i] - 1) * 86400; ?>
                        <th align="center" width="8%">
                            <a class="calhead" href="<?= $controller->url_for('calendar/single/month', ['atime' => $calendar->getStart() + $ts_month]) ?>">
                                <b><?= strftime('%B', $ts_month); ?></b>
                            </a>
                        </th>
                    <? endfor; ?>
                </tr>
            </thead>

            <tbody>
                <? $now = date('Ymd'); ?>
                <? for ($i = 1; $i < 32; $i++) : ?>
                    <tr>
                        <? for ($month = 1; $month < 13; $month++) : ?>

                            <? $aday = mktime(12, 0, 0, $month, $i, date('Y', $calendar->getStart())); ?>
                            <? $iday = date('Ymd', $aday); ?>
                            <? if ($i <= $days_per_month[$month]) : ?>
                                <? $wday = date('w', $aday);
                                // emphasize current day
                                if (date('Ymd', $aday) == $now) {
                                    $day_class = ' class="celltoday"';
                                } else if ($wday == 0 || $wday == 6) {
                                    $day_class = ' class="weekend"';
                                } else {
                                    $day_class = ' class="weekday"';
                                }
                                ?>

                                <td <?= $day_class ?> <?= $month == 1 ? 'height="25"' : '' ?>>

                                    <? if (isset($count_list[$iday]) && count($count_list[$iday])) : ?>
                                        <table width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td<?= $day_class ?>>
                                    <? endif; ?>

                                    <? $weekday = strftime('%a', $aday); ?>

                                    <span class="yday">
                                        <? $hday = holiday($aday); ?>
                                        <? if ($hday['col'] == '1') : ?>
                                            <? if (date('w', $aday) == '0') : ?>
                                                <a style="font-weight:bold;" class="sday" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                                <? $count++; ?>
                                            <? else : ?>
                                                <a style="font-weight:bold;" class="day" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                            <? endif; ?>
                                        <? elseif ($hday['col'] == '2' || $hday['col'] == '3') : ?>
                                            <? if (date('w', $aday) == '0') : ?>
                                                <a style="font-weight:bold;" class="sday" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                                <? $count++; ?>
                                            <? else : ?>
                                                <a style="font-weight:bold;" class="hday" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                            <? endif; ?>
                                        <? else : ?>
                                            <? if (date('w', $aday) == '0') : ?>
                                                <a style="font-weight:bold;" class="sday" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                                <? $count++; ?>
                                            <? else : ?>
                                                <a style="font-weight:bold;" class="day" href="<?= $controller->url_for('calendar/single/day', ['atime' => $aday]) ?>"><?= $i ?></a> <?= $weekday; ?>
                                            <? endif; ?>
                                        <? endif; ?>
                                    </span>

                                    <? if (isset($count_list[$iday]) && count($count_list[$iday])) : ?>
                                        <? $event_count_txt = sprintf(ngettext('1 Termin', '%s Termine', count($count_list[$iday])), count($count_list[$iday])) ?>
                                                </td>
                                                <td<?= $day_class ?> align="right">
                                                    <?= Icon::create('date', 'clickable', ['title' => $event_count_txt])->asImg(16, ["alt" => $event_count_txt]); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    <? endif; ?>

                                </td>

                            <? else : ?>
                                <td class="weekday"> </td>
                            <? endif; ?>

                        <? endfor; ?>
                    </tr>
                <? endfor; ?>
            </tbody>

            <tfoot>
                <tr>
                    <? $ts_month = 0; ?>
                    <? for ($i = 1; $i < 13; $i++) : ?>
                        <? $ts_month += ( $days_per_month[$i] - 1) * 86400; ?>
                        <th align="center" width="8%">
                            <a class="calhead" href="<?= $controller->url_for('calendar/single/month', ['atime' => $calendar->getStart() + $ts_month]) ?>">
                                <b><?= strftime('%B', $ts_month); ?></b>
                            </a>
                        </th>
                    <? endfor; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
