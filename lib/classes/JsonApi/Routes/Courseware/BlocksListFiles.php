<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays one Block.
 */
class BlocksListFiles extends JsonApiController
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
        if (!($resource = Block::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlock($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource->files);
    }
}
