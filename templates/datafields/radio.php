<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>

<? foreach ($type_param as $pkey => $pval): ?>
    <label>
        <input type="radio" name="<?= $name ?>[<?= $model->id ?>]"
               value="<?= $is_assoc ? (string) $pkey : $pval ?>"
               <?= !$entry->isEditable() ? "disabled" : "" ?>
               <? if ($value === ($is_assoc ? (string)$pkey : $pval)) echo 'checked'; ?>
               <? if ($model->is_required) echo 'required'; ?>>
        <?= htmlReady($pval) ?>
    </label>
<? endforeach ?>
