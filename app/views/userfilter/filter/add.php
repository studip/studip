<div class="condition" id="<?= $condition->getId() ?>">
    <input type="checkbox" name="conditions_checkbox[]"/>
    <?= $condition->toString() ?>
    <input type="hidden" name="conditions[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>"/>
    <input type="hidden" name="conditiongroup_<?=$condition->getId()?>" value=""/>
    <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
            class="conditionfield_delete">
        <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
</div>