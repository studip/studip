<? foreach ($studiengaenge as $studiengang) : ?>
    <? $perm = new MvvPerm($studiengang) ?>
    <tbody class="<?= ($studiengang_id === $studiengang->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="table-header header-row" id="studiengang_<?= $studiengang->id ?>">
            <td class="toggle-indicator">
                <a class="mvv-load-in-new-row"
                   href="<?= $controller->url_for('/details_studiengang/' . $studiengang->id) ?>">
                    <? $ampel_icon = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['icon'] ?>
                    <? $ampelstatus = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
                    <? if ($ampel_icon) : ?>
                        <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                    <? endif; ?>
                    <?= htmlReady($studiengang->name) ?> <?= (mb_strlen($studiengang->name_kurz) ? '(' . htmlReady($studiengang->name_kurz) . ')' : '') ?>
                    <? if ($studiengang->count_dokumente) : ?>
                        <?= Icon::create(
                            'staple',
                            Icon::ROLE_INFO,
                            [
                                'title' => sprintf(
                                    ngettext('%s Dokument zugeordnet', '%s Dokumente zugeordnet', $studiengang->count_dokumente),
                                    $studiengang->count_dokumente
                                ),
                                'style' => 'vertical-align: text-top;'
                            ]) ?>
                    <? endif; ?>
                </a>
            </td>
            <td class="dont-hide">
                <? if ($studiengang->responsible_institute) : ?>
                    <?= htmlReady($studiengang->responsible_institute->getDisplayName()) ?>
                <? else : ?>
                    <?= _('unbekannte Einrichtung') ?>
                <? endif; ?>
            </td>
            <td class="dont-hide">
                <?= htmlReady($studiengang->abschluss->category->getDisplayName()) ?>
            </td>
            <td class="actions dont-hide">
                <form method="post">
                    <?= CSRFProtection::tokenTag(); ?>
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if ($studiengang->stat === 'planung' && MvvPerm::haveFieldPermStat($studiengang)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/approve/' . $studiengang->id),
                            _('Studiengang genehmigen'),
                            Icon::create('accept', Icon::ROLE_CLICKABLE , ['title' => _('Studiengang genehmigen')]),
                            ['data-dialog' => 'buttons=false'])
                        ?>
                    <? endif; ?>
                    <? if ($perm->havePerm(MvvPerm::PERM_WRITE)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/studiengang/' . $studiengang->id),
                            _('Studiengang bearbeiten'),
                            Icon::create('edit', Icon::ROLE_CLICKABLE , ['title' => _('Studiengang bearbeiten')]))
                        ?>
                    <? endif; ?>
                    <? if ($perm->havePerm(MvvPerm::PERM_CREATE)) : ?>
                        <? if (!$studiengang->count_faecher) : ?>
                            <? $actionMenu->addButton(
                                'delete',
                                _('Studiengang löschen'),
                                Icon::create('trash', Icon::ROLE_CLICKABLE ,tooltip2(_('Studiengang löschen'))),
                                [
                                    'formaction'   => $controller->url_for('/delete/' . $studiengang->id),
                                    'data-confirm' => sprintf(_('Wollen Sie wirklich den Studiengang "%s" löschen?'), htmlReady($studiengang->name))
                                ]
                            ) ?>
                        <? endif; ?>
                    <? endif; ?>
                    <?= $actionMenu->render() ?>
                </form>
            </td>
        </tr>
        <? if ($studiengang_id === $studiengang->id) : ?>
            <? if ($studiengang->typ == 'mehrfach') : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengaenge/stgteil_bezeichnungen', compact('studiengang_id', 'studiengang', 'bez_stgteile', 'stgteile', 'stg_stgbez_id', 'search_stgteil', 'search')) ?>
                </tr>
            <? else : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengaenge/studiengangteile', compact('studiengang', 'stgteile', 'search_stgteil', 'search')) ?>
                </tr>
            <? endif; ?>
        <? endif; ?>
    </tbody>
<? endforeach; ?>