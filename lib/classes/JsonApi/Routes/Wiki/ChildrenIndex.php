<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;

/*
 * Get paginated list of all children of a page
 */
class ChildrenIndex extends JsonApiController
{
    use HelperTrait;

    protected $allowedPagingParameters = ['offset', 'limit'];
    protected $allowedIncludePaths = ['author', 'children', 'descendants', 'parent', 'range'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $wikiPage = self::findWikiPage($args['id']);


        if (!Authority::canShowWiki($this->getUser($request), $wikiPage)) {
            throw new AuthorizationFailedException();
        }

        $children = $wikiPage->children;
        $total = count($children);

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($children, $offset, $limit),
            $total
        );
    }
}
