<?php

namespace JsonApi\Routes\Forum;

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ForumCat;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Löscht eine Forum-Kategorie.
 */
class ForumCategoriesDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$category = ForumCat::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!$course = \Course::find($category->seminar_id)) {
            throw new RecordNotFoundException('Course does not exist.');
        }
        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$this->deleteCategory($category)) {
            throw new RecordNotFoundException();
        }

        return $this->getCodeResponse(204);
    }

    protected static function deleteCategory($category)
    {
        return $category->deleteCategory($category->category_id, $category->seminar_id);
    }
}
