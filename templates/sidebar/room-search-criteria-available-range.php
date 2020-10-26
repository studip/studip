<?php
/**
 * Template documentation:
 *
 * A special time range search criteria named $criteria must be passed
 * to this template and must contain the following indexes:
 * - $range: The time range search criteria.
 * - $semester: The semester selector search criteria.
 * - $day_of_week: The day of week selector search criteria.
 *
 * This criteria has the following structure:
 * [
 *     'name' => The criteria's internal name.
 *     'title' => The title of the criteria.
 *     'optional' => Whether this criteria is optional (true) or not.
 *     'enabled' => Whether this criteria is enabled (true) or not.
 *     'semester' => Data for the semester search criteria.
 *         This key must contain an array with the following structure:
 *         [
 *             'objects' => Semester SORM objects.
 *             'value' => The ID of the selected semester.
 *         ]
 *     'range' => Data for the time range search criteria.
 *         This key must contain the following array:
 *         [
 *             'begin' => The DateTime object representing the begin time.
 *             'end' => The DateTime object representing the end time.
 *         ]
 *     'day_of_week' => Data for the day of week search criteria.
 *         This key must contain the following array:
 *         [
 *             'options' => An array with the days of the week, where the
 *                 array keys are the day numbers and the values are the
 *                 displayed names of the days.
 *             'value' => The day number of the selected day.
 *         ]
 * ]
 */
?>
<li class="item">
    <input type="checkbox" class="special-item-switch studip-checkbox" value="1"
           title="<?= _('Kriterium ausgewählt'); ?>" id="cb_<?= htmlReady($criteria['name']); ?>"
           name="<?= htmlReady($criteria['name'] . '_enabled')?>"
           <?= $criteria['enabled'] ? 'checked="checked"' : ''?>>
    <label class="undecorated" for="cb_<?= htmlReady($criteria['name']) ?>">
        <span><?= htmlReady($criteria['title']) ?></span>
        <? if ($criteria['semester']): ?>
            <div><?= _('Semester') ?></div>
            <select name="<?= htmlReady($criteria['name'] . '_semester_id') ?>">
                <option value=""><?= _('Bitte wählen') ?></option>
                <? if (is_array($criteria['semester']['objects'])): ?>
                    <? foreach ($criteria['semester']['objects'] as $semester): ?>
                        <option value="<?= htmlReady($semester->id) ?>"
                                <?= ($semester->id == $criteria['semester']['value']
                                   ? 'selected="selected"'
                                   : '') ?>
                                data-begin="<?= htmlReady($semester->vorles_beginn) ?>"
                                data-end="<?= htmlReady($semester->vorles_ende) ?>">
                            <?= htmlReady($semester->name) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            </select>
        <? endif ?>
        <? if ($criteria['range']): ?>
            <div><?= _('Zeitbereich') ?></div>
            <div class="range-input-container">
                <input type="text"
                       id="<?= htmlReady($criteria['name']) ?>_begin_date"
                       name="<?= htmlReady($criteria['name']) ?>_begin_date"
                       value="<?= htmlReady($criteria['range']['begin']->format('d.m.Y')) ?>"
                       class="has-date-picker">
                <input type="text" data-time="yes"
                       data-time-picker='{"<":"#<?=htmlReady($criteria['name']) ?>_end_time"}'
                       id="<?= htmlReady($criteria['name']) ?>_begin_time"
                       name="<?= htmlReady($criteria['name']) ?>_begin_time"
                       value="<?= htmlReady($criteria['range']['begin']->format('H:i')) ?>"
                       class="has-time-picker">
                <?= _('Uhr') ?>
                <input type="text"
                       data-date-picker='{">=":"#<?=htmlReady($criteria['name']) ?>_begin_date"}'
                       id="<?= htmlReady($criteria['name']) ?>_end_date"
                       name="<?= htmlReady($criteria['name']) ?>_end_date"
                       value="<?= htmlReady($criteria['range']['end']->format('d.m.Y')) ?>"
                       class="has-date-picker">
                <input type="text" data-time="yes"
                       data-time-picker='{">":"#<?=htmlReady($criteria['name']) ?>_begin_time"}'
                       id="<?= htmlReady($criteria['name']) ?>_end_time"
                       name="<?= htmlReady($criteria['name']) ?>_end_time"
                       value="<?= htmlReady($criteria['range']['end']->format('H:i')) ?>"
                       class="has-time-picker">
                    <?= _('Uhr') ?>
            </div>
        <? endif ?>
        <? if ($criteria['day_of_week']): ?>
            <div><?= _('Wochentag') ?></div>
            <select name="<?= htmlReady($criteria['name'] . '_day_of_week') ?>">
                <? if (is_array($criteria['day_of_week']['options'])): ?>
                    <option value=""><?= _('Bitte wählen') ?></option>
                    <? foreach ($criteria['day_of_week']['options'] as $value => $title): ?>
                        <option value="<?= htmlReady($value) ?>"
                                <?= ($value == $day_of_week['value']
                                   ? 'selected="selected"'
                                   : '') ?>>
                            <?= htmlReady($title) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            </select>
        <? endif ?>
    </label>
