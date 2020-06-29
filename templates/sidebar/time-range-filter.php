<form class="default" method="post" action="" data-autosubmit="filter_time_range">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <?= _('Dateien neuer als') ?>:
        <input type="text" name="begin" value="<?= htmlReady($begin) ?>"
               class="has-date-picker">
    </label>
    <label>
        <?= _('Dateien älter als') ?>:
        <input type="text" name="end" value="<?= htmlReady($end) ?>"
               class="has-date-picker submit-on-change">
    </label>
    <? if ($course_options) : ?>
        <label>
            <?= _('Veranstaltung') ?>:
            <select name="course_id">
                <option value=""><?= _('Bitte wählen') ?></option>
                <? foreach ($course_options as $course_id => $course_name) : ?>
                    <option value="<?= htmlReady($course_id) ?>"
                            <?= $course_id == $selected_course_id
                              ? 'selected' : '' ?>>
                        <?= htmlReady($course_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
    <? endif ?>
    <?= \Studip\Button::create(_('Übernehmen'), 'filter') ?>
</form>
