<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays one Container.
 */
class ContainersShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        'blocks',
        'blocks.edit-blocker',
        'blocks.editor',
        'blocks.owner',
        'blocks.user-data-field',
        'blocks.user-progress',
        'editor',
        'edit-blocker',
        'owner',
        'structural-element',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = Container::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowContainer($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
