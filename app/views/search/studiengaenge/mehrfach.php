<?= $this->render_partial('search/breadcrumb') ?>
<table class="default nohover">
    <caption>
        <?= _('Studiengang') ?>: <?= htmlReady($studiengang->getDisplayName(ModuleManagementModel::DISPLAY_ABSCHLUSS)) ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Fächer') ?></th>
        <? foreach ($studiengangTeilBezeichnungen as $teil_bezeichnung): ?>
            <th style="text-align: center;"><?= htmlReady($teil_bezeichnung->getDisplayName()) ?></th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <? foreach ($data as $fach_id => $fach): ?>
        <tr>
            <td>
                <?= htmlReady($fachNamen[$fach_id]) ?>
            </td>
            <? foreach ($studiengangTeilBezeichnungen as $teil_bezeichnung): ?>
                <td style="text-align: center;">
                    <? if (isset($fach[$teil_bezeichnung->id])) : ?>
                    <a href="<?= $controller->url_for($verlauf_url, $fach[$teil_bezeichnung->id], $teil_bezeichnung->id, $studiengang_id) ?>">
                        <?= Icon::create('info-circle-full', 'clickable', ['title' => _('Studienverlaufsplan anzeigen')])->asImg(); ?>
                    </a>
                    <? endif; ?>
                </td>
            <? endforeach; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>