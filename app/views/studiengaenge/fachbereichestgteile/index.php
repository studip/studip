<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <colgroup>
            <col>
            <col style="width: 1%;">
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('studiengaenge/fachbereichestgteile/', _('Fachbereich'), 'fachbereich') ?>
                <th colspan="4"></th>
            </tr>
        </thead>
        <? foreach ($fachbereiche as $fachbereich) : ?>
            <tbody class="<?= $fachbereich['stgteile'] ? '' : 'empty' ?> <?= ((count($stgteil_ids) || $details_id === $fachbereich['institut_id']) ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <? if ($fachbereich['stgteile']) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details_fachbereich/' . $fachbereich['institut_id']) ?>">
                                <?= htmlReady($fachbereich['name']) ?>
                            </a>
                        <? else: ?>
                            <?= htmlReady($fachbereich['name']) ?>
                        <? endif; ?>
                    </td>
                    <td class="actions" style="white-space: nowrap;">
                        <? if (MvvPerm::havePermCreate('StudiengangTeil')) : ?>
                            <a href="<?= $controller->url_for('/stgteil_fachbereich/' . $fachbereich['institut_id']) ?>">
                                <?= Icon::create('file+add', Icon::ROLE_CLICKABLE , ['title' => _('Neuen Studiengangteil in diesem Fachbereich anlegen')])->asImg(); ?>
                            </a>
                        <? endif; ?>
                    </td>
                    <? if ($details_id === $fachbereich['institut_id'] || count($stgteil_ids)) : ?>
                    <? $stgteile = StudiengangTeil::findByFachbereich($fachbereich['institut_id'], ['mvv_stgteil.stgteil_id' => $stgteil_ids], 'fach_name,zusatz,kp', 'ASC'); ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengangteile/details_grouped', compact('stgteile')) ?>
                </tr>
                <? endif; ?>
            </tbody>
        <? endforeach ?>
    </table>
</form>