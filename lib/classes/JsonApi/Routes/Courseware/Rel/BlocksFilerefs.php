<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\Block;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class BlocksFilerefs extends RelationshipsController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        return $this->getIdentifiersResponse($related->files ?: null);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        $data = self::arrayGet($json, 'data');

        if (!Authority::canUpdateBlock($this->getUser($request), $related)) {
            throw new ConflictException();
        }

        if (null === $data) {
            $fileId = null;
        } else {
            $fileId = self::arrayGet($json, 'data.id');
            if (!$file = \FileRef::find($fileId)) {
                throw new BadRequestException('Invalid fileref specified.');
            }
        }

        $related->files = $file;
        $related->store();

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!$related = Block::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowBlock($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canUpdateBlock($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\Courseware\Block::REL_FILES);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return null;
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at file´s top level.';
        }

        $data = self::arrayGet($json, 'data');

        if ($data === null) {
            return;
        }

        if ($data['type'] !== \JsonApi\Schemas\FileRef::TYPE) {
            return 'Wrong `data.type`.';
        }
    }
}
