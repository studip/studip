<?php

namespace JsonApi\Routes\Courseware;

use Courseware\BlockComment;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays a comment on a block.
 */
class BlockCommentsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['block', 'user'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = BlockComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlockComment($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
