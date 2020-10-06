<? foreach ($select_attributes as $key => $value) : ?>
    <? $attributes .= sprintf('%s = "%s"', $key, $value) ?>
<? endforeach ?>
<select <?= $attributes ?>>
    <? foreach ($semesters as $sem_key => $one_sem) : ?>
        <? $one_sem['key'] = $sem_key; ?>
        <option value="<?= $one_sem[$option_value] ?>" <?= ($one_sem[$option_value] == $default ? "selected" : "") ?>>
            <?= htmlReady($one_sem['name']) ?>
        </option>
    <? endforeach ?>
</select>
