<?php
if ($rule->getStartTime() && $rule->getEndTime()) {
    printf(_('Diese Regel gilt von %s bis %s.') . '<br>',
           strftime('%d.%m.%Y %H:%M', $rule->getStartTime()),
           strftime('%d.%m.%Y %H:%M', $rule->getEndTime()));
} else if ($rule->getStartTime() && !$rule->getEndTime()) {
    printf(_('Diese Regel gilt ab %s.') . '<br>',
           strftime('%d.%m.%Y %H:%M', $rule->getStartTime()));
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    printf(_('Diese Regel gilt bis %s.') . '<br>',
           strftime('%d.%m.%Y %H:%M', $rule->getEndTime()));
}
?>
<? if (count($rule->getUngroupedConditions()) == 1): ?>
    <?= _('Folgende Bedingung muss zur Anmeldung erfüllt sein:') ?>
    <br>
    <div id="conditions">
        <?php
        $conditions = $rule->getUngroupedConditions();
        $condition = reset($conditions);
        ?>
        <div id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </div>
    </div>
<? elseif (count($rule->getUngroupedConditions()) > 1): ?>
    <?= _('Mindestens eine der folgenden Bedingungen muss zur Anmeldung '.
        'erfüllt sein:') ?>
    <br>
    <ul id="conditions">
    <? foreach ($rule->getUngroupedConditions() as $condition): ?>
        <li id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </li>
    <? endforeach; ?>
    </ul>
<? elseif (count($rule->getConditionGroups())): ?>
    <?= _('Mindestens eine der folgenden Bedingungen muss zur Anmeldung '.
        'erfüllt sein:') ?>
    <br>
    <ul id="conditions">
    <? foreach ($rule->getConditiongroups() as $conditiongroup_id => $conditions): ?>
        <? if ($rule->conditiongroupsAllowed()): ?>
            <li>
                <i><?= sprintf(_('Kontingent: %s Prozent'), $rule->getQuota($conditiongroup_id)) ?></i>
            </li>
        <? endif; ?>
        <li>
            <ul id="conditiongroup_<?=$conditiongroup_id?>">
            <? foreach ($conditions as $condition): ?>
                <li id="condition_<?= $condition->getId() ?>">
                    <i><?= $condition->toString() ?></i>
                </li>
            <? endforeach; ?>
            </ul>
        </li>
        
    <? endforeach; ?>
    </ul>
<? endif; ?>
