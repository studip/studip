<?= $this->render_partial('search/breadcrumb') ?>
<section class="contentbox">
    <header>
        <h1><?= htmlReady($fach->name) . ' (' . htmlReady($abschluss->name) . ') - ' . _('Angebotene Studiengänge') ?></h1>
    </header>
    <ul class="mvv-result-list">
    <? foreach($studiengaenge as $studiengang):?>
        <li>
            <a href="<?= $controller->url_for($url, $studiengang->id) ?>"><?= htmlReady($studiengang->getDisplayName()) ?></a> 
            <? if ($studiengang->getValue('beschreibung')) : ?>
                <a data-dialog href="<?= $this->controller->url_for('/info', $studiengang->id) ?>">
                    <?= Icon::create('info-circle', 'clickable', ['title' => _('Informationen zum Studiengang')]); ?>
                </a>
            <? endif; ?>
        </li>
    <? endforeach; ?>
    </ul>
</section>

