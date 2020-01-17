<table class="default nohover">
    <colgroup>
        <col width="30%">
        <col width="70%">
    </colgroup>
    <thead>
        <tr>
        <? if (trim($stg->name_kurz)) : ?>
            <th class="mvv-modul-details-head"><?= htmlReady($stg->name_kurz) ?></th>
            <th class="mvv-modul-details-head"><?= htmlReady($stg->getDisplayName()) ?></th>
        <? else : ?>
            <th class="mvv-modul-details-head" colspan="2"><?= htmlReady($stg->getDisplayName()) ?></th>
        <? endif; ?>
        </tr>
        <tr>
            <td colspan="2">
                <? printf(_('%s %s vom %s'), ModuleManagementModel::getLocaleOrdinalNumberSuffix($stg->fassung_nr),
                        htmlReady($GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP']['values'][$stg->fassung_typ]['name']),
                        strftime('%x', $stg->beschlussdatum));
                ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong><?= _('Name') ?></strong></td>
            <td><?= htmlReady($stg->getDisplayName()) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Abschluss') ?></strong></td>
            <td><?= htmlReady($stg->abschluss->getDisplayName()) ?></td>
        </tr>
        <tr>
            <td><strong><?= _('Gültigkeit') ?></strong></td>
            <td>
            <? $start_sem = Semester::find($stg->start) ?>
            <? $end_sem = Semester::find($stg->end) ?>
            <? if ($stg->start_sem == $stg->end_sem) : ?>
                <?= htmlReady($start_sem->name) ?>
            <? elseif (!$stg->end) : ?>
                <? printf(_('%s bis unbegrenzt'), htmlReady($start_sem->name)) ?>
            <? else : ?>
                <? printf(_('%s bis %s'), htmlReady($start_sem->name), htmlReady($end_sem->name)) ?>
            <? endif; ?>
        </tr>
        <tr>
            <td>
                <strong><?= _('Beschreibung') ?></strong>
            </td>
            <td>
                <?= formatReady($stg->beschreibung) ?>
            </td>
        </tr>
    </tbody>
</table>