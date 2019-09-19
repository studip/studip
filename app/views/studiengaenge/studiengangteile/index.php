<?= $controller->jsUrl() ?>
<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <caption>
            <?= _('Liste der Studiengangteile') ?>
            <span class="actions"><? printf(_('%s Studiengangteile'), $count) ?></span>
        </caption>
        <colgroup>
            <col>
            <col style="width: 40%;">
            <col span="3" style="width: 1%">
        </colgroup>
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Fach'), 'fach_name,zusatz,kp') ?>
                <?= $controller->renderSortLink('/index', _('Zweck'), 'zusatz,fach_name,kp') ?>
                <?= $controller->renderSortLink('/index', _('CP'), 'kp,fach_name,zusatz', ['style' => 'text-align: center;']) ?>
                <?= $controller->renderSortLink('/index', _('Versionen'), 'count_versionen,fach_name,kp', ['style' => 'text-align: center;']) ?>
                <th></th>
            </tr>
        </thead>
        <? if ($count) : ?>
            <? foreach ($stgteile as $stgteil): ?>
                <tbody class="<?php if (!$stgteil->count_versionen) echo 'empty' ?>  <?= $stgteil_id === $stgteil->getId() ? 'not-collapsed' : 'collapsed' ?>">
                    <tr class="header-row">
                        <td class="toggle-indicator">
                            <? if ($stgteil->count_versionen) : ?>
                                <a class="mvv-load-in-new-row"
                                   href="<?= $controller->url_for('/details/' . $stgteil->getId()) ?>">
                                    <?= htmlReady($stgteil->fach_name) ?>
                                    <? if ($stgteil->count_fachberater) : ?>
                                        <?= Icon::create('community', Icon::ROLE_INFO, ['title' => sprintf(ngettext('%s Fachberater zugeordnet', '%s Fachberater zugeordnet', $stgteil->count_fachberater), $stgteil->count_fachberater)]) ?>
                                    <? endif; ?>
                                </a>
                            <? else : ?>
                                <?= htmlReady($stgteil->fach_name) ?>
                                <? if ($stgteil->count_fachberater) : ?>
                                    <?= Icon::create('community', Icon::ROLE_INFO, ['title' => sprintf(ngettext('%s Fachberater zugeordnet', '%s Fachberater zugeordnet', $stgteil->count_fachberater), $stgteil->count_fachberater)]) ?>
                                <? endif; ?>
                            <? endif; ?>
                        </td>
                        <td class="dont-hide"><?= htmlReady($stgteil->zusatz) ?> </td>
                        <td class="dont-hide" style="text-align: center;"><?= htmlReady($stgteil->kp) ?> </td>
                        <td class="dont-hide" style="text-align: center;"><?= $stgteil->count_versionen ?> </td>
                        <td class="dont-hide actions" style="white-space: nowrap;">
                            <? $actionMenu = ActionMenu::get() ?>
                            <? if (MvvPerm::havePermCreate('StgteilVersion')) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/version/' . $stgteil->getId()),
                                    _('Neue Version anlegen'),
                                    Icon::create('file+add', Icon::ROLE_CLICKABLE, ['title' => _('Neue Version anlegen')])
                                ) ?>
                            <? endif; ?>
                            <? if (MvvPerm::havePermWrite($stgteil)) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/stgteil/' . $stgteil->getId()),
                                    _('Studiengangteil bearbeiten'),
                                    Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Studiengangteil bearbeiten')])
                                ) ?>
                            <? endif; ?>
                            <? if (MvvPerm::havePermCreate('StudiengangTeil')) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/copy/' . $stgteil->getId()),
                                    _('Studiengangteil kopieren'),
                                    Icon::create('files', Icon::ROLE_CLICKABLE, ['title' => _('Studiengangteil kopieren')])
                                ) ?>
                            <? endif; ?>
                            <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                                <? $actionMenu->addButton(
                                    'delete_part',
                                    _('Studiengangteil löschen'),
                                    Icon::create('trash', Icon::ROLE_CLICKABLE, [
                                        'title'        => _('Studiengangteil löschen'),
                                        'formaction'   => $controller->url_for('/delete/' . $stgteil->getId()),
                                        'data-confirm' => sprintf(_('Wollen Sie wirklich den Studiengangteil "%s" löschen?'), htmlReady($stgteil->getDisplayName())),
                                    ])
                                ) ?>
                            <? endif; ?>
                            <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                    <? if ($stgteil_id == $stgteil->getId()) : ?>
                        <? $versionen = StgteilVersion::findByStgteil($stgteil->getId()); ?>
                        <tr class="loaded-details nohover">
                            <?= $this->render_partial('studiengaenge/studiengangteile/details', compact('stgteil_id', 'versionen')) ?>
                        </tr>
                    <? endif; ?>
                </tbody>
            <? endforeach ?>
            <? if ($count > MVVController::$items_per_page) : ?>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right;">
                            <?
                            $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_studiengangteile=%s';
                            
                            $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                            $pagination->clear_attributes();
                            $pagination->perPage      = MVVController::$items_per_page;
                            $pagination->num_postings = $count;
                            $pagination->page         = $page;
                            $pagination->pagelink     = $page_link;
                            echo $pagination->render();
                            ?>
                        </td>
                    </tr>
                </tfoot>
            <? endif; ?>
        <? else : ?>
            <tbody>
                <tr>
                    <td style="text-align: center" colspan="5">
                        <?= _('Es wurden noch keine Studiengangteile angelegt.') ?>
                    </td>
                </tr>
            </tbody>
        <? endif ?>
    </table>
</form>
