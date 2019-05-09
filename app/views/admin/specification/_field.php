<?php
$fields = Request::getArray('fields');
$order  = Request::getArray('order');
?>

<section>
    <? if ($required) : ?>
    <span class="required">
        <?= htmlReady($name) ?>
    </span>
    <? else : ?>
    <?= htmlReady($name) ?>
    <? endif ?>


    <div class="hgroup">
        <label class="col-2">
            <?= _('Sortierung') ?>
            <input id="order_<?= $id ?>" min="0" type="number" size="3" name="order[<?= $id ?>]"
                   value="<?= (int)(($order && isset($order[$id])) ? $order[$id] : @$rule['order'][$id]) ?>">
            <input type="hidden" name="fields[<?= $id ?>]" value="0">
        </label>
        <label class="col-2">
            <input type="checkbox"
                   name="fields[<?= $id ?>]"
                   value="1"
                    <?= (($fields && isset($fields[$id])) ? $fields[$id] : @$rule['attributes'][$id]) ? 'checked="checked"' : '' ?>>
            <?= _('Aktivieren') ?>
        </label>
        <label class="col-1">
            <? if ($institution) : ?>
                <?= htmlReady($institution->name)?>
            <? endif; ?>
        </label>
    </div>
</section>
