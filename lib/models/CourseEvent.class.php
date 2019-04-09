<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 */

class CourseEvent extends CourseDate implements Event
{
    private $properties = null;
    private $permission_user_id = null;

    protected static function configure($config = [])
    {
        $config['alias_fields']['event_id'] = 'termin_id';
        $config['alias_fields']['start'] = 'date';
        $config['alias_fields']['end'] = 'end_time';
        $config['alias_fields']['category_intern'] = 'date_typ';
        $config['alias_fields']['author_id'] = 'autor_id';
        $config['alias_fields']['sem_id'] = 'range_id';

        $config['additional_fields']['location']['get'] = 'getRoomName';
        $config['additional_fields']['type'] = true;
        $config['additional_fields']['name']['get'] = function ($event) {
            return $event->course->getFullname();
        };
        $config['additional_fields']['title']['get'] = 'getTitle';
        $config['additional_fields']['editor_id']['get'] = function ($date) {
            return null;
        };
        $config['additional_fields']['uid']['get'] = function ($date) {
            return 'Stud.IP-SEM-' . $date->getId()
                    . '@' . $_SERVER['SERVER_NAME'];
        };
        $config['additional_fields']['summary']['get'] = function ($date) {
            return $date->course->name;
        };
        $config['additional_fields']['description']['get'] = function ($date) {
            return '';
        };
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        $this->permission_user_id = $GLOBALS['user']->id;
        parent::__construct($id);
    }

