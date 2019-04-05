<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <textarea name="<?= $name ?>[<?= $model->id ?>]"
              <?= !$entry->isEditable() ? "disabled" : "" ?>
              id="<?= $name ?>_<?= $model->id ?>"
              rows="6"
              <? if ($model->is_required) echo 'required'; ?>
    ><?= htmlReady($value) ?></textarea>
</label>
