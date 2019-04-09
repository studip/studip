<?
$width1 = 0;
$width2 = 0;
$cols = ceil(($settings['end'] - $settings['start'] + 1) * 3600 / $settings['step_week_group']) + 1;
$start = $settings['start'] * 3600;
$end = ($settings['end'] + 1) * 3600;
$wlength = count($calendars[0]) - 1;
// add skip link
SkipLinks::addIndex(_('Wochenansicht'), 'main_content', 100);
?>
<table style="width: 100%">
    <tr>
        <td colspan="<?= $colspan_2 ?>" style="vertical-align: middle; text-align: center;">
            <div style="text-align: left; width: 25%; display: inline-block; white-space: nowrap;">
                <a href="<?= $controller->url_for('calendar/group/week', ['atime' => mktime(12, 0, 0, date('n', $atime), date('j', $atime) - 7, date('Y', $atime))]) ?>">
                    <?= Icon::create('arr_1left', 'clickable', ['title' => _('Eine Woche zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
                    <?= sprintf(_('%u. Woche'), strftime('%V'), strtotime('-1 week', $atime)) ?>
                </a>
            </div>
            <div style="display: inline-block; text-align: center;" class="calhead">
                <? printf(_("%s. Woche vom %s bis %s"), strftime("%V", $calendars[0][0]->getStart()), strftime("%x", $calendars[0][0]->getStart()), strftime("%x", $calendars[0][$wlength]->getEnd())) ?>
            </div>
            <div style="width: 25%; text-align: right; display: inline-block; white-space: nowrap;">
                <a href="<?= $controller->url_for('calendar/group/week', ['atime' => mktime(12, 0, 0, date('n', $atime), date('j', $atime) + 7, date('Y', $atime))]) ?>">
                    <?= sprintf(_('%u. Woche'), strftime('%V', strtotime('+1 week', $atime))) ?>
                    <?= Icon::create('arr_1right', 'clickable', ['title' => _('Eine Woche vor')])->asImg(16, ["style" => 'vertical-align: text-top;']) ?>
                </a>
            </div>
        </td>
    </tr>
</table>
<div style="overflow:auto; width:100%;">
    <table id="main_content" style="width: 100%;">
        <thead>
            <tr>
                <td width="<?= $width2 ?>%" class="precol1w"> </td>
                <? $time = $calendars[0][0]->getStart(); ?>
                <? for ($i = 0; $i <= $wlength; $i++) : ?>
                    <td colspan="<?= $cols ?>" style="text-align: center;" class="precol1w">
                        <a href="<?= $controller->url_for('calendar/group/day/' . $this->range_id, ['atime' => $time]) ?>" class="calhead">
                            <?= strftime('%a', $time) . ' ' . date('d', $time) ?>
                        </a>
                    </td>
                    <? $time += 86400; ?>
                <? endfor ?>
            </tr>
            <tr>
                <td width="<?= $width2 ?>%" class="precol1w" style="text-align: center;">
                    <?= _('Mitglied') ?>
                </td>
                <? foreach ($calendars[0] as $day) : ?>
                    <td class="precol1w" style="text-align: center; width: <?= $width1 ?>%;">
                        <a data-dialog="size=auto" title="<?= strftime(_('Neuer Tagestermin am %x'), $day->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $day->getStart(), 'isdayevent' => '1']) ?>">
                            <?= Icon::create('schedule', 'clickable')->asImg() ?>
                        </a>
                    </td>
                    <? for ($i = $day->getStart() + $start; $i < $day->getStart() + $end; $i += 3600 * ceil($settings['step_week_group'] / 3600)) : ?>
                        <td colspan="<?= ceil(3600 / $settings['step_week_group']) ?>" class="precol2w" style="text-align: center;">
                            <a data-dialog="size=auto" title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i]) ?>" class="calhead">
                                <?= (date('G', $i) < 10 ? '&nbsp;' . date('G', $i) . '&nbsp;' : date('G', $i)) ?>
                            </a>
                        </td>
                    <? endfor ?>
                <? endforeach ?>
            </tr>
        </thead>
        <tbody>
        <? foreach ($calendars as $user_calendar) : ?>
            <tr>
                <td style="width: <?= $width2 ?>%; white-space: nowrap;" class="month">
                    <a class="calhead" href="<?= $controller->url_for('calendar/single/week/' . $user_calendar[0]->getRangeId(), ['atime' => $atime]) ?>">
                        <?= htmlReady($user_calendar[0]->havePermission(Calendar::PERMISSION_OWN) ? _('Eigener Kalender') : get_fullname($user_calendar[0]->getRangeId(), 'no_title_short')) ?>
                    </a>
                </td>
                <? $k = 1; ?>
                <? foreach ($user_calendar as $day) : ?>
                    <?
                    if (date('Ymd', $day->getStart()) == date('Ymd')) {
                        $css_class = 'celltoday';
                    } else {
                        if ($k % 2) {
                            $css_class = 'lightmonth';
                        } else {
                            $css_class = 'month';
                        }
                    }
                    $k++;
                    $adapted = $day->adapt_events($start, $end, $settings['step_week_group']);

                    // display day events
                    $js_events = []; ?>
                    <? for ($i = 0; $i < count($adapted['day_events']); $i++) : ?>
                        <? $js_events[] = $day->events[$adapted['day_map'][$i]]; ?>
                    <? endfor; ?>
                    <? if (count($js_events) && $day->havePermission(Calendar::PERMISSION_WRITABLE)) : ?>
                        <td <?= count($js_events) ? 'data-tooltip ' : '' ?>style="text-align: right; width: <?= $width1 ?>%" class="calendar-day-edit <?= $css_class ?><?= count($js_events) ? ' calendar-group-events' : '' ?>">
                        <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $day, 'events' => $js_events]) ?>
                        <a title="<?= strftime(_('Neuer Tagestermin am %x'), $day->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $day->getStart(), 'isdayevent' => '1', 'user_id' => $day->getRangeId()]) ?>">+</a>
                    <? else : ?>
                        <td <?= count($js_events) ? 'data-tooltip ' : '' ?>style="text-align: right; width: <?= $width1 ?>%" class="<?= $css_class ?><?= count($js_events) ? ' calendar-group-events' : '' ?>">
                        <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $day, 'events' => $js_events]) ?>
                    <? endif;?>
                    </td>

                    <? for ($i = $start + $day->getStart(); $i < $end + $day->getStart(); $i += $settings['step_week_group']) : ?>
                        <? $js_events = []; ?>
                        <? for ($j = 0; $j < count($adapted['events']); $j++) : ?>
                            <? if (($adapted['events'][$j]->getStart() <= $i && $adapted['events'][$j]->getEnd() > $i) || ($adapted['events'][$j]->getStart() > $i && $adapted['events'][$j]->getStart() < $i + $settings['step_week_group'])) : ?>
                                <? $js_events[] = $day->events[$adapted['map'][$j]]; ?>
                            <? endif ?>
                        <? endfor ?>
                        <? if ($day->havePermission(Calendar::PERMISSION_WRITABLE)) : ?>
                            <td <?= count($js_events) ? 'data-tooltip ' : '' ?>style="text-align: right; width: <?= $width1 ?>%" class="calendar-day-edit <?= $css_class ?><?= count($js_events) ? ' calendar-group-events' : '' ?>">
                                <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $day, 'events' => $js_events]) ?>
                                <a title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i, 'user_id' => $day->getRangeId()]) ?>">+</a>
                        <? else : ?>
                            <td <?= count($js_events) ? 'data-tooltip ' : '' ?>style="text-align: right; width: <?= $width1 ?>%" class="<?= $css_class ?><?= count($js_events) ? ' calendar-group-events' : '' ?>">
                                <?= $this->render_partial('calendar/group/_tooltip', ['calendar' => $day, 'events' => $js_events]) ?>
                        <? endif; ?>
                        </td>
                    <? endfor; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td style="width:<?= $width2 ?>%; text-align: center;" class="precol1"> </td>
                <? foreach ($calendars[0] as $day) : ?>
                    <td style="width:<?= $width1 ?>%; text-align: center;" class="precol1w">
                        <a data-dialog="size=auto" title="<?= strftime(_('Neuer Tagestermin am %x'), $day->getStart()) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $atime, 'isdayevent' => '1']) ?>">
                            <?= Icon::create('schedule', 'clickable')->asImg() ?>
                        </a>
                    </td>
                    <? for ($i = $day->getStart() + $start; $i < $day->getStart() + $end; $i += 3600 * ceil($settings['step_week_group'] / 3600)) : ?>
                        <td colspan="<?= ceil(3600 / $settings['step_week_group']) ?>" class="precol2w" style="text-align: center;">
                            <a data-dialog="size=auto" title="<?= strftime(_('Neuer Termin um %R Uhr'), $i) ?>" href="<?= $controller->url_for('calendar/group/edit/' . $range_id, ['atime' => $i]) ?>" class="calhead">
                                <?= (date('G', $i) < 10 ? '&nbsp;' . date('G', $i) . '&nbsp;' : date('G', $i)) ?>
                            </a>
                        </td>
                    <? endfor; ?>
                <? endforeach; ?>
            </tr>
        </tfoot>
    </table>
</div>
