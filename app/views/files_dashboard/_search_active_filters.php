<?
$filter = $query->getFilter();
$removeActiveFilterUrl = function ($key) use ($query, $filter) {
    $array = $filter->toArray();
    unset($array[$key]);

    return URLHelper::getLink(
        'dispatch.php/files_dashboard/search',
        [ 'q' => $query->getQuery(), 'filter' => $array ]
    );
};
?>
<? if ($filter->isFiltering()) : ?>
    <div class="files-search-active-filters">
        <ul>
            <li><?= _('Aktive Filter') ?>:</li>
            <? if ($filter->hasCategory()) : ?>
                <?
                $categories = \FilesSearch\Filter::getCategories();
                $categoryLabel = $categories[$filter->getCategory()];
                ?>
                <li class="files-search-active-filter">
                    <a href="<?= $removeActiveFilterUrl('category') ?>">
                        <?= _('Kategorie') ?>:
                        <?= htmlReady($categoryLabel) ?>
                        <?= Icon::create('trash') ?>
                    </a>
                </li>
            <? endif ?>

            <? if ($filter->hasSemester()) : ?>
                <li class="files-search-active-filter">
                    <a href="<?= $removeActiveFilterUrl('semester') ?>">
                        <?= _('Semester') ?>:
                        <?= htmlReady($filter->getSemester()->name) ?>

                        <?= Icon::create('trash') ?>
                    </a>
                </li>
            <? endif ?>
        </ul>
    </div>
<? endif ?>
