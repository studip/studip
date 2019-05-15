<? if ($folder->start_time && !$folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar ab %s'),
            strftime('%x %X', $folder->start_time)) ?>
    </strong>
    <br>
<? elseif (!$folder->start_time && $folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar bis %s'),
            strftime('%x %X', $folder->end_time)) ?>
    </strong>
    <br>
<? elseif ($folder->start_time && $folder->end_time) : ?>
    <strong>
        <?= sprintf(_('Sichtbar von %s bis %s'),
            strftime('%x %X', $folder->start_time),
            strftime('%x %X', $folder->end_time)) ?>
    </strong>
    <br>
<? endif ?>
<? if ($folder->data_content['permission']  == 3) : ?>
    <div>
        <?= _("Dieser Ordner ist ein Hausaufgabenordner. Es können nur Dateien eingestellt werden.") ?>
    </div>
<? endif ?>
<? if ($folder->data_content['permission']  == 5) : ?>
    <div>
        <?= _('Ein Ordner für Materialien, welche nur zum Download zu Verfügung gestellt werden sollen.') ?>
    </div>
    <div>
        <?= _('Den Inhalt des Ordners können nur Lehrende und TutorInnen verändern.') ?>
        <?= _('Alle anderen Teilnehmenden der Veranstaltung können diese Materialien nur herunterladen.') ?>
    </div>
<? endif ?>
<? if ($folderdata['description']) : ?>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>
<? if (!empty($own_files) && count($own_files) > 0) : ?>
    <div>
        <?= _('Sie selbst haben folgende Dateien in diesen Ordner eingestellt:') ?>
        <ul>
            <? foreach ($own_files as $own_file) : ?>
                <li><?= htmlReady($own_file->name) ?> - <?= strftime('%x %X', $own_file->chdate) ?></li>
            <? endforeach ?>
        </ul>
    </div>
<? endif ?>
