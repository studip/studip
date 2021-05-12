<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one Block.
 */
class BlocksDelete extends JsonApiController
{
    use EditBlockAwareTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = Block::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteBlock($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $this->deleteResource($user, $resource);

        return $this->getCodeResponse(204);
    }
}
