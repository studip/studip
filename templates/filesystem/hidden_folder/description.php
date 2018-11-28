<div>
    <div><?= _('Ein unsichtbarer Ordner, welcher nur von Lehrenden und TutorInnen gesehen werden kann.') ?></div>
</div>
<? if ($folderdata['description']) : ?>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>