<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;

/**
 * Create a news where the range is the user himself.
 */
class UserNewsCreate extends AbstractNewsCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $observer = $this->getUser($request);
        if (!$user = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canCreateUserNews($observer, $user)) {
            throw new AuthorizationFailedException();
        }
        if (!$news = $this->createNewsFromJSON($observer, $user, $json)) {
            throw new InternalServerError('Could not create news.');
        }

        return $this->getCreatedResponse($news);
    }
}
