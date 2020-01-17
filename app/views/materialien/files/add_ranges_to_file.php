<form class="default" action="<?= $controller->url_for('/add_ranges_to_file', $mvvfile_id,  $range_type) ?>" method="post" data-dialog="size=auto">

    <label>
    <? if ($range_type == 'Studiengang'): ?>
        <select id="search-file-studiengang-select" class="nested-select"  multiple name="ranges[]">
        <? if ($mvv_objects) : ?>
            <? foreach ($mvv_objects as $mvv_object) : ?>
                <option value="<?= $mvv_object->id ?>" selected><?= htmlReady($mvv_object->getDisplayName()) ?></option>
            <? endforeach; ?>
        <? endif; ?>
        </select>
    <? elseif ($range_type == 'Modul'): ?>
        <select id="search-file-modul-select" class="nested-select"  multiple name="ranges[]">
        <? if ($mvv_objects) : ?>
            <? foreach ($mvv_objects as $mvv_object) : ?>
                <option value="<?= $mvv_object->id ?>" selected><?= htmlReady($mvv_object->getDisplayName()) ?></option>
            <? endforeach; ?>
        <? endif; ?>
        </select>
    <? elseif ($range_type == 'AbschlussKategorie'): ?>
        <select id="search-file-abschlusskategorie-select" class="nested-select"  multiple name="ranges[]">
        <? if ($mvv_objects) : ?>
            <? foreach ($mvv_objects as $mvv_object) : ?>
                <option value="<?= $mvv_object->id ?>" selected><?= htmlReady($mvv_object->getDisplayName()) ?></option>
            <? endforeach; ?>
        <? endif; ?>
        </select>
    <? endif; ?>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

</form>
