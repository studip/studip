<?php

namespace JsonApi\Routes\InstituteMemberships;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Routes\Institutes\Authority;

class ByUserIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['user', 'institute'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$user = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexInstitutesOfUser($this->getUser($request), $user)) {
            throw new AuthorizationFailedException();
        }

        $institutes = $user->institute_memberships;
        $total = count($institutes);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($institutes->limit($offset, $limit), $total);
    }
}
