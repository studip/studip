<? if ($derived_resource instanceof Room): ?>
The room <?= $derived_resource->name ?> has been locked for bookings in the time range from <?= date('d.m.Y H:i', $booking->begin) ?> to <?= date('d.m.Y H:i', $booking->end) ?>.
<? elseif ($derived_resource instanceof Building): ?>
The building <?= $derived_resource->name ?> has been locked for bookings in the time range
from <?= date('d.m.Y H:i', $booking->begin) ?> to <?= date('d.m.Y H:i', $booking->end) ?>.
<? elseif ($derived_resource instanceof Building): ?>
The location <?= $derived_resource->name ?> has been locked for bookings in the time range from <?= date('d.m.Y H:i', $booking->begin) ?> to <?= date('d.m.Y H:i', $booking->end) ?>.
<? else: ?>
The resource <?= $derived_resource->name ?> has been locked for bookings in the time range from <?= date('d.m.Y H:i', $booking->begin) ?> to <?= date('d.m.Y H:i', $booking->end) ?>.
<? endif ?>
