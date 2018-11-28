<?php if ($className) { ?>
    <? if (count($field->getValidCompareOperators()) > 1) : ?>
        <select name="compare_operator[]" size="1" class="conditionfield_compare_op">
            <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
            <option value="<?= $op ?>"><?= htmlReady($text) ?></option>
            <?php } ?>
        </select>
    <? else : ?>
        <input type="hidden" class="conditionfield_compare_op" name="compare_operator[]" value="<?=key($field->getValidCompareOperators())?>">
    <? endif ?>
    <? if (count($field->getValidValues()) > 1) : ?>
        <select name="value[]" class="conditionfield_value">
            <?php foreach ($field->getValidValues() as $id => $name) { ?>
            <option value="<?= $id ?>"><?= htmlReady($name) ?></option>
            <?php } ?>
        </select>
        <? elseif (count($field->getValidValues()) == 1) : ?>
            <input type="hidden" name="value[]" class="conditionfield_value" value="<?=key($field->getValidValues())?>">
    <? else : ?>
        <input type="text" name="value[]" class="conditionfield_value" value="">
    <? endif ?>
<?php } else { ?>
    <?= (!$is_first ? '<strong>' . _("und") . '</strong>' : '')?>
    <div class="conditionfield">
    <select name="field[]" class="conditionfield_class" size="1" onchange="STUDIP.UserFilter.getConditionFieldConfiguration(this, '<?= $controller->url_for('userfilter/field/configure') ?>')">
        <option value="">-- <?= _('bitte auswählen') ?> --</option>
        <?php foreach ($conditionFields as $className => $displayName) { ?>
        <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php } ?>
    </select>
    <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
        class="conditionfield_delete">
        <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
</div>
<?php } ?>