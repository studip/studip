<?
# Lifter010: TODO
?>
<tr>
    <? if(empty($flash['founders']) && empty($founders)) :?>
        <td style='text-align:right; vertical-align:top;'><?= _("GruppengründerIn hinzufügen:") ?></td>
        <td nowrap>
            <div style="width: 49%; float: left;">
            <? if (is_array($results_choose_founders)) : ?>
                <select name="choose_founder">
                    <? foreach ($results_choose_founders as $user_id => $data) : ?>
                    <option value="<?= $user_id ?>"><?= htmlReady(my_substr($data['fullname']." (".$data['username'],0,35)) ?>) - <?= $data['perms'] ?></option>
                    <? endforeach; ?>
                </select>
                <?= Icon::create('accept', 'clickable', ['title' => _('NutzerIn hinzufügen')])->asInput(["type" => "image", "class" => "middle", "name" => "add_founder"]) ?>
                <?= Icon::create('refresh', 'clickable', ['title' => _('neue Suche starten')])->asInput(["type" => "image", "class" => "middle", "name" => "new_search"]) ?>
                <? if (sizeof($results_choose_founders) == 500) : ?>
                <br><span style="color:red"><?= sprintf(_("Es werden nur die ersten %s Treffer angezeigt!"), 500) ?></span>
                <? endif; ?>
            <? else : ?>
                <input type="text" name="search_for_founder">
                <?= Icon::create('search', 'clickable', ['title' => _('Suchen')])->asInput(["type" => "image", "class" => "middle", "name" => "search_founder"]) ?>
                <?= _("Geben Sie zur Suche den Vor-, Nach- oder Benutzernamen ein.") ?>
            <? endif; ?>
            </div>
        </td>
    <? else: ?>
        <td style='text-align:right; vertical-align:top;'><?= _("Gruppengründer:") ?></td>
        <td nowrap>
            <div style="width: 49%; float: left;">
            <? if (is_array($founders) && sizeof($founders) > 0) :
                foreach ($founders as $user_id) :?>
                    
                    <?= htmlReady(get_fullname($user_id, 'full_rev')) ?> (<?= get_username($user_id) ?>)
                    <input type="hidden" name="founders[]" value="<?= $user_id ?>">
                    <?= Icon::create('refresh', 'clickable', ['title' => _('NutzerIn entfernen')])->asInput(["type" => "image", "class" => "middle", "name" => "remove_founder"]) ?>
                    <br>
                <? endforeach; ?>
            <? endif; ?>
            </div>
        </td>
    <? endif; ?>
</tr>
