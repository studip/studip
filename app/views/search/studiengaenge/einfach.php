<?= $this->render_partial('search/breadcrumb') ?>
<h2><?= htmlReady($studiengang->getDisplayName(ModuleManagementModel::DISPLAY_ABSCHLUSS)) ?></h2>
<h3><?= _('Ausprägungen') ?></h3>
<ul class="mvv-result-list">
<? foreach ($data as $fach_id => $fach) : ?>
    <li>
        <a href="<?= $controller->url_for($verlauf_url, $fach_id) ?>"><?= htmlReady($fach) ?></a>
    </li>
<? endforeach; ?>
</ul>