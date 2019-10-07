<div style="font-style: italic">
    <div><?= _('Ein Ordner für Materialien, welche nur zum Download zu Verfügung gestellt werden sollen.') ?></div>
    <div>
        <?= _('Den Inhalt des Ordners können nur Lehrende und TutorInnen verändern.') ?>
    </div>
</div>
    <? if ($folderdata['description']) : ?>
    <hr>
        <div>
            <?= formatReady($folderdata['description']) ?>
        </div>
    <? endif ?>

