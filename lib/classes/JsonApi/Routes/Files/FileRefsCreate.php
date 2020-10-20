<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\File as FileSchema;

class FileRefsCreate extends JsonApiController
{
    use RangeHelperTrait, RoutesHelperTrait, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        // validate parent folder
        if (!$parent = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateFileRefsInFolder($user = $this->getUser($request), $parent)) {
            throw new AuthorizationFailedException();
        }

        $fileRef = $this->createFileRef($user, $json, $parent);

        return $this->getCreatedResponse($fileRef);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if ($err = $this->validateFileRefResourceObject($json, null)) {
            return $err;
        }

        // Relationship: file
        if (self::arrayHas($json, 'data.relationships.file')) {
            $file = self::arrayGet($json, 'data.relationships.file');
            if ($err = $this->validateResourceIdentifier($file, FileSchema::TYPE, false)) {
                return $err;
            }
            $fileId = self::arrayGet($file, 'data.id');
            if (!\File::find($fileId)) {
                return 'Invalid `file` specified.';
            }
        }
    }

    private function createFileRef(
        \User $user,
        array $json,
        \FolderType $parentFolder
    ) {
        $getTrimmed = function ($key, $default = '') use ($json) {
            return trim(self::arrayGet($json, $key, $default));
        };

        $name = $getTrimmed('data.attributes.name');
        $description = $getTrimmed('data.attributes.description');
        $licenseId = $getTrimmed('data.relationships.terms-of-use.data.id');

        if (self::arrayHas($json, 'data.relationships.file')) {
            $fileId = $getTrimmed('data.relationships.file.data.id');
            $source = \File::find($fileId);
            if ($source->user_id === $user->id) {
                $file = $source;
            } else {
                $file = $this->copyFile($source, $user);
            }
        } else {
            try {
                $file = \File::create(
                    [
                        'filetype' => 'StandardFile',
                        'name' => $name,
                        'size' => 0,
                        'user_id' => $user->id,
                    ]
                );

                $emptyTmpFile = $this->getTmpFile();
                $file->connectWithDataFile($emptyTmpFile);
            } finally {
                @unlink($emptyTmpFile);
            }
        }

        $fileRef = new \FileRef();
        $fileRef->name = $name;
        $fileRef->description = $description;
        $fileRef->content_terms_of_use_id = $licenseId;

        $filetype = $parentFolder->addFile(
            new \StandardFile($fileRef, $file)
        );

        return $filetype->getFileRef();
    }


    private function copyFile(\File $source, \User $user)
    {
        $file = new \File();
        $file->user_id = $user->id;
        $file->mime_type = $source->mime_type;
        $file->name = $source->name;
        $file->size = $source->size;
        $file->metadata = $source->metadata;
        $file->author_name = $user->getFullName('no_title');
        $file->id = $file->getNewId();

        // We must copy the physical data.
        if ($source->getPath()) {
            $copyPath = $this->getTmpPath() . '/' . $file->id;
            if (!copy($source->getPath(), $copyPath)) {
                throw new InternalServerError('Could not copy File.');
            }
            if ($file->connectWithDataFile($copyPath)) {
                @unlink($copyPath);
            } else {
                throw new InternalServerError('Could not connect file.');
            }
        } else {
            $file->url = $source->url;
        }

        $file->store();

        return $file;
    }
}
