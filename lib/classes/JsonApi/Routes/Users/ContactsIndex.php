<?php

namespace JsonApi\Routes\Users;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContactsIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$user = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canEditUser($this->getUser($request), $user)) {
            throw new AuthorizationFailedException();
        }

        $contacts = $user->contacts;

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($contacts->limit($offset, $limit), count($contacts));
    }
}
