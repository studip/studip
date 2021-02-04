<label>
    <span class="required"><?= _('Teilnahmebedingungen') ?></span>
    <textarea class="size-l" style="min-height: 32em;" name="terms" placeholder="<?=_('Formulieren Sie hier die Teilnahmebedingungen.')?>" required><?= htmlReady($rule->terms) ?></textarea>
</label>
