<div style="font-style: italic">
    <?=_("Dieser Ordner ist ein Hausaufgabenordner. Es können nur Dateien eingestellt werden.")?>
</div>
<? if (!empty($own_files) && count($own_files) > 0) : ?>
<?= _("Sie selbst haben folgende Dateien in diesen Ordner eingestellt:") ?>
    <ul>
        <? foreach ($own_files as $own_file) :?>
            <li><?=htmlReady($own_file->name)?> - <?=strftime('%x %X', $own_file->chdate)?></li>
        <? endforeach ?>
    </ul>
<? endif ?>
<? if ($folderdata['description']) : ?>
<hr>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>
