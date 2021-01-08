<? if ($GLOBALS['perm']->have_perm("admin") || !LockRules::Check($course->id, "seminar_visibility")) : ?>
    <label>
        <input type="hidden" name="all_sem[]" value="<?= htmlReady($course->id) ?>">
        <input name="visibility[<?= htmlReady($course->id) ?>]" type="checkbox" value="1" <?= $values['visible'] ? 'checked' : '' ?>>
    </label>
<? endif ?>
