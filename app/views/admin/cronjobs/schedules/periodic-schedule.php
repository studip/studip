<?php
$cronify = function ($n) {
    if ($n === null) {
        return '*';
    }
    if ($n < 0) {
        return '*/' . abs($n);
    }
    return $n;
}
?>
<? if ($display === 'table-cells'): ?>
    <td><?= $cronify($minute) ?></td>
    <td><?= $cronify($hour) ?></td>
    <td><?= $cronify($day) ?></td>
    <td><?= $cronify($month) ?></td>
    <td><?= $cronify($day_of_week) ?></td>
<? else: ?>
<ul class="crontab">
    <li class="crontab-minute">
        <span class="label">Minute</span>
        <?= $cronify($minute) ?>
    </li>
    <li class="crontab-hour">
        <span class="label">Stunde</span>
        <?= $cronify($hour) ?>
    </li>
    <li class="crontab-day">
        <span class="label">Tag</span>
        <?= $cronify($day) ?>
    </li>
    <li class="crontab-month">
        <span class="label">Monat</span>
        <?= $cronify($month) ?>
    </li>
    <li class="crontab-day-of-week">
        <span class="label">Wochentag</span>
        <?= $cronify($day_of_week) ?>
    </li>
</ul>
<? endif; ?>
