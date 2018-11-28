<?= $this->render_partial('search/breadcrumb') ?>
<? foreach ($studiengaenge as $abschluss_id => $studiengaenge_abschluss): ?>
<section class="contentbox">
    <header>
        <h1><?= htmlReady($abschluesse[$abschluss_id]->getDisplayName()) ?></h1>
    </header>
    <ul class="mvv-result-list">
        <? foreach ($studiengaenge_abschluss as $id => $s) : ?>
        <li>
            <a href="<?= $controller->url_for('search/studiengaenge/studiengang', $id) ?>"><?= htmlReady($s->getDisplayName()); ?></a>
        </li>
        <? endforeach; ?>
    </ul>
</section>
<? endforeach; ?>