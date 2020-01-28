<?php

namespace JsonApi\Routes\Forum;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\InternalServerError;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Models\ForumEntry;

/**
 * Edits content of a news.
 */
class ForumEntriesUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!$entry = ForumEntry::find($args['id'])) {
            throw new RecordNotFoundException('Entry has not been found.');
        }
        if (!$course = \Course::find($entry->seminar_id)) {
            throw new RecordNotFoundException('Course does not exist.');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$entry = $this->updateEntryFromJSON($entry, $json)) {
            throw new InternalServerError('Could not update the entry.');
        }

        return $this->getContentResponse($entry);
    }

    protected function updateEntryFromJSON($entry, $json)
    {
        $title = self::arrayGet($json, 'data.attributes.title');
        $content = self::arrayGet($json, 'data.attributes.content');
        if (!empty($title)) {
            $entry->name = $title;
        }
        if (!empty($content)) {
            if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
                $content = transformBeforeSave(\Studip\Markup::purifyHtml($content));
            }
            $entry->content = $content;
        }
        if ($entry->isDirty()) {
            $entry->store();
        }

        return $entry;
    }

    protected function validateResourceDocument($json)
    {
        $title = self::arrayGet($json, 'data.attributes.title');
        $content = self::arrayGet($json, 'data.attributes.content');

        if (empty($title) && empty($content)) {
            return 'You must change entry-data to update.';
        }
    }
}
