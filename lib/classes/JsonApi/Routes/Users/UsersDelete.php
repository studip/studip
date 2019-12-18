<?php

namespace JsonApi\Routes\Users;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;

class UsersDelete extends JsonApiController
{
    protected $allowedIncludePaths = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$observedUser = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteUser($this->getUser($request), $observedUser)) {
            throw new AuthorizationFailedException();
        }

        $observedUser->delete();

        return $this->getCodeResponse(204);
    }
}
