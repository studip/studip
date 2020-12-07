<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class ConsultationBlock extends SchemaProvider
{
    const TYPE = 'consultation-blocks';
    const REL_SLOTS = 'slots';
    const REL_RANGE = 'range';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        $attributes = [
            'start' => date('c', $resource->start),
            'end'   => date('c', $resource->end),

            'room' => $resource->room,
            'size' => (int) $resource->size,

            'show-participants' => (bool) $resource->show_participants,
            'require-reason'    => $resource->require_reason,

            'confirmation-text'      => $resource->confirmation_text ?: null,
            'confirmation-text-html' => formatLinks($resource->confirmation_text) ?: null,

            'note'      => $resource->note,
            'note-html' => formatLinks($resource->note),

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
        ];

        return $attributes;
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
        $relationships = $this->getSlotsRelationship($relationships, $resource, $shouldInclude(self::REL_SLOTS));

        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getRangeRelationship($relationships, $resource, $shouldInclude(self::REL_RANGE));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getSlotsRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $relatedSlots = $resource->slots;
        } else {
            $relatedSlots = array_map(function ($slot) {
                return \ConsultationSlot::build(['id' => $slot->id], false);
            }, $resource->slots);
        }

        $relationships[self::REL_SLOTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_SLOTS),
            ],
            self::DATA => $relatedSlots,
        ];

        return $relationships;
    }

    private function getRangeRelationship($relationships, $resource, $includeData)
    {
        $range = $resource->range;

        $relationships[self::REL_RANGE] = [
            self::LINKS => [
                Link::RELATED => $this->getLinkForRange($range),
            ],
            self::DATA => $includeData ? $range : $this->getMinimalRange($range),
        ];

        return $relationships;
    }

    private function getLinkForRange(Range $range)
    {
        if ($range instanceof \Course) {
            return new Link("/courses/{$range->id}");
        }

        if ($range instanceof \Institute) {
            return new Link("/institutes/{$range->id}");
        }

        if ($range instanceof \User) {
            return new Link("/users/{$range->id}");
        }

        throw new \Exception('Unknown range type');
    }

    private function getMinimalRange(Range $range)
    {
        if ($range instanceof \Course) {
            return Course::build(['id' => $range->id], false);
        }

        if ($range instanceof \Institute) {
            return Institute::build(['id' => $range->id], false);
        }

        if ($range instanceof \User) {
            return User::build(['id' => $range->id], false);
        }

        throw new \Exception('Unknown range type');
    }
}
