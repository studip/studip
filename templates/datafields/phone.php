<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>
</label>

<div style="white-space: nowrap;">
    +<input type="tel" name="<?= $name ?>[<?= $model->id ?>][]"
            value="<?= htmlReady($values[0]) ?>" maxlength="3" size="2"
            <?= !$entry->isEditable() ? "disabled" : "" ?>
            title="<?= _('Landesvorwahl ohne führende Nullen') ?>"
            placeholder="49" class="no-hint" style="width: 3em;"
            <? if ($model->is_required) echo 'required'; ?>>

    <input type="tel" name="<?= $name ?>[<?= $model->id ?>][]"
            value="<?= htmlReady($values[1]) ?>" maxlength="6" size="5"
            <?= !$entry->isEditable() ? "disabled" : "" ?>
            title="<?= _('Ortsvorwahl ohne führende Null') ?>"
            placeholder="541" class="no-hint"
            <? if ($model->is_required) echo 'required'; ?>>

    <input type="tel" name="<?= $name ?>[<?= $model->id ?>][]"
             value="<?= htmlReady($values[2]) ?>" maxlength="10" size="9"
             <?= !$entry->isEditable() ? "disabled" : "" ?>
             title="<?= _('Rufnummer') ?>"
             placeholder="969-0000" class="no-hint"
             <? if ($model->is_required) echo 'required'; ?>>
</div>
