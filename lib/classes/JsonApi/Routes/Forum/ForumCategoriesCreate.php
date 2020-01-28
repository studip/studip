<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Models\ForumCat;

/**
 * Create a Forum-Category by a given course-id.
 */
class ForumCategoriesCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$category = $this->createCategoryFromJSON($course->id, $json)) {
            throw new InternalServerError('Could not create the category.');
        }

        return $this->getCreatedResponse($category);
    }

    protected function createCategoryFromJSON($courseId, $json)
    {
        $title = self::arrayGet($json, 'data.attributes.title');

        $category = new ForumCat();
        $category->seminar_id = $courseId;
        $category->entry_name = $title;
        $category->store();

        return $category;
    }

    protected function validateResourceDocument($json)
    {
        $title = self::arrayGet($json, 'data.attributes.title', '');
        if (empty($title)) {
            return 'Categorys must have a title. ';
        }
    }
}
