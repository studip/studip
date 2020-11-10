<?php

namespace JsonApi\Routes\StudyAreas;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class ChildrenOfStudyAreas extends JsonApiController
{
    protected $allowedIncludePaths = [
        'children',
        'courses',
        'institute',
        'parent',
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
        $children = $studyArea->getChildren();

        return $this->getPaginatedContentResponse(
            $children->limit($offset, $limit),
            count($children)
        );
    }
}
