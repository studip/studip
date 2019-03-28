<td colspan="3">
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
        <tbody>
            <? foreach ($stgteile as $stgteil) : ?>
                <tr>
                    <td>
                        <? if ($ampel_icon) : ?>
                            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                        <? endif; ?>
                        <?= htmlReady($stgteil->getDisplayName()) ?>
                    </td>
                    <td class="actions" style="white-space: nowrap;">
                        <? $actionMenu = ActionMenu::get() ?>
                        <? if (MvvPerm::havePermWrite($stgteil)) : ?>
                            <? $actionMenu->addLink(
                                $controller->url_for('/stgteil/' . $stgteil->id),
                                _('Studiengangteil bearbeiten'),
                                Icon::create('edit', Icon::ROLE_CLICKABLE , ['title' => _('Studiengangteil bearbeiten')]))
                            ?>
                        <? endif; ?>
                        <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                            <? $actionMenu->addLink(
                                $controller->url_for('/copy/' . $stgteil->id),
                                _('Studiengangteil kopieren'),
                                Icon::create('files', Icon::ROLE_CLICKABLE , ['title' => _('Studiengangteil kopieren')]))
                            ?>
                        <? endif; ?>
                        <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                            <? $actionMenu->addButton(
                                'delete_part',
                                _('Studiengangteil löschen'),
                                Icon::create(
                                    'trash',
                                    Icon::ROLE_CLICKABLE ,
                                    ['title'        => _('Studiengangteil löschen'),
                                     'formaction'   => $controller->url_for('/delete/' . $stgteil->getId()),
                                     'data-confirm' => sprintf(_('Wollen Sie wirklich den Studiengangteil "%s" löschen?'), htmlReady($stgteil->getDisplayName()))]
                                ))
                            ?>
                        <? endif; ?>
                        <?= $actionMenu->render() ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
</td>
