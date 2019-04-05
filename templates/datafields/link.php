<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <input type="url" name="<?= $name ?>[<?= $model->id ?>]"
           value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $model->id ?>"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           size="30" placeholder="http://"
           <? if ($model->is_required) echo 'required'; ?>>
</label>
