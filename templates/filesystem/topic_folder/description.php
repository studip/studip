<div style="font-style: italic">
<?= _('Dieser Ordner ist ein themenbezogener Dateiordner.')?>
    <? if (Seminar_Perm::get()->have_studip_perm('tutor', $folder->range_id)) : ?>
        <? if ($folder->checkPermission('w')) : ?>
            <?= _('(Studierende dürfen Dateien hochladen.)')?>
        <? else : ?>
            <?= _('(Studierende dürfen keine Dateien hochladen.)')?>
        <? endif ?>
    <? endif ?>
    <?$dates = isset($topic) ? $topic->dates->getFullname() : [];?>
    <? if (count($dates)) :?>
    <?=_('Folgende Termine sind diesem Thema zugeordnet:') ?>
        <div>
            <strong>
                <?=join('; ', $dates)?>
            </strong>
        </div>
    <? endif ?>
</div>
<? if ($folderdata['description']) : ?>
<hr>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>
