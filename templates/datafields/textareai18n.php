<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <? if ($entry->isEditable()) : ?>
        <?= I18N::textarea($name, $value, ['required' => (bool) $model->is_required, 'locale_names' => $locale_names]) ?>
    <? else : ?>
        <textarea name="<?= $name ?>[<?= $model->id ?>]"
              disabled
              id="<?= $name ?>_<?= $model->id ?>"
              rows="6"
    ><?= htmlReady($value) ?></textarea>
    <? endif ?>
</label>
