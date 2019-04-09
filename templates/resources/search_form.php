<? if ($search_string): ?>

    <input type="hidden" name="search_string_<?= $name ?>" value="<?= htmlReady($search_string) ?>">
    <?= Icon::create('arr_2down', 'clickable', ['title' => _('diesen Eintrag übernehmen')])->asInput(['name'=>'send_'.$name,'value'=>_('übernehmen'),]) ?>

    <select align="absmiddle" name="submit_<?= $name ?>">
    <? if ($allow_all): ?>
        <option value="all"><?= _('jedeR') ?></option>
    <? endif; ?>

    <? foreach ($results as $art => $items): ?>
        <optgroup label="<?= htmlReady($art) ?>">
        <? foreach ($items as $key => $val): ?>
            <option value="<?= htmlReady($key) ?>" <?= tooltip($val['name']) ?>>
                <?= htmlReady(my_substr($val['name'], 0, 30)) ?>
            </option>
        <? endforeach; ?>
        </optgroup>
    <? endforeach; ?>
    </select>

    <?= Icon::create('refresh', 'clickable', ['title' => _('Suche zurücksetzen')])->asInput(['align'=>'absmiddle','name'=>'reset_'.$name,'value'=>_('neue Suche'),]) ?>

<? else: ?>

    <input type="text" align="absmiddle" size="30" maxlength="255"
           name="search_string_<?= $name ?>">
    <?= Icon::create('search', 'clickable', ['title' => _('Starten Sie hier Ihre Suche')])->asInput(['align'=>'absmiddle','name'=>'do_'.$name,'value'=>_('suchen'),]) ?>

<? endif; ?>