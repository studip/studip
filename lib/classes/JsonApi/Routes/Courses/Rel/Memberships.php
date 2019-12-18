<?php

namespace JsonApi\Routes\Courses\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Routes\Courses\Authority;
use JsonApi\Routes\RelationshipsController;

class Memberships extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $memberships = $related->members;
        $total = count($memberships);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse(
            $memberships->limit($offset, $limit),
            $total,
            $this->getRelationshipLinks($related)
        );
    }

    protected function findRelated(array $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $course;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
        case 'GET':
            return Authority::canShowCourse($this->getUser($request), $resource, Authority::SCOPE_EXTENDED);

        default:
            return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'memberships');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'memberships');
    }
}
