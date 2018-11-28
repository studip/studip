<?php
use Studip\Button, Studip\LinkButton;
?>
<div id="errormessage"></div>
<form action="<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>"
      id="ruleform" class="default"
      onsubmit="return STUDIP.Admission.checkAndSaveRule('<?= $rule->getId() ?>', 'errormessage', '<?= $controller->url_for('admission/rule/validate', get_class($rule)) ?>', 'rules', '<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>')">
    <?= $ruleTemplate ?>
    <footer data-dialog-button>
        <input type="hidden" id="action" name="action" value="">
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), 'cancel') ?>
    </footer>
</form>
