<?php

namespace JsonApi\Routes\Courseware;

use Courseware\BlockFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays feedback on a block.
 */
class BlockFeedbacksShow extends JsonApiController
{
    protected $allowedIncludePaths = ['user', 'block'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?BlockFeedback $resource */
        $resource = BlockFeedback::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlockFeedback($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
