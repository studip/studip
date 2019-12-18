<?php

namespace JsonApi\Routes\Schedule;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ScheduleEntry;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Zeige einen selbst eingetragenen Eintrag eines Stundenplans.
 */
class ScheduleEntriesShow extends JsonApiController
{
    protected $allowedIncludePaths = ['owner'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$entry = ScheduleEntry::find($args['id'])) {
            throw new RecordNotFoundException('Could not find entry.');
        }
        $user = $this->getUser($request);
        if ($entry->user_id !== $user->id) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($entry);
    }
}
