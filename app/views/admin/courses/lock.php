<? $current_lock_rule = $all_lock_rules->findOneBy('lock_id', $values['lock_rule']); ?>
<? if (!$GLOBALS['perm']->have_perm('root') && ($current_lock_rule['permission'] == 'admin' || $current_lock_rule['permission'] == 'root')) : ?>
    <?= htmlReady($current_lock_rule['name'])?>
<? else : ?>
    <select name="lock_sem[<?=$semid?>]" style="max-width: 200px">
        <? for ($i = 0; $i < count($all_lock_rules); $i++) : ?>
            <option value="<?= $all_lock_rules[$i]["lock_id"] ?>"
            <?= ($all_lock_rules[$i]["lock_id"]==$values['lock_rule']) ?  'selected' :''?>>
                <?= htmlReady($all_lock_rules[$i]["name"]) ?>
            </option>
        <? endfor ?>
    </select>
<? endif ?>