<h3><?= htmlReady($stgteil->getDisplayName()) ?></h3>
<table class="default mvv-modul-details" id="<?= $stgteil->getId() ?>" data-mvv-id="<?= $stgteil->getId(); ?>" data-mvv-type="stgteil">
    <colgroup>
        <col style="width: 20%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 30%;">
        <col style="width: 10%;">
    </colgroup>
    <tr>
        <th><?= _('Fach') ?></th>
        <th><?= _('Kredit-Punkte') ?></th>
        <th><?= _('Semesterzahl') ?></th>
        <th><?= _('Titelzusatz') ?></th>
        <th><?= _('Studienfachberater') ?></th>   
    </tr>
    <tr>
        <td data-mvv-field="mvv_stgteil.fach">
            <? if ($stgteil->fach) : ?>
                <?= htmlReady($stgteil->fach->name) ?>
            <? endif; ?>
        </td>
        <td data-mvv-field="mvv_stgteil.kp">
            <?= htmlReady($stgteil->kp) ?>
        </td>
        <td data-mvv-field="mvv_stgteil.semester">
            <?= htmlReady($stgteil->semester) ?>
        </td>
        <td data-mvv-field="mvv_stgteil.zusatz">
            <?= htmlReady($stgteil->zusatz) ?>   
        </td>
        <td data-mvv-field="mvv_stgteil.contact_assignments">
            <? if (empty($stgteil->contact_assignments)): ?>
                -
            <? else: ?>
                <? foreach ($stgteil->contact_assignments as $contact_assignment) : ?>
                    <?= htmlReady($contact_assignment->contact->getDisplayName()) ?><br>
                <? endforeach; ?>
            <? endif; ?>
        </td>
    </tr>
</table>