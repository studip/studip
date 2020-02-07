<?php

namespace JsonApi\Routes\Feedback;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays a certain feedback element.
 */
class FeedbackElementsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'course', 'entries', 'range'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = \FeedbackElement::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFeedbackElement($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
