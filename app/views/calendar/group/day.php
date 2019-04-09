<?
$step_day = $settings['step_day_group'];
$step = 3600 / $step_day;
// add one cell for day events
$cells = (($settings['end'] - $settings['start']) / (1 / $step)) + 2;
$width1 = floor(90 / $cells);
$width2 = 10 + (90 - $width1 * $cells);
$start = $settings['start'] * 3600;
$end = ($settings['end'] + 1) * 3600;
// add skip link
SkipLinks::addIndex(_('Tagesansicht'), 'main_content', 100);
?>
<table id="main_content" style="width:100%; table-layout:fixed;">
    <thead>
        <tr>
            <td style="text-align: center; width: 10%; height: 40px;">
                <div style="text-align: left; width: 20%; display: inline-block; white-space: nowrap;">
                    <a href="<?= $controller->url_for('calendar/group/day', ['atime' => $atime - 86400]) ?>">
                        <?= Icon::create('arr_1left', 'clickable', ['title' => _('Einen Tag zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                        <?= strftime(_('%x'), strtotime('-1 day', $calendars[0]->getStart())) ?>
                    </a>
                </div>
                <div class="calhead" style="width: 50%; display: inline-block;">
                    <?= strftime('%A, %e. %B %Y', $atime) ?>
                    <div style="text-align: center; font-size: 12pt; color: #bbb; height: auto; overflow: visible; font-weight: bold;"><? $hd = holiday($atime); echo $holiday['name']; ?></div>
                </div>
                <div style="text-align: right; width: 20%; display: inline-block; white-space: nowrap;">
                    <a href="<?= $controller->url_for('calendar/group/day', ['atime' => $atime + 86400]) ?>">
                        <?= strftime(_('%x'), strtotime('+1 day', $calendars[0]->getStart())) ?>
                        <?= Icon::create('arr_1right', 'clickable', ['title' => _('Einen Tag vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                    </a>
                </div>
            </td>
        </tr>
    </head>
    <tbody>
        <tr>
            <td>
                <div style="overflow:auto; width:100%;">
                <table style="width: 100%;">
                    <? $time = mktime(0, 0, 0, date('n', $atime), date('j', $atime), date('Y', $atime)); ?>
                    <? if ($step_day < 3600) : $colsp = ' colspan="' . $step . '"'; endif ?>
                    <tr>
                        <td class="precol1w" nowrap="nowrap" style="text-align: center; width: <?= $width2 ?>%;">
                            <?= _('Mitglied') ?>
                        </td>
                        <td class="precol1w" style="text-align: center; width: <?= $width1 ?>%">
                            <a data-dialog="size=auto" title="<?= strftime(_('Neuer Tagestermin am %x'), $calendars[0]->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $calendars[0]->getStart(), 'isdayevent' => '1']) ?>">
                                <?= Icon::create('schedule', 'clickable')->asImg() ?>
                            </a>
                        </td>
                        <? for ($i = $time + $start; $i < $time + $end; $i += $step_day) : ?>
                            <? if (!($i % 3600)) : ?>
                            <td<?= $colsp ?> class="precol1w" style="text-align: center;">
                                <a data-dialog="size=auto" title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i]) ?>" class="calhead">
                                    <?= date('G', $i) ?>
                                </a>
                            </td>
                            <? endif ?>
                        <? endfor ?>
                    </tr>
                    <? foreach ($calendars as $calendar) : ?>
                        <? $adapted = $calendar->adapt_events($start, $end, $settings['step_day_group']); ?>
                        <tr>
                            <td style="width: <?= $width2 ?>%; white-space: nowrap;" class="month">
                                <a class="calhead" href="<?= $controller->url_for('calendar/single/day/' . $calendar->getRangeId(), ['atime' => $atime,]); ?>">
                                    <?= htmlReady(($calendar->havePermission(Calendar::PERMISSION_OWN) ? _('Eigener Kalender') : get_fullname($calendar->getRangeId(), 'no_title_short'))) ?>
                                </a>
                            </td>
                            <? // display day events
                            $js_events = []; ?>
                            <? for ($i = 0; $i < sizeof($adapted['day_events']); $i++) :
                                $js_events[] = $calendar->events[$adapted['day_map'][$i]];
                            endfor; ?>
                            <? if ($calendar->havePermission(Calendar::PERMISSION_WRITABLE)) : ?>
                            <td <?= sizeof($js_events) ? 'data-tooltip ' : '' ?>style="width: <?= $width1 ?>%; text-align: right;" class="calendar-day-edit<?= sizeof($js_events) ? ' calendar-group-events' : ' lightmonth' ?>">
                                <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $calendar, 'events' => $js_events]) ?>
                                <a title="<?= strftime(_('Neuer Tagestermin am %x'), $calendar->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $calendar->getStart(), 'isdayevent' => '1', 'user_id' => $calendar->getRangeId()]) ?>">+</a>
                            <? else : ?>
                            <td <?= sizeof($js_events) ? 'data-tooltip ' : '' ?>style="width: <?= $width1 ?>%;" class="<?= sizeof($js_events) ? 'calendar-group-events' : 'lightmonth' ?>">
                                <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $calendar, 'events' => $js_events]) ?>
                            <? endif; ?>
                            </td>
                            <? $k = 0;
                            for ($i = $start + $calendar->getStart(); $i < $end + $calendar->getStart(); $i += $step_day) :
                                if (!($i % 3600)) {
                                    $k++;
                                }
                                $js_events = [];
                                ?>
                                <? for ($j = 0; $j < sizeof($adapted['events']); $j++) : ?>
                                    <? if (($adapted['events'][$j]->getStart() <= $i && $adapted['events'][$j]->getEnd() > $i) || ($adapted['events'][$j]->getStart() > $i && $adapted['events'][$j]->getStart() < $i + $step_day)) :
                                        $js_events[] = $calendar->events[$adapted['map'][$j]];
                                    endif; ?>
                                <? endfor; ?>
                                <? if ($calendar->havePermission(Calendar::PERMISSION_WRITABLE)) : ?>
                                <td <?= sizeof($js_events) ? 'data-tooltip ' : '' ?>style="width: <?= $width1 ?>%; text-align: right;" class="calendar-day-edit<?= sizeof($js_events) ? ' calendar-group-events' : ' lightmonth' ?>">
                                    <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $calendar, 'events' => $js_events]) ?>
                                    <a title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i, 'user_id' => $calendar->getRangeId()]) ?>">+</a>
                                <? else : ?>
                                <td <?= sizeof($js_events) ? 'data-tooltip ' : '' ?> style="width: <?= $width1 ?>%; text-align: right;" class="<?= sizeof($js_events) ? 'calendar-group-events' : 'lightmonth' ?>">
                                    <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $calendar, 'events' => $js_events]) ?>
                                <? endif; ?>
                            </td>
                            <? endfor ?>
                        </tr>
                    <? endforeach; ?>
                    <tr>
                        <td style="width: <?= $width2 ?>%;" class="precol1w"> </td>
                        <td width="<?= $width1 ?>%" class="precol1w" style="text-align: center;">
                            <a data-dialog="size=auto" title="<?= strftime(_('Neuer Tagestermin am %x'), $calendars[0]->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $calendars[0]->getStart(), 'isdayevent' => '1']) ?>">
                                <?= Icon::create('schedule', 'clickable')->asImg() ?>
                            </a>
                        </td>
                        <? for ($i = $time + $start; $i < $time + $end; $i += $step_day) : ?>
                            <? if (!($i % 3600)) : ?>
                            <td<?= $colsp ?> class="precol1w" style="text-align: center;">
                                <a data-dialog="size=auto" title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i]) ?>" class="calhead">
                                    <?= date('G', $i) ?>
                                </a>
                            </td>
                            <? endif ?>
                        <? endfor ?>
                    </tr>
                </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>
