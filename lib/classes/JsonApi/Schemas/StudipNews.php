<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class StudipNews extends SchemaProvider
{
    const TYPE = 'news';
    const REL_AUTHOR = 'author';
    const REL_COMMENTS = 'comments';
    const REL_RANGES = 'ranges';

    protected $resourceType = self::TYPE;

    public static function getRangeClasses()
    {
        return [
            'sem' => \Course::class,
            'user' => \User::class,
            'inst' => \Institute::class,
            'fak' => \Institute::class,
        ];
    }

    public static function getRangeTypes()
    {
        return [
            'global' => Studip::TYPE,
            'sem' => Course::TYPE,
            'user' => User::TYPE,
            'inst' => Institute::TYPE,
            'fak' => Institute::TYPE,
        ];
    }

    public function getId($news)
    {
        return $news->news_id;
    }

    public function getAttributes($news)
    {
        return [
            'title' => (string) $news->topic,
            'content' => (string) $news->body,
            'mkdate' => date('c', $news->mkdate),
            'chdate' => date('c', $news->chdate),
            'publication-start' => date('c', $news->date),
            'publication-end' => date('c', $news->date + $news->expire),
            'comments-allowed' => (bool) $news->allow_comments,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($news, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships = $this->addAuthorRelationship($relationships, $news, $includeList);
        $relationships = $this->addCommentsRelationship($relationships, $news, $includeList);
        $relationships = $this->addRangesRelationship($relationships, $news, $includeList);

        return $relationships;
    }

    private function addAuthorRelationship($relationships, $news, $includeList)
    {
        $data = in_array(self::REL_AUTHOR, $includeList)
              ? $news->owner
              : \User::build(['id' => $news->user_id], false);
        $relationships[self::REL_AUTHOR] = [
            self::LINKS => [
                Link::RELATED => new Link('/users/'.$news->user_id),
            ],
            self::DATA => $data,
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addCommentsRelationship($relationships, $news, $includeList)
    {
        $relationships[self::REL_COMMENTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($news, self::REL_COMMENTS)
            ]
        ];

        return $relationships;
    }

    private function addRangesRelationship($relationships, $news, $includeList)
    {
        $relationships[self::REL_RANGES] = [
            self::SHOW_SELF => true,
            self::DATA => $this->prepareRanges($news, $includeList),
        ];

        return $relationships;
    }

    private function prepareRanges($news, $includeList)
    {
        $include = in_array(self::REL_RANGES, $includeList);

        return $news->news_ranges->map(function ($range) use ($include) {
            switch ($range->type) {
                case 'global':
                    return $this->getDiContainer()->get('studip-system-object');

                case 'sem':
                    return $include
                        ? $range->course
                        : \Course::build(['id' => $range->range_id], false);

                case 'user':
                    return $include
                        ? $range->user
                        : \User::build(['id' => $range->range_id], false);

                case 'inst':
                case 'fak':
                    return $include
                        ? $range->institute
                        : \Institute::build(['id' => $range->range_id], false);
            }
        });
    }
}
