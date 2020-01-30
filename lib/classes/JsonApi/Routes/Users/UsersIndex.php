<?php

namespace JsonApi\Routes\Users;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Schemas\User as UserSchema;

class UsersIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexUsers($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $users = \User::findBySql('1 ORDER BY username LIMIT ? OFFSET ?', [$limit, $offset]);
        $total = \User::countBySql();

        return $this->getPaginatedContentResponse($users, $total);
    }
}
