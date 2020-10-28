<form class="default" action="<?= $controller->url_for('/select_range', $range_type) ?>" method="post" data-dialog="size=auto">
    <?= CSRFProtection::tokenTag(); ?>

    <label>
    <? if ($range_type == 'Studiengang'): ?>
        <select id="search-contact-select" data-search_type="studiengang" data-placeholder="<?= _('Studiengang suchen') ?>" class="nested-select" multiple name="range_id[]"></select>
    <? elseif ($range_type == 'Modul'): ?>
        <select id="search-contact-select" data-search_type="modul" data-placeholder="<?= _('Modul suchen') ?>" class="nested-select" multiple name="range_id[]"></select>
    <? else : ?>
        <select id="search-contact-select" data-search_type="stgteil" data-placeholder="<?= _('Studiengangteil suchen') ?>" class="nested-select" multiple name="range_id[]"></select>
    <? endif; ?>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Weiter'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </footer>

    <div id="search-contact-params" data-contact=""></div>
</form>