    /**
     * Returns all CourseEvents in the given time range for the given range_id.
     *
     * @param string $user_id Id of Stud.IP object from type user, course, inst
     * @param DateTime $start The start date time.
     * @param DateTime $end The end date time.
     * @return SimpleORMapCollection Collection of found CourseEvents.
     */
    public static function getEventsByInterval($user_id, DateTime $start, dateTime $end)
    {
        $stmt = DBManager::get()->prepare('SELECT termine.* FROM seminar_user '
                . 'INNER JOIN termine ON seminar_id = range_id '
                . 'WHERE user_id = :user_id '
                . 'AND bind_calendar = 1 '
                . 'AND date BETWEEN :start AND :end '
                . "AND (IFNULL(metadate_id, '') = '' "
                . 'OR metadate_id NOT IN ( '
                . 'SELECT metadate_id FROM schedule_seminare '
                . 'WHERE user_id = :user_id AND visible = 0) ) '
                . 'ORDER BY date ASC');
        $stmt->execute([
            ':user_id' => $user_id,
            ':start'   => $start->getTimestamp(),
            ':end'     => $end->getTimestamp()
        ]);
       $event_collection = [];
       foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
           $event = new CourseEvent();
           $event->setData($row);
           $event->setNew(false);
           // related persons (dozenten) or groups
           if (self::checkRelated($event, $user_id)) {
               $event_collection[] = $event;
           }
        }
        $event_collection = SimpleORMapCollection::createFromArray($event_collection, false);
        $event_collection->setClassName('Event');
        return $event_collection;
    }

    /**
     * Checks if given user is the responsible lecturer or is member of a
     * related group
     *
     * @global object $perm The globa perm object.
     * @param CourseEvent $event The course event to check against.
     * @param string $user_id The id of the user.
     * @return boolean
     */
    protected static function checkRelated(CourseEvent $event, $user_id)
    {
        global $perm;

        $check_related = false;
        $permission = $perm->get_studip_perm($event->range_id, $user_id);
        switch ($permission) {
            case 'dozent' :
                $related_persons = $event->dozenten->pluck('user_id');
                if (sizeof($related_persons)) {
                    if (in_array($user_id, $related_persons)) {
                        $check_related = true;
                    }
                } else {
                    $check_related = true;
                }
                break;
            case 'tutor' :
                $check_related = true;
                break;
            default :
                $group_ids = $event->statusgruppen->pluck('statusgruppe_id');
                if (sizeof($group_ids)) {
                    $member = StatusgruppeUser::findBySQL(
                            'statusgruppe_id IN(?) AND user_id = ?',
                            [$group_ids, $user_id]);
                    $check_related = sizeof($member) > 0;
                } else {
                    $check_related = true;
                }
        }
        return $check_related;
    }

    /**
     * Returns the name of the category.
     *
     * @return string the name of the category
     */
    public function toStringCategories($as_array = false)
    {
        $category = '';
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            $category = $GLOBALS['TERMIN_TYP'][$this->getCategory()]['name'];
        }
        return $as_array ? [$category] : $category;
    }

    /**
     * Returns the id of the related course
     *
     * @return string The id of the related course.
     */
    public function getSeminarId()
    {
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            return $this->sem_id;
        }
        return null;
    }

    /**
     * Returns an array that represents the recurrence rule for this event.
     * If an index is given, returns only this field of the rule.
     *
     * @return array|string The array with th recurrence rule or only one field.
     */
    public function getRecurrence($index = null)
    {
        $rep = ['ts' => 0, 'linterval' => 0, 'sinterval' => 0, 'wdays' => '',
            'month' => 0, 'day' => 0, 'rtype' => 'SINGLE', 'duration' => 1];
        return $index ? $rep[$index] : $rep;
    }

    /**
     * Returns the name of the related course.
     *
     * @return string The name of the related course.
     */
    public function getSemName()
    {
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            return $this->course->name;
        }
        return '';
    }

    /**
     * TODO Wird das noch benötigt?
     */
    public function getType()
    {
        return 1;
    }

    /**
     * Returns the title of this event.
     * The title of a course event is the name of the course or if a topic is
     * assigned, the title of this topic. If the user has not the permission
     * Event::PERMISSION_READABLE, the title is "Keine Berechtigung.".
     *
     * @return string
     */
    public function getTitle()
    {
        $title = _('Keine Berechtigung.');
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            $description = trim($this->cycle->description) ?: '';
            if (sizeof($this->topics)) {
                $title = $this->course->name.": ".implode(', ', $this->topics->pluck('title'));
            } else {
                $title = $this->course->name;
            }
            $title = ($description ? $description . ', ' : '') . $title;
        }
        return $title;
    }

    /**
     * Returns the starttime as unix timestamp of this event.
     *
     * @return int The starttime of this event as a unix timestamp.
     */
    public function getStart()
    {
        return $this->date;
    }

    /**
     * Sets the start date time with given unix timestamp.
     *
     * @param string $timestamp Unix timestamp.
     */
    public function setStart($timestamp)
    {
        $this->date = $timestamp;
    }

    /**
     * Returns the endtime of this event.
     *
     * @return int The endtime of this event as a unix timestamp.
     */
    public function getEnd()
    {
        return $this->end_time;
    }

    /**
     * Sets the end date time by given unix timestamp.
     *
     * @param string $timestamp Unix timestamp.
     */
    public function setEnd($timestamp)
    {
        $this->end_time = $timestamp;
    }

    /**
     * Returns the duration of this event in seconds.
     *
     * @return int the duration of this event in seconds
     */
    function getDuration()
    {
        return $this->end - $this->start;
    }

    /**
     * Returns the location.
     * Without permission or the location is not set an empty string is returned.
     *
     * @see ClendarDate::getRoomName()
     * @return string The location
     */
    function getLocation()
    {
        $location = '';
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            $location = $this->getRoomName();
        }
        return $location;
    }

    /**
     * Returns the global unique id of this event.
     *
     * @return string The global unique id.
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Returns the description of the topic.
     * If the user has no permission or the event has no topic
     * or the topics have no descritopn an empty string is returned.
     *
     * @return String the description
     */
    function getDescription()
    {
        $description = '';
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            $descriptions = $this->topics->map(function ($topic) {
                $desc = $topic->title . "\n";
                $desc .= $topic->description;
            });
            $description = implode("\n\n", $descriptions);
        }
        return $description;
    }

    /**
     * Returns the Stud.IP build in category as integer value.
     * If the user has no permission, 255 is returned.
     *
     * @See config.inc.php $PERS_TERMIN
     * @return int the categories
     */
    public function getStudipCategory()
    {
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            return $this->date_typ;
        }
        return 255;
    }

    /**
     * Returns the index of the category.
     * If the user has no permission, 255 is returned.
     *
     * TODO remove? use getStudipCategory instead?
     *
     * @see config/config.inc.php $TERMIN_TYP
     * @return int The index of the category
     */
    public function getCategory()
    {
        if ($this->havePermission(Event::PERMISSION_READABLE)) {
            return $this->date_typ;
        }
        return 255;
    }

    /**
     * Returns the user id of the last editor.
     * Since course events have no editor null is returned.
     *
     * @return null|int Returns always null.
     */
    public function getEditorId()
    {
        return null;
    }

    /**
     * Returns whether the event is a all day event.
     *
     * @return
     */
    public function isDayEvent()
    {
        return (($this->end - $this->start) / 60 / 60) > 23;
    }

    /**
     * Returns the accessibility of this event. The value is not influenced by
     * the permission of the actual user.
     *
     * According to RFC5545 the accessibility (property CLASS) is represented
     * by the 3 state PUBLIC, PRIVATE and CONFIDENTIAL
     *
     * TODO check this statement:
     * An course event is always CONFIDENTIAL
     *
     * @return string The accessibility as string.
     */
    public function getAccessibility()
    {
        return 'CONFIDENTIAL';
    }

    /**
     * Returns the unix timestamp of the last change.
     *
     * @access public
     */
    public function getChangeDate()
    {
        return $this->chdate;
    }

    /**
     * Returns the date time the event was imported.
     * Since course events are not imported normaly, returns the date time
     * of creation.
     *
     * @return int Date time of import as unix timestamp:
     */
    public function getImportDate()
    {
        return $this->mkdate;
    }

    /**
     * Returns all related groups.
     *
     * TODO remove, use direct access to field CourseDate::statusgruppen.
     *
     * @return SimpleORMapCollection The collection of statusgruppen.
     */
    public function getRelatedGroups()
    {
        return $this->statusgruppen;
    }

    public function getProperties()
    {
        if ($this->properties === null) {
            $this->properties = [
                'DTSTART' => $this->getStart(),
                'DTEND' => $this->getEnd(),
                'SUMMARY' => $this->getTitle(),
                'DESCRIPTION' => $this->getDescription(),
                'LOCATION' => $this->getLocation(),
                'CATEGORIES' => $this->toStringCategories(),
                'STUDIP_CATEGORY' => $this->getStudipCategory(),
                'CREATED' => $this->mkdate,
                'LAST-MODIFIED' => $this->chdate,
                'STUDIP_ID' => $this->termin_id,
                'SEM_ID' => $this->range_id,
                'SEMNAME' => $this->course->name,
                'CLASS' => 'CONFIDENTIAL',
                'UID' => CourseEvent::getUid(),
                'RRULE' => CourseEvent::getRecurrence(),
                'EXDATE' => '',
                'EVENT_TYPE' => 'sem',
                'STATUS' => 'CONFIRMED',
                'DTSTAMP' => time()];
        }
        return $this->properties;
    }

    /**
     * Returns the value of property with given name.
     *
     * @param type $name See CalendarEvent::getProperties() for accepted values.
     * @return mixed The value of the property.
     * @throws InvalidArgumentException
     */
    public function getProperty($name)
    {
        if ($this->properties === null) {
            $this->getProperties();
        }

        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        throw new InvalidArgumentException(get_class($this)
                . ': Property ' . $name . ' does not exist.');
    }

    public function setPermissionUser($user_id)
    {
        $this->permission_user_id = $user_id;
    }

    public function havePermission($permission, $user_id = null)
    {
        $perm = $this->getPermission($user_id);
        return $perm >= $permission;
    }

    public function getPermission($user_id = null)
    {
        global $perm;

        $user_id = $user_id ?: $this->permission_user_id;
        $course_perm = $perm->get_studip_perm($this->range_id, $user_id);
        $permission = Event::PERMISSION_FORBIDDEN;
        switch ($course_perm) {
            case 'tutor':
            case 'dozent':
                $permission = Event::PERMISSION_WRITABLE;
                break;
            case 'user':
            case 'autor':
                $permission = Event::PERMISSION_READABLE;
                break;
            default:
                $permission = Event::PERMISSION_FORBIDDEN;
        }

        return $permission;
    }

    /**
     * Course events have no priority so returns always an empty string.
     *
     * @return string The priority as a string.
     */
    public function toStringPriority()
    {
        return '';
    }

    /**
     * Course events have no accessibility settings so returns always the
     * an empty string.
     *
     * @return string The accessibility as string.
     */
    public function toStringAccessibility()
    {
        return '';
    }

    /**
     * Returns a string representation of the recurrence rule.
     * Since course events have no recurence defined it returns an empty string.
     *
     * @param bool $only_type If true returns only the type of recurrence.
     * @return string The recurrence rule - human readable
     */
    public function toStringRecurrence($only_type = false)
    {
        return '';
    }

    /**
     * Returns the author of this event as user object.
     *
     * @return User|null User object.
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Course events have no editor so always null is returned.
     *
     * @return null
     */
    public function getEditor()
    {
        return null;
    }
}
