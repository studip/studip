<?= htmlReady($rule->getName()) ?>

<div class="messagebox_details">
    <?= formatLinks($rule->terms) ?>
</div>

<label>
    <input type="checkbox" name="terms_accepted" value="1">
    <?= _('Hiermit akzeptiere ich die oben angezeigten Teilnahmebedingungen') ?>
</label>
