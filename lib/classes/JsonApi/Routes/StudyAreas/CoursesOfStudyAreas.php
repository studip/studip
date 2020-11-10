<?php

namespace JsonApi\Routes\StudyAreas;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class CoursesOfStudyAreas extends JsonApiController
{
    protected $allowedIncludePaths = [
        'blubber-threads',
        'end-semester',
        'events',
        'feedback-elements',
        'file-refs',
        'folders',
        'forum-categories',
        'institute',
        'memberships',
        'news',
        'participating-institutes',
        'sem-class',
        'sem-type',
        'start-semester',
        'status-groups',
        'wiki-pages',
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$studyArea = \StudipStudyArea::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $courses = $studyArea->courses;

        return $this->getPaginatedContentResponse(
            $courses->limit($offset, $limit),
            count($courses)
        );
    }
}
