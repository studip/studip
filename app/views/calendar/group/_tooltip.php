<? $events = $events ?: $calendar->events ?>
<? if (sizeof($events)) : ?>
<div class="calendar-tooltip tooltip-content">
    <h3><?= htmlReady($calendar->range_object->getFullname('no_title')) ?></h3>
    <? foreach ($events as $event) : ?>
    <div>
        <? if (date('Ymd', $event->getStart()) == date('Ymd', $event->getEnd())) : ?>
            <? if ($event->isDayEvent()) : ?>
                <?= strftime('%x ', $event->getStart()) . '(' . _('ganztägig') . ')' ?>
            <? else : ?>
                <?= strftime('%x %X', $event->getStart()) . strftime(' - %X', $event->getEnd()) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($event->isDayEvent()) : ?>
                <?= strftime('%x', $event->getStart()) . strftime(' - %x', $event->getEnd()) . '(' . _('ganztägig') . ')' ?>
            <? else : ?>
                <?= strftime('%x %X', $event->getStart()) . strftime(' - %x %X', $event->getEnd()) ?>
            <? endif; ?>
        <? endif; ?>
    </div>
    <div>
        <?= htmlReady($event->getTitle()) ?>
    </div>
    <hr>
    <? endforeach; ?>
</div>
<? endif; ?>
