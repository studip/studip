<?php

namespace JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\UnsupportedRequestError;
use JsonApi\JsonApiController;
use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class RelationshipsController extends JsonApiController
{
    use ValidationTrait;

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$this->authorize($request, $related = $this->findRelated($args))) {
            throw new AuthorizationFailedException();
        }

        $map = [
            'GET' => 'fetchRelationship',
            'PATCH' => 'replaceRelationship',
            'POST' => 'addToRelationship',
            'DELETE' => 'removeFromRelationship',
        ];

        if (!isset($map[$request->getMethod()])) {
            throw new UnsupportedRequestError();
        }

        return call_user_func([$this, $map[$request->getMethod()]], $request, $related);
    }

    abstract protected function findRelated(array $args);

    abstract protected function authorize(Request $request, $resource);

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResourceDocument($json, $data)
    {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        throw new UnsupportedRequestError();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addToRelationship(Request $request, $related)
    {
        throw new UnsupportedRequestError();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function removeFromRelationship(Request $request, $related)
    {
        throw new UnsupportedRequestError();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function replaceRelationship(Request $request, $related)
    {
        throw new UnsupportedRequestError();
    }

    abstract protected function getRelationshipSelfLink($resource, $schema, $userData);

    abstract protected function getRelationshipRelatedLink($resource, $schema, $userData);

    protected function getRelationshipLinks($resource, $userData = null)
    {
        $schema = $this->getSchema($resource);

        return array_reduce(
            [
                [DocumentInterface::KEYWORD_SELF, 'getRelationshipSelfLink'],
                [DocumentInterface::KEYWORD_RELATED, 'getRelationshipRelatedLink'],
            ],
            function ($links, $entry) use ($resource, $schema, $userData) {
                list($keyword, $method) = $entry;
                if ($link = $this->$method($resource, $schema, $userData)) {
                    $links[$keyword] = $link;
                }

                return $links;
            },
            []
        );
    }
}
