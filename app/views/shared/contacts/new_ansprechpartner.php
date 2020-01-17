<form class="default" action="<?= $controller->url_for('/new_ansprechpartner') ?>" method="post" data-dialog="size=auto">

    <label>
        <select id="mvv-contacts-range_types" name="range_type">
        <? foreach ($allowed_object_types as $object_type) : ?>
            <option value="<?= $object_type ?>"><?= htmlReady($object_type::getClassDisplayName()) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Weiter'), 'store') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

</form>