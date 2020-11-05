<?php

namespace JsonApi\Routes\CourseMemberships;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Schemas\CourseMember;

class CourseMembershipsShow extends JsonApiController
{
    protected $allowedIncludePaths = [CourseMember::REL_COURSE, CourseMember::REL_USER];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $membership = self::findMembership($args['id']);

        if (!Authority::canShowMemberships($this->getUser($request), $membership)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($membership);
    }

    private function findMembership($id)
    {
        if (!preg_match('/^([^_]+)_(.+)$/', $id, $matches)) {
            throw new BadRequestException();
        }

        if (!$membership = \CourseMember::find([$matches[1], $matches[2]])) {
            throw new RecordNotFoundException();
        }

        return $membership;
    }
}
