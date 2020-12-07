<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class ConsultationSlot extends SchemaProvider
{
    const TYPE = 'consultation-slots';
    const REL_BLOCK = 'block';
    const REL_BOOKINGS = 'bookings';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        $attributes = [
            'note'      => $resource->note,
            'note-html' => formatLinks($resource->note),

            'start_time' => date('c', $resource->start_time),
            'end_time'   => date('c', $resource->end_time),

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
        $relationships = $this->getBlockRelationship($relationships, $resource, $shouldInclude(self::REL_BLOCK));

        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getBookingsRelationship($relationships, $resource, $shouldInclude(self::REL_BOOKINGS));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getBlockRelationship($relationships, $resource, $includeData)
    {
        $block = $resource->block;

        $relationships[self::REL_BLOCK] = [
            self::LINKS => [
                Link::RELATED => new Link("/consultation-block/{$block->id}"),
            ],
            self::DATA => $includeData ? $block : \ConsultationBlock::build(['id' => $block->id], false),
        ];

        return $relationships;
    }

    private function getBookingsRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $relatedBookings = $resource->bookings;
        } else {
            $relatedBookings = array_map(function ($booking) {
                return \ConsultationBooking::build(['slot_id' => $booking->slot_id], false);
            }, $resource->bookings);
        }

        $relationships[self::REL_BOOKINGS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BOOKINGS),
            ],
            self::DATA => $relatedBookings,
        ];

        return $relationships;
    }
}
