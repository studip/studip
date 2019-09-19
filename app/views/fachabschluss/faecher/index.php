<form method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <table class="default collapsable">
        <caption>
            <?= _('Fächer mit verwendeten Abschlüssen') ?>
            <span class="actions"><? printf(ngettext('%s Fach', '%s Fächer', $count), $count) ?></span>
        </caption>
        <colgroup>
            <col>
            <col style="width: 5%">
            <col style="width: 10%">
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Fach'), 'name') ?>
                <?= $controller->renderSortLink('/index', _('Abschlüsse'), 'count_abschluesse') ?>
                <th></th>
            </tr>
        </thead>
        <? foreach ($faecher as $fach): ?>
            <tbody class="<?= $fach->count_abschluesse ? '' : 'empty' ?>  <?= ($fach_id === $fach->id ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <? if ($fach->count_abschluesse) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details/' . $fach->id) ?>"><?= htmlReady($fach->name) ?></a>
                        <? else: ?>
                            <?= htmlReady($fach->name) ?>
                        <? endif; ?>
                    </td>
                    <td class="dont-hide" style="text-align: center;"><?= $fach->count_abschluesse ?> </td>
                    <td class="dont-hide actions" style="white-space: nowrap;">
                        <? if (MvvPerm::havePermWrite($fach)) : ?>
                            <a href="<?= $controller->url_for('/fach/' . $fach->id) ?>">
                                <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Fach bearbeiten')])->asImg(); ?>
                            </a>
                        <? endif; ?>
                        <? if (MvvPerm::havePermCreate($fach)) : ?>
                            <? if ($fach->count_abschluesse == 0): ?>
                                <?= Icon::create('trash', Icon::ROLE_CLICKABLE, tooltip2(_('Fach löschen')))->asInput(
                                    [
                                        'formaction'   => $controller->url_for('/delete/' . $fach->id),
                                        'data-confirm' => sprintf(_('Wollen Sie wirklich das Fach "%s" löschen?'), htmlReady($fach->name)),
                                        'name'         => 'delete'
                                    ]); ?>
                            <? else : ?>
                                <?= Icon::create('trash', Icon::ROLE_INACTIVE, tooltip2(_('Fach kann nicht glöscht werden')))->asImg(); ?>
                            <? endif; ?>
                        <? endif; ?>
                    </td>
                </tr>
                <? if ($fach_id === $fach->id) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('fachabschluss/faecher/details', compact('fach')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endforeach ?>
        <? if ($count > MVVController::$items_per_page) : ?>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">
                        <?
                        $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                        $pagination->clear_attributes();
                        $pagination->set_attribute('perPage', MVVController::$items_per_page);
                        $pagination->set_attribute('num_postings', $count);
                        $pagination->set_attribute('page', $page);
                        $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_faecher=%s';
                        $pagination->set_attribute('pagelink', $page_link);
                        echo $pagination->render('shared/pagechooser');
                        ?>

                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</form>
