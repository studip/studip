<?php
/**
 * SingleCalendar.php - Model class for a calendar
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.2
 */

require_once 'app/models/calendar/Calendar.php';

class SingleCalendar
{
    /**
     * This collection holds all Events in this calendar.
     *
     * @var SimpleORMapCollection Collection of Objects which inherits Event.
     */
    public $events;

    /**
     * The owner of this calendar.
     *
     * @var Object A Stud.IP object of type User, Institute or Course.
     */
    public $range_object;

    public $type;

    public $range;

    /**
     * The start of this calendar.
     *
     * @var int Unix timestamp.
     */
    public $start;

    /**
     * The end of this calendar.
     *
     * @var int Unix timestamp.
     */
    public $end;

    public function __construct($range_id, $start = null, $end = null)
    {
        $this->setRangeObject($range_id);
        $this->start = $start ?: 0;
        $this->end = $end ?: Calendar::CALENDAR_END;
        $this->events = new SimpleORMapCollection();
        $this->events->setClassName('Event');
        $this->type = get_class($this->range_object);
    }

    /**
     * Sets the range object and checks whether the calendar is available.
     *
     * @param string $range_id The id of a course, institute or user.
     * @throws AccessDeniedException
     */
    private function setRangeObject($range_id)
    {
        $this->range_object = get_object_by_range_id($range_id);
        if (!is_object($this->range_object)) {
            throw new AccessDeniedException();
        }
        $range_map = [
            'User' => Calendar::RANGE_USER,
            'Course' => Calendar::RANGE_SEM,
            'Institute' => Calendar::RANGE_INST
        ];
        $this->range = $range_map[get_class($this->range_object)];
        if ($this->range == Calendar::RANGE_INST
                || $this->range == Calendar::RANGE_SEM) {
            $modules = new Modules();
            if (!$modules->getStatus('calendar', $this->range_object->getId())) {
                throw new AccessDeniedException();
            }
        }
    }

    /**
     * Returns all events of given class names between start and end.
     * Returns events of all types if no class names are given.
     *
     * @param array|null $class_names The names of classes that implements Event.
     * @param int $start The start date time.
     * @param int $end The end date time.
     * @return \SingleCalendar This calendar object.
     * @throws InvalidArgumentException
     */
    public function getEvents($class_names = null, $start = null, $end = null)
    {
        $start = !is_null($start) ? $start : $this->start;
        $end = !is_null($end) ? $end : $this->end;
        if (!is_array($class_names)) {
            $class_names = ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent', 'CourseMarkedEvent'];
        }
        $events = $this->events->getArrayCopy();
        foreach ($class_names as $type) {
            if (in_array('Event', class_implements($type))) {
                $events = array_merge($events, $type::getEventsByInterval(
                        $this->range_object->getId(), new DateTime('@' . $start),
                        new DateTime('@' . $end))->getArrayCopy());
            } else {
                throw new InvalidArgumentException(sprintf('Class %s does not implements Event.', $type));
            }
        }
        $this->events = SimpleORMapCollection::createFromArray($events, false);
        $this->events->setClassName('Event');
        return $this;
    }

    /**
     * Stores the event in the calendars of all attendees.
     *
     * @param CalendarEvent $event The event to store.
     * @param array $attendee_ids The user ids of the attendees.
     * @return bool|int The number of stored events or false if an error occured.
     */
    public function storeEvent(CalendarEvent $event, $attendee_ids = [])
    {
        $attendee_ids = array_filter($attendee_ids, function($id) {
            return trim($id) != '';
        });
        if (!$attendee_ids) {
            $attendee_ids = [$GLOBALS['user']->id];
        }
        if (count($attendee_ids) == 1) {
            if (!$this->havePermission(Calendar::PERMISSION_WRITABLE)) {
                return false;
            }
            $is_new = $event->isNew();
            $stored = $event->store();
            if ($stored !== false && $this->getRange() == Calendar::RANGE_USER
                    && $this->getRangeId() != $GLOBALS['user']->id) {
                $this->sendStoreMessage($event, $is_new);
            }
            return $stored;
        } else {
            if (in_array($this->getRangeId(), $attendee_ids)) {
                // set default status if the organizer is an attendee...
                $event->group_status = CalendarEvent::PARTSTAT_TENTATIVE;
            }
            if ($event->isNew()) {
                return $this->storeAttendeeEvents($event, $attendee_ids);
            } else {
                if ($event->havePermission(Event::PERMISSION_WRITABLE)) {
                    return $this->storeAttendeeEvents($event, $attendee_ids);
                }
            }
        }
    }

    /**
     * Helper function for SingleCalendar::storeEvent().
     *
     * @param CalendarEvent $event The event to store.
     * @param type $attendee_ids The user ids of the attendees.
     * @return bool|int The number of stored events or false if an error occured.
     */
    private function storeAttendeeEvents(CalendarEvent $event, $attendee_ids)
    {
        $ret = 0;
        $new_attendees = [];
        $recipient_ids = [];
        $is_new = false;
        foreach ($attendee_ids as $attendee_id) {
            if (trim($attendee_id)) {
                $attendee_calendar = new SingleCalendar($attendee_id);

                // SEMBBS
                // Gruppentermine können ab Calendar::PERMISSION_READABLE angelegt werden
                // if ($attendee_calendar->havePermission(Calendar::PERMISSION_READABLE)) {

                if ($attendee_calendar->havePermission(Calendar::PERMISSION_WRITABLE)
                        || Config::get()->CALENDAR_GRANT_ALL_INSERT) {
                    $attendee_event = new CalendarEvent(
                            [$attendee_calendar->getRangeId(), $event->event_id]);
                    $attendee_event->event = $event->event;
                    $is_new = $attendee_event->isNew();
                    if ($is_new) {
                        $attendee_event->group_status = $event->group_status;
                    }
                    $stored = $attendee_event->store();
                    if ($stored !== false) {
                        // send message if not own calendar
                        if (!$attendee_calendar->havePermission(Calendar::PERMISSION_OWN)) {
                            $recipient_ids[] = $attendee_event->range_id;
                        }
                        $new_attendees[] = $attendee_event->range_id;
                        $ret += $stored;
                    }
                }
            }
        }
        if (count($recipient_ids)) {
            $this->sendStoreMessage($attendee_event, $is_new, $recipient_ids);
        }

        $events_delete = CalendarEvent::findBySQL('event_id = ? AND range_id NOT IN(?)',
                [$event->event_id, $new_attendees]);
        foreach ($events_delete as $event_delete) {
            $calendar = new SingleCalendar($event_delete->range_id);
            $calendar->deleteEvent($event_delete);
        }
        return $ret;
    }

