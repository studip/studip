<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <? if ($entry->isEditable()) : ?>
        <?= I18N::input($name, $value, ['required' => (bool) $model->is_required, 'locale_names' => $locale_names]) ?>
    <? else : ?>
        <input type="text" name="<?= $name ?>[<?= $model->id ?>]"
               value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $model->id ?>"
               disabled>
    <? endif ?>
</label>
