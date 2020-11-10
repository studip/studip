<?php

namespace JsonApi\Routes\StudyAreas;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class StudyAreasShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        'children',
        'courses',
        'institute',
        'parent',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$studyArea = \StudipStudyArea::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($studyArea);
    }
}
