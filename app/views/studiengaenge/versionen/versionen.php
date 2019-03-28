<? foreach ($versionen as $version) : ?>
    <? $perm = MvvPerm::get($version); ?>
    <tbody class="<?= ($version->count_abschnitte ? '' : 'empty') ?> <?= ($version_id === $version->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <? if ($version->count_abschnitte) : ?>
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/abschnitte/' . $version->id) ?>">
                    <? endif; ?>
                    <? $ampel_icon = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['icon'] ?>
                    <? $ampelstatus = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['name'] ?>
                    <? if ($ampel_icon) : ?>
                        <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                    <? endif; ?>
                    <?= htmlReady($version->getDisplayName()) ?>
                    <? if ($version->count_abschnitte) : ?>
                </a>
            <? endif; ?>
            </td>
            <td class="dont-hide" style="text-align: center;">
                <? if ($version->count_dokumente) : ?>
                    <?= Icon::create('staple', Icon::ROLE_INFO, ['title' => sprintf(ngettext('%s Dokument zugeordnet', '%s Dokumente zugeordnet', $version->count_dokumente), $version->count_dokumente)])->asImg(); ?>
                <? endif; ?>
            </td>
            <td class="dont-hide" style="white-space: nowrap; text-align: right;">
                <form method="post">
                    <?= CSRFProtection::tokenTag(); ?>
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if ($version->stat === 'planung' && MvvPerm::haveFieldPermStat($version)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/approve/' . $version->id),
                            _('Version genehmigen'),
                            Icon::create('accept', Icon::ROLE_CLICKABLE, ['title' => _('Version genehmigen')]),
                            ['data-dialog' => 'title=' . htmlReady($version->getDisplayName()) . ''])
                        ?>
                    <? endif; ?>
                    <? if ($perm->haveFieldPerm('abschnitte', MvvPerm::PERM_CREATE)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/abschnitt', ['version_id' => $version->id]),
                            _('Studiengangteil-Abschnitt anlegen'),
                            Icon::create('file+add', Icon::ROLE_CLICKABLE, ['title' => _('Studiengangteil-Abschnitt anlegen')]),
                            ['data-dialog' => true])
                        ?>
                    <? endif; ?>
                    <? if ($perm->havePermWrite()) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/version/' . $version->stgteil_id . '/' . $version->id),
                            _('Version bearbeiten'),
                            Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Version bearbeiten')]))
                        ?>
                    <? endif; ?>
                    <? if (MvvPerm::havePermCreate('StgteilVersion')) : ?>
                        <? $actionMenu->addButton(
                            'copy',
                            _('Version kopieren'),
                            Icon::create(
                                'files',
                                Icon::ROLE_CLICKABLE,
                                ['title'        => _('Version kopieren'),
                                 'formaction'   => $controller->url_for('/copy_version/' . $version->id),
                                 'data-confirm' => sprintf(
                                     _('Wollen Sie wirklich die Version "%s" des Studiengangteils kopieren?'),
                                     htmlReady($version->getDisplayName()))
                                ]
                            ))
                        ?>
                    <? endif; ?>
                    <? if ($perm->havePermCreate()) : ?>
                        <? $actionMenu->addButton(
                            'delete',
                            _('Version löschen'),
                            Icon::create('trash', Icon::ROLE_CLICKABLE,
                                ['title'        => _('Version löschen'),
                                 'formaction'   => $controller->url_for('/delete_version/' . $version->id),
                                 'data-confirm' => sprintf(
                                     _('Wollen Sie wirklich die Version "%s" des Studiengangteils löschen?'),
                                     htmlReady($version->getDisplayName()))
                                ]
                            ))
                        ?>
                    <? endif; ?>
                    <?= $actionMenu->render() ?>
                </form>
            </td>
        </tr>
        <? if ($version_id === $version->id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('studiengaenge/versionen/abschnitte', compact('version', 'abschnitte')) ?>
            </tr>
        <? endif; ?>
    </tbody>
<? endforeach; ?>