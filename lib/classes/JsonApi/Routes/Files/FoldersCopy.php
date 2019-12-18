<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use JsonApi\Providers\JsonApiConfig as C;

class FoldersCopy extends NonJsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$sourceFolder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException('Could not find source folder.');
        }

        $body = $request->getParsedBody();
        if (!$destinationFolder = \FileManager::getTypedFolder($body['destination'])) {
            throw new RecordNotFoundException('Could not find destination folder.');
        }

        if (!Authority::canCopyFolder($user = $this->getUser($request), $sourceFolder, $destinationFolder)) {
            throw new AuthorizationFailedException();
        }

        $folder = \FileManager::copyFolder($sourceFolder, $destinationFolder, $user);
        if (!$folder instanceof \FolderType) {
            throw new BadRequestException('Fehler beim Kopieren des Ordners.');
        }

        return $this->redirectToFolder($response, $folder);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function redirectToFolder(Response $response, \FolderType $folder)
    {
        $pathinfo = $this->getSchema($folder)->getSelfSubLink($folder)->getSubHref();
        $old = \URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = \URLHelper::getURL($this->container->get(C::JSON_URL_PREFIX).$pathinfo, [], true);
        \URLHelper::setBaseURL($old);

        return $response->withRedirect($url, 201);
    }
}
