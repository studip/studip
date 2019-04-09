<?php
# Lifter010: TODO

if (!$calendar_view || !($calendar_view instanceof CalendarView)) {
    throw new Exception('You need to pass a variable named $calendar_view, which holds an instance of CalendarView, to this template ('. __FILE__ .')!');
}

list($start_hour, $end_hour) = $calendar_view->getRange();

$cell_height = $calendar_view->getHeight() + 2;
$cell_steps = $cell_height / 60;
?>

<script>
  STUDIP.Calendar.cell_height = <?= $cell_height ?>;
  STUDIP.Calendar.the_entry_content = '<?= str_replace("\n", '', $this->render_partial('calendar/entries/empty_entry')) ?>';
  STUDIP.Calendar.start_hour = <?= $start_hour ?>;

  <? if ($js_function = $calendar_view->getInsertFunction()) : ?>
  jQuery(function() {

    jQuery('[id^=calendar_view_<?= $view_id ?>_column_] div.schedule_entry').bind('mousedown', function(event) {
        STUDIP.Calendar.click_in_progress = true;
    });

    jQuery('[id^=calendar_view_<?= $view_id ?>_column_]').bind('mousedown', function(event) {
        if (STUDIP.Calendar.click_in_progress) return;

        var column_id = this.id.substr(this.id.lastIndexOf("_")+1);
        
        STUDIP.Calendar.click_start_hour = Math.floor(((event.pageY - Math.ceil(jQuery(this).offset().top)) - 2)
            / STUDIP.Calendar.cell_height) + STUDIP.Calendar.start_hour;

        STUDIP.Calendar.click_entry = jQuery(STUDIP.Calendar.the_entry_content);
        jQuery("span.empty_entry_start", STUDIP.Calendar.click_entry).text(STUDIP.Calendar.click_start_hour);
        jQuery("span.empty_entry_end", STUDIP.Calendar.click_entry).text(STUDIP.Calendar.click_start_hour + 1);
        jQuery(STUDIP.Calendar.click_entry).css("top", Math.floor((STUDIP.Calendar.click_start_hour - STUDIP.Calendar.start_hour) * STUDIP.Calendar.cell_height - 2) + "px")
            .css("height", STUDIP.Calendar.cell_height + "px")
            .css("display", "none")
            .appendTo('#calendar_view_<?= $view_id ?>_column_' + column_id)
            .show();
    });

    jQuery('[id^=calendar_view_<?= $view_id ?>_column_]').bind('mousemove', function(event) {
        if (STUDIP.Calendar.click_in_progress) return;

        if (STUDIP.Calendar.click_entry) {
            var end_stunde = Math.max(Math.floor(((event.pageY - Math.ceil(jQuery(this).offset().top)) - 2)
                / STUDIP.Calendar.cell_height) + STUDIP.Calendar.start_hour, STUDIP.Calendar.click_start_hour);

            jQuery("span.empty_entry_end", STUDIP.Calendar.click_entry).text(end_stunde + 1);
            jQuery(STUDIP.Calendar.click_entry).css('height', (STUDIP.Calendar.cell_height * ((end_stunde + 1) - STUDIP.Calendar.click_start_hour) - 2) + 'px');
            window.getSelection().removeAllRanges();
        }
    });

    jQuery('[id^=calendar_view_<?= $view_id ?>_column_]').bind('mouseup', function(event) {
        if (STUDIP.Calendar.click_in_progress) return;

        var column_id = this.id.substr(this.id.lastIndexOf("_")+1);
        var end_stunde = Math.max(Math.floor(((event.pageY - Math.ceil(jQuery(this).offset().top)) - 2)
            / STUDIP.Calendar.cell_height) + STUDIP.Calendar.start_hour, STUDIP.Calendar.click_start_hour);

        var func = <?= $js_function ?>;

        jQuery("span.empty_entry_end", STUDIP.Calendar.click_entry).text(end_stunde + 1);
        jQuery(STUDIP.Calendar.click_entry).css('height', (STUDIP.Calendar.cell_height * ((end_stunde + 1) - STUDIP.Calendar.click_start_hour) - 2) + 'px');

        func(STUDIP.Calendar.click_entry, column_id, STUDIP.Calendar.click_start_hour, end_stunde + 1);

        STUDIP.Calendar.click_entry = null;
        STUDIP.Calendar.click_start_hour = null;
    });
  });
  <? endif ?>
