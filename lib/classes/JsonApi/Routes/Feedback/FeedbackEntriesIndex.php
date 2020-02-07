<?php

namespace JsonApi\Routes\Feedback;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays the entries of a certain feedback element.
 */
class FeedbackEntriesIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'feedback-element'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = \FeedbackElement::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexFeedbackEntries($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        /** @var \SimpleOrMapCollection */
        $entries = $resource->entries;

        if (!Authority::canSeeResultsOfFeedbackElement($user, $resource)) {
            $entries = $entries->filter(function ($entry) use ($user) {
                return $entry->user_id === $user->id;
            });
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = count($entries);

        return $this->getPaginatedContentResponse($entries->limit($offset, $limit), $total);
    }
}
