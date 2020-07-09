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

class RangeFileRefsCreate extends JsonApiController
{
    use RangeHelperTrait, RoutesHelperTrait, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$this->validateResourceType($args['type'])) {
            throw new BadRequestException('Bad resource type.');
        }

        if (!$range = $this->findResource($args['type'], $args['id'])) {
            throw new RecordNotFoundException();
        }

        $fileRef = $this->validateAndCreateFileRef($request, $range);

        return $this->getCreatedResponse($fileRef);
    }

    private function validateAndCreateFileRef(Request $request, \SimpleORMap $range)
    {
        if (!Authority::canShowFileArea($user = $this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        if (!$parent = $this->getRelationshipParent($json)) {
            throw new RecordNotFoundException('Bad `parent` folder.');
        }
        if ($parentFolder->range_id !== $range->id) {
            throw new BadRequestException('Parent folder does not belong to this file area.');
        }
        if (!$parent = $parentFolder->getTypedFolder()) {
            throw new InternalServerError();
        }

        if (!Authority::canCreateFileRefsInFolder($user, $parent)) {
            throw new AuthorizationFailedException();
        }

        return $this->createFileRef($user, $json, $parent);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if ($err = $this->validateFileRefResourceObject($json, null)) {
            return $err;
        }
    }

    private function getRelationshipParent($json)
    {
        if (!$parentId = $this->getRelationshipParentId($json)) {
            return null;
        }

        return \Folder::find($parentId);
    }

    private function getRelationshipParentId($json)
    {
        return self::arrayGet($json, 'data.relationships.parent.data.id', false);
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
        $licenseId = $getTrimmed('data.relationships.terms-of-use.data.id.');
        $tmp_path = $this->getTmpFile();

        $file = \StandardFile::create([
            'name' => $name,
            'description' => $description,
            'tmp_name' => $tmp_path,
            'size' => filesize($tmp_path),
            'content_terms_of_use_id' => $licenseId
        ]);
        $error = $parentFolder->validateUpload($file, $user->getId());
        if ($error && is_string($error)) {
            throw new InternalServerError($error);
        }

        $file = $parentFolder->addFile($file);

        return $file->getFileRef();
    }
}
