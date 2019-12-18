<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Models\ForumEntry;

class ForumEntryEntriesCreate extends AbstractEntriesCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);

        if (!$related = ForumEntry::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!$course = \Course::find($related->seminar_id)) {
            throw new RecordNotFoundException('Could not find course.');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$entry = $this->createEntryFromJSON($user, $related->id, $json)) {
            throw new InternalServerError('could not create forum-entry');
        }

        return $this->getCreatedResponse($entry);
    }
}
