<?= $this->render_partial('basicdata/index.php', ['value' => $value]) ?>

<section>
    <label for="wizard-subtitle">
        <?= _('Untertitel') ?>
    </label>
    <input type="text" name="subtitle" id="wizard-subtitle" size="75" maxlength="254" value="<?= htmlReady($values['subtitle']) ?>"/>
</section>
<section>
    <label for="wizard-kind">
        <?= _('Veranstaltungsart') ?>
    </label>
    <input type="text" name="kind" id="wizard-kind" size="75" maxlength="254" value="<?= htmlReady($values['kind']) ?>"/>
</section>
<section>
    <label for="wizard-ects">
        <?= _('ECTS-Punkte') ?>
    </label>
    <input type="text" name="ects" id="wizard-ects" size="20" maxlength="32" value="<?= htmlReady($values['ects']) ?>"/>
</section>
<section>
    <label for="wizard-maxmembers">
        <?= _('max. Teilnehmendenzahl') ?>
    </label>
    <input type="number" name="maxmembers" id="wizard-maxmember" min="0" value="<?= htmlReady($values['maxmembers']) ?>"/>
</section>
