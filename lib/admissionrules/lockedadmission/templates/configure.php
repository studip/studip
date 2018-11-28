<h3><?= $rule->getName() ?></h3>
<label for="message" class="caption">
    <?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:
</label>
<textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>