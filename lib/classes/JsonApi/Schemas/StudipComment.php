<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class StudipComment extends SchemaProvider
{
    const TYPE = 'comments';
    const REL_AUTHOR = 'author';
    const REL_NEWS = 'news';

    protected $resourceType = self::TYPE;

    public function getId($comment)
    {
        return $comment->comment_id;
    }

    public function getAttributes($comment)
    {
        return [
            'content' => $comment->content,
            'mkdate' => date('c', $comment->mkdate),
            'chdate' => date('c', $comment->chdate),
        ];
    }

    public function getRelationships($comment, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships[self::REL_AUTHOR] = [
                self::LINKS => [
                    Link::RELATED => new Link('/users/'.$comment->user_id),
                ],
                self::DATA => \User::build(['id' => $comment->user_id], false),
            ];
            $relationships[self::REL_NEWS] = [
                self::LINKS => [
                    Link::RELATED => new Link('/news/'.$comment->object_id),
                ],
                self::DATA => in_array(self::REL_NEWS, $includeList)
                ? $comment->news :
                \StudipNews::build(['id' => $comment->object_id], false),
            ];
        }

        return $relationships;
    }
}
