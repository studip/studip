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
            'token' => (string) $semester->token,
            'start' => date('c', $semester->beginn),
            'end' => date('c', $semester->ende),
            'start-of-lectures' => date('c', $semester->vorles_beginn),
            'end-of-lectures' => date('c', $semester->vorles_ende),
            'visible' => (bool) $semester->visible,
        ];
    }
}
