<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>

<label>
    <input type="radio" name="<?= $name ?>[<?= $model->id ?>][combo]"
           value="select" id="combo_<?= $model->id ?>_select"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           <? if (in_array($value, $values)) echo 'checked'; ?>>
    <select style="display: inline-block; width: auto;" <?= !$entry->isEditable() ? "disabled" : "" ?>
        name="<?= $name ?>[<?= $model->id ?>][select]" onfocus="$('#combo_<?= $model->id ?>_select').prop('checked', true);">
    <? foreach ($values as $v): ?>
        <option value="<?= htmlReady($v) ?>" <? if ($v === $value) echo 'selected'; ?>>
            <?= htmlReady($v) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>

<label>
    <input type="radio" name="<?= $name ?>[<?= $model->id ?>][combo]"
           value="text" id="combo_<?= $model->id ?>_text"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           <? if (!in_array($value, $values)) echo 'checked'; ?>>
    <input name="<?= $name ?>[<?= $model->id ?>][text]"
           value="<? if (!in_array($value, $values)) echo htmlReady($value); ?>"
           <?= !$entry->isEditable() ? "disabled" : "" ?>
           onfocus="$('#combo_<?= $model->id ?>_text').prop('checked', true);">
</label>
