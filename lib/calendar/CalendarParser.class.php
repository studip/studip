<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarParser.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calendar
 */

class CalendarParser
{
    private $events = [];
    protected $components;
    private $type;
    private $number_of_events;
    protected $public_to_private = false;
    protected $client_identifier;
    private $time;

    public function __construct()
    {
        $this->client_identifier = '';
    }

    public function parse($data, $ignore = null)
    {
        foreach ($data as $properties) {
            if ($this->public_to_private && $properties['CLASS'] == 'PUBLIC') {
                $properties['CLASS'] = 'PRIVATE';
            }
            $properties['CATEGORIES'] = implode(', ', $properties['CATEGORIES']);
            $this->components[] = $properties;
        }
    }

    public function getCount($data)
    {
        return 0;
    }

    public function parseIntoDatabase($range_id, $data, $ignore)
    {
        if ($this->parseIntoObjects($range_id, $data, $ignore)) {
            foreach ($this->events as $event) {
                $event->store();
            }
            return true;
        }

        return false;
    }

    public function parseIntoObjects($range_id, $data, $ignore)
    {
        $this->time = time();
        if ($this->parse($data, $ignore)) {
            if (is_array($this->components)) {
                foreach ($this->components as $component) {
                    $calendar_event = CalendarEvent::findByUid($component['UID'], $range_id);
                    if ($calendar_event) {
                        $this->setProperties($calendar_event, $component);
                        $calendar_event->setRecurrence($component['RRULE']);
                        $this->events[] = $calendar_event;
                    } else {
                        $calendar_event = new CalendarEvent();
                        $event = new EventData();
                        $event->author_id = $GLOBALS['user']->id;
                        $event->event_id = $event->getNewId();
                        $event->uid = $component['UID'];
                        $calendar_event->range_id = $range_id;
                        $calendar_event->event_id = $event->event_id;
                        $calendar_event->event = $event;
                        $this->setProperties($calendar_event, $component);
                        $calendar_event->setRecurrence($component['RRULE']);
                        $this->events[] = $calendar_event;
                    }
                }
            }
            return true;
        }
        $message = _('Die Import-Daten konnten nicht verarbeitet werden!');

        return false;
    }
    
    private function setProperties($calendar_event, $component)
    {
        $calendar_event->setStart($component['DTSTART']);
        $calendar_event->setEnd($component['DTEND']);
        $calendar_event->setTitle($component['SUMMARY']);
        $calendar_event->event->description = $component['DESCRIPTION'];
        $calendar_event->setAccessibility($component['CLASS']);
        $calendar_event->setUserDefinedCategories($component['CATEGORIES']);
        $calendar_event->event->category_intern = $component['STUDIP_CATEGORY'] ?: 1;
        $calendar_event->setPriority($component['PRIORITY']);
        $calendar_event->event->location = $component['LOCATION'];
        $calendar_event->setExceptions($component['EXDATE']);
        $calendar_event->event->mkdate = $component['CREATED'];
        $calendar_event->event->chdate = $component['LAST-MODIFIED'] ?: $component['CREATED'];
        $calendar_event->event->importdate = $this->time;
    }

    public function getType()
    {
        return $this->type;
    }

    public function &getObjects()
    {
        return $objects =& $this->events;
    }

    public function changePublicToPrivate($value = true)
    {
        $this->public_to_private = $value;
    }

    public function getClientIdentifier($data = null)
    {
        return $this->client_identifier;
    }
}

