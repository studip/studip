<?
if ($settings['step_day'] >= 3600) {
    $rowspan_precol = '';
} else {
    $rowspan_precol = ' rowspan="' . 3600 / $settings['step_day'] . '"';
}
?>
<tr>
    <td class="precol1w" <?= $rowspan_precol ? ' colspan="2"' : '' ?>><?= _('Tag') ?></td>
    <?= $this->render_partial('calendar/single/_day_dayevents', ['em' => $em]); ?>
</tr>
<? for ($i = $start / $settings['step_day']; $i < $end / $settings['step_day'] + 3600 / $settings['step_day']; $i++) : ?>
<? $row = $i - $start / $settings['step_day']; ?>
<tr>
    <? if (($i * $settings['step_day']) % 3600 == 0) : ?>
    <td class="precol1w" <?= $rowspan_precol ?>>
        <?= $i / (3600 / $settings['step_day']) ?>
    </td>
    <? endif ?>
    <? if ($settings['step_day'] % 3600 != 0) : ?>
    <? $minute = ($row % (3600 / $settings['step_day'])) * ($settings['step_day'] / 60); ?>
    <td class="precol2w" style="height: 20px; width: 1%; padding-right: 3px;">
        <?= $minute ? $minute : '00' ?>
    </td>
    <? endif ?>
    <?= $this->render_partial('calendar/single/_day_cell', ['events' => $calendar->events, 'start' => $start, 'em' => $em, 'row' => $row, 'i' => $i, 'step' => $settings['step_day']]); ?>
</tr>
<? endfor; ?>