<? $modulTeil = $modul->modulteile->first(); ?>
<? $modulTeilDeskriptor = $modulTeil->getDeskriptor($display_language); ?>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modulTeil->id; ?>" data-mvv-type="modulteil">
    <tbody>
        <? $modulTeilSumme = $modulTeil->wl_praesenz + $modulTeil->wl_bereitung + $modulTeil->wl_selbst + $modulTeil->wl_pruef ?>
        <tr>
            <td style="width: 30%;"><strong><?= _('Lehrveranstaltungsform') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.lernlehrform"><?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulTeil->lernlehrform]['name'] ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Veranstaltungstitel') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil_deskriptor.bezeichnung"><?= htmlReady($modulTeilDeskriptor->bezeichnung) ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('SWS') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.sws mvv_modulteil_deskriptor.sws_alternative"><?= $modulTeil->sws ?: '' ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Workload Präsenz') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.wl_praesenz mvv_modulteil_deskriptor.kommentar_wl_praesenz"><?= $modulTeil->wl_praesenz ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_praesenz) ? sprintf(" (%s)", formatReady($modulTeilDeskriptor->kommentar_wl_praesenz)) : '' ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Workload Vor- / Nachbereitung') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.wl_bereitung mvv_modulteil_deskriptor.kommentar_wl_bereitung"><?= $modulTeil->wl_bereitung ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_bereitung) ? sprintf(" (%s)", formatReady($modulTeilDeskriptor->kommentar_wl_bereitung)) : '' ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Workload selbstgestaltete Arbeit') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.wl_selbst mvv_modulteil_deskriptor.kommentar_wl_selbst"><?= $modulTeil->wl_selbst ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_selbst) ? sprintf(" (%s)", formatReady($modulTeilDeskriptor->kommentar_wl_selbst)) : '' ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Workload Prüfung incl. Vorbereitung') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.wl_pruef mvv_modulteil_deskriptor.kommentar_wl_pruef"><?= $modulTeil->wl_pruef ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_pruef) ? sprintf(" (%s)", formatReady($modulTeilDeskriptor->kommentar_wl_pruef)) : '' ?></td>
        </tr>  
        <tr>
            <td style="width: 30%;"><strong><?= _('Workload insgesamt') ?></strong></td>
            <td style="width: 70%;"><?= $modulTeilSumme ?></td>
        </tr>
        <? if ((int) $modul->wl_selbst) : ?>
            <tr>
                <td style="width: 30%;"><strong><?= _('Workload selbstgestaltete Arbeit (modulbezogen') ?></strong></td>
                <td style="width: 70%;" data-mvv-field="mvv_modul.wl_selbst mvv_modul_deskriptor.kommentar_wl_selbst"><?= $modul->wl_selbst ?> <?= MVVController::trim($modulDeskriptor->kommentar_wl_selbst) ? sprintf(" (%s)", formatReady($modulDeskriptor->kommentar_wl_selbst)) : '' ?></td>
            </tr>
        <? endif; ?>
        <? if ((int) $modul->wl_pruef) : ?>
            <tr>
                <td style="width: 30%;"><strong><?= _('Workload Prüfung incl. Vorbereitung (modulbezogen)') ?></strong></td>
                <td style="width: 70%;" data-mvv-field="mvv_modul.wl_pruef mvv_modul_deskriptor.kommentar_wl_pruef"><?= $modul->wl_pruef ?> <?= MVVController::trim($modulDeskriptor->kommentar_wl_pruef) ? sprintf(" (%s)", formatReady($modulDeskriptor->kommentar_wl_pruef)) : '' ?></td>
            </tr>
        <? endif; ?>
        <? if ($modul->wl_selbst + $modul->wl_pruef) : ?>
            <tr>
                <td style="width: 30%;"><strong><?= _('Workload Modul insgesamt') ?></strong></td>
                <td style="width: 70%;"><?= $modulTeilSumme + $modul->wl_selbst + $modul->wl_pruef ?></td>
            </tr>
        <? endif; ?>
    </tbody>
</table>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modulTeilDeskriptor->id; ?>" data-mvv-type="modulteil_deskriptor">
    <tbody>
        <? if (trim($modulTeilDeskriptor->pruef_vorleistung)) : ?>
        <tr>
            <td style="width: 30%;"><strong><?= _('Prüfungsvorleistung') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil_deskriptor.pruef_vorleistung"><?= formatReady($modulTeilDeskriptor->pruef_vorleistung) ?></td>
        </tr>
        <? endif; ?>
        <tr>
            <td style="width: 30%;"><strong><?= _('Prüfungsform') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil_deskriptor.pruef_leistung"><?= formatReady($modulTeilDeskriptor->pruef_leistung) ?></td>
        </tr>
    </tbody>
</table>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modulTeil->id; ?>" data-mvv-type="modulteil">
    <tbody>
        <tr>
            <td style="width: 30%;"><strong><?= _('Angebotsrhythmus') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.semester"><?= $GLOBALS['MVV_NAME_SEMESTER']['values'][$modulTeil->semester]['name'] ?></td>
        </tr>
        <tr>
            <td style="width: 30%;"><strong><?= _('Aufnahmekapazität') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.kapazitaet mvv_modulteil_deskriptor.kommentar_kapazitaet"><?= trim($modulTeil->kapazitaet) ?: _('unbegrenzt') ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_kapazitaet) ? sprintf("(%s)", formatReady($modulTeilDeskriptor->kommentar_kapazitaet)) : '' ?></td>
        </tr>
        <? if ($modulTeil->pflicht) : ?>
        <tr>
            <td style="width: 30%;"><strong><?= _('Anwesenheitspflicht') ?></strong></td>
            <td style="width: 70%;" data-mvv-field="mvv_modulteil.pflicht mvv_modulteil_deskriptor.kommentar_pflicht"><?= $modulTeil->pflicht ? _('Ja') : _('Nein') ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_pflicht) ? sprintf("(%s)", formatReady($modulTeilDeskriptor->kommentar_pflicht)) : '' ?></td>
        </tr>
        <? endif; ?>
    </tbody>
</table>
<? if (count($modulTeilDeskriptor->datafields)) : ?>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modulTeilDeskriptor->id; ?>" data-mvv-type="modulteil_deskriptor">
    <tbody>
        <? foreach ($modulTeilDeskriptor->datafields as $entry) : ?>
        <? $df = $entry->getTypedDatafield(); ?>
        <tr>
            <td style="width: 30%;"><strong><?= htmlReady($df->getName()) ?></strong></td>
            <td style="width: 70%;"><?= $df->getDisplayValue(); ?></td>
        </tr>
        <? endforeach; ?>
    </tbody>
</table>
<? endif; ?>
