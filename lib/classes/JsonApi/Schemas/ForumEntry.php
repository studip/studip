<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;
use JsonApi\Models\ForumCat;

class ForumEntry extends SchemaProvider
{
    const TYPE = 'forum-entries';
    const REL_CAT = 'category';
    const REL_ENTRY = 'entries';

    protected $resourceType = self::TYPE;

    public function getId($entry)
    {
        return $entry->topic_id;
    }

    public function getAttributes($entry)
    {
        return [
            'title' => $entry->name,
            'area' => (int) $entry->area,
            'content' => $entry->content,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($entry, $isPrimary, array $includeList)
    {
        $relationships = [];
        if ($isPrimary) {
            $relationships = $this->addCategoryRelationship($relationships, $entry, $includeList);
            $relationships = $this->addChildEntryRelationship($relationships, $entry, $includeList);
        }

        return $relationships;
    }

    private function addCategoryRelationship($relationships, $entry, $includeList)
    {
        $cat_link = new Link('/forum-categories/'.($entry->category)->id);
        $cat_data = in_array(self::REL_CAT, $includeList)
            ? ForumCat::find($entry->category->id)
            : ForumCat::buildExisting(['id' => $entry->category->id]);

        $relationships[self::REL_CAT] = [
            self::LINKS => [
                Link::RELATED => $cat_link,
            ],
            self::DATA => $cat_data,
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addChildEntryRelationship($relationships, $entry, $includeList)
    {
        $relationships[self::REL_ENTRY] = [
            self::DATA => $entry->getChildEntries($entry->id),

            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($entry, self::REL_ENTRY),
            ],
        ];

        return $relationships;
    }
}
