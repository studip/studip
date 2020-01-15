<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ForumEntry;

/**
 * Löscht eine Forum-Kategorie.
 */
class ForumEntriesDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$entry = ForumEntry::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!$course = \Course::find($entry->seminar_id)) {
            throw new RecordNotFoundException('could not find course');
        }
        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$entry->deleteEntry($entry->topic_id)) {
            throw new RecordNotFoundException();
        }

        return $this->getCodeResponse(204);
    }
}
