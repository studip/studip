<? 
$abschnitte = StgteilAbschnitt::findByStgteilVersion($version->getId());
$abschnitteData = [];
$fachsemesterData = [];
foreach ($abschnitte as $abschnitt) {
    $abschnitteData[$abschnitt->getId()] = [
            'name' => $abschnitt->getDisplayName(),
            'creditPoints' => $abschnitt->kp,
            'zwischenUeberschrift' => $abschnitt->ueberschrift,
            'kommentar' => $abschnitt->kommentar,
            'module' => [],
            'rowspan' => 0
    ];
    //$module = Modul::findByStgteilAbschnitt($abschnitt->getId());
    $abschnitt_module = $abschnitt->getModulAssignments();
    foreach ($abschnitt_module as $abschnitt_modul) {

        $abschnitteData[$abschnitt->getId()]['module'][$abschnitt_modul->modul->getId()] = [
                'name' => $abschnitt_modul->getDisplayName(),
                'modulTeile' => []
        ];
        
        foreach ($abschnitt_modul->modul->modulteile as $teil) {
            $fachSemester = $abschnitt_modul->getAllFachSemester($teil->getId());

            $abschnitteData[$abschnitt->getId()]['module'][$abschnitt_modul->modul->getId()]['modulTeile'][$teil->getId()] = [
                    'name' => $teil->getDisplayName(),
                    'fachsemester' => []
            ];
            $abschnitteData[$abschnitt->getId()]['rowspan']++;
            foreach ($fachSemester as $fachsem) {
                $fachsemesterData[$fachsem->fachsemester] = $fachsem->fachsemester;
                $abschnitteData[$abschnitt->getId()]['module'][$abschnitt_modul->modul->getId()]['modulTeile'][$teil->getId()]['fachsemester'][$fachsem->fachsemester] = $fachsem->differenzierung;
            }
        }
    }
}
?>
<h2><?= _('Liste der Studiengangteilabschnitte') ?></h2>
<dl class="mvv-form" >
<? foreach ($abschnitteData as $abschnitt_id => $abschnitt): ?>
    <span data-mvv-id="<?= $abschnitt_id; ?>" data-mvv-type="stgteilabschnitt">
    <? $displayedAbschnittName = false; ?>
    <? $ueberschrift = (mb_strlen($abschnitt['zwischenUeberschrift'])) ?>
    <? if (!$ueberschrift): ?>
        <dt>
            <span data-mvv-field="mvv_stgteilabschnitt mvv_stgteilabschnitt.name"><?= $abschnitt['name'] ?></span> <span data-mvv-field="mvv_stgteilabschnitt.kp"><?= $abschnitt['creditPoints'] ? $abschnitt['creditPoints'] . 'CP' : '' ?></span>
        </dt>
        <dd>
            <? if (trim($abschnitt['kommentar'])) : ?>
            <b><?= _('Kommentar:') ?></b>
            <span data-mvv-field="mvv_stgteilabschnitt.kommentar"><?= formatReady($abschnitt['kommentar']) ?></span>
            <? endif; ?>
        <? if (!empty($abschnitt['module'])) : ?>
        <table class="mvv-modul-details">
            <thead>
                <tr>
                    <th rowspan="2"><?= _('Modul') ?></th>
                    <th rowspan="2"><?= _('Modulteil') ?></th>
                    <th colspan="<?= count($fachsemesterData) ?>" align="center"><?= _('Semester') ?></th>
                </tr>
                <tr>
                <? foreach ($fachsemesterData as $fachsemester) : ?>
                    <th ><?= $fachsemester ?></th>
                <? endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <? foreach ($abschnitt['module'] as $modul_id => $modul) : ?>
                <? $displayedModulName = false; ?>
                <? foreach ($modul['modulTeile'] as $modulTeil_id => $modulTeil): ?>
                <tr data-mvv-id="<?= $modulTeil_id; ?>" data-mvv-type="modulteil">
                    <? if (!$displayedModulName) : ?>
                        <? $displayedModulName = true; ?>
                    <td rowspan="<?= count($modul['modulTeile']) ?>"  data-mvv-field="mvv_modul_deskriptor.bezeichnung mvv_modul_deskriptor.start mvv_modul_deskriptor.end">
                        <a data-dialog href="<?= URLHelper::getLink('/shared/modul/overview/' . $modul_id) ?>">
                            <? // Anzeige der alternativen Bezeichnung aus mvv_stgteilabschnitt_modul ?>
                            <? $abschnitt_modul = StgteilabschnittModul::findOneBySQL('abschnitt_id = ? AND modul_id = ?', [$abschnitt_id, $modul_id]) ?>
                            <?= htmlReady($abschnitt_modul->getDisplayName()) ?>
                        </a>
                        <a data-dialog title="<?= htmlReady($modul['name']) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= URLHelper::getLink('shared/modul/description/' . $modul_id) ?>">
                            <?= Icon::create('info-circle', 'clickable', [])->asImg(); ?>
                        </a>
                    </td>
                    <? endif;?>   
                    <td><?= htmlReady($modulTeil['name']) ?> </td>
                    <? foreach ($fachsemesterData as $i => $fachsemester) : ?>
                        <? $typ = isset($modulTeil['fachsemester'][$fachsemester]) ? $modulTeil['fachsemester'][$fachsemester] : null; ?>
                        <? if ($typ == 'kann') : ?>
                        <td data-mvv-field="mvv_modulteil_stgteilabschnitt.differenzierung" data-mvv-index="<?= $i; ?>" data-mvv-coid="<?= $abschnitt_id; ?>" class="type kann">o_</td>
                        <? elseif ($typ == 'soll') : ?>
                        <td data-mvv-field="mvv_modulteil_stgteilabschnitt.differenzierung" data-mvv-index="<?= $i; ?>" data-mvv-coid="<?= $abschnitt_id; ?>" class="type soll">+_</td>
                        <? else : ?>
                        <td data-mvv-field="mvv_modulteil_stgteilabschnitt.differenzierung" data-mvv-index="<?= $i; ?>" data-mvv-coid="<?= $abschnitt_id; ?>" class="type">&nbsp;</td>
                        <? endif; ?>
                    <? endforeach; ?>
                   </tr>  
                <? endforeach; ?>
            <? endforeach; ?>
            </tbody>
        </table>
        <? endif; ?>
        </dd>
    <? else : ?>  
        <dt><?= htmlReady($abschnitt['zwischenUeberschrift']) ?></dt>
        <? if (trim($abschnitt['kommentar'])) : ?>
        <dd data-mvv-field="mvv_stgteilabschnitt.kommentar">
            <b><?= _('Kommentar:') ?></b>
            <div><?= formatReady($abschnitt['kommentar']) ?></div>
        </dd>
        <? endif; ?>
    <? endif; ?>
    </span>
<? endforeach; ?>
</dl>