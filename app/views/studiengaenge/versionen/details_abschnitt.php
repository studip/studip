<td colspan="2">
    <form method="post">
        <?= CSRFProtection::tokenTag(); ?>
        <table id="module_<?= $abschnitt->id ?>" class="default collapsable sortable">
            <colgroup>
                <col>
                <col style="width: 1%">
            </colgroup>
        <? foreach ($assignments as $assignment) : ?>
            <tbody class="<?= count($assignment->modul->modulteile) ? '' : 'empty' ?> <?= ($modul_id == $assignment->modul->id ? 'not-collapsed' : 'collapsed') ?><?= MvvPerm::haveFieldPermPosition($assignment, MvvPerm::PERM_WRITE) ? ' sort_items' : '' ?>" id="<?= $assignment->id ?>">
                <tr id="modul_<?= $assignment->modul->id ?>" class="header-row">
                    <td class="toggle-indicator">
                        <? if (count($assignment->modul->modulteile)) : ?>
                        <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/modulteile', $assignment->id) ?>"><?= htmlReady($assignment->getDisplayName()) ?></a>
                        <? else : ?>
                        <?= htmlReady($assignment->getDisplayName()) ?>
                        <? endif; ?>
                        <? if (trim($assignment->modulcode) || trim($assignment->bezeichnung)) : ?>
                        <span style="color: #636a71; font-size: smaller;">
                            <? printf(_('Orig.: %s - %s'),
                                    htmlReady(trim($assignment->modul->code) ?: trim($assignment->modulcode)),
                                    htmlReady(trim($assignment->modul->getDeskriptor()->bezeichnung) ?: trim($assignment->bezeichnung))) ?>
                        <? endif; ?>
                    </td>
                    <td class="dont-hide actions" style="white-space: nowrap;">
                    <? if (MvvPerm::haveFieldPermModul_zuordnungen($abschnitt, MvvPerm::PERM_WRITE)) : ?>
                        <a data-dialog="" href="<?= $controller->link_for('/modul_zuordnung', $assignment->id) ?>">
                            <?= Icon::create('edit', Icon::ROLE_CLICKABLE , tooltip2(_('Modulzuordnung bearbeiten')))->asImg(); ?>
                        </a>
                    <? endif; ?>
                    <? if (MvvPerm::havePermCreate($assignment)) : ?>
                        <?= Icon::create('trash', Icon::ROLE_CLICKABLE , tooltip2(_('Modulzuordnung löschen')))
                            ->asInput([
                                'name'         => 'delete',
                                'formaction'   => $controller->url_for('/delete_modul', $assignment->abschnitt_id, $assignment->modul_id),
                                'data-confirm' => sprintf(
                                        _('Wollen Sie die Zuordnung des Moduls "%s" zum Studiengangteil-Abschnitt "%s" wirklich löschen?'),
                                        htmlReady($assignment->modul->getDisplayName()),
                                        htmlReady($abschnitt->getDisplayName())
                                )
                            ]); ?>
                    <? endif; ?>
                    </td>
                </tr>
                <? if ($modul_id == $assignment->modul->id) : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/versionen/modulteile', ['modul' => $assignment->modul, 'abschnitt_id' => $assignment->abschnitt_id, 'assignment' => $assignment]) ?>
                </tr>
                <? endif; ?>
            </tbody>
        <? endforeach; TextHelper::reset_cycle(); ?>
        <? if (MvvPerm::haveFieldPermModul_zuordnungen($abschnitt, MvvPerm::PERM_CREATE)) : ?>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?= _('Modul hinzufügen') ?>
                        <?= CSRFProtection::tokenTag() ?>
                        <?= $search_modul_abschnitt->render(); ?>
                        <?= Icon::create('search', Icon::ROLE_CLICKABLE, ['title' => _('Modul suchen'), 'name' => 'search_stgteil', 'data-qs_name' => $search_modul_abschnitt->getId(), 'data-qs_id' => $qs_search_modul_abschnitt_id, 'data-qs_submit' => 'no',  'class' => 'mvv-qs-button'])->asInput(); ?>
                        <input type="hidden" name="abschnitt_id" value="<?= $abschnitt->id ?>">
                        <?= Icon::create('accept',  Icon::ROLE_CLICKABLE , tooltip2(_('Modul hinzufügen')))
                            ->asInput(
                                [
                                    'formaction'   => $controller->url_for('/add_modul', $version->id),
                                    'name'         => 'add_modul',
                                    'class'        => 'text-top mvv-submit'
                                ]
                            ); ?>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
        </table>
    </form>
</td>
