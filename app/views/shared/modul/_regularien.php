<table class="mvv-modul-details default nohover">
    <thead>
        <tr>
            <th><?= _('Regularien') ?></th>
            <th><?= _('Teilnahme&shy;voraussetzungen') ?></th>
            <th><?= _('Angebots&shy;rhythmus') ?></th>
            <th><?= _('Anwesenheits&shy;pflicht') ?></th>
            <th><?= _('Gewicht an Modulnote in %') ?></th>
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
                <td style="vertical-align: top; font-weight: bold;" data-mvv-field="mvv_modulteil.nummer mvv_modulteil.num_bezeichnung"><?= $name_kurz ?></td>
                <td data-mvv-field="mvv_modulteil_deskriptor.voraussetzung"><?= formatReady($modulTeilDeskriptor->voraussetzung) ?></td>
                <td data-mvv-field="mvv_modulteil.semester"><?= $GLOBALS['MVV_NAME_SEMESTER']['values'][$modulTeil->semester]['name'] ?></td>
                <td data-mvv-field="mvv_modulteil.pflicht"><?= ($modulTeil->pflicht ? _('Ja') : _('Nein')) ?> <?= $modulTeilDeskriptor->kommentar_pflicht ? formatReady($modulTeilDeskriptor->kommentar_pflicht) : '' ?></td>
                <td data-mvv-field="mvv_modulteil.anteil_note"><?= $modulTeil->anteil_note ?>%</td>
            </tr>                  
        <? endforeach; ?>
    </tbody>
</table>