<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class FeedbackElement extends SchemaProvider
{
    const TYPE = 'feedback-elements';
    const REL_AUTHOR = 'author';
    const REL_COURSE = 'course';
    const REL_ENTRIES = 'entries';
    const REL_RANGE = 'range';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return (int) $resource->id;
    }

    public function getAttributes($resource)
    {
        $attributes = [
            'question' => (string) $resource['question'],
            'description' => (string) $resource['description'],
            'mode' => (int) $resource['mode'],
            'results-visible' => (bool) $resource['results_visible'],
            'is-commentable' => (bool) $resource['commentable'],

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate'])
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta($resource)
    {
        return $resource['mode'] === 0
            ? null
            : [
                'ratings' => [
                    'count' => \FeedbackEntry::countBySql('feedback_id = ?', [$resource->id]),
                    'mean' => (float) $resource->getMeanOfRating()
                ]
            ];
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($resource, $isPrimary, array $includeList)
    {
        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];

        $relationships = $this->getAuthorRelationship($relationships, $resource, $shouldInclude(self::REL_AUTHOR));
        $relationships = $this->getCourseRelationship($relationships, $resource, $shouldInclude(self::REL_COURSE));
        $relationships = $this->getEntriesRelationship($relationships, $resource, $shouldInclude(self::REL_ENTRIES));
        $relationships = $this->getRangeRelationship($relationships, $resource, $shouldInclude(self::REL_RANGE));

        return $relationships;
    }

    private function getAuthorRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        $userId = $resource['user_id'];
        $related = $includeData ? \User::find($userId) : \User::build(['id' => $userId], false);
        $relationships[self::REL_AUTHOR] = [
            self::LINKS => [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($related)
                    ->getSelfSubLink($related)
            ],
            self::DATA => $related
        ];

        return $relationships;
    }

    private function getCourseRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        if ($courseId = $resource['course_id']) {
            $related = $includeData ? \Course::find($courseId) : \Course::build(['id' => $courseId], false);
            $relationships[self::REL_COURSE] = [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                        ->getSchema($related)
                        ->getSelfSubLink($related)
                ],
                self::DATA => $related
            ];
        }

        return $relationships;
    }

    private function getEntriesRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        $relationships[self::REL_ENTRIES] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_ENTRIES)
            ],
            self::DATA => $resource->entries
        ];

        return $relationships;
    }

    private function getRangeRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        $rangeType = $resource['range_type'];
        $rangeSchema = null;

        try {
            $rangeSchema = $this->getSchemaContainer()->getSchemaByType($rangeType);
        } catch (\InvalidArgumentException $e) {
        }

        if (
            isset($rangeSchema) &&
            is_subclass_of($rangeType, \FeedbackRange::class) &&
            is_subclass_of($rangeType, \SimpleORMap::class)
        ) {
            if ($range = $rangeType::find($resource['range_id'])) {
                $link = $rangeSchema->getSelfSubLink($range);

                $relationships[self::REL_RANGE] = [
                    self::LINKS => [Link::RELATED => $link],
                    self::DATA => $range
                ];
            }
        }

        return $relationships;
    }
}
