<? if ($GLOBALS['perm']->have_perm("admin") || !LockRules::Check($semid, "seminar_visibility")) : ?>
    <label>
        <input type="hidden" name="all_sem[]" value="<?= $semid?>">
        <input name="visibility[<?=$semid?>]" type="checkbox" value="1" <?= ((int)$values['visible'] == 1 ? 'checked' : '')?> />
    </label>
<? endif ?>
