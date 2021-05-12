<?php

namespace JsonApi\Routes\Files\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Files\Authority;
use JsonApi\Routes\RelationshipsController;

class FileRefsOfFile extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $refs = $related->refs->filter(
            function ($ref) use ($request) {
                return Authority::canShowFileRef($this->getUser($request), $ref);
            }
        );
        $total = count($refs);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedIdentifiersResponse(
            $refs->limit($offset, $limit),
            $total,
            $this->getRelationshipLinks($related),
            [
                'total-refs' => count($related->refs),
            ]
        );
    }

    protected function findRelated(array $args)
    {
        if (!$related = \File::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
        case 'GET':
            return Authority::canShowFile($this->getUser($request), $resource);

        default:
            return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'file-refs');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'file-refs');
    }
}
