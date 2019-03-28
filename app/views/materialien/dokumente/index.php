<? if (sizeof($dokumente)) : ?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default collapsable">
            <colgroup>
                <col>
                <col style="width: 40%">
                <col style="width: 5%">
                <col span="2" style="width: 1%">
            </colgroup>
            <thead>
                <tr>
                    <?= $controller->renderSortLink('materialien/dokumente/', _('Name'), 'name') ?>
                    <?= $controller->renderSortLink('materialien/dokumente/', _('Linktext'), 'linktext') ?>
                    <?= $controller->renderSortLInk('materialien/dokumente/', _('Geändert am'), 'chdate', ['style' => 'white-space: nowrap;']) ?>
                    <?= $controller->renderSortLink('materialien/dokumente/', _('Referenzierungen'), 'count_zuordnungen', ['style' => 'text-align: center;']) ?>
                    <th></th>
                </tr>
            </thead>
            <? foreach ($dokumente as $dokument): ?>
                <? $perm = MvvPerm::get($dokument) ?>
                <tbody class="<?= ($dokument_id == $dokument->id ? 'not-collapsed' : 'collapsed') ?>">
                    <tr class="header-row">
                        <td class="toggle-indicator">
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details/' . $dokument->id) ?>"><?= htmlReady($dokument->name) ?></a>
                        </td>
                        <td class="dont-hide">
                            <?= htmlReady($dokument->linktext) ?>
                        </td>
                        <td class="dont-hide">
                            <?= strftime('%x, %X', $dokument->chdate) ?>
                        </td>
                        <td style="text-align: center;" class="dont-hide">
                            <?= $dokument->count_zuordnungen ?>
                        </td>
                        <td style="white-space: nowrap;" class="dont-hide actions">
                        <? $actionMenu = ActionMenu::get() ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('shared/log_event/show/' . $dokument->id),
                            _('Log-Einträge dieses Dokumentes'),
                            Icon::create('log', Icon::ROLE_CLICKABLE, ['title' => _('Log-Einträge dieses Dokumentes')]),
                            ['data-dialog' => 'size=auto']
                        ) ?>
                        <? if ($perm->havePermWrite()) : ?>
                            <? $actionMenu->addLink(
                                $controller->url_for('/dokument/' . $dokument->id),
                                _('Dokument bearbeiten'),
                                Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Dokument bearbeiten')])
                            ) ?>
                        <? endif; ?>
                        <? if ($perm->havePermCreate()) : ?>
                            <? if ($relations = $dokument->getCountRelations()) {
                                $msg = sprintf(
                                    _('Wollen Sie das Dokument "%s" wirklich löschen?')
                                    . ' '
                                    . ngettext(
                                        'Dieses Dokument wird von einem Objekt referenziert.',
                                        'Dieses Dokument wird von %s Objekten referenziert.',
                                        $relations
                                    ),
                                    $dokument->name,
                                    $relations
                                );
                            } else {
                                $msg = sprintf(_('Wollen Sie das Dokument "%s" wirklich löschen?'), $dokument->name);
                            } ?>
                            <? $actionMenu->addButton(
                                'delete_file',
                                _('Dokument löschen'),
                                Icon::create('trash', Icon::ROLE_CLICKABLE, ['title' => _('Dokument löschen')]),
                                ['formaction'   => $controller->url_for('/delete/' . $dokument->id),
                                 'data-confirm' => $msg]
                            ) ?>
                        <? endif; ?>
                        <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                    <? if ($dokument_id === $dokument->getId()) : ?>
                        <tr class="loaded-details nohover">
                            <?= $this->render_partial('materialien/dokumente/details', compact('dokument')) ?>
                        </tr>
                    <? endif; ?>
                </tbody>
            <? endforeach ?>
            <? if ($count > MVVController::$items_per_page) : ?>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right">
                            <?php
                            $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                            $pagination->clear_attributes();
                            $pagination->set_attribute('perPage', MVVController::$items_per_page);
                            $pagination->set_attribute('num_postings', $count);
                            $pagination->set_attribute('page', $page);
                            // ARGH!
                            $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_dokumente=%s';
                            $pagination->set_attribute('pagelink', $page_link);
                            echo $pagination->render("shared/pagechooser");
                            ?>
                        </td>
                    </tr>
                </tfoot>
            <? endif; ?>
        </table>
    </form>
<? endif; ?>
