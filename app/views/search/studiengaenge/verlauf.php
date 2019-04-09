<div>
<?= $this->render_partial('search/breadcrumb') ?>
</div>
<? if ($studiengangTeilName) : ?>
    <? $max_fachsemester = count($fachsemesterData) ? max($fachsemesterData) : 0; ?>
    <table class="mvv-modul-details default nohover">
        <caption>
            <?= htmlReady($studiengangTeilName) ?>
    <? if ($studiengang && $stgTeilBez) : ?>
    <h3><?= sprintf(_('%s im Studiengang %s'), htmlReady($stgTeilBez->getDisplayName()), htmlReady($studiengang->getDisplayName(ModuleManagementModel::DISPLAY_ABSCHLUSS))) ?></h3>
    <? endif; ?>
    <? $current_version = $versionen->findOneBy('id', $cur_version_id); ?>
    <? if ($current_version) : ?>
        <h4><?= $current_version->getDisplayName(); ?></h4>
    <? else : ?>
        <h4><?= htmlReady($versionen->first()->getDisplayName()) ?></h4>
    <? endif; ?>
        </caption>
        <thead>
            <tr >
                <th rowspan="2"><?= _('Name / CP') ?></th>
                <th rowspan="2"><?= _('Modul') ?></th>
                <th rowspan="2"><?= _('Modulteil') ?></th>
                <? if ($max_fachsemester) : ?>
                <th colspan="<?= $max_fachsemester ?>" align="center"><?= _('Semester') ?></th>
                <? endif; ?>
            </tr>
            <tr>
            <? for ($i = 1; $i <= $max_fachsemester; $i++) : ?>
                <th><?= $i ?></th>
            <? endfor; ?>
    		</tr>
        </thead>
        <tbody>
            <? foreach ($abschnitteData as $abschnitt_id => $abschnitt): ?>
                <? $displayedAbschnittName = false; ?>
                <? $ueberschrift = (mb_strlen($abschnitt['zwischenUeberschrift'])) ?>
                <?// if (!$ueberschrift): ?>
                <? if ($ueberschrift): ?>
                	<tr class="table_header">
                        <td colspan="<?= $max_fachsemester + 3 ?>"><?= htmlReady($abschnitt['zwischenUeberschrift']) ?></td>
                    </tr>
                <? endif; ?>
                           
                <? foreach ($abschnitt['module'] as $modul_id => $modul): ?>
                    <? $displayedModulName = false; ?>

                    <? foreach ($modul['modulTeile'] as $modulTeil_id => $modulTeil): ?>
                        <? $displayedModulTeilName = false; ?>
                        <tr>
                            <? if (!$displayedAbschnittName) : ?>
                                <? $displayedAbschnittName = true; ?>
                                <td rowspan="<?= $abschnitt['rowspan'] ?: 1 ?>">
                                    <?= htmlReady($abschnitt['name']) ?><br/><?= $abschnitt['creditPoints'] ? $abschnitt['creditPoints'] . ' ' . _('CP') : '' ?>
                                    <? if (trim($abschnitt['kommentar'])) : ?>
                                        <a data-dialog title="<?= sprintf(_('%s (Kommentar)'), htmlReady($abschnitt['name'])) ?>" href="<?= $controller->url_for('search/studiengaenge/kommentar', $abschnitt_id) ?>">
                                            <?= Icon::create('item', 'clickable', ['title' => _('Zusatzinformationen zum Studiengangsabschnitt')])->asImg(); ?>
                                        </a>
                                    <? endif; ?>
                                </td>
                            <? endif; ?>
                            <? if (!$displayedModulName) : ?>
                                <? $displayedModulName = true; ?>
                                <td rowspan="<?= count($modul['modulTeile']) ?>">
                                   <? // Anzeige der alternativen Bezeichnung aus mvv_stgteilabschnitt_modul ?>
                                    <? $abschnitt_modul = StgteilabschnittModul::findOneBySQL('`abschnitt_id` = ? AND `modul_id` = ?', [$abschnitt_id, $modul_id]); ?>
                                    <a data-dialog title="<?= htmlReady($modul['name']) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= $controller->url_for('shared/modul/description/' . $modul_id, ['display_language' => ModuleManagementModel::getLanguage()]) ?>">
                                        <?= Icon::create('log', 'clickable', ['title' => _('Vollständige Modulbeschreibung')])->asImg(); ?>
                                    </a>
                                    <? if($modul['veranstaltungen']):?>                          
                                    <a data-dialog href="<?= $controller->url_for('shared/modul/overview', $modul_id, $active_sem->getId(), ['display_language' => ModuleManagementModel::getLanguage()])  ?>">
                                        <?= htmlReady($abschnitt_modul->getDisplayName()) ?>
                                    </a>
                                    <? else: ?>
                                        <?= htmlReady($abschnitt_modul->getDisplayName()) ?>
                                    <? endif; ?>
                                </td>
                            <? endif; ?>
                            <td><?= htmlReady($modulTeil['name']) ?> </td>
                            <? for ($i = 1; $i <= $max_fachsemester; $i++) : ?>
                                <? $fachsemester_typ = $GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$modulTeil['fachsemester'][$fachsemesterData[$i]]] ?>
                                <? if ($fachsemester_typ['visible']) : ?>
                            <td class="mvv-type-<?= $modulTeil['fachsemester'][$fachsemesterData[$i]] ?>"><pan title="<? printf(_('%s Semester (%s)'), $i . ModuleManagementModel::getLocaleOrdinalNumberSuffix($i), $fachsemester_typ['name']) ?>"><?= $fachsemester_typ['icon'] ?></span></td>
                                <? else : ?>
                                <td class="mvv-type">&nbsp;</td>
                                <? endif; ?>
                            <? endfor; ?>
                        </tr>
                    <? endforeach; ?>
                <? endforeach; ?>
            <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
