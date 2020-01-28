<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class ScheduleEntry extends SchemaProvider
{
    const TYPE = 'schedule-entries';
    const REL_OWNER = 'owner';

    protected $resourceType = self::TYPE;

    public function getId($entry)
    {
        return $entry->id;
    }

    public function getAttributes($entry)
    {
        return [
            'title' => $entry->title,
            'description' => mb_strlen(trim($entry->content)) ? $entry->content : null,

            'start' => $this->formatTime($entry->start),
            'end' => $this->formatTime($entry->end),
            'weekday' => (int) $entry->day,

            'color' => $entry->color,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($entry, $isPrimary, array $includeList)
    {
        $link = $this->getSchemaContainer()->getSchema($entry->user)->getSelfSubLink($entry->user);

        $relationships = [
            self::REL_OWNER => [
                self::LINKS => [
                    Link::RELATED => $link,
                ],
                self::DATA => $entry->user,
            ],
        ];

        return $relationships;
    }

    private function formatTime($time)
    {
        return sprintf('%02d:%02d', (int) ($time / 100), (int) ($time % 100));
    }
}
