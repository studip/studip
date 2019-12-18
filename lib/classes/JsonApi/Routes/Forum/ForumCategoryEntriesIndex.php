<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ForumEntry;
use JsonApi\Models\ForumCat;

class ForumCategoryEntriesIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['category', 'entries'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$category = ForumCat::find($args['id'])) {
            throw new RecordNotFoundException('Could not find category.');
        }
        if (!$course = \Course::find($category->seminar_id)) {
            throw new RecordNotFoundException('Could not find course.');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }

        $entries = ForumEntry::getEntriesFromCat($category);

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($entries, $offset, $limit),
            count($entries)
        );
    }
}