</script>

<!-- the view -->
<div id="schedule">

<table id="schedule_data" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td></td>
            <? foreach ($calendar_view->getColumns() as $column) : ?>
            <td>
            <? if ($column->getURL()): ?>
                <a href="<?= URLHelper::getLink($column->getURL()) ?>">
            <? endif; ?>
                    <?= htmlReady($column->getTitle()) ?>
            <? if ($column->getURL()): ?>
                </a>
            <? endif; ?>
            </td>
            <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
            </td>
            <td colspan="<?= sizeof($calendar_view->getColumns()) ?>" style="padding: 0px">
                <!-- the lines separating the hours and half-hours -->
                <div style="position: relative">
                    <div style="position: absolute; width: 100%;">
                        <? for ($i = $start_hour; $i <= $end_hour; $i++) : ?>
                        <div id="marker_<?= $i ?>" class="schedule_marker"></div>
                        <? endfor; ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <!-- the hours for the time-table -->
                <? for ($i = $start_hour; $i <= $end_hour; $i++) : ?>
                <div class="schedule_hours"><?= ($i < 10) ? '0'.$i : $i ?>:00</div>
                <? endfor; ?>
            </td>
            <? foreach ($calendar_view->getColumns() as $column) : ?>
            <td style="vertical-align: top">
                <!-- the days with the date for the timetable -->
                <div id="calendar_view_<?= $view_id ?>_column_<?= $column->getId() ?>" class="schedule_day" style="overflow: hidden">
                    <? $groups = $column->getGroupedEntries();
                    if (!empty($groups)) :
                    $width = floor( 98 / sizeof($groups));
                    $col = 0;
                    foreach ($groups as $grouped_entries) :

                        foreach ($grouped_entries as $entry) :
                            // if we have a grouped entry, use start- and end-time of any entry, the are all the same
                            $calc_entry = $calendar_view->isGrouped() ? $entry[0] : $entry;

                            // move the box up, if the user has set a later start-hour for his schedule
                            $calc_entry['end'] -= $start_hour * 100;

                            // if the start is out of range of the displayed hours, clip the box
                            if ($calc_entry['start'] < $start_hour * 100) {
                                $calc_entry['start'] = 0;
                            } else {
                                $calc_entry['start'] -= $start_hour * 100;
                            }

                            // calculate the position and height of the entry
                            $top = floor($cell_height * floor($calc_entry['start'] / 100)
                                + $cell_steps * floor($calc_entry['start'] % 100));
                            $bottom = floor($cell_height * floor($calc_entry['end'] / 100)
                                + $cell_steps * floor($calc_entry['end'] % 100)) - 1;

                            // how many concurring entries has this entry?
                            $max = 0;
                            $current_matrix = $column->getMatrix();
                            for ($i = ($calc_entry['start'] + 1 + ($start_hour * 100)); $i < $calc_entry['end'] + ($start_hour * 100); $i++) {
                                $max = max($max, $current_matrix[$i]);
                            }

                            // set height and width
                            $height = $bottom - $top;

                            $this->top    = $top;
                            $this->width  = $width;
                            $this->height = $height;
                            $this->entry  = $entry;
                            $this->col    = $col;

                            // if we have no concurring entries set the maximum useful width
                            if ($max == 1) $this->width = '98';

                            if ($calendar_view->isGrouped()) {
                                echo $this->render_partial('calendar/entries/grouped_entry', ['day' => $column->getId()]);
                            } else {
                                echo $this->render_partial('calendar/entries/entry');
                            }

                        endforeach; /* cycle thrugh entries  */
                        $col++;

                    endforeach; /* cycle through columns */
                endif; ?>
                </div>
            </td>
            <? endforeach; /* cycle through days*/ ?>
        </tr>
    </tbody>
</table>
</div>
