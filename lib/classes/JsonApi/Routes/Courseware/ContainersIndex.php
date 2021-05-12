<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all containers of a structural element.
 */
class ContainersIndex extends JsonApiController
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
        if (!$resource = StructuralElement::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexContainers($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            $resource->containers->limit($offset, $limit),
            count($resource->containers)
        );
    }
}
