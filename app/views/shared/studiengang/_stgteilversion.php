<h2><?= htmlReady($version->getDisplayName()) ?></h2>
<table class="default  mvv-modul-details" id="<?= $version->id ?>" data-mvv-id="<?= $version->id; ?>" data-mvv-type="stgteilversion">
    <tbody>
        <tr>
            <td><strong><?= _('Gültigkeit') ?></strong></td>
            <td nowrap data-mvv-field="mvv_stgteilversion.start_sem mvv_stgteilversion.beschlussdatum">
                <?= _('von Semester:') ?>
                <? $sem = Semester::find($version->start_sem) ?>
                <?= htmlReady($sem->name) ?>
                <br>
                <?= _('Beschlussdatum:') ?>          
                <?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>    
            </td>
            <td nowrap data-mvv-field="mvv_stgteilversion.end_sem mvv_stgteilversion.fassung_typ">                
                <?= _('bis Semester:') ?>                
                <? if ($version->end_sem != "") : ?>
                    <? $sem = Semester::find($version->end_sem) ?>
                    <?= htmlReady($sem->name) ?>
                <? else : ?>
                    <?= _('unbegrenzt gültig') ?>
                <? endif; ?>  
                <br>
                <?= _('Fassung:') ?> 
                <?= htmlReady($version->fassung_nr) ?>. 
                <?= ($version->fassung_typ === '0' ? '--' : $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$version->fassung_typ]['name']) ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Code') ?></strong></td>
            <td colspan=2 data-mvv-field="mvv_stgteilversion.code">
                <?= htmlReady($version->code) ?>
            </td>    
        </tr>
        <tr>
            <td><strong><?= _('Beschreibung') ?></strong></td>
            <td data-mvv-field="mvv_stgteilversion.beschreibung">
                <?= formatReady($version->beschreibung) ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Status der Bearbeitung') ?></strong></td>
            <td data-mvv-field="mvv_stgteilversion.stat">
                <?= $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['name'] ?>
            </td>
            <td>
                <?= formatReady($version->kommentar_status) ?>
            </td>
        </tr>
    </tbody>
</table>