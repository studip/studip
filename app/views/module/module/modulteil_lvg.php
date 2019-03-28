<td colspan="3">
    <table id="modulteil_<?= $modulteil_id ?>" class=" default collapsable sortable">
        <colgroup>
            <col>
            <col style="width: 150px;">
        </colgroup>
        <? foreach ($modulteil->lvgruppen as $lvgruppe) : ?>
            <? $lvgruppe_modulteil = LvgruppeModulteil::get([$lvgruppe->getId(), $modulteil->getId()]) ?>
            <tbody id="<?= $modulteil_id . '_' . $lvgruppe->getId() ?>"<?= MvvPerm::haveFieldPermPosition($lvgruppe_modulteil) ? 'class="sort_items"' : '' ?>>
                <tr class="header-row">
                    <td><?= htmlReady($lvgruppe->getDisplayName()) ?></td>
                    <td class="actions">
                        <form method="post">
                            <?= CSRFProtection::tokenTag(); ?>
                            <? $actionMenu = ActionMenu::get() ?>
                            <? if (MvvPerm::haveFieldPermLvgruppen($modulteil, MvvPerm::PERM_WRITE)) : ?>
                                <? $actionMenu->addLink(
                                    $controller->url_for('/lvgruppe/' . $modulteil->id . '/' . $lvgruppe->id),
                                    _('LV-Gruppe bearbeiten'),
                                    Icon::create('edit', Icon::ROLE_CLICKABLE ,['title' => _('LV-Gruppe bearbeiten')]),
                                    [
                                        'data-dialog' => 'size=auto',
                                        'title'       => _('LV-Gruppe bearbeiten')
                                    ]
                                ) ?>
                            <? endif; ?>
                            <? if (MvvPerm::haveFieldPermLvgruppen($modulteil, MvvPerm::PERM_CREATE)) : ?>
                                <? $actionMenu->addButton(
                                    'delete',
                                    _('Zuordnung der LV-Gruppe löschen'),
                                    Icon::create('trash', Icon::ROLE_CLICKABLE , ['title' => _('Zuordnung der LV-Gruppe löschen')]),
                                    [
                                        'formaction'   => $controller->url_for('/delete_lvgruppe/' . $modulteil->id . '/' . $lvgruppe->id),
                                        'data-confirm' => sprintf(
                                            _('Wollen Sie wirklich die Lehrveranstaltungsgruppe "%s" vom Modulteil "%s" entfernen?'),
                                            htmlReady($lvgruppe->getDisplayName()),
                                            htmlReady($modulteil->getDisplayName())
                                        )
                                    ]
                                ) ?>
                            <? endif; ?>
                            <?= $actionMenu->render() ?>
                        </form>
                    </td>
                </tr>
            </tbody>
        <? endforeach; ?>
        <? if (MvvPerm::haveFieldPermLvgruppen($modulteil, MvvPerm::PERM_CREATE)) : ?>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <form action="<?= $controller->url_for('/add_lvgruppe/' . $modulteil->id) ?>" method="post">
                            <?= CSRFProtection::tokenTag(); ?>
                            <input type="hidden" name="security_token" value="<?= $security_token ?>">
                            <div style="float: left; padding-right: 10px;"><?= _('LV-Gruppe hinzufügen:') ?></div>
                            <?= $search->render(); ?>
                            <?= Icon::create('search', Icon::ROLE_CLICKABLE , ['title' => _('LV-Gruppe suchen'), 'name' => 'search_stgteil', 'data-qs_name' => $search->getId(), 'data-qs_id' => $qs_search_id, 'data-qs_submit' => 'no', 'class' => 'mvv-qs-button'])->asInput(); ?>
                            <?= Icon::create('accept', Icon::ROLE_CLICKABLE , ['title' => _('LV-Gruppe zuordnen')])->asInput(['class' => 'mvv-submit', 'name' => 'add_lvgruppe']); ?>
                            <input type="hidden" name="modulteil_id" value="<?= $modulteil_id ?>">
                        </form>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</td>
