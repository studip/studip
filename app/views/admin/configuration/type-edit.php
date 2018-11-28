<? if ($type === 'boolean') : ?>
    <label>
        <input type="hidden" name="value" value="0">
        <input type="checkbox" name="value" value="1" id="item-value"
                <? if ($value) echo 'checked'; ?>>
        <?= _('aktiviert') ?>
    </label>
<? else : ?>
    <label>
    <?= _('Inhalt') ?>
    <? if ($type === 'integer'): ?>
        <input class="allow-only-numbers" name="value" type="number"
               id="item-value" value="<?= htmlReady($value) ?>">
        </label>
    <? elseif ($type === 'array') : ?>
        <label>
        <?php $v = version_compare(PHP_VERSION, '5.4.0', '>=') ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_encode($value) ?>
        <textarea cols="80" rows="5" name="value" id="item-value"><?= htmlReady($v, true, true) ?></textarea>
    <? else: ?>
        <textarea cols="80" rows="3" name="value" id="item-value"><?= htmlReady($value) ?></textarea>
    <? endif; ?>
    </label>
<? endif ?>
