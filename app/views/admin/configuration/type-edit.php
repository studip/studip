<? if ($type === 'boolean') : ?>

    <input type="hidden" name="value" value="0">
    <input type="checkbox" name="value" value="1" id="item-value" class="studip-checkbox"
        <? if ($value) echo 'checked'; ?>>
    <label for="item-value">
        <?= _('aktiviert') ?>
    </label>
<? else : ?>
    <label>
        <?= _('Inhalt') ?>
        <? if ($type === 'integer'): ?>
            <input name="value" type="number" id="item-value"
                   value="<?= htmlReady($value) ?>">
        <? elseif ($type === 'array') : ?>
            <?php $v = version_compare(PHP_VERSION, '5.4.0', '>=') ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_encode($value) ?>
            <textarea cols="80" rows="5" name="value" id="item-value"><?= htmlReady($v, true, true) ?></textarea>
        <? elseif ($type === 'i18n'): ?>
            <?= I18N::textarea('value', $value, [
                'id' => 'item-value',
                'cols' => 80,
                'rows' => 3,
            ]) ?>
        <? else: ?>
            <textarea cols="80" rows="3" name="value" id="item-value"><?= htmlReady($value) ?></textarea>
        <? endif; ?>
    </label>
<? endif ?>
