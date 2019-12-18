<?php

namespace JsonApi\Routes\CourseMemberships;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\CourseMember;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ByUserIndex extends JsonApiController
{
    protected $allowedIncludePaths = [CourseMember::REL_COURSE, CourseMember::REL_USER];

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

        $memberships = $user->course_memberships;
        $total = count($memberships);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($memberships->limit($offset, $limit), $total);
    }
}
