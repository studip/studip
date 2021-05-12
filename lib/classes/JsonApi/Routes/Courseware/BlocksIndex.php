<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all blocks of a container.
 */
class BlocksIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'container',
        'owner',
        'editor',
        'edit_blocker',
        'user-data-field',
        'user-progress',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = Container::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexBlocks($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse($resource->blocks->limit($offset, $limit), count($resource->blocks));
    }
}
