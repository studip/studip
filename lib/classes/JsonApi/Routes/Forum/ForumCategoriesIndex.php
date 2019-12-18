<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays all data to a special Forum category.
 */
class ForumCategoriesIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['course', 'entries'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }

        if (!$categories = \JsonApi\Models\ForumCat::getCategories($course)) {
            throw new RecordNotFoundException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($categories, $offset, $limit),
            count($categories)
        );
    }
}
