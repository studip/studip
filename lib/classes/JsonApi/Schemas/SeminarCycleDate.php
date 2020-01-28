<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class SeminarCycleDate extends SchemaProvider
{
    const TYPE = 'seminar-cycle-dates';
    const REL_OWNER = 'owner';

    protected $resourceType = self::TYPE;

    public function getId($entry)
    {
        return $entry->id;
    }

    public function getAttributes($entry)
    {
        $course = \Course::find($entry->seminar_id);

        return [
            'title' => self::createTitle($course),
            'description' => mb_strlen(trim($entry->description)) ? $entry->description : null,

            'start' => sprintf('%02d:%02d', $entry->start_hour, $entry->start_minute),
            'end' => sprintf('%02d:%02d', $entry->end_hour, $entry->end_minute),
            'weekday' => (int) $entry->weekday,

            'recurrence' => $this->getRecurring($entry),

            'locations' => self::createLocation($entry),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($entry, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($course = \Course::find($entry->seminar_id)) {
            $link = $this->getSchemaContainer()->getSchema($course)->getSelfSubLink($course);
            $relationships = [
                self::REL_OWNER => [self::LINKS => [Link::RELATED => $link], self::DATA => $course],
            ];
        }

        return $relationships;
    }

    private function getRecurring($entry)
    {
        $dateFn = function ($date) {
            return self::icalDate($date['date']);
        };

        $recurring = [
            'FREQ' => 'WEEKLY',
            'INTERVAL' => $entry->cycle + 1,
            'DTSTART' => $dateFn($entry->dates->first()),
            'UNTIL' => $dateFn($entry->dates->last()),
        ];

        if (count($entry->exdates)) {
            $recurring['EXDATES'] = $entry->exdates->map($dateFn);
        }

        return $recurring;
    }

    private static function icalDate($dateTime0)
    {
        return date('c', $dateTime0);
    }

    private static function createTitle($course)
    {
        if (!isset($course)) {
            return null;
        }

        if ($course->veranstaltungsnummer) {
            $title = sprintf('%s %s', $course->veranstaltungsnummer, $course->name);
        } else {
            $title = $course->name;
        }

        return $title;
    }

    private static function createLocation(\SeminarCycleDate $entry)
    {
        $cycle = new \CycleData($entry);

        // check, if the date is assigned to a room
        if ($rooms = $cycle->getPredominantRoom(0, 0)) {
            return array_unique(getPlainRooms($rooms));
        } elseif ($rooms = $cycle->getFreeTextPredominantRoom(0, 0)) {
            unset($rooms['']);

            return array_keys($rooms);
        }

        return [];
    }
}