    /**
     * Sets the start date time by given unix timestamp.
     *
     * @param int $start Unix timestamp.
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Returns the start date time of this calendar as a unix timestamp.
     *
     * @return int Unix timestamp.
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets the end date time by given unix timestamp.
     *
     * @param int $end Unix timestamp.
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Returns the end date time of this calendar as a unix timestamp.
     *
     * @return int Unix timestamp.
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns a event by given $event_id. Returns a new event of type
     * CalendarEvent with default data if the id is null or unknown.
     * If $class_names is set, only these types of Object will be returned.
     *
     * @param string $event_id
     * @param array $class_names Names of classes which inherits Event.
     * @return Event|null The found event, a new CalendarEvent or null if no
     * event other than a CalendarEvent was found.
     */
    public function getEvent($event_id = null, $class_names = null)
    {
        if (!is_array($class_names)) {
            $class_names = ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent', 'CourseMarkedEvent'];
        }
        foreach ($class_names as $type) {
            if ($type == 'CalendarEvent') {
                $event = CalendarEvent::find([$this->getRangeId(), $event_id]);
            } else {
                $event = $type::find($event_id);
            }
            if ($event && $event->havePermission(Event::PERMISSION_READABLE)) {
                return $event;
            }
        }
        return $this->getNewEvent();
    }

    /**
     * Creates a new event, sets some default data and returns it.
     *
     * @return \CalendarEvent The new event.
     */
    public function getNewEvent()
    {
        $event_data = new EventData();
        $event_data->setId($event_data->getNewId());
        $now = time();
        $event_data->start = $now;
        $event_data->end = $now + 3600;
        $calendar_event = new CalendarEvent();
        $calendar_event->setId([$this->getRangeId(), $event_data->getId()]);
        $calendar_event->event = $event_data;
        return $calendar_event;
    }

    /**
     * Sorts all events by start time.
     */
    public function sortEvents()
    {
        $this->events->orderBy('start');
    }

    /**
     * An alias for SingleCalendar::getRangeId().
     *
     * @see SingleCalendar::getRangeId()
     */
    public function getId()
    {
        return $this->getRangeId();
    }

    /**
     * Returns the range id of this calendar.
     * Possible range id are for objects of type user, inst, fak, group.
     *
     * @return string The range id.
     */
    public function getRangeId()
    {
        return $this->range_object->getId();
    }

    /**
     * Returns the object range of this calendar.
     *
     * @return int The object range.
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Returns the range object (user, course, institute) of this calendar.
     *
     * @return int The object range.
     */
    public function getRangeObject()
    {
        return $this->range_object;
    }

    /**
     * Returns the permission of the given user for this calendar.
     *
     * @param string $user_id User id.
     * @return int The calendar permission.
     */
    public function getPermissionByUser($user_id = null)
    {
        static $user_permission = [];

        $user_id = $user_id ?: $GLOBALS['user']->id;
        $id = $user_id . $this->getRangeId();
        if ($user_permission[$id]) {
            return $user_permission[$id];
        }
        // own calendar
        if ($this->range == Calendar::RANGE_USER
                && $this->getRangeId() == $user_id) {
            $user_permission[$id] = Calendar::PERMISSION_OWN;
            return $user_permission[$id];
        }
        switch ($this->type) {
            case 'User' :
                // alle Dozenten haben gegenseitig schreibenden Zugriff, ab dozent immer schreibenden Zugriff
                /*
                if ($GLOBALS['perm']->have_perm('dozent') && $GLOBALS['perm']->get_perm($this->range_object->getId()) == 'dozent') {
                    return Calendar::PERMISSION_WRITABLE;
                }
                 *
                 */
                $cal_user = CalendarUser::find([$this->getRangeId(), $user_id]);
                if ($cal_user) {
                    switch ($cal_user->permission) {
                        case 1 :
                            $user_permission[$id] = Calendar::PERMISSION_FORBIDDEN;
                            break;
                        case 2 :
                            $user_permission[$id] = Calendar::PERMISSION_READABLE;
                            break;
                        case 4 :
                            $user_permission[$id] = Calendar::PERMISSION_WRITABLE;
                            break;
                        default :
                            $user_permission[$id] = Calendar::PERMISSION_FORBIDDEN;
                    }
                } else {
                    $user_permission[$id] = Calendar::PERMISSION_FORBIDDEN;
                }
                break;
                /*
            case 'group' :
                $stmt = DBManager::get()->prepare('SELECT range_id FROM statusgruppen WHERE statusgruppe_id = ?');
                $stmt->execute(array($range_id));
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    if ($result['range_id'] == $user_id) {
                        return Calendar::PERMISSION_OWN;
                    }
                }
                return Calendar::PERMISSION_FORBIDDEN;
                 *
                 */
            case 'Course' :
                switch ($GLOBALS['perm']->get_studip_perm($this->range_object->getId(), $user_id)) {
                    case 'user' :
                    case 'autor' :
                        $user_permission[$id] = Calendar::PERMISSION_READABLE;
                        break;
                    case 'tutor' :
                    case 'dozent' :
                    case 'admin' :
                    case 'root' :
                        $user_permission[$id] = Calendar::PERMISSION_WRITABLE;
                        break;
                    default :
                        $user_permission[$id] = Calendar::PERMISSION_FORBIDDEN;
                }
                break;
            case 'Institute' :
                switch ($GLOBALS['perm']->get_studip_perm($this->range_object->getId(), $user_id)) {
                    case 'user' :
                        $user_permission[$id] = Calendar::PERMISSION_READABLE;
                        break;
                    case 'autor' :
                        $user_permission[$id] = Calendar::PERMISSION_READABLE;
                        break;
                    case 'tutor' :
                    case 'dozent' :
                    case 'admin' :
                    case 'root' :
                        $user_permission[$id] = Calendar::PERMISSION_WRITABLE;
                        break;
                    default :
                        // readable for all
                        $user_permission[$id] = Calendar::PERMISSION_READABLE;
                }
                break;
            default :
                $user_permission[$id] = Calendar::PERMISSION_FORBIDDEN;
        }
        return $user_permission[$id];
    }

    /**
     * Returns whether the given user has at least the $permission to this calendar.
     * It checks for the actual user if $user_id is null.
     *
     * @param int $permission An accepted calendar permission.
     * @param string|null $user_id The id of the user.
     * @return bool True if the user has at least the given permission.
     */
    public function havePermission($permission, $user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        return ($permission <= $this->getPermissionByUser($user_id));
    }

