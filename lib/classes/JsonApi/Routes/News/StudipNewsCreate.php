<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;

/**
 * Create a global news where the range is studip.
 */
class StudipNewsCreate extends AbstractNewsCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        if (!Authority::canCreateStudipNews($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }
        if (!$news = $this->createNewsFromJSON($user, 'studip', $json)) {
            throw new InternalServerError('Could not create news.');
        }

        return $this->getCreatedResponse($news);
    }
}
