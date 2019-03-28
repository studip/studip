<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <colgroup>
            <col>
            <col style="width:1%;">
            <col style="width:1%;">
        <colgroup>
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Fach'), 'name') ?>
                <?= $controller->renderSortLink('/index', _('Studiengangteile'), 'count_stgteile', ['style' => 'text-align: center;']) ?>
                <th></th>
            </tr>
        </thead>
        <? foreach ($faecher as $fach): ?>
            <tbody class="<?= $fach->count_stgteile ? '' : 'empty' ?> <?= (($details_id === $fach->id || (isset($stgteil_ids) && count($stgteil_ids))) ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <? if ($fach->count_stgteile) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details_fach/' . $fach->id) ?>">
                                <?= htmlReady($fach->name) ?>
                            </a>
                        <? else: ?>
                            <?= htmlReady($fach->name) ?>
                        <? endif; ?>
                    </td>
                    <td style="text-align: center;" class="dont-hide"><?= $fach->count_stgteile ?> </td>
                    <td class="dont-hide actions">
                        <? if (MvvPerm::havePermCreate('StudiengangTeil')) : ?>
                            <a href="<?= $controller->url_for('/stgteil_fach/' . $fach->id) ?>">
                                <?= Icon::create('file+add',  Icon::ROLE_CLICKABLE ,['title' => _('Neuen Studiengangteil für gewähltes Fach anlegen')])->asImg(); ?>
                            </a>
                        <? endif; ?>
                    </td>
                </tr>
                <? if ($details_id === $fach->getId() || (isset($stgteil_ids) && count($stgteil_ids))) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('studiengaenge/studiengangteile/details_grouped', compact('stgteile')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endforeach ?>
        <? if ($count > MVVController::$items_per_page) : ?>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;">
                        <?
                        $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                        $pagination->clear_attributes();
                        $pagination->set_attribute('perPage', MVVController::$items_per_page);
                        $pagination->set_attribute('num_postings', $count);
                        $pagination->set_attribute('page', $page);
                        $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_faecher=%s';
                        $pagination->set_attribute('pagelink', $page_link);
                        echo $pagination->render("shared/pagechooser");
                        ?>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</form>