<div style="font-style: italic"><?= htmlReady($type )?>:

    <input disabled type="checkbox" <? if ($folder->isReadable()) echo 'checked'; ?>>
    <strong>Lesen</strong>
    <input disabled type="checkbox" <? if ($folder->isWritable()) echo 'checked'; ?>>
    <strong>Schreiben</strong>
    <input disabled type="checkbox" <? if ($folder->isVisible()) echo 'checked'; ?>>
    <strong>Sichtbarkeit</strong>
</div>
<? if ($folderdata['description']) : ?>
<hr>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>