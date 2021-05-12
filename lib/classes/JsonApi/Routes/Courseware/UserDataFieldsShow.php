<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\UserDataField;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays a user data field.
 */
class UserDataFieldsShow extends JsonApiController
{
    use UserDataFieldsHelper;

    protected $allowedIncludePaths = ['block', 'user'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = $this->findWithId($args['id']);

        if (!Authority::canShowUserDataField($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
