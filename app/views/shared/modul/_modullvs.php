<table class="mvv-modul-details default nohover">
    <thead>
        <tr>
            <th><?= _('Modulveran&shy;staltung') ?></th>
            <th><?= _('Lehrveranstaltungs&shy;form') ?></th>
            <th><?= _('Veranstaltungs&shy;titel') ?></th>
            <th><?= _('SWS') ?></th>
            <th><?= _('Workload Präsenz') ?></th>
            <th><?= _('Workload Vor- / Nach&shy;bereitung') ?></th>
            <th><?= _('Workload selbstge&shy;staltete Arbeit') ?></th>
            <th><?= _('Workload Prüfung incl. Vorbereitung') ?></th>
            <th><?= _('Workload Summe') ?></th>
        </tr>
    </thead>
    <tbody>
        <? $wlSelbst = 0; ?>
        <? $wlPruef = 0; ?>
        <? $modulSumme = 0; ?>
        <? $nummer_modulteil = 1; ?>
        <? foreach ($modul->modulteile as $modulTeil): ?>
            <? $modulTeilDeskriptor = $modulTeil->getDeskriptor($display_language);
            // Für die Kenntlichmachung der Modulteile in Listen die Nummer des
            // Modulteils und den ausgewählten Namen verwenden.
            // Ist keine Nummer vorhanden, dann Durchnummerieren und Standard-
            // Bezeichnung verwenden.
            if (trim($modulTeil->nummer)) {
                $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulTeil->num_bezeichnung]['name'];
                $name_kurz = sprintf('%s %d', $num_bezeichnung, $modulTeil->nummer);
            } else {
                $num_bezeichnung_default = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['default'];
                $name_kurz = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$num_bezeichnung_default]['name']
                        . ' ' . $nummer_modulteil;
                $nummer_modulteil++;
            } ?>
            <? $wlSelbst += $modulTeil->wl_selbst; ?>
            <? $wlPruef += $modulTeil->wl_pruef; ?>
            <? $modulTeilSumme = $modulTeil->wl_praesenz + $modulTeil->wl_bereitung + $modulTeil->wl_selbst + $modulTeil->wl_pruef; ?>
            <? $modulSumme += $modulTeilSumme; ?>
            <? if ($show_synopse) : ?>
            <tr data-mvv-id="<?= $modulTeil->getId(); ?>" data-mvv-type="modulteil">
                <td data-mvv-field="mvv_modulteil.nummer mvv_modulteil.num_bezeichnung"><strong><?= htmlReady($name_kurz) ?></strong></td>
                <td data-mvv-field="mvv_modulteil.lernlehrform"><?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulTeil->lernlehrform]['name'] ?></td>
                <td data-mvv-field="mvv_modulteil_deskriptor.bezeichnung"><?= htmlReady($modulTeilDeskriptor->bezeichnung) ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.sws"><?= htmlReady($modulTeil->sws) ?: '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_praesenz mvv_modulteil_deskriptor.kommentar_wl_praesenz"><?= $modulTeil->wl_praesenz ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_praesenz) ? sprintf(' (%s)', formatReady($modulTeilDeskriptor->kommentar_wl_praesenz)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_bereitung mvv_modulteil_deskriptor.kommentar_wl_bereitung"><?= $modulTeil->wl_bereitung ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_bereitung) ? sprintf(' (%s)', formatReady($modulTeilDeskriptor->kommentar_wl_bereitung)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_selbst mvv_modulteil_deskriptor.kommentar_wl_selbst"><?= $modulTeil->wl_selbst ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_selbst) ? sprintf(' (%s)', formatReady($modulTeilDeskriptor->kommentar_wl_selbst)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_pruef mvv_modulteil_deskriptor.kommentar_wl_pruef"><?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_pruef) ? sprintf(' (%s)',formatReady($modulTeilDeskriptor->kommentar_wl_pruef)) : '' ?><?= $modulTeil->wl_pruef ?></td>
                <td style="text-align: right;"><?= $modulTeilSumme ?></td>
            </tr>
            <? else : ?>
            <tr data-mvv-id="<?= $modulTeil->getId(); ?>" data-mvv-type="modulteil">
                <td data-mvv-field="mvv_modulteil.nummer mvv_modulteil.num_bezeichnung"><strong><?= htmlReady($name_kurz) ?></strong></td>
                <td data-mvv-field="mvv_modulteil.lernlehrform"><?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulTeil->lernlehrform]['name'] ?></td>
                <td data-mvv-field="mvv_modulteil_deskriptor.bezeichnung"><?= htmlReady($modulTeilDeskriptor->bezeichnung) ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.sws"><?= htmlReady($modulTeil->sws) ?: '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_praesenz mvv_modulteil_deskriptor.kommentar_wl_praesenz"><?= $modulTeil->wl_praesenz ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_praesenz) ? tooltipIcon(formatReady($modulTeilDeskriptor->kommentar_wl_praesenz)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_bereitung mvv_modulteil_deskriptor.kommentar_wl_bereitung"><?= $modulTeil->wl_bereitung ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_bereitung) ? tooltipIcon(formatReady($modulTeilDeskriptor->kommentar_wl_bereitung)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_selbst mvv_modulteil_deskriptor.kommentar_wl_selbst"><?= $modulTeil->wl_selbst ?> <?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_selbst) ? tooltipIcon(formatReady($modulTeilDeskriptor->kommentar_wl_selbst)) : '' ?></td>
                <td style="text-align: right;" data-mvv-field="mvv_modulteil.wl_pruef mvv_modulteil_deskriptor.kommentar_wl_pruef"><?= MVVController::trim($modulTeilDeskriptor->kommentar_wl_pruef) ? tooltipIcon(formatReady($modulTeilDeskriptor->kommentar_wl_pruef)) : '' ?><?= $modulTeil->wl_pruef ?></td>
                <td style="text-align: right;"><?= $modulTeilSumme ?></td>
            </tr>
            <? endif; ?>
        <? endforeach; ?>
        <?
        $modulWLSumme = $modul->wl_selbst + $modul->wl_pruef;
        $modulSumme += $modulWLSumme;
        ?>
        <? if ($modulWLSumme > 0) : ?>
        <tr>
            <td colspan="6"><strong><?= _('Workload modulbezogen') ?></strong></td>
            <td style="text-align: right;"><?= htmlReady($modul->wl_selbst) ?></td>
            <td style="text-align: right;"><?= htmlReady($modul->wl_pruef) ?></td>
            <td style="text-align: right;"><?= $modulWLSumme ?></td>
        </tr>
        <? endif; ?>
        <tr>
            <td colspan="8"><strong><?= _('Workload Modul insgesamt') ?></strong></td>
            <td style="text-align: right;"><?= $modulSumme ?></td>
        </tr>
    </tbody>
</table>
