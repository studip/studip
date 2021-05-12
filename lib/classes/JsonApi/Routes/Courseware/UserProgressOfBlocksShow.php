<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\UserProgress;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays the user progress of a block.
 */
class UserProgressOfBlocksShow extends JsonApiController
{
    protected $allowedIncludePaths = ['block', 'user'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($block = Block::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        // this is automatically scoped to the requesting user
        $resource = UserProgress::getUserProgress($user = $this->getUser($request), $block);

        if (!Authority::canShowUserProgress($user, $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
