<form name="select_rule_type" class="default" action="<?= $controller->url_for('admission/rule/configure') ?>" method="post">
    <fieldset>
        <legend><?= _('Anmelderegel konfigurieren') ?></legend>
    <?php
    use Studip\Button, Studip\LinkButton;

    foreach ($ruleTypes as $className => $classDetail) {
        $disabled = $courseset && !$courseset->isAdmissionRuleAllowed($className) ? 'disabled' : '';
    ?>
        <section id="<?= $className ?>">
            <label>
                <input <?=$disabled ?> type="radio" name="ruletype" value="<?= $className ?>"/>
                <span <?=($disabled ? 'style="text-decoration:line-through"' : '')?>><?=$classDetail['name'] ?></span>
                <?= Icon::create('question-circle', 'clickable', ['title' => $classDetail['description']])->asImg() ?>
            </label>
        </section>

    <?php
    }
    ?>
    </fieldset>

    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::create(_('Weiter >>'), 'configure', [
            'onclick' => "return $('input[name=ruletype]:checked').val() ? STUDIP.Admission.configureRule($('input[name=ruletype]:checked').val(), '".
                $controller->url_for('admission/rule/configure')."') : false"]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/courseset/configure'), ['onclick' => "STUDIP.Admission.closeDialog('configurerule'); return false;"]) ?>
    </footer>
</form>
