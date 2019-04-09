<? use Studip\LinkButton; ?>
<div>
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
            <b><?= _('Veranstaltung') ?>:</b>
            <? if ($GLOBALS['perm']->have_studip_perm('user', $event->range_id)) : ?>
            <a href="<?= URLHelper::getLink('dispatch.php/course/details/?cid=' . $event->range_id) ?>">
            <? else : ?>
            <a href="<?= URLHelper::getLink('seminar_main.php?auswahl=' . $event->range_id) ?>">
            <? endif; ?>
                <?= htmlReady($event->course->getFullname()) ?>
            </a>
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
        <? if ($event instanceof CalendarEvent) : ?>
            <? if (get_config('CALENDAR_GROUP_ENABLE')) : ?>
                <?= $this->render_partial('calendar/single/_attendees.php') ?>
                <? if ($calendar->havePermission(Calendar::PERMISSION_OWN)
                        && $event->toStringGroupStatus()) : ?>
                    <?= $this->render_partial('calendar/single/_edit_status') ?>
                <? else : ?>
                <div style="text-align: center;" data-dialog-button>
                    <? if ($event->havePermission(Event::PERMISSION_DELETABLE)) : ?>
                    <?= LinkButton::create(_('Löschen'), $controller->url_for('calendar/single/delete/' . implode('/', $event->getId()))) ?>
                    <? endif; ?>
                    <? if (!Request::isXhr()) : ?>
                    <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view, [$event->getStart()])) ?>
                    <? endif; ?>
                </div>
                <? endif; ?>
            <? endif; ?>
        <? else : ?>
            <? // durchführende Dozenten ?>
            <? $related_persons = $event->dozenten; ?>
            <? if (sizeof($related_persons)) : ?>
            <div>
                <b><?= ngettext('Durchführender Dozent', 'Durchführende Dozenten', sizeof($related_persons)) ?>:</b>
                <ul class="list-unstyled">
                <? foreach ($related_persons as $related_person) : ?>
                    <li>
                        <?= ObjectdisplayHelper::link($related_person) ?>
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
                <?= htmlReady(implode(', ', $related_groups->pluck('name'))) ?>
            </div>
            <? endif; ?>
            <? if (!Request::isXhr()) : ?>
            <div style="text-align: center;" data-dialog-button>
                <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view, [$event->getStart()])) ?>
            </div>
            <? endif; ?>
        <? endif; ?>
    <? endif; ?>
</div>