<form class="default" action="<?= $controller->link_for('/select_range', $range_type) ?>" method="post" data-dialog="size=auto">
    <?= CSRFProtection::tokenTag(); ?>

    <label>
    <? if ($range_type == 'Studiengang'): ?>
        <select id="search-contact-studiengang-select" class="nested-select" multiple name="range_id[]"></select>
    <? elseif ($range_type == 'Modul'): ?>
        <select id="search-contact-modul-select" class="nested-select" multiple name="range_id[]"></select>
    <? endif; ?>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Weiter'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </footer>

    <div id="search-contact-params" data-contact=""></div>
</form>
