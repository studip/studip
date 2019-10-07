<div style="font-style: italic">
    <?= _('Ein unsichtbarer Ordner, welcher nur von Lehrenden und TutorInnen gesehen werden kann.') ?>
</div>
<? if ($folderdata['description']) : ?>
<hr>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>