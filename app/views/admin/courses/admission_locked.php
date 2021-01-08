<input type="hidden" name="all_sem[]" value="<?= htmlReady($course->id) ?>">
<label>
    <input type="checkbox" <?= $values['admission_locked'] == 'disable' ? 'disabled' : '' ?>
           name="admission_locked[<?= htmlReady($course->id) ?>]"
           value="1" <?= $values['admission_locked'] ? 'checked' : '' ?>>
</label>
