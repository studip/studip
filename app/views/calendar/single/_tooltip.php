<div class="calendar-tooltip tooltip-content">
    <h4><?= htmlReady($event->getTitle()) ?></h4>
    <div>
        <b><?= _('Beginn') ?>:</b> <?= strftime('%c', $event->getStart()) ?>
    </div>
    <div>
        <b><?= _('Ende') ?>:</b> <?= strftime('%c', $event->getEnd()) ?>
    </div>
    <? if ($event->havePermission(Event::PERMISSION_READABLE)) : ?>
        <? if ($event instanceof CourseEvent) : ?>
        <div>
            <b><?= _('Veranstaltung') ?>:</b> <?= htmlReady($event->course->getFullname()) ?>
        </div>
        <? endif;?>
        <? if ($text = $event->getDescription()) : ?>
            <div>
                <b><?= _('Beschreibung') ?>:</b> <?= htmlReady(mila($text, 50)) ?>
            </div>
        <? endif; ?>
        <? if ($text = $event->toStringCategories()) : ?>
            <div>
                <b><?= _('Kategorie') ?>:</b> <?= htmlReady(mila($text, 50)) ?>
            </div>
        <? endif; ?>
        <? if ($text = $event->getLocation()) : ?>
            <div>
                <b><?= _('Raum/Ort') ?>:</b> <?= htmlReady(mila($text, 50)) ?>
            </div>
        <? endif; ?>
        <? if ($text = $event->toStringPriority()) : ?>
            <div>
                <b><?= _('Priorität') ?>:</b> <?= htmlReady(mila($text, 50)) ?>
            </div>
        <? endif; ?>
        <? if ($text = $event->toStringAccessibility()) : ?>
            <div>
                <b><?= _('Zugriff') ?>:</b> <?= htmlReady(mila($text, 50)) ?>
            </div>
        <? endif; ?>
        <? if ($text = $event->toStringRecurrence()) : ?>
            <div>
                <b><?= _('Wiederholung') ?>:</b> <?= htmlReady($text) ?>
            </div>
        <? endif; ?>
    <? endif; ?>
    <? if ($event->havePermission(Event::PERMISSION_READABLE)) : ?>
        <? if ($event instanceof CalendarEvent
                && get_config('CALENDAR_GROUP_ENABLE')
                && $calendar->getRange() == Calendar::RANGE_USER) : ?>
            <? $group_status = [
                    CalendarEvent::PARTSTAT_TENTATIVE => _('Abwartend'),
                    CalendarEvent::PARTSTAT_ACCEPTED => _('Angenommen'),
                    CalendarEvent::PARTSTAT_DECLINED => _('Abgelehnt'),
                    CalendarEvent::PARTSTAT_DELEGATED => _('Angenommen (keine Teilnahme)'),
                    CalendarEvent::PARTSTAT_NEEDS_ACTION => ''] ?>
            <? $show_members = $event->attendees->findOneBy('range_id',
                    $calendar->getRangeId(), '!=') ?>
            <? // Entkommentieren, wenn Mitglieder eines Termins sichtbar sein
               // sollen, auch wenn man nicht selbst Mitglied ist und ... ?>
            <? // $show_members_visiter = $event->attendees->findOneBy('range_id', $GLOBALS['user']->id) ?>
            <? // folgende Zeile auskommentieren (siehe _attendees.php). ?>
            <? $show_members_visiter = true; ?>
            <? if ($show_members && $show_members_visiter) : ?>
            <div>
                <b><?= _('Teilnehmende:') ?></b>
                    <?= implode(', ', $event->attendees->map(
                        function ($att) use ($event, $group_status) {
                            if ($event->havePermission(Event::PERMISSION_OWN, $att->owner->id)) {
                                $ret = htmlReady($att->owner->getFullname())
                                    . ' (' . _('Organisator') . ')';
                            } else {
                                $ret = htmlReady($att->owner->getFullname());
                                if ($group_status[$att->group_status]) {
                                    $ret .= ' (' . $group_status[$att->group_status] . ')';
                                }
                            }
                            return $ret;
                        })); ?>
            </div>
            <? endif; ?>
        <? endif; ?>
        <? if ($event instanceof CourseEvent) : ?>
            <? // durchführende Dozenten ?>
            <? $related_persons = $event->dozenten; ?>
            <? if (sizeof($related_persons)) : ?>
            <div>
                <b><?= ngettext('Durchführender Dozent', 'Durchführende Dozenten', sizeof($related_persons)) ?>:</b>
                <ul class="list-unstyled">
                <? foreach ($related_persons as $related_person) : ?>
                    <li>
                        <?= htmlReady($related_person->getFullName()) ?>
                    </li>
                <? endforeach; ?>
                </ul>
            </div>
            <? endif; ?>
            <? // related groups ?>
            <? $related_groups = $event->getRelatedGroups(); ?>
            <? if (sizeof($related_groups)) : ?>
            <div>
                <b><?= _('Betroffene Gruppen') ?>:</b>
                <ul class="list-unstyled">
                <? foreach ($related_groups as $group) : ?>
                    <li>
                        <?= htmlReady($group->name) ?>
                    </li>
                <? endforeach; ?>
                </ul>
            </div>
            <? endif; ?>
        <? endif; ?>
    <? endif; ?>
</div>
