<?php

namespace JsonApi\Routes\Wiki;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Document\Link;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/*
 * Get a course-wiki-page
 */
class WikiShow extends JsonApiController
{
    use HelperTrait;

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

        return $this->getContentResponse($wikiPage);
    }
}
