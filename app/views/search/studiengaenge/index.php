<section class="contentbox">
    <header>
        <h1><?= _('Abschluss-Kategorien') ?></h1>
    </header>
    <ul class="mvv-result-list">
    <? foreach ($categories as $category) : ?>
        <? if ($category->count_studiengaenge) : ?>
            <li>
                <a href="<?= $controller->url_for('search/studiengaenge/kategorie', $category->id) ?>">
                <?= htmlReady($category->getDisplayName()) ?>
                </a>
            </li>
        <? endif; ?>
    <? endforeach; ?>
    </ul>
</section>
