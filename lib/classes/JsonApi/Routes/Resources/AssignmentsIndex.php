<?php

namespace JsonApi\Routes\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Models\Resources\ResourcesObject;

class AssignmentsIndex extends JsonApiController
{
    protected $allowedFilteringParameters = ['start', 'end'];

    protected $allowedIncludePaths = ['owner', 'resources-object'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = ResourcesObject::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexAssignments($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $assignments = $this->getAssignments($resource, $this->getFilters());

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($assignments, $offset, $limit),
            count($assignments)
        );
    }

    private function getFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters();
        $filter = [
            'start' => strtotime('today'),
            'end' => strtotime('tomorrow'),
        ];

        return array_reduce(
            words('start end'),
            function ($filter, $key) use ($filtering) {
                if (isset($filtering[$key])) {
                    $filter[$key] = (int) $filtering[$key];
                }

                return $filter;
            },
            $filter
        );
    }

    private function getAssignments(ResourcesObject $resource, $filters)
    {
        // WORKAROUND: $filters['start'] cannot be "0"
        if ($filters['start'] === 0) {
            $filters['start'] = 1;
        }

        $assignEvents = new \AssignEventList($filters['start'], $filters['end'], $resource->id, '', '', true);

        $assignments = [];
        while ($event = $assignEvents->nextEvent()) {
            $assignments[] = $event;
        }

        return $assignments;
    }
}
