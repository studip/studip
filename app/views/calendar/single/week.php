<?
// add skip link
SkipLinks::addIndex(_('Wochenansicht'), 'main_content', 100);
$at = date('G', $atime);
if ($at >= $settings['start']
    && $at <= $settings['end'] || !$atime) {
    $start = $settings['start'];
    $end = $settings['end'];
} elseif ($at < $settings['start']) {
    $start = 0;
    $end = $settings['start'] + 2;
} else {
    $start = $settings['end'] - 2;
    $end = 23;
}
$tab_arr = [];
$max_columns = 0;
$week_type = $settings['type_week'] == 'SHORT' ? 5 : 7;
$rows = ($end - $start + 1) * 3600 / $settings['step_week'];

for ($i = 0; $i < $week_type; $i++) {
    $tab_arr[$i] = $calendars[$i]->createEventMatrix($start * 3600, $end * 3600, $settings['step_week']);
    if ($tab_arr[$i]['max_cols']) {
        $max_columns += ($tab_arr[$i]['max_cols'] + 1);
    } else {
        $max_columns++;
    }
}

$rowspan = ceil(3600 / $settings['step_week']);
$height = ' height="20"';

if ($rowspan > 1) {
    $colspan_1 = ' colspan="2"';
    $colspan_2 = $max_columns + 4;
    $width_daycols = 100 - (4 + $week_type) * 0.1;
} else {
    $colspan_1 = '';
    $colspan_2 = $max_columns + 2;
    $width_daycols = 100 - (2 + $week_type) * 0.1;
}
?>

