<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;
use JsonApi\Models\ForumEntry as Entry;

class ForumCategory extends SchemaProvider
{
    const TYPE = 'forum-categories';
    const REL_COURSE = 'course';
    const REL_ENTRY = 'entries';

    protected $resourceType = self::TYPE;

    public function getId($category)
    {
        return $category->id;
    }

    public function getAttributes($category)
    {
        return [
            'title' => $category->entry_name,
            'position' => (int) $category->pos,
        ];
    }

    public function getRelationships($category, $isPrimary, array $includeList)
    {
        $relationships = [];
        if ($isPrimary) {
            $relationships = $this->addCourseRelationship($category, $isPrimary, $includeList);
            $relationships = $this->addEntryRelationship($category, $isPrimary, $includeList);
        }

        return $relationships;
    }

    public function addCourseRelationship($category, $isPrimary, $includeList)
    {
        $link = new Link('/courses/'.$category->seminar_id);
        $data = $isPrimary && in_array(self::REL_COURSE, $includeList)
              ? \Course::find($category->seminar_id)
              : \Course::buildExisting(['id' => $category->seminar_id]);
        $relationships = [
            self::REL_COURSE => [
                self::LINKS => [Link::RELATED => $link],
                self::DATA => $data,
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addEntryRelationship($category, $isPrimary, $includeList)
    {
        $data = Entry::getEntriesFromCat($category);
        $link = new Link('/forum-categories/'.($category->id).'/entries');
        $relationships[self::REL_ENTRY] = [
            self::DATA => $data,
            self::LINKS => [
                Link::RELATED => $link,
            ],
        ];

        return $relationships;
    }
}
