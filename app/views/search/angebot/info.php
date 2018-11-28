<h1><?= sprintf(_('Informationen zum Studiengang: %s'), $studiengang->getDisplayName()); ?></h1>
<div style="padding:10px;"><?= formatReady($studiengang->getValue('beschreibung'))?></div>