</li>
<script type="text/javascript">
jQuery(function ($) {
    $("#<?= htmlReady($criteria['name']) ?>_begin_date").on('change', function(){
        var selected_beginn_val = $(this).val().split('.');
        var selected_end_val = $("#<?= htmlReady($criteria['name']) ?>_end_date").val().split('.');
        if (selected_beginn_val.length < 3) {
            $(this).val(new Date().toLocaleDateString('de-DE'));
        } else if (selected_beginn_val[0] > 31 || selected_beginn_val[1] > 12 || selected_beginn_val[2] < 1970) {
            var today = new Date();
            if (selected_beginn_val[2] < 1970) {
                $(this).val(selected_beginn_val[0] + '.' + selected_beginn_val[1] + '.' + today.getFullYear());
            } else if (selected_beginn_val[1] > 12) {
                $(this).val(selected_beginn_val[0] + '.' + (today.getMonth() + 1) + '.' + selected_beginn_val[2]);
            } else {
                $(this).val(today.getDate() + '.' + selected_beginn_val[1] + '.' + selected_beginn_val[2]);
            }
        }
        if (selected_beginn_val.length == 3 && selected_end_val.length == 3) {
            var selected_beginn_date = new Date(selected_beginn_val[2] + '-' + selected_beginn_val[1] + '-' +selected_beginn_val[0]);
            var selected_end_date = new Date(selected_end_val[2] + '-' + selected_end_val[1] + '-' +selected_end_val[0]);
            if (selected_beginn_date > selected_end_date) {
                $("#<?= htmlReady($criteria['name']) ?>_end_date").val($(this).val());
            }
        }
    });

    $("#<?= htmlReady($criteria['name']) ?>_end_date").on('change', function(){
        var selected_beginn_val = $(this).val().split('.');
        if (selected_beginn_val.length < 3) {
            $(this).val(new Date().toLocaleDateString('de-DE'));
        } else if (selected_beginn_val[0] > 31 || selected_beginn_val[1] > 12 || selected_beginn_val[2] < 1970) {
            var today = new Date();
            if (selected_beginn_val[2] < 1970) {
                $(this).val(selected_beginn_val[0] + '.' + selected_beginn_val[1] + '.' + today.getFullYear());
            } else if (selected_beginn_val[1] > 12) {
                $(this).val(selected_beginn_val[0] + '.' + (today.getMonth() + 1) + '.' + selected_beginn_val[2]);
            } else {
                $(this).val(today.getDate() + '.' + selected_beginn_val[1] + '.' + selected_beginn_val[2]);
            }
        }
        $("#<?= htmlReady($criteria['name']) ?>_begin_date").trigger('change');
    });

    $("#<?= htmlReady($criteria['name']) ?>_begin_time").on('change', function(){
        var selected_beginn_val = $(this).val().split(':');
        var selected_end_val = $("#<?= htmlReady($criteria['name']) ?>_end_time").val().split(':');
        if (selected_beginn_val.length < 2) {
            if (selected_beginn_val[0] == '') {
                $(this).val('00');
            }
            $(this).val($(this).val() + ':00');
        }
        if (selected_beginn_val.length == 2 && selected_end_val.length == 2) {
            var selected_beginn_date = new Date('1970-01-01T' + selected_beginn_val[0] + ':' + selected_beginn_val[1] + ':00+00:00');
            var selected_end_date = new Date('1970-01-01T' + selected_end_val[0] + ':' + selected_end_val[1] + ':00+00:00');
            if (selected_beginn_date > selected_end_date) {
                $("#<?= htmlReady($criteria['name']) ?>_end_time").val(selected_beginn_val[0] + ':' + (parseInt(selected_beginn_val[1]) + 30));
            }
        }
    });

    $("#<?= htmlReady($criteria['name']) ?>_end_time").on('change', function(){
        var selected_end_val = $(this).val().split(':');
        if (selected_end_val.length < 2) {
            if (selected_end_val[0] == '') {
                $(this).val('00');
            }
            $(this).val($(this).val() + ':00');
        }
        $("#<?= htmlReady($criteria['name']) ?>_begin_time").trigger('change');
    });

});
</script>
