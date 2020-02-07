<?php

namespace JsonApi\Routes\Feedback;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all feedback elements of a file ref.
 */
class FeedbackElementsByFileRefIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'course', 'entries', 'range'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($fileRef = \FileRef::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexFeedbackElementsOfFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        $resources = \FeedbackElement::findBySQL('range_id = ? AND range_type = ?', [$fileRef->id, \FileRef::class]);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($resources, $offset, $limit),
            count($resources)
        );
    }
}
