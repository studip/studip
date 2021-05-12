<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class StructuralElementsIndex.
 */
class StructuralElementsIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'ancestors',
        'containers',
        'containers.blocks',
        'containers.blocks.edit-blocker',
        'containers.blocks.editor',
        'containers.blocks.owner',
        'containers.blocks.user-data-field',
        'containers.blocks.user-progress',
        'course',
        'descendants',
        'descendants.containers',
        'descendants.containers.blocks',
        'editor',
        'owner',
        'parent',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexStructuralElements($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $resources = StructuralElement::findBySQL('1 ORDER BY mkdate LIMIT ? OFFSET ?', [$limit, $offset]);

        return $this->getPaginatedContentResponse($resources, count($resources));
    }
}
