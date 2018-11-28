<div>
    <?= _('Dieser Ordner ist einem Veranstaltungstermin zugeordnet.')?>
    <? if (Seminar_Perm::get()->have_studip_perm('tutor', $folder->range_id)) : ?>
        <? if ($folder->checkPermission('w')) : ?>
            <?= _('(Studierende dürfen Dateien hochladen.)')?>
        <? else : ?>
            <?= _('(Studierende dürfen keine Dateien hochladen.)')?>
        <? endif ?>
    <? endif ?>
</div>
<? if ($folderdata['description']) : ?>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>
