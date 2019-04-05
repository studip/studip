<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>

<div style="white-space: nowrap;">
    <input type="text" name="<?= $name ?>[<?= $model->id ?>]"
           class="no-hint"
           value="<? if ($value) echo date('d.m.Y ', $timestamp); ?>"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           title="<?= _('Datum') ?>"
           style="width: 8em;"
           data-date-picker
           <? if ($model->is_required) echo 'required'; ?>>
</div>
