<?php

namespace JsonApi\Routes\Forum;

use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Models\ForumEntry;
use JsonApi\Models\ForumCat;

abstract class AbstractEntriesCreate extends JsonApiController
{
    use ValidationTrait;

    protected function validateResourceDocument($json)
    {
        $content = self::arrayHas($json, 'data.attributes.title');
        if (empty($content)) {
            return 'Entries should not be empty.';
        }
    }

    protected function createEntryFromJSON($user, $parentId, $json)
    {
        //Check whether the parent is category or entry of first or seccond depth
        $title = self::arrayGet($json, 'data.attributes.title');
        $content = self::arrayGet($json, 'data.attributes.content');
        if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
            $content = transformBeforeSave(\Studip\Markup::purifyHtml($content));
        }
        $parent = $this->getParentObject($parentId);

        return $this->createEntry($title, $content, $parent, $user);
    }

    protected function getParentObject($parentId)
    {
        if ($parent = ForumCat::find($parentId)) {
            return $parent;
        }

        return ForumEntry::find($parentId);
    }

    protected function createEntry($title, $content, $parent, $user)
    {
        //Do we create id's like this?
        $topicId = md5(uniqid(rand()));
        if (empty($title)) {
            $title = $parent->name;
        }
        $data = [
            'topic_id' => $topicId,
            'seminar_id' => $parent->seminar_id,
            'user_id' => $user->id,
            'name' => $title,
            'content' => $content,
            'author' => $user->getFullName(),
            'anonymous' => 0,
        ];

        $entry = new ForumEntry();
        $entry->setData($data);
        $entry->storeWith($parent, $entry);

        return $entry;
    }
}
