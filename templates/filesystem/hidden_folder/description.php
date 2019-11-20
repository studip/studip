<div style="font-style: italic">
    <?= _('Ein unsichtbarer Ordner, welcher nur von Lehrenden und TutorInnen gesehen werden kann.') ?>
    <? if ($folder->download_allowed) : ?>
    <div>
        <?= _('Dateien aus diesem Ordner können heruntergeladen werden, wenn ein Downloadlink bekannt ist.')?>
    </div>
    <? endif ?>
</div>
<? if ($folder->description) : ?>
<hr>
    <div>
        <?= formatReady($folder->description) ?>
    </div>
<? endif ?>