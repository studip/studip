<input type="hidden" name="all_sem[]" value="<?= $semid ?>">
<label>
    <input type="checkbox" <?= $values['admission_locked'] == 'disable' ? 'disabled' : '' ?>
           name="admission_locked[<?= $semid ?>]"
           value="1" <?= ($values['admission_locked'] ? 'checked' : '') ?> />
</label>