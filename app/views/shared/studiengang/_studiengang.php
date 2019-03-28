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
    </tbody>
</table>