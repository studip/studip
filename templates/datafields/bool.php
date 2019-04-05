<label>
    <input type="checkbox" name="<?= $name ?>[<?= $model->id ?>]"
           value="1" id="<?= $name ?>_<?= $model->id ?>"
           <? if ($value) echo 'checked'; ?>
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           <? if ($model->is_required) echo 'required'; ?>>
    <span class="datafield_title <?= $model->is_required && $entry->isEditable() ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>
