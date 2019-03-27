<td colspan="6">
    <table class="default collapsable sortable" id="<?= $modul->id ?>">
        <colgroup>
            <col>
            <col span="2" style="width: 150px;">
        </colgroup>
        <? foreach ($modul->modulteile as $modulteil) : ?>
            <? $perm = MvvPerm::get($modulteil) ?>
            <tbody class="<?= ($modulteil_id === $modulteil->getId() ? 'not-collapsed' : 'collapsed') ?><?= $perm->haveFieldPerm('position') ? ' sort_items' : '' ?>"
                   id="<?= $modulteil->getId() ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <? if (count($modulteil->lvgruppen) || $perm->haveFieldPermLvgruppen(MvvPerm::PERM_CREATE)) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/modulteil_lvg/' . $modulteil->id) ?>">
                                <?= htmlReady($modulteil->getDisplayName()) ?></a>
                        <? else : ?>
                            <?= htmlReady($modulteil->getDisplayName()) ?>
                        <? endif; ?>
                    </td>
                    <td class="dont-hide actions" style="white-space: nowrap; text-align: center;">
                        <? if ($perm->havePermWrite()) : ?>
                            <? foreach ($modulteil->deskriptoren->getAvailableTranslations() as $language) : ?>
                                <? $lang = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$language]; ?>
                                <a href="<?= $controller->url_for('/modulteil/' . join('/', [$modulteil->id, $institut_id]), ['display_language' => $language]) ?>">
                                    <img src="<?= Assets::image_path('languages/lang_' . mb_strtolower($language) . '.gif') ?>"
                                         alt="<?= $lang['name'] ?>" title="<?= $lang['name'] ?>">
                                </a>
                            <? endforeach; ?>
                        <? endif; ?>
                    </td>
                    s
                    <td class="dont-hide actions" style="white-space: nowrap;">
                        <form method="post">
                            <?= CSRFProtection::tokenTag(); ?>
                            <? $actionMenu = ActionMenu::get() ?>
                            <? if (MvvPerm::havePermCreate('Lvgruppe') && $perm->haveFieldPermLvgruppen(MvvPerm::PERM_CREATE)) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/lvgruppe/' . $modulteil->id),
                                    _('Neue LV-Gruppe anlegen'),
                                    Icon::create('file+add', ['title' => _('Neue LV-Gruppe anlegen')]),
                                    [
                                        'data-dialog' => 'size=normal',
                                        'title'       => _('Neue LV-Gruppe anlegen')
                                    ]
                                ) ?>
                            <? endif; ?>
                            <? if ($perm->havePermWrite()) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/modulteil/' . $modulteil->id),
                                    _('Modulteil bearbeiten'),
                                    Icon::create('edit', ['title' => _('Modulteil bearbeiten')])
                                ) ?>
                            <? endif; ?>
                            <? if ($perm->havePermCreate()) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/copy_modulteil/' . $modulteil->id),
                                    _('Modulteil kopieren'),
                                    Icon::create('files', ['title' => _('Modulteil kopieren')])
                                ) ?>
                            <? endif; ?>
                            <? if ($perm->havePermCreate()) : ?>
                                <? $actionMenu->addButton(
                                    'delete',
                                    _('Modulteil löschen'),
                                    Icon::create('trash', ['title' => _('Modulteil löschen')]),
                                    [
                                        'formaction'   => $controller->url_for('/delete_modulteil/' . $modulteil->id),
                                        'data-confirm' => sprintf(_('Wollen Sie wirklich den Modulteil "%s" löschen?'), $modulteil->getDisplayName())
                                    ]
                                ) ?>
                            <? endif; ?>
                            <?= $actionMenu->render() ?>
                        </form>
                    </td>
                </tr>
                <? if ($modulteil_id === $modulteil->id) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('module/module/modulteil_lvg', compact('modulteil')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endforeach; ?>
    </table>
</td>
