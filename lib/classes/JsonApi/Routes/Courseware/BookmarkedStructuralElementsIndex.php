<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays the user's bookmarked structural elements.
 */
class BookmarkedStructuralElementsIndex extends JsonApiController
{
    use CoursewareInstancesHelper;

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

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $instance = $this->findInstanceWithRange($args['type'], $args['id']);
        if (!Authority::canIndexBookmarks($user = $this->getUser($request), $instance)) {
            throw new AuthorizationFailedException();
        }
        $resources = $instance->getUsersBookmarks($user);
        $total = count($resources);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedResponse(array_slice($resources, $offset, $limit), $total);
    }
}
