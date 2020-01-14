<? if ($categories) : ?>
    <p><?= _('Bitte eine Gebäudekategorie auswählen:') ?></p>
    <section class="resource-category-select">
        <? foreach ($categories as $category) : ?>
            <?
            $building_class = $category->class_name;
            ?>
            <a href="<?= URLHelper::getLink(
                     'dispatch.php/resources/building/add',
                     ['category_id' => $category->id]
                     ) ?>" class="category-link" data-dialog="size=auto">
                <?= $building_class::getIconStatic('clickable')->asImg('50px') ?>
                <div class="resource-category-select-text">
                    <?= htmlReady($category->name) ?>
                </div>
            </a>
        <? endforeach ?>
    </section>
<? endif ?>
