<?
$at = date('G', $atime);
if ($at >= $settings['start']
        && $at <= $settings['end'] || !$atime) {
    $start = $settings['start'] * 3600;
    $end = $settings['end'] * 3600;
} elseif ($at < $settings['start']) {
    $start = 0;
    $end = ($settings['start'] + 2) * 3600;
} else {
    $start = ($settings['end'] - 2) * 3600;
    $end = 23 * 3600;
}
$em = $calendar->createEventMatrix($start, $end, $settings['step_day']);
$max_columns = $em['max_cols'] ?: 1;
?>

<nav class="calendar-nav">
    <span style="white-space: nowrap;">
        <a href="<?= $controller->url_for('calendar/single/day', ['atime' => strtotime('-1 day', $atime)]) ?>">
            <?= Icon::create('arr_1left', 'clickable', ['title' => _('Einen Tag zurück')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
            <span class="hidden-tiny-down">
                <?= strftime(_('%x'), strtotime('-1 day', $calendar->getStart())) ?>
            </span>
        </a>
    </span>

    <?
    $calType = 'day';
    $calLabel = $this->render_partial('calendar/single/_calhead_label_day');
    ?>

    <?= $this->render_partial('calendar/single/_calhead', compact('calendar', 'atime', 'calType', 'calLabel')) ?>

    <span style="white-space: nowrap;">
        <a href="<?= $controller->url_for('calendar/single/day', ['atime' => strtotime('+1 day', $atime)]) ?>">
            <span class="hidden-tiny-down">
                <?= strftime(_('%x'), strtotime('+1 day', $calendar->getStart())) ?>
            </span>
            <?= Icon::create('arr_1right', 'clickable', ['title' => _('Einen Tag vor')])->asImg(16, ['style' => 'vertical-align: text-top;']) ?>
        </a>
    </span>
</nav>

<table class="calendar-day">
    <colgroup>
        <col style="max-width: 2em; width: 2em;">
        <? if ($settings['step_day'] < 3600) : ?>
        <col style="max-width: 2em; width: 2em;">
        <? $max_columns_head = $max_columns + 3 ?>
        <? else : ?>
        <? $max_columns_head = $max_columns + 2 ?>
        <? endif; ?>
        <col span="<?= $em['max_cols'] ?: '1' ?>" style="width: <?= 100 / ($em['max_cols'] ?: 1) ?>%">
        <col style="max-width: 0.8em; width: 0.8em;">
    </colgroup>
    <thead>
        <? if ($start > 0) : ?>
        <tr>
            <td align="center"<?= $settings['step_day'] < 3600 ? ' colspan="2"' : '' ?>>
                <a href="<?= $controller->url_for('calendar/single/day', ['atime' => ($atime - (date('G', $atime) * 3600 - $start + 3600))]) ?>">
                    <?= Icon::create('arr_1up', 'clickable', ['title' => _('Früher')])->asImg() ?>
                </a>
            </td>
            <td colspan="<?= $max_columns + 1 ?>">
            </td>
        </tr>
        <? endif; ?>
    </thead>
    <tbody>
        <?= $this->render_partial('calendar/single/_day_table', ['start' => $start, 'end' => $end, 'em' => $em]) ?>
    </tbody>
    <tfoot>
    <? if ($end / 3600 < 23) : ?>
        <tr>
            <td align="center"<?= $settings['step_day'] < 3600 ? ' colspan="2"' : '' ?>>
                <a href="<?= $controller->url_for('calendar/single/day', ['atime' => ($atime + $end - date('G', $atime) * 3600 + 3600)]) ?>">
                    <?= Icon::create('arr_1down', 'clickable', ['title' => _('Später')])->asImg() ?>
                </a>
            </td>
            <td colspan="<?= $max_columns + 1 ?>">
            </td>
        </tr>
    <? else : ?>
        <tr>
            <td>&nbsp;</td>
        </tr>
    <? endif ?>
    </tfoot>
</table>
