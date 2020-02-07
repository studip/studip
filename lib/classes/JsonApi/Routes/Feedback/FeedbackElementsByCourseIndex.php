<?php

namespace JsonApi\Routes\Feedback;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays all feedback elements of a course.
 */
class FeedbackElementsByCourseIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'course', 'entries', 'range'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($course = \Course::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexFeedbackElementsOfCourse($this->getUser($request), $course)) {
            throw new AuthorizationFailedException();
        }

        $resources = \FeedbackElement::findBySQL('range_id = ? AND range_type = ?', [$course->id, \Course::class]);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($resources, $offset, $limit),
            count($resources)
        );
    }
}
