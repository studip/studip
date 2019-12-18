<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/*
 * Get a course-wiki-page
 */
class WikiIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'range'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexWiki($this->getUser($request), $course)) {
            throw new AuthorizationFailedException();
        }

        if (!$wiki = \WikiPage::findLatestPages($course->id)) {
            throw new RecordNotFoundException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            $wiki->limit($offset, $limit),
            count($wiki)
        );
    }
}
