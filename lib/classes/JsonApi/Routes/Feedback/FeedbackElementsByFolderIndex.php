<?php

namespace JsonApi\Routes\Feedback;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays all feedback elements of a folder.
 */
class FeedbackElementsByFolderIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'course', 'entries', 'range'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($folder = \Folder::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexFeedbackElementsOfFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $resources = \FeedbackElement::findBySQL('range_id = ? AND range_type = ?', [$folder->id, \Folder::class]);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($resources, $offset, $limit),
            count($resources)
        );
    }
}
