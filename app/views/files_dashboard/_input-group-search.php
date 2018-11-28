<div class="input-group files-search">
    <input
        name="q"
        value="<?= htmlReady($query) ?>"
        placeholder="<?= _('Was suchen Sie?') ?>"
        aria-label="<?= _('Was suchen Sie?') ?>"
        minlength="4"
        size="10"
        type="text">

    <span class="input-group-append">
        <button type="submit" class="button">
            <?= Icon::create('search')->asImg(['title' => _("Suche beginnen")]) ?>
        </button>

        <? if ($query != '') : ?>
            <?= \Studip\LinkButton::createReset(_('Zurücksetzen'), $controller->url_for('files_dashboard/search')) ?>
        <? endif ?>
    </span>
</div>
