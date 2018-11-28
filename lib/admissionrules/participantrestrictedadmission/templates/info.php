<?= _("Es wird eine festgelegte Anzahl von Plätzen in den Veranstaltungen verteilt.") ?>
<br>
<? if ($rule->getDistributionTime()) : ?>
    <? if ($rule->getDistributionTime() > time()) : ?>
    <?= sprintf(_('Die Plätze in den betreffenden Veranstaltungen werden am %s '.
    'um %s verteilt.'), date("d.m.Y", $rule->getDistributionTime()), 
    date("H:i", $rule->getDistributionTime())) ?>
    <? else : ?>
    <?= sprintf(_('Die Plätze in den betreffenden Veranstaltungen wurden am %s '.
    'um %s verteilt. Weitere Plätze werden evtl. über Wartelisten zur Verfügung gestellt.'), date("d.m.Y", $rule->getDistributionTime()), 
    date("H:i", $rule->getDistributionTime())) ?>
    <? endif ?>
<? elseif ($rule->isFCFSallowed()) :?>
    <?= _("Die Plätze werden in der Reihenfolge der Anmeldung vergeben.")?>
<? endif ?>