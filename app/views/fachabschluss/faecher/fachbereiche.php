<table class="default collapsable">
    <colgroup>
        <col>
        <col style="width: 5%;">
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('fachabschluss/faecher/fachbereiche/', _('Fachbereich'), 'name') ?>
            <?= $controller->renderSortLink('fachabschluss/faecher/fachbereiche/', _('Fächer'), 'faecher', ['style' => 'text-align: center;']) ?>
        </tr>
    </thead>
    <? foreach ($fachbereiche as $fachbereich): ?>
        <? if ($fachbereich['faecher']) : ?>
            <tbody class="<?= ($fachbereich_id === $fachbereich['institut_id'] ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <a class="mvv-load-in-new-row"
                           href="<?= $controller->url_for('/details_fachbereich/' . $fachbereich['institut_id']) ?>"><?= htmlReady($fachbereich['name']) ?></a>
                    </td>
                    <td style="text-align: center;" class="dont-hide"><?= htmlReady($fachbereich['faecher']) ?> </td>
                </tr>
                <? if ($fachbereich_id === $fachbereich['institut_id']) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('fachabschluss/faecher/details_fachbereich', compact('fach')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endif; ?>
    <? endforeach ?>
</table>
