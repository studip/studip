<? $i = 0 ?>
<? $html = '' ?>
<? $list_day = date('Ymd', $aday) ?>
<? foreach ($calendars as $calendar) : ?>
    <? if ($count_lists[$i][$list_day]) : ?>
        <? 
        $html .= '<div>'
                . sprintf(ngettext('%s hat 1 Termin', '%s hat %s Termine',
                        count($count_lists[$i][$list_day])),
                        $calendar->range_object->getFullname('no_title'),
                        count($count_lists[$i][$list_day]))
                . '</div>';
        ?>
    <? endif; ?>
    <? $i++ ?>
<? endforeach; ?>
<? if ($html) : ?>
<div class="calendar-tooltip tooltip-content">
    <?= $html ?>
</div>
<? endif; ?>
