<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\BlockComment;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays the user progress of a block.
 */
class BlockCommentsOfBlocksIndex extends JsonApiController
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
        if (!Authority::canIndexBlockComments($this->getUser($request), $block)) {
            throw new AuthorizationFailedException();
        }
        $resources = BlockComment::findBySql('block_id = ?', [$block->id]);

        return $this->getContentResponse($resources);
    }
}