<nav class="calendar-nav" style="vertical-align: middle">
    <span style="white-space: nowrap;">
        <a href="<?= $controller->url_for('calendar/single/week', ['atime' => strtotime('-1 week', $atime)]) ?>">
            <?= Icon::create('arr_1left', 'clickable', ['title' => _('Eine Woche zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
            <span class="hidden-tiny-down"><?= sprintf(_('%u. Woche'), strftime('%V', strtotime('-1 week', $atime))) ?></span>
        </a>
    </span>

    <?
    $calType = 'week';
    $calLabel = $this->render_partial('calendar/single/_calhead_label_week', compact('week_type'));
    ?>

    <?= $this->render_partial('calendar/single/_calhead', compact('calendar', 'atime', 'calType', 'calLabel')) ?>

    <span style="white-space: nowrap; text-align: right;">
        <a href="<?= $controller->url_for('calendar/single/week', ['atime' => strtotime('+1 week', $atime)]) ?>">
            <span class="hidden-tiny-down"><?= sprintf(_('%u. Woche'), strftime('%V', strtotime('+1 week', $atime))) ?></span>
            <?= Icon::create('arr_1right', 'clickable', ['title' => _('Eine Woche vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
        </a>
    </span>
</nav>

<table id="main_content" class="calendar-week">
    <colgroup>
        <col style="max-width: 1.5em; width: 1.5em;">
        <? if ($rowspan > 1) : ?>
            <col style="max-width: 1.5em; width: 1.5em;">
        <? endif; ?>
        <? for ($i = 0; $i < $week_type; $i++) : ?>
            <? if ($tab_arr[$i]['max_cols'] > 0) : ?>
                <? $event_cols = $tab_arr[$i]['max_cols'] ?: 1; ?>
                <col span="<?= $event_cols ?>" style="width: <?= 100 / $week_type / $event_cols ?>%">
                <col style="max-width: 0.9em; width: 0.9em;">
            <? else : ?>
                <col style="width: <?= 100 / $week_type ?>%">
            <? endif; ?>
        <? endfor; ?>
        <col class="hidden-tiny-down" style="max-width: 1.5em; width: 1.5em;">
        <? if ($rowspan > 1) : ?>
            <col class="hidden-tiny-down" style="max-width: 1.5em; width: 1.5em;">
        <? endif; ?>
    </colgroup>
    <thead>
        <tr>
            <td style="text-align: center; white-space: nowrap;" <?= $colspan_1 ?>>
                <? if ($start > 0) : ?>
                    <a href="<?= $controller->url_for('calendar/single/week', ['atime' => mktime($start - 1, 0, 0, date('n', $atime), date('j', $atime), date('Y', $atime))]) ?>">
                        <?= Icon::create('arr_1up', 'clickable', ['title' => _('Früher')])->asImg() ?>
                    </a>
                <? endif ?>
            </td>
            <? // weekday and date as title for each column ?>
            <? for ($i = 0; $i < $week_type; $i++) : ?>
                <td style="text-align:center; font-weight:bold;"<?= ($tab_arr[$i]['max_cols'] > 0 ? ' colspan="' . ($tab_arr[$i]['max_cols'] + 1) . '"' : '' ) ?>>
                    <a class="calhead" href="<?= $controller->url_for('calendar/single/day', ['atime' => $calendars[$i]->getStart()]) ?>">
                        <span class="hidden-tiny-down"><?= strftime('%a', $calendars[$i]->getStart()) ?></span> <?= date('d', $calendars[$i]->getStart()) ?>
                    </a>
                    <? if ($holiday = holiday($calendars[$i]->getStart())) : ?>
                        <div class="hidden-tiny-down" style="font-size:9pt; color:#bbb; height:auto; overflow:visible; font-weight:bold;"><?= $holiday['name'] ?></div>
                    <? endif ?>
                </td>
            <? endfor ?>
            <td style="text-align: center; white-space: nowrap;" <?= $colspan_1 ?>>
                <? if ($start > 0) : ?>
                    <a href="<?= $controller->url_for('calendar/single/week', ['atime' => mktime($start - 1, 0, 0, date('n', $calendars[0]->getStart()), date('j', $calendars[0]->getStart()), date('Y', $calendars[0]->getStart()))]) ?>">
                        <?= Icon::create('arr_1up', 'clickable', ['title' => _('Früher')])->asImg() ?>
                    </a>
                <? endif ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <? // Zeile mit Tagesterminen ausgeben ?>
            <td class="precol1w"<?= $colspan_1 ?> height="20">
                <?= _("Tag") ?>
            </td>
            <? for ($i = 0; $i < $week_type; $i++) : ?>
                <?
                if (date('Ymd', $calendars[$i]->getStart()) == date('Ymd')) {
                    $class_cell = 'celltoday';
                } else {
                    $class_cell = '';
                }
                ?>
                <?= $this->render_partial('calendar/single/_day_dayevents', ['em' => $tab_arr[$i], 'calendar' => $calendars[$i], 'class_cell' => $class_cell]) ?>
            <? endfor ?>
            <td class="precol1w"<?= $colspan_1 ?>>
                <?= _('Tag') ?>
            </td>
        </tr>
        <? $j = $start ?>
        <? for ($i = 0; $i < $rows; $i++) : ?>
            <tr>
                <? if ($i % $rowspan == 0) : ?>
                    <? if ($rowspan == 1) : ?>
                        <td class="precol1w"<?= $height ?>><?= $j ?></td>
                    <?  else : ?>
                        <td class="precol1w" rowspan="<?= $rowspan ?>"><?= $j ?></td>
                    <? endif ?>
                <? endif ?>
                <? if ($rowspan > 1) : ?>
                    <? $minutes = (60 / $rowspan) * ($i % $rowspan); ?>
                    <? if ($minutes == 0) : ?>
                        <td class="precol2w"<?= $height ?>>00</td>
                    <? else : ?>
                        <td class="precol2w"<?= $height ?>><?= $minutes ?></td>
                    <? endif ?>
                <? endif ?>
                <? for ($y = 0; $y < $week_type; $y++) : ?>
                    <?
                    if (date('Ymd', $calendars[$y]->getStart()) == date('Ymd')) {
                        $class_cell = 'celltoday';
                    } else {
                        $class_cell = '';
                    }
                    ?>
                    <?= $this->render_partial('calendar/single/_day_cell', ['calendar' => $calendars[$y], 'em' => $tab_arr[$y], 'row' => $i, 'start' => $start * 3600, 'i' => $i + ($start * 3600 / $settings['step_week']), 'step' => $settings['step_week'], 'class_cell' => $class_cell]); ?>
                <? endfor ?>
                <? if ($rowspan > 1) : ?>
                    <? if ($minutes == 0) : ?>
                        <td class="precol2w"<?= $height ?>>00</td>
                    <? else : ?>
                        <td class="precol2w"<?= $height ?>><?= $minutes ?></td>
                    <? endif ?>
                <? endif ?>
                <? if (($i + 2) % $rowspan == 0) : ?>
                    <? if ($rowspan == 1) : ?>
                        <td class="precol1w"<?= $height ?>><?= $j ?></td>
                    <?  else : ?>
                        <td class="precol1w" rowspan="<?= $rowspan ?>"><?= $j ?></td>
                    <? endif ?>
                    <? $j = $j + ceil($settings['step_week'] / 3600); ?>
                <? endif ?>
            </tr>
        <? endfor ?>
    </tbody>
    <tfoot>
        <tr>
            <td<?= $colspan_1 ?> style="text-align:center;">
                <? if ($end < 23) : ?>
                    <a href="<?= $controller->url_for('calendar/single/week', ['atime' => mktime($end + 1, 0, 0, date('n', $calendars[0]->getStart()), date('j', $calendars[0]->getStart()), date('Y', $calendars[0]->getStart()))]) ?>">
                        <?= Icon::create('arr_1down', 'clickable', ['title' => _('Später')])->asImg() ?>
                    </a>
                <? endif ?>
            </td>
            <td colspan="<?= $max_columns ?>">&nbsp;</td>
            <td<?= $colspan_1 ?> style="text-align:center;">
                <? if ($end < 23) : ?>
                    <a href="<?= $controller->url_for('calendar/single/week', ['atime' => mktime($end + 1, 0, 0, date('n', $calendars[0]->getStart()), date('j', $calendars[0]->getStart()), date('Y', $calendars[0]->getStart()))]) ?>">
                        <?= Icon::create('arr_1down', 'clickable', ['title' => _('Später')])->asImg() ?>
                    </a>
                <? endif ?>
            </td>
        </tr>
    </tfoot>
</table>
