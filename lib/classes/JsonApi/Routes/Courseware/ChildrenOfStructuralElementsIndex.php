<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all children of a structural element.
 */
class ChildrenOfStructuralElementsIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['containers', 'course', 'owner', 'editor', 'parent'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = StructuralElement::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowStructuralElement($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            $resource->children->limit($offset, $limit),
            count($resource->children)
        );
    }
}
