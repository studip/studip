<? if ($categories) : ?>
    <p><?= _('Bitte eine Standortkategorie auswählen:') ?></p>
    <section class="resource-category-select">
        <? foreach ($categories as $category) : ?>
            <?
            $location_class = $category->class_name;
            ?>
            <a href="<?= URLHelper::getLink(
                     'dispatch.php/resources/location/add',
                     ['category_id' => $category->id]
                     ) ?>" class="category-link" data-dialog="size=auto">
                <?= $location_class::getIconStatic('clickable')->asImg('50px') ?>
                <div class="resource-category-select-text">
                    <?= htmlReady($category->name) ?>
                </div>
            </a>
        <? endforeach ?>
    </section>
<? endif ?>
