<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

class FoldersUpdate extends JsonApiController
{
    use RoutesHelperTrait, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateFolder($user = $this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request, $folder);

        $this->updateFolder($folder, $json, $user);

        return $this->getContentResponse($folder);
    }

    private function updateFolder(\FolderType $folder, array $json, \User $user)
    {
        $getTrimmed = function ($key, $default = '') use ($json) {
            return trim(self::arrayGet($json, $key, $default));
        };

        // rename AND/OR change description
        $name = $getTrimmed('data.attributes.name', $folder->name);
        $description = $getTrimmed('data.attributes.description', $folder->description);

        if ($name !== $folder->name || $description !== $folder->description) {
            $result = self::editFolder($folder, $user, $name, $description);
            if (is_array($result)) {
                throw new BadRequestException($result[0]);
            }
        }

        // move folder
        $destinationFolderId = $getTrimmed('data.relationships.parent.data.id');
        if (!$destinationFolderId) {
            return;
        }

        if (!$destinationFolder = \FileManager::getTypedFolder($destinationFolderId)) {
            throw new BadRequestException('Could not find destination folder.');
        }

        if (is_array($result = \FileManager::moveFolder($folder, $destinationFolder, $user))) {
            throw new BadRequestException($result[0]);
        }
    }

    protected function validateResourceDocument($json, \FolderType $folder)
    {
        if ($err = $this->validateFolderResourceObject($json, $folder)) {
            return $err;
        }
    }
}
