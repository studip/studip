<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\CourseMember;

/**
 * Returns all comments of the blubber starting with the newest.
 * Returns an empty array if blubber_id is from a comment.
 */
class CoursesMembershipsIndex extends JsonApiController
{
    protected $allowedIncludePaths = [CourseMember::REL_COURSE, CourseMember::REL_USER];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexMemberships($user = $this->getUser($request), $course)) {
            throw new AuthorizationFailedException();
        }

        $memberships = $this->getMemberships($course, $user);

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($memberships->limit($offset, $limit), count($memberships));
    }

    private function getMemberships(\Course $course, \User $user)
    {
        $memberships = $course->members;

        return Authority::canEditCourse($user, $course)
            ? $memberships
            : $memberships->filter(function ($membership) use ($user) {
                return
                    $membership['user_id'] == $user->id
                    || !in_array($membership['status'], ['autor', 'user'])
                    || $membership['visible'] != 'no';
            });
    }
}
