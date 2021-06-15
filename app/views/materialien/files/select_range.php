<form class="default" action="<?= $controller->url_for('/select_range', $range_type) ?>" method="post" data-dialog="">
    <label>
        <select class="nested-select" multiple name="range_id[]">
    <? if ($range_type == 'Studiengang'): ?>
        <? foreach(Studiengang::getAllEnriched() as $studiengang) : ?>
            <option value="<?= $studiengang->id; ?>"><?= htmlReady($studiengang->getDisplayName()); ?></option>
        <? endforeach; ?>
    <? elseif ($range_type == 'AbschlussKategorie'): ?>
        <? foreach(AbschlussKategorie::getAllEnriched() as $abs_kat) : ?>
            <option value="<?= $abs_kat->id; ?>"><?= htmlReady($abs_kat->getDisplayName()); ?></option>
        <? endforeach; ?>
    <? endif; ?>
        </select>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Weiter'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

    <div id="search-contact-params"
        data-contact=""
    ></div>
</form>