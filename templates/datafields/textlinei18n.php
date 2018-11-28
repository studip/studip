<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($model->description): ?>
        <?= tooltipIcon($model->description) ?>
    <? endif ?>

    <?= I18N::input($name, $value, ['required' => (bool) $model->is_required, 'locale_names' => $locale_names]) ?>
</label>
