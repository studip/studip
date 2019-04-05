<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>

<div style="white-space: nowrap;">
    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           value="<?= $value ?>" title="<?= _('Uhrzeit') ?>"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           maxlength="2" class="size-s no-hint has-time-picker"
           <? if ($model->is_required) echo 'required'; ?>>
</div>
