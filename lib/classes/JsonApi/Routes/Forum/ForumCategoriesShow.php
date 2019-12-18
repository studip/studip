<?php

namespace JsonApi\Routes\Forum;

// require_once 'public/plugins_packages/core/Forum/models/ForumCat.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class ForumCategoriesShow extends JsonApiController
{
    protected $allowedIncludePaths = ['course', 'entries'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$category = \JsonApi\Models\ForumCat::find($args['id'])) {
            throw new RecordNotFoundException('could not find category');
        }
        if (!$course = \Course::find($category->seminar_id)) {
            throw new RecordNotFoundException('could not find course');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($category);
    }
}