    /**
     * Returns whether the given user has the $permission to this calendar.
     * It checks for the actual user if $user_id is null.
     *
     * @param int $permission An accepted calendar permission.
     * @param string|null $user_id The id of the user.
     * @return bool True if the user has the given permission.
     */
    public function checkPermission($permission, $user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        return $permission == $this->getPermissionByUser($user_id);
    }

    /**
     * Sends a message to the owner of the calendar that a new event was inserted
     * or an old event was modified by another user.
     *
     * @param CalendarEvent $event The new or updated event.
     * @param bool $is_new True if the event is new.
     * @param null|array Array with user_ids of the recipients. If not set the
     * owner of the given event is used as recipient.
     */
    protected function sendStoreMessage($event, $is_new, $recipient_ids = null)
    {
        $message = new messaging();
        $event_data = '';

        if ($is_new) {
            $msg_text = sprintf(_("%s hat einen neuen Termin in Ihren Kalender eingetragen."), get_fullname());
            $subject = strftime(_('Neuer Termin am %c'), $event->getStart());
            $msg_text .= "\n\n**";
        } else {
            $msg_text = sprintf(_("%s hat einen Termin in Ihrem Kalender geändert."), get_fullname());
            $subject = strftime(_('Termin am %c geändert'), $event->getStart());
            $msg_text .= "\n\n**";
        }
        $msg_text .= _('Zeit') . ':' . '** ' . strftime(' %c - ', $event->getStart())
                . strftime('%c', $event->getEnd()) . "\n**";
        $msg_text .= _("Zusammenfassung:") . '** ' . $event->getTitle() . "\n";
        if ($event_data = $event->getDescription()) {
            $msg_text .= '**' . _("Beschreibung:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringCategories()) {
            $msg_text .= '**' . _("Kategorie:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringPriority()) {
            $msg_text .= '**' . _("Priorität:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringAccessibility()) {
            $msg_text .= '**' . _("Zugriff:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringRecurrence()) {
            $msg_text .= '**' . _("Wiederholung:") . "** $event_data\n";
        }
        if (get_config('CALENDAR_GROUP_ENABLE') && $event->attendees->count()) {
            $msg_text .= '**' . _("Teilnehmende:") . '** ';
            $msg_text .= implode(', ', $event->attendees->map(
                function ($att) use ($event) {
                    $att_name = $att->user->getFullname();
                    if ($event->havePermission(Event::PERMISSION_OWN, $att->user->getId())) {
                        $att_name .= ' (' . _('Organisator') . ')';
                    } else {
                        if ($event->toStringGroupStatus()) {
                            $att_name .= ' (' . $att->toStringGroupStatus() . ')';
                        }
                    }
                    return $att_name;
                }));
            $msg_text .= "\n";
        }
        $msg_text .= "\n\n" . _('Hier kommen Sie direkt zum Termin in Ihrem Kalender:') . "\n";
                $msg_text .= URLHelper::getURL('dispatch.php/calendar/single/edit/'
                    . implode('/', $event->getId()), false);

        $recipient_unames = is_array($recipient_ids)
                ? array_map('get_username', $recipient_ids)
                : [get_username($event->range_id)];

        $message->insert_message($msg_text, $recipient_unames,
                '____%system%____', '', '', '', '', $subject);
    }

    /**
     * Deletes an event from this calendar.
     *
     * @param string|object $calendar_event The id of an event or an event object of type CalendarEvent.
     * @param boolean $all If true all events of a group event will be deleted.
     * @return boolean|int The number of deleted events. False if the event was not deleted.
     */
    public function deleteEvent($calendar_event, $all = false)
    {
        if (!is_object($calendar_event)) {
            $calendar_event = CalendarEvent::find(
                    [$this->getRangeId(), $calendar_event]);
        }
        if (!$calendar_event
            || !is_a($calendar_event, 'CalendarEvent')
            || !$calendar_event->havePermission(Event::PERMISSION_DELETABLE)) {
            return false;
        }
        if ($this->havePermission(Calendar::PERMISSION_WRITABLE)
                || $calendar_event->havePermission(Event::PERMISSION_OWN)) {

            if (!($calendar_event
                    && $calendar_event->havePermission(Event::PERMISSION_WRITABLE))) {
                return false;
            }

            if (!is_a($calendar_event, 'CalendarEvent')) {
                return false;
            }

            if ($this->getRange() == Calendar::RANGE_USER) {
                $event_message = clone $calendar_event;
                $author_id = $calendar_event->getAuthorId();
                $deleted = $calendar_event->delete();
                if ($deleted && !$this->havePermission(Calendar::PERMISSION_OWN)) {
                    $this->sendDeleteMessage($event_message);
                }
                if ($all && $deleted && $author_id == $this->getRangeId()) {
                    CalendarEvent::findEachBySQL(function ($ce) use ($deleted) {
                        $calendar = new SingleCalendar($ce->range_id);
                        $deleted += $calendar->deleteEvent($ce);
                    }, 'event_id = ?', [$event_message->event_id]);
                }
                return $deleted;
            } else if ($this->getRange() == Calendar::RANGE_SEM) {
                $deleted = $calendar_event->delete();
                return $deleted;
            }
        }
        return false;
    }

    /**
     * Sends a message to the owner of the calendar that this event was deleted
     * by another user.
     *
     * @param CalendarEvent $event The deleted event.
     * @param null|array Array with user_ids of the recipients. If not set the
     * owner of the given event is used as recipient.
     */
    protected function sendDeleteMessage($event, $recipient_ids = null)
    {
        $message = new messaging();
        $event_data = '';

        $subject = strftime(_('Termin am %c gelöscht'), $event->getStart());
        $msg_text = sprintf(_("%s hat folgenden Termin in Ihrem Kalender gelöscht:"), get_fullname());
        $msg_text .= "\n\n";

        $msg_text .= '**' . _('Zeit:') . '**' . strftime(' %c - ', $event->getStart())
                . strftime('%c', $event->getEnd()) . "\n";
        $msg_text .= '**' . _("Zusammenfassung:") . '** ' . $event->getTitle() . "\n";
        if ($event_data = $event->getDescription()) {
            $msg_text .= '**' . _("Beschreibung:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringCategories()) {
            $msg_text .= '**' . _("Kategorie:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringPriority()) {
            $msg_text .= '**' . _("Priorität:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringAccessibility()) {
            $msg_text .= '**' . _("Zugriff:") . "** $event_data\n";
        }
        if ($event_data = $event->toStringRecurrence()) {
            $msg_text .= '**' . _("Wiederholung:") . "** $event_data\n";
        }
        $recipient_unames = is_array($recipient_ids)
                ? array_map('get_username', $recipient_ids)
                : [get_username($event->range_id)];
        $message->insert_message($msg_text, $recipient_unames,
                '____%system%____', '', '', '', '', $subject);
    }

    /**
     * Returns an array of all events (with calculated recurrences)
     * in the given time range.
     *
     * @param string $owner_id The user id of calendar owner.
     * @param int $time A unix timestamp of this day.
     * @param string $user_id The id of the user who gets access to the calendar (optional, default current user)
     * @param array $restrictions An array with key value pairs of properties to filter the result (optional).
     * @param array $class_names Array of class names. The class must implement Event (optional).
     * @return array All events in the given time range (with calculated recurrences)
     */
    public static function getEventList($owner_id, $start, $end, $user_id = null,
            $restrictions = null, $class_names = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        $end_time = mktime(12, 0, 0, date('n', $end), date('j', $end), date('Y', $end));
        $start_day = date('j', $start);
        $events = [];
        do {
            $time = mktime(12, 0, 0, date('n', $start), $start_day, date('Y', $start));
            $start_day++;
            $day = self::getDayCalendar($owner_id, $time, $user_id, $restrictions, $class_names);
            foreach ($day->events as $event) {
                $event_key = implode('', (array) $event->getId()) . $event->getStart();
                $events["$event_key"] = $event;
            }
        } while ($time <= $end_time);
        return $events;
    }

    /**
     * Returns a SingleCalendar object with all events of the given owner or
     * SingleCalendar object for one day set by timestamp.
     *
     * @param string|SingleCalendar $owner The user id of calendar owner or a calendar object.
     * @param int $time A unix timestamp of this day.
     * @param string $user_id The id of the user who gets access to the calendar (optional, default current user)
     * @param array $restrictions An array with key value pairs of properties to filter the result (optional).
     * @param array $class_names Array of class names. The class must implement Event (optional).
     * @return \SingleCalendar Calendar Object with all events of given day.
     */
    public static function getDayCalendar($owner, $time, $user_id = null,
            $restrictions = null, $class_names = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        if (!is_array($class_names)) {
            $class_names = ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent', 'CourseMarkedEvent'];
        }

        $day = date('Y-m-d-', $time);
        $start = DateTime::createFromFormat('Y-m-d-H:i:s', $day . '00:00:00');
        $end = DateTime::createFromFormat('Y-m-d-H:i:s', $day . '23:59:59');
        if (is_object($owner)) {
            if ($owner instanceof SingleCalendar) {
                $calendar = $owner;
                $calendar->setStart($start->format('U'))->setEnd($end->format('U'));
            } else {
                throw new InvalidArgumentException('The owner must be a user id or an object of type SingleCalendar.');
            }
        } else {
            $calendar = new SingleCalendar($owner,
                    $start->format('U'), $end->format('U'));
        }
        $calendar->getEvents($class_names)->sortEvents();

        $dow = date('w', $calendar->getStart());
        $month = date('n', $calendar->getStart());
        $year = date('Y', $calendar->getStart());
        $events_created = [];

        foreach ($calendar->events as $event) {
            if (!$calendar->havePermission(Calendar::PERMISSION_READABLE, $user_id)
                   && $event->getAccessibility() != 'PUBLIC') {
                continue;
            }
            if (!$event->havePermission(Event::PERMISSION_CONFIDENTIAL, $user_id)) {
                continue;
            }
            if (!SingleCalendar::checkRestriction($event, $restrictions)) {
                continue;
            }
            $properties = $event->getProperties();
            $ts = mktime(12, 0, 0, date('n', $calendar->start), date('j', $calendar->start), date('Y', $calendar->start));
            $rep = $properties['RRULE'];
            $duration = (int) ((mktime(12, 0, 0, date('n', $properties['DTEND']), date('j', $properties['DTEND']), date('Y', $properties['DTEND']))
                    - mktime(12, 0, 0, date('n', $properties['DTSTART']), date('j', $properties['DTSTART']), date('Y', $properties['DTSTART'])))
                    / 86400);
            // single events or first event
            if ($properties['DTSTART'] >= $calendar->getStart()
                    && $properties['DTEND'] <= $calendar->getEnd()) {
                self::createDayViewEvent($event, $properties['DTSTART'], $properties['DTEND'],
                        $calendar->getStart(), $calendar->getEnd(), $events_created);
            } elseif ($properties['DTSTART'] >= $calendar->getStart()
                    && $properties['DTSTART'] <= $calendar->getEnd()) {
                self::createDayViewEvent($event, $properties['DTSTART'], $properties['DTEND'],
                        $calendar->getStart(), $calendar->getEnd(), $events_created);
            } elseif ($properties['DTSTART'] < $calendar->getStart()
                    && $properties['DTEND'] > $calendar->getEnd()) {
                self::createDayViewEvent($event, $properties['DTSTART'], $properties['DTEND'],
                        $calendar->getStart(), $calendar->getEnd(), $events_created);
            } elseif ($properties['DTEND'] > $calendar->getStart()
                    && $properties['DTEND'] <= $calendar->getEnd()) {
                self::createDayViewEvent($event, $properties['DTSTART'], $properties['DTEND'],
                        $calendar->getStart(), $calendar->getEnd(), $events_created);
            }
            switch ($rep['rtype']) {
                case 'DAILY':
                    /*
                    if ($calendar->getEnd() > $rep['expire'] + $duration * 86400) {
                        continue;
                    }
                     *
                     */
                    if ($end > $rep['expire'] + $duration * 86400) {
                        continue;
                    }
                    $ts = $ts + (date('I', $rep['ts']) * 3600);
                    $pos = (($ts - $rep['ts']) / 86400) % $rep['linterval'];
                    $start = $ts - $pos * 86400;
                    $end = $start + $duration * 86400;
                    self::createDayViewEvent($event, $start, $end, $calendar->getStart(),
                            $calendar->getEnd(), $events_created);
                    break;
                case 'WEEKLY':
                    $rep['ts'] = $rep['ts'] + ((date('I', $rep['ts']) - date('I', $ts)) * 3600);
                    for ($i = 0; $i < mb_strlen($rep['wdays']); $i++) {
                        $pos = ((($ts - $dow * 86400) - $rep['ts']) / 86400
                                - ($rep['wdays']{$i} - 1) + $dow)
                                % ($rep['linterval'] * 7);
                        $start = $ts - $pos * 86400;
                        $end = $start + $duration * 86400;
                        if ($start >= $properties['DTSTART'] && $start <= $ts && $end >= $ts) {
                            self::createDayViewEvent($event, $start, $end,
                                    $calendar->getStart(), $calendar->getEnd(), $events_created);
                        }
                    }
                    break;
                case 'MONTHLY':
                    if ($rep['day']) {
                        $lwst = mktime(12, 0, 0, $month
                                - ((($year - date('Y', $rep['ts'])) * 12
                                + ($month - date('n', $rep['ts']))) % $rep['linterval']),
                                $rep['day'], $year);
                        $hgst = $lwst + $duration * 86400;
                        self::createDayViewEvent($event, $lwst, $hgst, $calendar->getStart(),
                                $calendar->getEnd(), $events_created);
                        break;
                    }
                    if ($rep['sinterval']) {
                        $mon = $month - $rep['linterval'];
                        do {
                            $lwst = mktime(12, 0, 0, $mon
                                    - ((($year - date('Y', $rep['ts'])) * 12
                                    + ($mon - date('n', $rep['ts']))) % $rep['linterval']),
                                    1, $year) + ($rep['sinterval'] - 1) * 604800;
                            $aday = strftime('%u', $lwst);
                            $lwst -= ( $aday - $rep['wdays']) * 86400;
                            if ($rep['sinterval'] == 5) {
                                if (date('j', $lwst) < 10) {
                                    $lwst -= 604800;
                                }
                                if (date('n', $lwst) == date('n', $lwst + 604800)) {
                                    $lwst += 604800;
                                }
                            } else {
                                if ($aday > $rep['wdays']) {
                                    $lwst += 604800;
                                }
                            }
                            $hgst = $lwst + $duration * 86400;
                            if ($ts >= $lwst && $ts <= $hgst) {
                                self::createDayViewEvent($event, $lwst, $hgst,
                                        $calendar->getStart(), $calendar->getEnd(), $events_created);
                            }
                            $mon += $rep['linterval'];
                        } while ($lwst < $ts);
                    }
                    break;
                case 'YEARLY':
                    if ($ts < $rep['ts']) {
                        break;
                    }
                    if ($rep['day']) {
                        if (date('Y', $properties['DTEND']) - date('Y', $properties['DTSTART'])) {
                            $lwst = mktime(12, 0, 0, $rep['month'], $rep['day'],
                                    $year - (($year - date('Y', $rep['ts'])) % $rep['linterval'])
                                    - $rep['linterval']);
                            $hgst = $lwst + 86400 * $duration;
                            if ($ts >= $lwst && $ts <= $hgst) {
                                self::createDayViewEvent($event, $lwst, $hgst,
                                        $calendar->getStart(), $calendar->getEnd(), $events_created);
                                break;
                            }
                        }
                        $lwst = mktime(12, 0, 0, $rep['month'], $rep['day'],
                                $year - (($year - date('Y', $rep['ts'])) % $rep['linterval']));
                        $hgst = $lwst + 86400 * $duration;
                        self::createDayViewEvent($event, $lwst, $hgst, $calendar->getStart(),
                                $calendar->getEnd(), $events_created);
                        break;
                    }
                    $ayear = $year - 1;
                    do {
                        if ($rep['sinterval']) {
                            $lwst = mktime(12, 0, 0, $rep['month'],
                                    1 + ($rep['sinterval'] - 1) * 7, $ayear);
                            $aday = strftime('%u', $lwst);
                            $lwst -= ( $aday - $rep['wdays']) * 86400;
                            if ($rep['sinterval'] == 5) {
                                if (date('j', $lwst) < 10) {
                                    $lwst -= 604800;
                                }
                                if (date('n', $lwst) == date('n', $lwst + 604800)) {
                                    $lwst += 604800;
                                }
                            } elseif ($aday > $rep['wdays']) {
                                $lwst += 604800;
                            }
                            $ayear++;
                            $hgst = $lwst + $duration * 86400;
                            if ($ts >= $lwst && $ts <= $hgst) {
                                self::createDayViewEvent($event, $lwst, $hgst,
                                        $calendar->getStart(), $calendar->getEnd(), $events_created);
                            }
                        }
                    } while ($lwst < $ts);
            }
        }
        $calendar->events->exchangeArray(array_values($events_created));
        return $calendar;
    }

    /**
     * Creates events for the day view.
     *
     * @param Event $event
     * @param int $lwst
     * @param int $hgst
     * @param int $cl_start
     * @param int $cl_end
     * @param array $events_created
     * @return boolean
     */
    private static function createDayViewEvent($event, $lwst, $hgst,
            $cl_start, $cl_end, Array &$events_created)
    {
        $lwst = mktime(12, 0, 0, date('n', $lwst), date('j', $lwst), date('Y', $lwst));
        $hgst = mktime(12, 0, 0, date('n', $hgst), date('j', $hgst), date('Y', $hgst));

        // if this date is in the exceptions?
        if ($event->getProperty('EXDATE')) {
            $exdates = explode(',', $event->getProperty('EXDATE'));
            foreach ($exdates as $exdate) {
                if ($exdate > 0 && $exdate >= $lwst && $exdate <= $hgst) {
                    return false;
                }
            }
        }
        // is event expired?
        $rrule = $event->getRecurrence();
        if ($rrule['rtype'] != 'SINGLE' && $rrule['expire'] > 0 && $rrule['expire'] < $hgst) {
            return false;
        }
        $start = mktime(date('G', $event->getStart()), date('i', $event->getStart()),
                date('s', $event->getStart()), date('n', $lwst), date('j', $lwst), date('Y', $lwst));
        $end = mktime(date('G', $event->getEnd()), date('i', $event->getEnd()),
                date('s', $event->getEnd()), date('n', $hgst), date('j', $hgst), date('Y', $hgst));

        if (($start <= $cl_start && $end >= $cl_end)
                || ($start >= $cl_start && $start < $cl_end)
                || ($end > $cl_start && $end <= $cl_end)) {

            if (!$events_created[implode('', (array) $event->getId()) . $start]) {
                $new_event = clone $event;
                $new_event->setStart($start);
                $new_event->setEnd($end);
                $events_created[implode('', (array) $event->getId()) . $start] = $new_event;
            }
        }
    }

    /**
     * Returns an array with all days between start and end of this SingleCalendar.
     * The keys are the timestamps of the days (12:00) and the values are number
     * of events for a day.
     *
     * @param string $user_id Use the permissions of this user.
     * @param array $restrictions
     * @return array An array with year day as key and number of events per day as value.
     */
    public function getListCountEvents($class_names = null, $user_id = null, $restrictions = null)
    {
        if (!is_array($class_names)) {
            $class_names = ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent', 'CourseMarkedEvent'];
        }
        $end = $this->getEnd();
        $start = $this->getStart();
        $year = date('Y', $start);
        $end_ts = mktime(12, 0, 0, date('n', $end), date('j', $end), date('Y', $end));
        $start_ts = mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start));
        $this->getEvents($class_names)->sortEvents();
        $daylist = [];
        $this->ts = mktime(12, 0, 0, 1, 1, $year);
        foreach ($this->events as $event) {
            if (!$event->havePermission(Event::PERMISSION_CONFIDENTIAL, $user_id)) {
                continue;
            }
            if (!SingleCalendar::checkRestriction($event, $restrictions)) {
                continue;
            }
            $properties = $event->getProperties();

            $rep = $properties['RRULE'];
            $duration = (int) ((mktime(12, 0, 0, date('n', $properties['DTEND']), date('j', $properties['DTEND']), date('Y', $properties['DTEND']))
                    - mktime(12, 0, 0, date('n', $properties['DTSTART']), date('j', $properties['DTSTART']), date('Y', $properties['DTSTART'])))
                    / 86400);

            // single event or first event
            $lwst = mktime(12, 0, 0, date('n', $properties['DTSTART']), date('j', $properties['DTSTART']), date('Y', $properties['DTSTART']));
            if ($start_ts > $lwst) {
                $adate = $start_ts;
            } else {
                $adate = $lwst;
            }
            $hgst = $lwst + $duration * 86400;
            while ($adate >= $start_ts && $adate <= $end_ts && $adate <= $hgst) {
                $md_date = $adate - date('I', $adate) * 3600;
                $this->countListEvent($properties, $md_date, $properties['DTSTART'], $properties['DTEND'], $daylist);
                $adate += 86400;
            }

            switch ($rep['rtype']) {
                case 'DAILY' :
                    if ($rep['ts'] < $start) {
                        // brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
                        if ($rep['linterval'] == 1) {
                            $adate = $this->ts;
                        } else {
                            $adate = $this->ts + ($rep['linterval'] - (($this->ts - $rep['ts']) / 86400)
                                    % $rep['linterval']) * 86400;
                        }
                        while ($adate <= $end_ts && $adate >= $this->ts && $adate <= $rep['expire']) {
                            $hgst = $adate + $duration * 86400;
                            $md_date = $adate;
                            while ($md_date <= $end_ts && $md_date >= $this->ts && $md_date <= $hgst) {
                                $md_date -= 3600 * date('I', $md_date);
                                $this->countListEvent($properties, $md_date, $adate, $hgst, $daylist);
                                $md_date += 86400;
                            }
                            $adate += $rep['linterval'] * 86400;
                        }
                    } else {
                        $adate = $rep['ts'];
                    }
                    while ($adate <= $end_ts && $adate >= $this->ts && $adate <= $rep['expire']) {
                        $hgst = $adate + $duration * 86400;
                        $md_date = $adate;
                        while ($md_date <= $end_ts && $md_date >= $this->ts && $md_date <= $hgst) {
                            $md_date += 3600 * date('I', $md_date);
                            $this->countListEvent($properties, $md_date, $adate, $hgst, $daylist);
                            $md_date += 86400;
                        }
                        $adate += $rep['linterval'] * 86400;
                    }
                    break;

                case 'WEEKLY' :
                    if ($properties['DTSTART'] >= $start && $properties['DTSTART'] <= $end) {
                        $lwst = mktime(12, 0, 0, date('n', $properties['DTSTART']), date('j', $properties['DTSTART']), date('Y', $properties['DTSTART']));
                        $hgst = $lwst + $duration * 86400;
                        if ($rep['ts'] != $adate) {
                            $wdate = $lwst;
                            while ($wdate <= $end_ts && $wdate >= $start_ts && $wdate <= $hgst) {
                              //  $md_date = $wdate - date('I', $wdate) * 3600;
                                $this->countListEvent($properties, $wdate, $lwst, $hgst, $daylist);
                                $wdate += 86400;
                            }
                        }
                        $aday = strftime('%u', $lwst) - 1;
                        for ($i = 0; $i < mb_strlen($rep['wdays']); $i++) {
                            $awday = (int) mb_substr($rep['wdays'], $i, 1) - 1;
                            if ($awday > $aday) {
                                $lwst = $lwst + ($awday - $aday) * 86400;
                                $hgst = $lwst + $duration * 86400;
                                $wdate = $lwst;
                                while ($wdate >= $start_ts && $wdate <= $end_ts && $wdate <= $hgst) {
                                  //  $md_date = $wdate - date('I', $wdate) * 3600;
                                    $this->countListEvent($properties, $wdate, $lwst, $hgst, $daylist);
                                    $wdate += 86400;
                                }
                            }
                        }
                    }
                    if ($rep['ts'] < $start) {
                        $adate = $start_ts - (strftime('%u', $start_ts) - 1) * 86400;
                        $adate += ( $rep['linterval'] - (($adate - $rep['ts']) / 604800)
                                % $rep['linterval']) * 604800;
                        $adate -= $rep['linterval'] * 604800;
                    } else {
                        $adate = $rep['ts'] + 604800 * $rep['linterval'];
                    }

                    while ($adate >= $properties['DTSTART'] && $adate <= $rep['expire'] && $adate <= $end) {
                        // event is repeated on different week days
                        for ($i = 0; $i < mb_strlen($rep['wdays']); $i++) {
                            $awday = (int) $rep['wdays']{$i};
                            $lwst = $adate + ($awday - 1) * 86400;
                            $hgst = $lwst + $duration * 86400;
                            if ($lwst < $start_ts) {
                                $lwst = $start_ts;
                            }
                            $wdate = $lwst;
                            while ($wdate >= $start_ts && $wdate <= $end_ts && $wdate <= $hgst) {
                               // $md_date = $wdate - date('I', $wdate) * 3600;
                                $this->countListEvent($properties, $wdate, $lwst, $hgst, $daylist);
                                $wdate += 86400;
                            }
                        }
                        $adate += 604800 * $rep['linterval'];
                    }
                    break;

                case 'MONTHLY' :
                    $bmonth = ($rep['linterval'] - ((($year - date('Y', $rep['ts'])) * 12)
                            - date('n', $rep['ts'])) % $rep['linterval']) % $rep['linterval'];

                    for ($amonth = $bmonth - $rep['linterval']; $amonth <= $bmonth; $amonth += $rep['linterval']) {
                        if ($rep['ts'] < $start) {
                            // is repeated at X. week day of X. month...
                            if (!$rep['day']) {
                                $lwst = mktime(12, 0, 0, $amonth
                                                - ((($year - date('Y', $rep['ts'])) * 12
                                                + ($amonth - date('n', $rep['ts']))) % $rep['linterval']), 1, $year)
                                        + ($rep['sinterval'] - 1) * 604800;
                                $aday = strftime('%u', $lwst);
                                $lwst -= ( $aday - $rep['wdays']) * 86400;
                                if ($rep['sinterval'] == 5) {
                                    if (date('j', $lwst) < 10) {
                                        $lwst -= 604800;
                                    }
                                    if (date('n', $lwst) == date('n', $lwst + 604800)) {
                                        $lwst += 604800;
                                    }
                                } else {
                                    if ($aday > $rep['wdays']) {
                                        $lwst += 604800;
                                    }
                                }
                            } else {
                                // or at X. day of month ?
                                $lwst = mktime(12, 0, 0, $amonth
                                        - ((($year - date('Y', $rep['ts'])) * 12
                                        + ($amonth - date('n', $rep['ts']))) % $rep['linterval']), $rep['day'], $year);
                            }
                        } else {
                            // first recurrence
                            $lwst = $rep['ts'];
                            $lwst = mktime(12, 0, 0, $amonth
                                        - ((($year - date('Y', $rep['ts'])) * 12
                                        + ($amonth - date('n', $rep['ts']))) % $rep['linterval']), $rep['day'], $year);

                        }
                        $hgst = $lwst + $duration * 86400;
                        $md_date = $lwst;
                        // events last longer than one day
                        while ($md_date >= $start_ts && $md_date <= $hgst && $md_date <= $end_ts) {
                            $this->countListEvent($properties, $md_date, $lwst, $hgst, $daylist);
                            $md_date += 86400;
                        }
                    }
                    break;

                case 'YEARLY' :
                    for ($ayear = $this->year - 1; $ayear <= $this->year; $ayear++) {
                        if ($rep['day']) {
                            $lwst = mktime(12, 0, 0, $rep['month'], $rep['day'], $ayear);
                            $hgst = $lwst + $duration * 86400;
                            $wdate = $lwst;
                            while ($hgst >= $start_ts && $wdate <= $hgst && $wdate <= $end_ts) {
                                $this->countListEvent($properties, $wdate, $lwst, $hgst, $daylist);
                                $wdate += 86400;
                            }
                        } else {
                            if ($rep['ts'] < $start) {
                                $adate = mktime(12, 0, 0, $rep['month'], 1, $ayear)
                                        + ($rep['sinterval'] - 1) * 604800;
                                $aday = strftime('%u', $adate);
                                $adate -= ( $aday - $rep['wdays']) * 86400;
                                if ($rep['sinterval'] == 5) {
                                    if (date('j', $adate) < 10) {
                                        $adate -= 604800;
                                    }
                                } elseif ($aday > $rep['wdays']) {
                                    $adate += 604800;
                                }
                            } else {
                                $adate = $rep['ts'];
                            }
                            $lwst = $adate;
                            $hgst = $lwst + $duration * 86400;
                            while ($hgst >= $start_ts && $adate <= $hgst && $adate <= $end_ts) {
                                $this->countListEvent($properties, $adate, $lwst, $hgst, $daylist);
                                $adate += 86400;
                            }
                        }
                    }
            }
        }
        return $daylist;
    }

    private function countListEvent($properties, $date, $lwst, $hgst, &$daylist)
    {
        if ($date < $this->getStart() || $date > $this->getEnd()) {
            return false;
        }
        $lwst = mktime(12, 0, 0, date('n', $lwst), date('j', $lwst), date('Y', $lwst));
        $hgst = mktime(12, 0, 0, date('n', $hgst), date('j', $hgst), date('Y', $hgst));

        // if this date is in the exceptions return false
        $exdates = explode(',', $properties['EXDATE']);
        foreach ($exdates as $exdate) {
            if ($exdate > 0 && $exdate >= $lwst && $exdate <= $hgst) {
                return false;
            }
        }
        // is event expired?
        if ($properties['RRULE']['expire'] > 0
                && $properties['RRULE']['expire'] <= $hgst) {
            return false;
        }
        $idate = date('Ymd', $date);
        $daylist["$idate"]["{$properties['STUDIP_ID']}"] =
                $daylist["$idate"]["{$properties['STUDIP_ID']}"]
                ? $daylist["$idate"]["{$properties['STUDIP_ID']}"]++ : 1;
        return true;
    }

    /**
     *
     * TODO use filter instead
     *
     * @param Event $event
     * @param array $restrictions
     * @return boolean
     */
    public static function checkRestriction(Event $event, $restrictions)
    {
        $properties = $event->getProperties();
        if (is_array($restrictions)) {
            foreach ($restrictions as $property_name => $restriction) {
                if (is_array($restriction)) {
                    return in_array($properties[mb_strtoupper($property_name)], $restriction);
                } else if ($restriction != '') {
                    return $properties[mb_strtoupper($property_name)] == $restriction;
                }
            }
            return true;
        }
        return true;
    }

    /**
     * Returns an array with all necessary informations to build the day view.
     *
     * @param type $start
     * @param type $end
     * @param type $step
     * @param type $params
     * @return type
     */
    public function createEventMatrix($start, $end, $step)
    {
        // correction of days where dst starts or ends
        $dst_offset = (date('I', $this->getStart()) - date('I', $this->getEnd())) * 3600;
        $start += $dst_offset;
        $end += $dst_offset;

        $term = [];
        $em = $this->adapt_events($start, $end, $step);
        $max_cols = 0;
        // calculate maximum number of columns
        $w = 0;
        for ($i = $start / $step; $i < $end / $step + 3600 / $step; $i++) {
            $col = 0;
            $row = $i - $start / $step;
            while ($w < sizeof($em['events']) && $em['events'][$w]->getStart() >= $this->getStart() + $i * $step
            && $em['events'][$w]->getStart() < $this->getStart() + ($i + 1) * $step) {
                $rows = ceil($em['events'][$w]->getDuration() / $step);
                if ($rows < 1) {
                    $rows = 1;
                }
                while ($term[$row][$col] != '' && $term[$row][$col] != '#') {
                    $col++;
                }
                $term[$row][$col] = $em['events'][$w];
                $mapping[$row][$col] = $em['map'][$w];

                $count = $rows - 1;
                for ($x = $row + 1; $x < $row + $rows; $x++) {
                    for ($y = 0; $y <= $col; $y++) {
                        if ($y == $col) {
                            $term[$x][$y] = $count--;
                        } elseif ($term[$x][$y] == '') {
                            $term[$x][$y] = '#';
                        }
                    }
                }
                if ($max_cols < sizeof($term[$row])) {
                    $max_cols = sizeof($term[$row]);
                }
                $w++;
            }
        }
        $row_min = 0;
        for ($i = $start / $step; $i < $end / $step + 3600 / $step; $i++) {
            $row = $i - $start / $step;
            $row_min = $row;
            while ($this->maxValue($term[$row], $step) > 1) {
                $row += $this->maxValue($term[$row], $step) - 1;
            }
            $size = 0;
            for ($j = $row_min; $j <= $row; $j++) {
                if (isset($term[$j]) && count($term[$j]) > $size) {
                    $size = count($term[$j]);
                }
            }
            for ($j = $row_min; $j <= $row; $j++) {
                $colsp[$j] = $size;
            }
            $i = $row + $start / $step;
        }
        $rows = [];
        for ($i = $start / $step; $i < $end / $step + 3600 / $step; $i++) {
            $row = $i - $start / $step;
            $cspan_0 = 0;
            if ($term[$row]) {
                if ($colsp[$row] > 0) {
                    $cspan_0 = (int) ($max_cols / $colsp[$row]);
                }
                for ($j = 0; $j < $colsp[$row]; $j++) {
                    $sp = 0;
                    $n = 0;
                    if ($j + 1 == $colsp[$row]) {
                        $cspan[$row][$j] = $cspan_0 + $max_cols % $colsp[$row];
                    }
                    if (is_object($term[$row][$j])) {
                        // Wieviele Termine sind zum aktuellen Termin zeitgleich?
                        $p = 0;
                        $count = 0;
                        while ($aterm = $em['events'][$p]) {
                            if ($aterm->getStart() >= $term[$row][$j]->getStart()
                                    && $aterm->getStart() <= $term[$row][$j]->getEnd()) {
                                $count++;
                            }
                            $p++;
                        }
                        if ($count == 0) {
                            for ($n = $j + 1; $n < $colsp[$row]; $n++) {
                                if (!is_int($term[$row][$n])) {
                                    $sp++;
                                } else {
                                    break;
                                }
                            }
                            $cspan[$row][$j] += $sp;
                        }
                        $rows[$row][$j] = ceil($term[$row][$j]->getDuration() / $step);
                        if ($rows[$row][$j] < 1) {
                            $rows[$row][$j] = 1;
                        }
                        if ($sp > 0) {
                            for ($m = $row; $m < $rows + $row; $m++) {
                                $colsp[$m] = $colsp[$m] - $sp + 1;
                                $v = $j;
                                while ($term[$m][$v] == '#') {
                                    $term[$m][$v] = 1;
                                }
                            }
                            $j = $n;
                        }
                    } elseif ($term[$row][$j] == '#') {
                        $csp = 1;
                        while ($term[$row][$j] == '#') {
                            $csp += $cspan[$row][$j];
                            $j++;
                        }
                        $cspan[$row][$j] = $csp;
                    } elseif ($term[$row][$j] == '') {
                        $cspan[$row][$j] = $max_cols - $j + 1;
                    }
                }
            }
        }
        if ($max_cols < 1 && isset($em['day_events']) && count($em['day_events'])) {
            $max_cols = 1;
        }
        $em['cspan'] = $cspan;
        $em['rows'] = $rows;
        $em['colsp'] = $colsp;
        $em['term'] = $term;
        $em['max_cols'] = $max_cols;
        $em['mapping'] = $mapping;
        return $em;
    }

    /**
     * Returns max value of colspan in calendar tables for day view.
     *
     * @param Array $term Array with table cell content.
     * @param int $st Seconds between each row in calendar table.
     * @return int Max value of colspan.
     */
    private function maxValue($term, $st)
    {
        $max_value = 0;

        if (is_array($term)) {
            for ($i = 0; $i < count($term); $i++) {
                if (is_object($term[$i])) {
                    $max = ceil($term[$i]->getDuration() / $st);
                } elseif ($term[$i] == '#') {
                    continue;
                } elseif ($term[$i] > $max_value) {
                    $max = $term[$i];
                }
                if ($max > $max_value) {
                    $max_value = $max;
                }
            }
        }

        return $max_value;
    }

    /**
     * Returns array with events and other information to build calendar tables
     * for day view.
     *
     * @param int $start Start time date as unix timestamp
     * @param int $end End time date as unix timestamp
     * @param int $step Seconds between each row in calendar table.
     * @return Array Array with new calculated events and some other things.
     */
    public function adapt_events($start, $end, $step = 900)
    {
        $tmp_events = [];
        $map_events = [];
        for ($i = 0; $i < sizeof($this->events); $i++) {
            $event = $this->events[$i];
            if (($event->getEnd() > $this->getStart() + $start)
                    && ($event->getStart() < $this->getStart() + $end + 3600)) {
                $cloned_event = clone $event;
                if ($event->isDayEvent()
                        || ($event->getStart() <= $this->getStart()
                        && $event->getEnd() >= $this->getEnd())) {
                    $cloned_event->setStart($this->getStart());
                    $cloned_event->setEnd($this->getEnd());
                    $tmp_day_event[] = $cloned_event;
                    $map_day_events[] = $i;
                } else {
                    $end_corr = $cloned_event->getEnd() % $step;
                    if ($end_corr > 0) {
                        $end_corr = $cloned_event->getEnd() + ($step - $end_corr);
                        $cloned_event->setEnd($end_corr);
                    }
                    if ($cloned_event->getStart() < ($this->getStart() + $start)) {
                        $cloned_event->setStart($this->getStart() + $start);
                    }
                    if ($cloned_event->getEnd() > ($this->getStart() + $end + 3600)) {
                        $cloned_event->setEnd($this->getStart() + $end + 3600);
                    }
                    $tmp_events[$cloned_event->id . $cloned_event->getStart()] = $cloned_event;
                    $map_events[$cloned_event->id . $cloned_event->getStart()] = $i;
                }
            }
        }

        uasort($tmp_events, function($a, $b) {return $a->start - $b->start;});
        $map = [];
        foreach (array_keys($tmp_events) as $key) {
            $map[] = $map_events[$key];
        }

        return [
            'events' => array_values($tmp_events),
            'map' => $map,
            'day_events' => $tmp_day_event,
            'day_map' => $map_day_events];
    }

}
