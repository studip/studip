<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\BlockFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays the feedback of a block.
 */
class BlockFeedbacksOfBlocksIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['user', 'block'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?Block $block */
        $block = Block::find($args['id']);
        if (!$block) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canIndexBlockFeedback($this->getUser($request), $block)) {
            throw new AuthorizationFailedException();
        }
        /** @var BlockFeedback[] $resources */
        $resources = $block->block_feedback;

        return $this->getContentResponse($resources);
    }
}
