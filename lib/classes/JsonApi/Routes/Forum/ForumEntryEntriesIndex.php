<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ForumEntry;

class ForumEntryEntriesIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['entries', 'category'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$entry = ForumEntry::find($args['id'])) {
            throw new RecordNotFoundException('Could not find entry.');
        }

        if (!$course = \Course::find($entry->seminar_id)) {
            throw new RecordNotFoundException('could not find course');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course, $entry)) {
            throw new AuthorizationFailedException();
        }

        if (!$entries = ForumEntry::getChildEntries($entry->id)) {
            throw new RecordNotFoundException('could not find forum-entries');
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = count($entries);
        $data = array_slice($entries, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
