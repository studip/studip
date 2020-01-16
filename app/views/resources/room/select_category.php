<? if ($categories) : ?>
    <form method="get" action="<?= $controller->url_for('resources/room/add/' . $room_id) ?>" class="default"
          data-dialog="size=auto">
        <label>
            <?= _('Raumkategorie') ?>
            <select name="category_id" required>
                <option value=""><?= _('Bitte eine Raumkategorie auswählen:') ?></option>
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
    </form>
<? endif ?>
