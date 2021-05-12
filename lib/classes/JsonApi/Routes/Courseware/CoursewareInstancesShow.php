<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Instance;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays an instance of a courseware.
 */
class CoursewareInstancesShow extends JsonApiController
{
    use CoursewareInstancesHelper;

    protected $allowedIncludePaths = ['bookmarks', 'root', 'root.descendants'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $instance = $this->findInstanceWithRange($args['type'], $args['id']);
        if (!Authority::canShowCoursewareInstance($this->getUser($request), $instance)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($instance);
    }
}
