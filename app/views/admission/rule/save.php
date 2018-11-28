<div class="hover_box admissionrule" id="rule_<?= $rule->getId() ?>">
    <span id="rule_data_<?= $rule->getId() ?>">
        <?= $rule->toString() ?>
        <input type="hidden" name="rules[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($rule)) ?>"/>
    </span>
    <span class="action_icons" id="rule_actions_<?= $rule->getId() ?>">
        <a href="#" onclick="return STUDIP.Admission.configureRule('<?= get_class($rule) ?>', '<?=
            $controller->url_for('admission/rule/configure', get_class($rule), $rule->getId()) ?>', '<?=
            $rule->getId() ?>')">
            <?= Icon::create('edit', 'clickable')->asImg(); ?></a>
        <a href="#" onclick="return STUDIP.Dialogs.showConfirmDialog('<?= 
                    _('Soll die Anmelderegel wirklich gelöscht werden?') ?>', 
                    'javascript:STUDIP.Admission.removeRule(\'rule_<?= $rule->getId() ?>\', \'rules\')')">
            <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
    </span>
</div>
