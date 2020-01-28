<?php

namespace JsonApi\Routes\Forum;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\InternalServerError;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Models\ForumCat;

/**
 * Edits content of a news.
 */
class ForumCategoriesUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        if (!$category = ForumCat::find($args['id'])) {
            throw new RecordNotFoundException('Category has not been found.');
        }
        if (!$course = \Course::find($category->seminar_id)) {
            throw new RecordNotFoundException('Course does not exist.');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$category = $this->updateCategoryFromJSON($category, $json)) {
            throw new InternalServerError('Could not update the category.');
        }

        return $this->getContentResponse($category);
    }

    protected function updateCategoryFromJSON($category, $json)
    {
        $title = self::arrayGet($json, 'data.attributes.title');
        $category->entry_name = $title;
        if ($category->isDirty()) {
            $category->store();

            return $category;
        }
    }

    protected function validateResourceDocument($json)
    {
        $title = self::arrayGet($json, 'data.attributes.title', '');
        if (empty($title)) {
            return 'Categories must have a title. ';
        }
    }
}
