<?php
$dates = CourseDate::findByRange_id($folder->range_id);
?>
<label>
    <?= _('Wählen sie einen zugehöriges Termin aus, der Titel wird automatisch aus dem Datum des Termins erzeugt.') ?>
    <select name="course_date_folder_termin_id">
    <? if (count($dates) === 0): ?>
        <option value="">
            <?= _('Es existiert kein Termin in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($dates as $one_date): ?>
        <option <?= @$date->id === $one_date->id ? 'selected' : '' ?> value="<?= htmlReady($one_date->id) ?>">
            <?= htmlReady(CourseDateFolder::formatDate($one_date)) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>
<label>
    <input name="course_date_folder_perm_write" type="checkbox" value="1" <? if ($folder->checkPermission('w')) echo 'checked'; ?>>
    <?= _('Studierende dürfen Dateien in diesen Ordner hochladen') ?>
</label>
<script>
    $('input#edit_folder_name').attr('disabled', $('input:radio[name=folder_type]:checked').attr('id') === 'folder-type-CourseDateFolder');
    $('input:radio[name=folder_type]').on('change', function (ev) {
        $('input#edit_folder_name').attr('disabled', $('input:radio[name=folder_type]:checked').attr('id') === 'folder-type-CourseDateFolder');
    });
</script>
