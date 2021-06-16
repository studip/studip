<h1><?= htmlReady($studiengang->getDisplayName()) ?></h1>
<table class="default mvv-modul-details" id="<?= $studiengang->id ?>" data-mvv-id="<?= $studiengang->id; ?>" data-mvv-type="studiengang">
    <tbody>
        <tr>
            <td>
                <strong><?= _('Name des Studiengangs') ?></strong>
            </td>
            <td data-mvv-field="mvv_studiengang.name">
                <?= htmlReady($studiengang->name) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Kurzbezeichnung') ?></strong>
            </td>
            <td data-mvv-field="mvv_studiengang.name_kurz">
                <?= htmlReady($studiengang->name_kurz) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Gültigkeit') ?></strong>
            </td>
            <td nowrap>
                <?= _('von Semester:') ?>
                <? $sem = Semester::find($studiengang->start) ?>
                <span data-mvv-field="mvv_studiengang.start">
                    <?= htmlReady($sem->name) ?>
                </span>
                <br>
                <?= _('Beschlussdatum:') ?>
                <span data-mvv-field="mvv_studiengang.beschlussdatum">
                    <?= ($studiengang->beschlussdatum ? strftime('%d.%m.%Y', $studiengang->beschlussdatum) : '') ?>
                </span>
            </td>
            <td nowrap>
                <?= _('bis Semester:') ?>
                <? if ($studiengang->end != "") : ?>
                    <? $sem = Semester::find($studiengang->end) ?>
                    <span data-mvv-field="mvv_studiengang.end">
                        <?= htmlReady($sem->name) ?>
                    </span>
                <? else : ?>
                    <?= _('unbegrenzt gültig') ?>
                <? endif; ?>
                <br>
                <?= _('Fassung:') ?>
                <span data-mvv-field="mvv_studiengang.fassung_nr">
                    <?= htmlReady($studiengang->fassung_nr) ?>.
                </span>
                <span data-mvv-field="mvv_studiengang.fassung_typ">
                <?= ($studiengang->fassung_typ === '0' ? '--' : htmlReady($GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'][$studiengang->fassung_typ]['name'])) ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Beschreibung') ?></strong>
            </td>
            <td data-mvv-field="mvv_studiengang.beschreibung">
                <?= formatReady($studiengang->beschreibung) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Studiengangteile') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.typ">
                <? if($studiengang->typ !== 'mehrfach') :?>
                    <?= _('Diesem Studiengang wird ein Fach direkt zugewiesen') ?>
                <? else: ?>
                    <?= _('Diesem Studiengang können mehrere Studiengangteile zugewiesen werden.') ?>
                <? endif;?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Abschluss') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.abschluss_id">
                <? $abschluss = Abschluss::find($studiengang->abschluss_id)?>
                <?= htmlReady($studiengang->abschluss->getDisplayName()) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Verantwortliche Einrichtung') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.institut_id">
                <? if ($studiengang->responsible_institute) : ?>
                    <?= htmlReady($studiengang->responsible_institute->getDisplayName()) ?>
                <? endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Status der Bearbeitung') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.stat">
                <?= $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'][$studiengang->stat]['name'] ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Kommentar Status') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.schlagworte">
                <?= formatReady($studiengang->kommentar_status) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Schlagworte') ?></strong>
            </td>
            <td colspan="2" data-mvv-field="mvv_studiengang.schlagworte">
                <?= htmlReady($studiengang->schlagworte) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Dauer') ?>:</strong>
            </td>
            <td>
                <? printf('%s Semester', htmlReady($studiengang->studienzeit)) ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?= _('Abschlussgrad') ?>:</strong>
            </td>
            <td>
                <?= htmlReady($GLOBALS['MVV_STUDIENGANG']['ABSCHLUSSGRAD']['values'][$studiengang->abschlussgrad]['name']) ?>
            </td>
        </tr>
        <? if (count($studiengang->languages)) : ?>
            <tr>
                <td>
                    <strong><?= _('Sprache') ?>:</strong>
                </td>
                <td>
                    <?= htmlReady(implode('/', $studiengang->languages->pluck('display_name'))) ?>
                </td>
            </tr>
        <? endif ?>
        
        <? foreach ($studiengang->datafields as $df) : ?>
            <? if (mb_strpos($df->datafield->object_class, 'settings') !== false
                    && !$df->isNew()) : ?>
                <? $tdf = $df->getTypedDatafield(); ?>
                <? if ($tdf->isVisible() && trim($tdf->getValue())) : ?>
                    <tr>
                        <td>
                            <strong><?= htmlReady($tdf->getName()) ?></strong>
                        </td>
                        <td colspan="2" data-mvv-field="mvv_studiengang.<?= $tdf->id ?>">
                            <?= $tdf->getDisplayValue() ?>
                        </td>
                    </tr>
                <? endif ?>
            <? endif; ?>
        <? endforeach; ?>
        <? foreach ($studiengang->datafields as $df) : ?>
            <? if (mb_strpos($df->datafield->object_class, 'info') !== false
                    && !$df->isNew()) : ?>
                <? $tdf = $df->getTypedDatafield(); ?>
                <? if ($tdf->isVisible() && trim($tdf->getValue())) : ?>
                    <tr>
                        <td>
                            <strong><?= htmlReady($tdf->getName()) ?></strong>
                        </td>
                        <td colspan="2" data-mvv-field="mvv_studiengang.<?= $tdf->id ?>">
                            <?= $tdf->getDisplayValue() ?>
                        </td>
                    </tr>
                <? endif ?>
            <? endif; ?>
        <? endforeach; ?>
        <? if (is_array($all_contacts) && count($all_contacts)) : ?>
            <tr>
                <td>
                    <strong><?= _('Ihre AnsprechpartnerInnen') ?></strong>
                </td>
                <td>
                <? foreach ($all_contacts as $category => $contacts) : ?>
                    <? if ($GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'][$category]['visible']) : ?>
                        <strong><?= htmlReady($GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'][$category]['name']) ?></strong>
                        <ul>
                            <? foreach ($contacts as $contact) : ?>
                            <li>
                                <?= htmlReady($contact['name']) ?>
                            </li>
                            <? endforeach; ?>
                        </ul>
                    <? endif; ?>
                <? endforeach; ?>
                </td>
            </tr>
        <? endif ?>
        <? if (is_array($all_documents) && count($all_documents)) : ?>
            <tr>
                <td>
                    <strong><?= _('Dokumente: Ordnungen, Formulare, Informationen') ?></strong>
                </td>
                <td>
                <? foreach ($all_documents as $category => $files) : ?>
                    <? if ($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['visible']) : ?>
                        <strong><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$category]['name']) ?></strong>
                        <ul>
                            <? foreach ($files as $file) : ?>
                            <li>
                                <? if ($file['is_link']) : ?>
                                    <a href="<?= $file['url'] ?>" target="_blank"><?= htmlReady($file['name']) ?></a>
                                <? else : ?>
                                <?= htmlReady($file['name']) ?>
                                <a href="<?= $file['url'] ?>"><?= htmlReady($file['extension']) ?></a>
                                <? endif ?>
                            </li>
                            <? endforeach; ?>
                        </ul>
                    <? endif; ?>
                <? endforeach; ?>
                </td>
            </tr>
        <? endif ?>
        <? if (count($all_aufbaustgs)) : ?>
            <tr>
                <td>
                    <strong><?= _('Aufbau-/Kombinationsstudiengänge') ?></strong>
                </td>
                <td>
                <? foreach ($all_aufbaustgs as $typ => $aufbaustgs) : ?>
                    <strong><?= htmlReady($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'][$typ]['name']) ?></strong>
                    <? if (count($aufbaustgs) > 4) : ?>
                        <input type="checkbox" class="mvv-cb-more-items" id="cb_more_aufbaustgs" checked>
                    <? endif; ?>
                    <ul>
                        <? foreach ($aufbaustgs as $i => $aufbaustg) : ?>
                        <li>
                            <?= htmlReady($aufbaustg->getDisplayName()) ?>
                            <? if ($i == 3) : ?>
                                <label class="cb-more-items" for="cb_more_aufbaustgs"><?= _('mehr...') ?></label>
                            <? endif; ?>
                        </li>
                        <? endforeach ?>
                    </ul>
                <? endforeach ?>
                </td>
            </tr>
        <? endif; ?>
    </tbody>
</table>