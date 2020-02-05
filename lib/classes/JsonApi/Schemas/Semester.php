<?php

namespace JsonApi\Schemas;

class Semester extends SchemaProvider
{
    const TYPE = 'semesters';

    protected $resourceType = self::TYPE;

    public function getId($semester)
    {
        return $semester->id;
    }

    public function getAttributes($semester)
    {
        return [
            'title' => (string) $semester->name,
            'description' => (string) $semester->description,
            'start' => date('c', $semester->beginn),
            'end' => date('c', $semester->ende),
            // TODO: vorles_beginn
            // TODO: vorles_ende
        ];
    }
}
