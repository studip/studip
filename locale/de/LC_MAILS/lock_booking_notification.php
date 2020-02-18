<? if ($derived_resource instanceof Room): ?>
Der Raum <?= $derived_resource->name ?> wurde für den Zeitbereich
von <?= date('d.m.Y H:i', $booking->begin) ?> bis  <?= date('d.m.Y H:i', $booking->end) ?> für Buchungen gesperrt.
<? elseif ($derived_resource instanceof Building): ?>
Das Gebäude <?= $derived_resource->name ?> wurde für den Zeitbereich von <?= date('d.m.Y H:i', $booking->begin) ?> bis <?= date('d.m.Y H:i', $booking->end) ?> für Buchungen gesperrt.
<? elseif ($derived_resource instanceof Building): ?>
Der Standort <?= $derived_resource->name ?> wurde für den Zeitbereich von <?= date('d.m.Y H:i', $booking->begin) ?> bis <?= date('d.m.Y H:i', $booking->end) ?> für Buchungen gesperrt.
<? else: ?>
Die Ressource <?= $derived_resource->name ?> wurde für den Zeitbereich von <?= date('d.m.Y H:i', $booking->begin) ?> bis <?= date('d.m.Y H:i', $booking->end) ?> für Buchungen gesperrt.
<? endif ?>
