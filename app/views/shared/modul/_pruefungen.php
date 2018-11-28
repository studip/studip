<table class="mvv-modul-details default nohover">
    <thead>
        <tr>
            <th><?= _('Prüfung') ?></th>
            <th><?= _('Prüfungsvorleistung') ?></th>
            <th><?= _('Prüfungsform') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($modul->modulteile as $modulTeil): ?>
            <?
            $modulTeilDeskriptor = $modulTeil->getDeskriptor($display_language);
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
            }
            ?>
            <tr data-mvv-id="<?= $modulTeil->getId(); ?>" data-mvv-type="modulteil">
                <td style="vertical-align: top; font-weight: bold;" data-mvv-field="mvv_modulteil.num_bezeichnung mvv_modulteil.nummer"><?= htmlReady($name_kurz) ?></td>
                <td data-mvv-field="mvv_modulteil_deskriptor.pruef_vorleistung"><?= formatReady($modulTeilDeskriptor->pruef_vorleistung) ?></td>
                <td data-mvv-field="mvv_modulteil_deskriptor.pruef_leistung"><?= formatReady($modulTeilDeskriptor->pruef_leistung) ?></td>
            </tr>                  
        <? endforeach; ?>
        <tr data-mvv-id="<?= $modul->getId(); ?>" data-mvv-type="modul">
            <? $modulDeskriptor = $modul->getDeskriptor($display_language) ?>
            <td style="vertical-align: top; font-weight: bold;">
                <?= _('Gesamtmodul') ?>
            </td>
            <td data-mvv-field="mvv_modul_deskriptor.pruef_vorleistung"><?= formatReady($modulDeskriptor->pruef_vorleistung) ?></td>
            <td data-mvv-field="mvv_modul_deskriptor.pruef_leistung"><?= formatReady($modulDeskriptor->pruef_leistung) ?></td>
        </tr>
        <tr>
            <td style="vertical-align: top; font-weight: bold;">
                <?= _('Wiederholungsprüfung') ?>
            </td>
            <td colspan="3">
                <?= formatReady($modulDeskriptor->pruef_wiederholung) ?>
            </td>
        </tr>
    </tbody>
</table>