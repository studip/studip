<?php
use Studip\Button, Studip\LinkButton;
?>

<h3><?= htmlReady($rule->getName()) ?></h3>
<?= $tpl ?>
<br>
<label for="conditionlist" class="caption">
    <span class="required"><?= _('Anmeldebedingungen') ?></span>
</label>

<br>

<a href="<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/condadmission_conditions') ?>" onclick="return STUDIP.UserFilter.configureCondition('condition', this.href)">
    <?= Icon::create('add', 'clickable', tooltip2(_('Bedingung hinzufügen')))->asImg() ?>
    <?= _('Bedingung hinzufügen') ?>
</a>

<br>

<div id="condadmission_conditions">
    <span class="nofilter" style="<?=(!$rule->getUngroupedConditions() && !$rule->getConditiongroups()) ? '' : 'display: none'?>">
        <i><?= _('Sie haben noch keine Bedingungen festgelegt.'); ?></i>
    </span>
    <div class="userfilter" style="<?=(!$rule->getUngroupedConditions() || !$rule->getConditiongroups()) ? '' : 'display: none'?>">
        <? if ($rule->conditiongroupsAllowed()): ?>
        <div class="grouped_conditions_template" id="new_conditiongroup" style="margin-bottom: 5px; display: none">
            <div class="condition_list">
                <?=_('Kontingent:')?> <input type="text" name="quota" size="5"> <?=_('Prozent')?>
            </div>
            <?= Button::create(_('Kontingent aufheben'), 'ungroup_conditions', ['class' => 'ungroup_conditions', 'onclick' => 'return STUDIP.UserFilter.ungroupConditions(this)']) ?>
        </div>
        <? else: ?>
            <? $rule->removeConditiongroups(); ?>
            <div id="no_conditiongroups"></div>
        <? endif; ?>
        <div class="ungrouped_conditions">
            <div class="condition_list">
            <? foreach ($rule->getUngroupedConditions() as $condition): ?>
                <? $condition->show_user_count = true; ?>
                <div class="condition" id="condition_<?= $condition->getId() ?>">
                    <? if ($rule->conditiongroupsAllowed()): ?>
                        <input type="checkbox" name="conditions_checkbox[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>">
                    <? endif; ?>
                    <?= $condition->toString() ?>
                    <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                        class="conditionfield_delete">
                    <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
                    <input type="hidden" name="conditions[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>">
                    <input type="hidden" name="conditiongroup_<?=$condition->getId()?>" value="">
                </div>
            <? endforeach; ?>
            </div>
        </div>
        <? if ($rule->conditiongroupsAllowed()): ?>
            <input type="hidden" name="conditiongroups_allowed" value="1">
            <?= Button::create(_('Kontingent erstellen'), 'group_conditions', ['class' => 'group_conditions', 'onclick' => 'return STUDIP.UserFilter.groupConditions()', 'style' => $rule->getUngroupedConditions() ? '' : 'display: none']) ?>
            <? foreach ($rule->getConditiongroups() as $conditiongroup_id => $conditiongroup): ?>
            <div class="grouped_conditions" id="conditiongroup_<?=$conditiongroup_id?>" style="margin-bottom: 5px">
                <div class="condition_list">
                    <?=_('Kontingent:')?> <input type="text" name="quota_<?=$conditiongroup_id?>" value="<?=$rule->getQuota($conditiongroup_id)?>" size="5"> <?=_('Prozent')?>
                    <? foreach ($conditiongroup as $condition): ?>
                        <? $condition->show_user_count = true; ?>
                        <div class="condition" id="condition_<?= $condition->getId() ?>">
                            <input type="checkbox" name="conditions_checkbox[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>" style="display: none">
                            <?= $condition->toString() ?>
                            <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                                        class="conditionfield_delete">
                            <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
                            <input type="hidden" name="conditions[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>">
                            <input type="hidden" name="conditiongroup_<?=$condition->getId()?>" value="<?= $conditiongroup_id ?>">
                        </div>
                    <? endforeach; ?>
                </div>
                <?= Button::create(_('Kontingent aufheben'), 'ungroup_conditions', ['class' => 'ungroup_conditions', 'onclick' => 'return STUDIP.UserFilter.ungroupConditions(this)']) ?>
            </div>
            <? endforeach; ?>
        <? endif; ?>
    </div>
</div>

<br>
