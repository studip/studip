<? if ($categories) : ?>
<form method="get" action="<?= $controller->link_for('resources/location/add' . $room_id) ?>" class="default"
      data-dialog="size=auto">
    <label>
        <?= _('Standortkategorie') ?>
        <select name="category_id" required>
            <option value=""><?= _('Bitte eine Standortkategorie auswählen:') ?></option>
            <? foreach ($categories as $category) : ?>
                <option value="<?= $category->id ?>">
                    <?= htmlReady($category->name) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Auswählen')) ?>
    </footer>
<? endif ?>
