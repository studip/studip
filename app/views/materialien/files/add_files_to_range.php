<form class="default" action="<?= $controller->url_for('/add_files_to_range',$range_type, $range_id) ?>" method="post" data-dialog="">
    <label>
        <select id="search-file-select" class="nested-select"  multiple name="files[]">
        <? if ($files) : ?>
            <? foreach ($files as $file) : ?>
                <option value="<?= $file->id ?>" selected><?= htmlReady($file->getDisplayName()) ?></option>
            <? endforeach; ?>
        <? endif; ?>
        </select>
    </label>
    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>
</form>

