<?php

namespace JsonApi\Routes\Institutes;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\RecordNotFoundException;

class StatusGroupsOfInstitutes extends JsonApiController
{
    protected $allowedIncludePaths = [
        'range'
    ];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$institute = \Institute::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $statusGroups = $institute->status_groups;
        $total = count($statusGroups);

        return $this->getPaginatedContentResponse(
            $statusGroups->limit($offset, $limit),
            $total
        );
    }
}
