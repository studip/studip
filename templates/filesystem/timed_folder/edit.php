<label>
    <?= _('Sichtbar ab') ?>
    <input type="text" name="start_time" id="start_time" data-datetime-picker
           value="<?= $folder->data_content['start_time'] ?
               date('d.m.Y H:i', $folder->data_content['start_time']) :
               _('unbegrenzt') ?>"
           placeholder="<?= _('unbegrenzt') ?>">
</label>
<label>
    <?= _('Sichtbar bis') ?>
    <input type="text" name="end_time" id="end_time" data-datetime-picker
           value="<?= $folder->data_content['end_time'] ? date('d.m.Y H:i', $folder->data_content['end_time']) :
               _('unbegrenzt') ?>"
           placeholder="<?= _('unbegrenzt') ?>">
</label>
<label>
    <input name="perm_read" type="checkbox" value="1" <? if ($folder->data_content['permission'] & 4) echo 'checked'; ?>>
    <strong>r</strong> - <?= _('Lesen (Dateien können heruntergeladen werden)') ?>
</label>
<label>
    <input name="perm_write" type="checkbox" value="1" <? if ($folder->data_content['permission'] & 2) echo 'checked'; ?>>
    <strong>w</strong> - <?= _('Schreiben (Dateien können hochgeladen werden)') ?>
</label>
