<?php

namespace JsonApi\Routes\Courses;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CoursesByUserIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['institute', 'start-semester', 'end-semester'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$user = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexMembershipsOfUser($this->getUser($request), $user)) {
            throw new AuthorizationFailedException();
        }

        $courses = $this->findCoursesByUser($user);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(array_slice($courses, $offset, $limit), count($courses));
    }

    private function findCoursesByUser(\User $user)
    {
        return \Course::findMany($user->course_memberships->pluck('seminar_id'), 'ORDER BY start_time, name');
    }
}
