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
    protected $allowedFilteringParameters = ['permission'];

    protected $allowedIncludePaths = [CourseMember::REL_COURSE, CourseMember::REL_USER];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($course = \Course::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexMemberships($user = $this->getUser($request), $course)) {
            throw new AuthorizationFailedException();
        }

        $this->validateFilters();

        $memberships = $this->getMemberships($course, $user, $this->getFilters());

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($memberships->limit($offset, $limit), count($memberships));
    }

    private function getMemberships(\Course $course, \User $user, array $filters)
    {
        $memberships = $course->members;

        $visibleMemberships = Authority::canEditCourse($user, $course)
            ? $memberships
            : $memberships->filter(function ($membership) use ($user) {
                return $membership['user_id'] == $user->id ||
                    !in_array($membership['status'], ['autor', 'user']) ||
                    'no' != $membership['visible'];
            });

        return isset($filters['permission'])
            ? $visibleMemberships->filter(function ($membership) use ($filters) {
                return $membership['status'] === $filters['permission'];
            })
            : $visibleMemberships;
    }

    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('permission', $filtering)) {
            if (!in_array($filtering['permission'], ['user', 'autor', 'tutor', 'dozent'])) {
                throw new BadRequestException('Filter `permission` must be one of `user`, `autor`, `tutor`, `dozent`.');
            }
        }
    }

    private function getFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        $filters['permission'] = $filtering['permission'] ?? null;

        return $filters;
    }
}
