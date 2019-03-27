<? foreach ($module as $modul) : ?>
    <? $perm = MvvPerm::get($modul) ?>
    <tbody class="<?= ($modul->count_modulteile ? '' : 'empty ') ?><?= ($modul_id === $modul->getId() ? 'not-collapsed' : 'collapsed') ?>">
        <? $ampel_icon = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['icon'] ?>
        <? $ampelstatus = $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$modul->stat]['name'] ?>
        <tr class="header-row" id="modul_<?= $modul->getId() ?>">
            <? if ($modul->count_modulteile) : ?>
                <td style="white-space:nowrap;" class="toggle-indicator">
                    <? $details_url = $details_url ?: '/details'; ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for($details_url, $modul->getId()) ?>">
                        <? if ($ampel_icon) : ?>
                            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                        <? endif; ?>
                        <?= htmlReady($modul->code) ?>
                    </a>
                </td>
                <td class="dont-hide toggle-indicator">
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for($details_url, $modul->getId()) ?>"
                       style="background-image: none; padding: 0;">
                        <?= htmlReady($modul->getDisplayName(0)) ?>
                    </a>
                </td>
            <? else : ?>
                <td style="white-space:nowrap;">
                    <? if ($ampel_icon) : ?>
                        <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                    <? endif; ?>
                    <?= htmlReady($modul->code) ?>
                </td>
                <td class="dont-hide" style="font-weight: bold;">
                    <?= htmlReady($modul->getDisplayName()) ?>
                </td>
            <? endif; ?>
            <td style="text-align:center;" class="dont-hide"><?= htmlReady($modul->fassung_nr) ?></td>
            <td style="text-align: center;" class="dont-hide"><?= $modul->count_modulteile ?></td>
            <td class="dont-hide actions" style="text-align: center;">
                <? if ($perm->havePermRead()) : ?>
                    <? $languages = $modul->deskriptoren->getAvailableTranslations(); ?>
                    <? foreach ($languages as $language) : ?>
                        <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
                        <a href="<?= $controller->url_for('/modul/' . $modul->id . '/', ['display_language' => $language]) ?>">
                            <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($language) . '.gif') ?>"
                                 alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
                        </a>
                    <? endforeach; ?>
                <? endif; ?>
            </td>
            <td class="dont-hide actions" style="white-space: nowrap;">
                <form method="post">
                    <?= CSRFProtection::tokenTag(); ?>
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if ($modul->stat === 'planung' && $perm->haveFieldPerm('stat')) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/approve/' . $modul->id),
                            _('Modul genehmigen'),
                            Icon::create('accept', ['title' => _('Modul genehmigen')]),
                            ['data-dialog' => 'size=auto;']
                        ) ?>
                    <? endif; ?>
                    <? if ($perm->havePermRead()) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/description', $modul->id),
                            _('Modulbeschreibung ansehen'),
                            Icon::create('log', ['title' => _('Modulbeschreibung ansehen')]),
                            [
                                'data-dialog' => 'size=auto',
                                'title'       => htmlReady($modul->getDisplayName())
                            ]
                        ) ?>
                    <? endif; ?>
                    <? if ($perm->haveFieldPerm('modulteile', MvvPerm::PERM_CREATE)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/modulteil', ['modul_id' => $modul->id]),
                            _('Modulteil anlegen'),
                            Icon::create('file+add', ['title' => _('Modulteil anlegen')])
                        ) ?>
                    <? endif; ?>
                    <? if ($perm->havePermWrite()) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/modul/' . $modul->id),
                            _('Modul bearbeiten'),
                            Icon::create('edit', ['title' => _('Modul bearbeiten')])
                        ) ?>
                    <? endif; ?>
                    <? if ($perm->haveFieldPerm('copy_module', MvvPerm::PERM_CREATE)) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/copy_form', $modul->id),
                            _('Modul kopieren'),
                            Icon::create('files', ['title' => _('Modul kopieren')]),
                            ['data-dialog' => '']
                        ) ?>
                    <? endif; ?>
                    <? if ($perm->havePermCreate()) : ?>
                        <? $actionMenu->addButton(
                            'delete',
                            _('Modul löschen'),
                            Icon::create('trash', ['title' => _('Modul löschen')]),
                            [
                                'formaction'   => $controller->url_for('/delete/' . $modul->id),
                                'data-confirm' => sprintf(_('Wollen Sie wirklich das Modul "%s" löschen?'), $modul->getDisplayName())
                            ]
                        ) ?>
                    <? endif; ?>
                    <?= $actionMenu->render() ?>
                </form>
            </td>
        </tr>
        <? if ($modul->count_modulteile && $modul_id === $modul->id) : ?>
            <tr class="loaded-details nohover">
                <?= $this->render_partial('module/module/details', compact('modul')) ?>
            </tr>
        <? endif; ?>
    </tbody>
<? endforeach; ?>
