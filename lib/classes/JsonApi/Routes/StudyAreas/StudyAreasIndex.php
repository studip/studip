<?php

namespace JsonApi\Routes\StudyAreas;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Zeigt eine bestimmte Veranstaltung an.
 */
class StudyAreasIndex extends JsonApiController
{

    protected $allowedIncludePaths = [
        'children',
        'courses',
        'institute',
        'parent',
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $tree = \TreeAbstract::getInstance('StudipSemTree', ['visible_only' => 1]);
        $studyAreas =  self::mapTree('root', $tree);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($studyAreas, $offset, $limit),
            count($studyAreas)
        );
    }

    private function mapTree($parentId, &$tree)
    {
        $level = [];
        $kids = $tree->getKids($parentId);
        if (is_array($kids) && count($kids) > 0) {
            foreach ($kids as $kid) {
                $level[] = \StudipStudyArea::find($kid);
                $level = array_merge($level, self::mapTree($kid, $tree));
            }
        }

        return $level;
    }
}
