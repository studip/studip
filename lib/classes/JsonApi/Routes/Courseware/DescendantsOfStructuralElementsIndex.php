<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all descendants of a structural element.
 */
class DescendantsOfStructuralElementsIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'containers',
        'course',
        'descendants',
        'editor',
        'owner',
        'parent',
    ];

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
        $descendants = $resource->findDescendants();

        return $this->getPaginatedContentResponse(
            array_slice($descendants, $offset, $limit),
            count($descendants)
        );
    }
}
