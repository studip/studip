<?php

namespace JsonApi\Schemas;

use JsonApi\Errors\InternalServerError;
use Neomerx\JsonApi\Document\Link;

class BlubberThread extends SchemaProvider
{
    const TYPE = 'blubber-threads';
    const REL_AUTHOR = 'author';
    const REL_COMMENTS = 'comments';
    const REL_CONTEXT = 'context';
    const REL_MENTIONS = 'mentions';

    protected $resourceType = self::TYPE;

    public function getId($resource)
    {
        return $resource->id;
    }

    public function getAttributes($resource)
    {
        $user = $this->getDiContainer()->get('studip-current-user');

        $attributes = [
            'context-type' => $resource['context_type'],
            'content' => $resource['content'],
            'content-html' => formatReady($resource['content']),

            'is-commentable' => (bool) $resource->isCommentable($user->id),
            'is-readable' => (bool) $resource->isReadable($user->id),
            'is-writable' => (bool) $resource->isWritable($user->id),

            'is-visible-in-stream' => (bool) $resource->isVisibleInStream(),

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
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
        $relationships = $this->getAuthorRelationship($relationships, $resource, $shouldInclude(self::REL_AUTHOR));

        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getCommentsRelationship($relationships, $resource, $shouldInclude(self::REL_COMMENTS));
        $relationships = $this->getContextRelationship($relationships, $resource, $shouldInclude(self::REL_CONTEXT));
        $relationships = $this->getMentionsRelationship($relationships, $resource, $shouldInclude(self::REL_MENTIONS));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getAuthorRelationship($relationships, $resource, $includeData)
    {
        if (!$resource['external_contact'] && $resource['user_id']) {
            $userId = $resource['user_id'];
            $related = $includeData ? \User::find($userId) : \User::build(['id' => $userId], false);
            $relationships[self::REL_AUTHOR] = [
                self::LINKS => [
                    Link::RELATED => $this->getSchemaContainer()
                                          ->getSchema($related)->getSelfSubLink($related),
                ],
                self::DATA => $related,
            ];
        }

        return $relationships;
    }

    // TODO #10245
    private function getMentionsRelationship(array $relationships, \BlubberThread $resource, $includeData)
    {
        if ($includeData) {
            $relatedUsers = $resource->mentions->pluck('user');
        } else {
            $relatedUsers = array_map(function ($mention) {
                return \User::build(['user_id' => $mention->user_id], false);
            }, \BlubberMention::findBySQL('thread_id = ?', [$resource->id]));
        }

        $relationships[self::REL_MENTIONS] = [
            self::SHOW_SELF => true,
            self::LINKS => [],
            self::DATA => $relatedUsers,
        ];

        return $relationships;
    }

    private function getCommentsRelationship(array $relationships, \BlubberThread $resource, $includeData)
    {
        $relationship = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_COMMENTS),
            ],
        ];

        if ($includeData) {
            $relationship[self::DATA] = $resource->comments;
        }

        $relationships[self::REL_COMMENTS] = $relationship;

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getContextRelationship(array $relationships, \BlubberThread $resource, $includeData)
    {
        $related = $data = null;

        if ('course' === $resource['context_type']) {
            if (!$course = \Course::find($resource['context_id'])) {
                throw new InternalServerError('Inconsistent data in BlubberThread.');
            }

            $related = new Link('/courses/'.$course->id);
            $data = $course;
        }

        if ('institute' === $resource['context_type']) {
            if (!$institute = \Institute::find($resource['context_id'])) {
                throw new InternalServerError('Inconsistent data in BlubberThread.');
            }

            $related = new Link('/institutes/'.$institute->id);
            $data = $institute;
        }

        if ($related && $data) {
            $relationships[self::REL_CONTEXT] = [
                self::SHOW_SELF => true,
                self::LINKS => [
                    Link::RELATED => $related,
                ],
                self::DATA => $data,
            ];
        }

        return $relationships;
    }
}
