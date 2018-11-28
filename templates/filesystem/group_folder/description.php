<div>
    <div><?= sprintf(_('Ein Ordner für die Mitglieder der Gruppe %s.'), htmlReady($groupname)) ?></div>
    <div><?= _('Der Inhalt ist nur für die eingetragenen Mitglieder sichtbar.') ?></div>
</div>
<? if ($folderdata['description']) : ?>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>