<? if ($categories) : ?>
    <p><?= _('Bitte eine Raumkategorie auswählen:') ?></p>
    <section class="resource-category-select">
        <? foreach ($categories as $category) : ?>
            <?
            $room_class = $category->class_name;
            ?>
            <a href="<?= URLHelper::getLink(
                     'dispatch.php/resources/room/add/' . $room_id,
                     ['category_id' => $category->id]
                     ) ?>" class="category-link" data-dialog="size=auto">
                <?= $room_class::getIconStatic('clickable')->asImg('50px') ?>
                <div class="resource-category-select-text">
                    <?= htmlReady($category->name) ?>
                </div>
            </a>
        <? endforeach ?>
    </section>
<? endif ?>
