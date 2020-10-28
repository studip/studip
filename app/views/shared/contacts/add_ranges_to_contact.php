<form class="default" action="<?= $controller->url_for('/add_ranges_to_contact', $mvvcontact_id,  $range_type) ?>" method="post" data-dialog="size=auto">

    <label>
    <? if ($range_type == 'Studiengang'): ?>
        <select id="search-contact-select" data-search_type="studiengang" data-placeholder="<?= _('Studiengang suchen') ?>" class="nested-select"  multiple name="ranges[]"></select>
    <? elseif ($range_type == 'Modul'): ?>
        <select id="search-contact-select" data-search_type="modul" data-placeholder="<?= _('Modul suchen') ?>" class="nested-select" multiple name="ranges[]"></select>
    <? elseif ($range_type == 'StudiengangTeil'): ?>
        <select id="search-contact-select" data-search_type="stgteil" data-placeholder="<?= _('Studiengangteil suchen') ?>" class="nested-select" multiple name="ranges[]"></select>
    <? endif; ?>
    </label>

    <? if ($range_type !== 'Modul') : ?>
    <label>
        <?= _('Ansprechpartnertyp') ?>
        <select style="display: inline-block;" name="ansp_typ">
            <option value=""<?= empty($ansp_typ) ? ' selected' : '' ?>></option>
        <? foreach ($GLOBALS['MVV_CONTACTS']['TYPE']['values'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $ansp_typ ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>

    <label>
        <?= _('Kategorie') ?>
        <select style="display: inline-block;" name="ansp_kat">
        <? foreach (MvvContactRange::getCategoriesByRangetype($range_type) as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $ansp_kat ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

</form>

<div id="search-contact-params"
    data-contact="<?= $mvvcontact_id; ?>"
></div>
