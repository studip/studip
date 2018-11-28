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
           class="no-hint"
           maxlength="2" size="1"
           value="<? if ($value) echo date('d', $timestamp); ?>"
           title="<?= _('Tag') ?>"
           style="display: inline-block; vertical-align: bottom; width: auto;"
           <? if ($model->is_required) echo 'required'; ?>>.

    <select name="<?= $name ?>[<?= $model->id ?>][]" title="<?= _('Monat') ?>"
            style="display: inline-block; vertical-align: bottom; width: auto;"
            <? if ($model->is_required) echo 'required'; ?>>
        <option value=""></option>
    <? for ($i = 1; $i <= 12; $i += 1): ?>
        <option value="<?= $i ?>"
                <? if ($value && date('n', $timestamp) == $i) echo 'selected'; ?>>
            <?= getMonthName($i, false) ?>
        </option>
    <? endfor;?>
    </select>

    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           class="no-hint"
           maxlength="4" size="3"
           value="<? if ($value) echo date('Y', $timestamp); ?>"
           title="<?= _('Jahr') ?>"
           style="display: inline-block; vertical-align: bottom; width: auto;"
           <? if ($model->is_required) echo 'required'; ?>>
</div>
