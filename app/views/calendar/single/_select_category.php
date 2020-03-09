<form class="default" name="filter_categories" method="post" action="<?= $action_url ?>">

    <section class="hgroup">
        <?= _('Kategorie') ?>
        <select class="sidebar-selectlist nested-select submit-upon-select" style="width: 16em;" name="category">
            <option value=""><?= _('Alle Kategorien') ?></option>
        <? foreach (Config::get()->getValue('PERS_TERMIN_KAT') as $key => $cat) : ?>
            <option value="<?= $key ?>"<?= ($category == $key ? ' selected="selected"' : '') ?> class="calendar-category<?= $key ?>" data-color-class="calendar-category<?= $key ?>">
                <?= htmlReady($cat['name']) ?>
            </option>
        <? endforeach; ?>
        </select>

        <?= Icon::create('accept', 'clickable')->asInput(['class' => "text-top"]) ?>
    </section>
</form>
