<? $modulDeskriptor = $modul->getDeskriptor($display_language); ?>
<table class="mvv-modul-details default nohover" data-mvv-id="<?= $modul->getId(); ?>" data-mvv-type="modul">
    <colgroup>
        <col width="30%">
        <col width="70%">
    </colgroup>
    <thead>
        <tr>
            <th class="mvv-modul-details-head" data-mvv-field="mvv_modul.code"><?= htmlReady($modul->code) ?></th>
            <th class="mvv-modul-details-head" data-mvv-field="mvv_modul.kp" style="text-align: right;"><?= sprintf("%d CP", $modul->kp) ?></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: normal;">
                <? if ($show_synopse) : ?>
                <?=
                sprintf(_('In der Fassung des <b>%s</b>. Beschlusses vom <b>%s</b> (<b>%s</b>) / Version <b>%s</b>.'),
                    '<span data-mvv-field="mvv_modul.fassung_nr">' . htmlReady($modul->fassung_nr) . '</span>',
                    '<span data-mvv-field="mvv_modul.beschlussdatum">' . date('d.m.Y', $modul->beschlussdatum) . '</span>',
                    '<span data-mvv-field="mvv_modul.fassung_typ">' . htmlReady($GLOBALS['MVV_MODUL']['FASSUNG_TYP'][$modul->fassung_typ]['name']) . '</span>',
                    '<span data-mvv-field="mvv_modul.version">' . htmlReady($modul->version) . '</span>'
                )
                ?>
                <? else : ?>
                <?=
                sprintf(_('In der Fassung des <b>%s</b>. Beschlusses vom <b>%s</b>.'),
                    '<span data-mvv-field="mvv_modul.fassung_nr">' . htmlReady($modul->fassung_nr) . '</span>',
                    '<span data-mvv-field="mvv_modul.beschlussdatum">' . date('d.m.Y', $modul->beschlussdatum) . '</span>'
                )
                ?>
                <? endif; ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong><?= _('Modulbezeichnung') ?></strong></td>
            <td data-mvv-field="mvv_modul.bezeichnung"><?= htmlReady($modulDeskriptor->bezeichnung) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Modulcode') ?></strong></td>
            <td data-mvv-field="mvv_modul.code"><?= htmlReady($modul->code) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Semester der erstmaligen Durchführung') ?></strong></td>
            <td data-mvv-field="mvv_modul.start"><?= htmlReady($startSemester['name']) ?></td>
        </tr>
        <? if ($instituteName) : ?>
        <tr>
            <td><strong><?= _('Fachbereich/Institut') ?></strong></td>
            <td data-mvv-field="mvv_modul_inst"><?= htmlReady($instituteName) ?></td>
        </tr>
        <? endif; ?>
        <tr>
            <td><strong><?= _('Verwendet in Studiengängen / Semestern') ?></strong></td>
            <td>
                <? $trails = $modul->getTrails(['StgteilAbschnitt', 'StgteilVersion', 'Studiengang']); ?>
                <? if (count($trails)) : ?>
                    <? $pathes = $modul->getPathes($trails, ' > ') ?>
                    <? if (count($pathes) > 9) : ?>
                    <input type="checkbox" class="mvv-cb-more" id="cb_more_studycourses" checked>
                    <? endif; ?>
                    <ul>
                    <? foreach ($pathes as $i => $path) : ?>
                    <? $version = $trails[$i]['StgteilVersion']['version_id'];?>
                    <? $statement = DBManager::get()->prepare(
                             'SELECT `mvv_stgteilabschnitt_modul`.`abschnitt_id`  '
                           . 'FROM mvv_stgteilabschnitt_modul LEFT JOIN mvv_stgteilabschnitt USING(abschnitt_id) '
                           . 'WHERE modul_id = ? AND version_id = ?');
                       $statement->execute([$modul->getId(), $version]);
                       $res = $statement->fetchOne();
                       $affect_id = $res['abschnitt_id'];
                    ?>
                    <li data-mvv-field="mvv_stgteilabschnitt_modul" data-mvv-id="<?= $affect_id; ?>" data-mvv-cooid="<?= $modul->getId(); ?>">
                        <?= htmlReady($path)?>
                        <? if (!$download && (count($pathes) > 9 && $i == 4)) : ?>
                        <label class="cb-more-label" for="cb_more_studycourses"><?= _('mehr...') ?></label>
                        <? endif; ?>
                    </li>
                    <? endforeach; ?>
                    </ul>
                <? endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Modulverantwortliche/r') ?></strong></td>
            <td>
                <?
                $modulVerantwortung = [];
                foreach (ModulUser::findByModul($modul->getId()) as $users) {
                    foreach ($users as $user) {
                        if (!isset($modulVerantwortung[$user->gruppe])) {
                            $modulVerantwortung[$user->gruppe] = [
                                'name' => $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$user->gruppe]['name'],
                                'users' => []
                            ];
                        }
                        $modulVerantwortung[$user->gruppe]['users'][$user->user_id] = [
                            'name' => get_fullname($user->user_id),
                            'id' => $user->user_id
                        ];
                    }
                }
                ?>
                <? foreach ($modulVerantwortung as $gruppe): ?>
                    <? foreach ($gruppe['users'] as $user): ?>
                        <span data-mvv-field="mvv_modul_user" data-mvv-coid="<?= $user['id']; ?>"><?= htmlReady($user['name']) ?> (<?= htmlReady($gruppe['name']) ?>)</span><br>
                    <? endforeach; ?>
                <? endforeach; ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Teilnahmevoraussetzungen') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.voraussetzung"><?= formatReady($modulDeskriptor->voraussetzung) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Kompetenzziele') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.kompetenzziele"><?= formatReady($modulDeskriptor->kompetenzziele) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Modulinhalte') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.inhalte"><?= formatReady($modulDeskriptor->inhalte) ?></td>
        </tr>
        <? if ($type !== 3) : ?>
        <tr>
            <td><strong><?= ngettext('Lehrveranstaltungsform', 'Lehrveranstaltungsformen', count($modul->modulteile)) ?></strong></td>
            <td data-mvv-field="mvv_modulteil_deskriptor.lernlehrform">
            <? foreach ($modul->modulteile as $modulteil) : ?>
                <? if (trim($modulteil->lernlehrform)) : ?>
                <?= $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulteil->lernlehrform]['name'] ?>
                    <? if (trim($modulteil->sws) && trim($modulteil->sws) != '0') : ?>
                        (<?= htmlReady($modulteil->sws) . ' ' . _('SWS') ?>)
                    <? endif; ?>
                    <br>
                <? endif; ?>
            <? endforeach; ?>
            </td>
        </tr>
        <? endif; ?>
        <tr>
            <td><strong><?= ngettext('Unterrichtssprache', 'Unterrichtsprachen', sizeof($modul->languages)) ?></strong></td>
            <td data-mvv-field="mvv_modul_language">
                <?= htmlReady(implode(', ', $modul->languages->map(function ($m) { return $m->getDisplayName(); }))); ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Dauer in Semestern') ?></strong></td>
            <td data-mvv-field="mvv_modul.dauer"><?= htmlReady($modul->dauer) ?> <?= _('Semester') ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Angebotsrhythmus Modul') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.turnus"><?= htmlReady($modulDeskriptor->turnus) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Aufnahmekapazität Modul') ?></strong></td>
            <td data-mvv-field="mvv_modul.kapazitaet"><?= htmlReady(trim($modul->kapazitaet)) ?: _('unbegrenzt') ?> <?= MVVController::trim($modulDeskriptor->kommentar_kapazitaet) ? sprintf("(%s)", formatReady($modulDeskriptor->kommentar_kapazitaet)) : '' ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Prüfungsebene') ?></strong></td>
            <td data-mvv-field="mvv_modul.pruef_ebene"><?= htmlReady($pruefungsEbene) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Credit-Points') ?></strong></td>
            <td data-mvv-field="mvv_modul.kp"><?= sprintf("%d CP", htmlReady($modul->kp)) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Modulabschlussnote') ?></strong></td>
            <td>
                <? if ($type !== 3) : ?>
                    <? $nummer_modulteil = 1; ?>
                    <? $note = []; ?>
                    <? foreach ($modul->modulteile as $modulteil): ?>
                        <? // Für die Kenntlichmachung der Modulteile in Listen die Nummer des
                        // Modulteils und den ausgewählten Namen verwenden.
                        // Ist keine Nummer vorhanden, dann Durchnummerieren und Standard-
                        // Bezeichnung verwenden.
                        if (trim($modulteil->nummer)) {
                            $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulteil->num_bezeichnung]['name'];
                            $note[] = sprintf('%s %s: %s%%',
                                    '<span data-mvv-id="'. $modulteil->getId().'" data-mvv-type="modulteil">' .
                                    '<span data-mvv-field="mvv_modulteil.num_bezeichnung">' . htmlReady($num_bezeichnung) . '</span>',
                                    '<span data-mvv-field="mvv_modulteil.nummer">' . htmlReady($modulteil->nummer) . '</span>',
                                    '<span data-mvv-field="mvv_modulteil.anteil_note">' . htmlReady($modulteil->anteil_note) . '</span>' .
                                    '</span>'
                                    );
                        } else {
                            $num_bezeichnung_default = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['default'];
                            $note[] = sprintf('%s %d: %s%%',
                                    htmlReady($GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$num_bezeichnung_default]['name']),
                                    htmlReady($nummer_modulteil),
                                    htmlReady($modulteil->anteil_note)
                                    );
                            $nummer_modulteil++;
                        } ?>
                    <? endforeach; ?>
                    <? if (count($note)) : ?>
                        <?= implode('; ', $note) . '. ' ?>
                    <? endif; ?>
                <? endif; ?>
                <?= formatReady(trim($modulDeskriptor->kommentar_note)) ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Faktor der Modulnote für die Endnote des Studiengangs') ?></strong></td>
            <td data-mvv-field="mvv_modul.faktor_note"><?= htmlReady($modul->faktor_note) ?></td>
        </tr>
        <? if (trim($modulDeskriptor->kommentar)) : ?>
        <tr>
            <td><strong><?= _('Hinweise') ?></strong></td>
            <td data-mvv-field="mvv_modul_deskriptor.kommentar"><?= formatReady($modulDeskriptor->kommentar) ?></td>
        </tr>
        <? endif; ?>
        <? foreach ($modulDeskriptor->datafields as $entry) : ?>
        <? $df = $entry->getTypedDatafield(); ?>
        <tr>
            <td><strong><?= htmlReady($df->getName()) ?></strong></td>
            <td><?= $df->getDisplayValue(); ?></td>
        </tr>
        <? endforeach; ?>
    </tbody>
</table>
