<label>
    <span class="required"><?= _('Teilnahmebedingungen') ?></span>
    <textarea style="min-height: 24em; min-width: 44em;" name="terms" placeholder="<?=_('Formulieren Sie hier die Teilnahmebedingungen.')?>" required><?= htmlReady($rule->terms) ?></textarea>
</label>
