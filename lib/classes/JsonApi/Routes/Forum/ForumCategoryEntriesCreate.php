<?php

namespace JsonApi\Routes\Forum;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Models\ForumCat;

class ForumCategoryEntriesCreate extends AbstractEntriesCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $categoryId = $args['id'];
        $courseId = ForumCat::find($categoryId)->seminar_id;

        if (!$course = \Course::find($courseId)) {
            throw new RecordNotFoundException('Could not find course.');
        }

        if (!ForumAuthority::has($this->getUser($request), 'view', $course)) {
            throw new AuthorizationFailedException();
        }

        if (!$entry = $this->createEntryFromJSON($this->getUser($request), $categoryId, $json)) {
            throw new InternalServerError('Could not create forum entry.');
        }

        return $this->getCreatedResponse($entry);
    }
}
