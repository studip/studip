<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <textarea name="<?= $name ?>[<?= $model->id ?>]"
              id="<?= $name ?>_<?= $model->id ?>"
              class="add_toolbar wysiwyg"
              <?= !$entry->isEditable() ? "disabled" : "" ?>
              <? if ($model->is_required) echo 'required'; ?>
    ><?= wysiwygReady($value) ?></textarea>
</label>
