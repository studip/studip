<?= $this->render_partial('search/breadcrumb') ?>
<? foreach ($studiengaenge as $abschluss_id => $studiengaenge_abschluss): ?>
<article class="studip toggle">
    <header>
        <h1>
            <a name="abschluss-<?= $abschluss_id ?>">
                <?= htmlReady($abschluesse[$abschluss_id]->getDisplayName()) ?>
            </a>
        </h1>
    </header>
    <ul class="mvv-result-list">
    <? foreach ($studiengaenge_abschluss as $id => $s) : ?>
        <li>
            <a href="<?= $controller->link_for('search/studiengaenge/studiengang', $id) ?>">
                <?= htmlReady($s->getDisplayName()); ?>
            </a>
        </li>
    <? endforeach; ?>
    </ul>
</article>
<? endforeach; ?>
