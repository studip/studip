<?php

namespace JsonApi\Routes\Files\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Files\Authority;
use JsonApi\Routes\RelationshipsController;

class TermsOfFileRef extends RelationshipsController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        return $this->getIdentifiersResponse($related->content_terms_of_use_id ? $related->terms_of_use : null);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);

        $data = self::arrayGet($json, 'data');

        if ($data === null) {
            $termsId = null;
        } else {
            $termsId = self::arrayGet($json, 'data.id');
            if (!\ContentTermsOfUse::find($termsId)) {
                throw new BadRequestException('Invalid terms of use specified.');
            }
        }

        $related->content_terms_of_use_id = $termsId;
        $related->store();

        return $this->getIdentifiersResponse($related->content_terms_of_use_id ? $related->terms_of_use : null);
    }

    protected function findRelated(array $args)
    {
        if (!$related = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowFileRef($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canUpdateFileRef($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\FileRef::REL_TERMS);
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

        if (count($data) !== 2 || !isset($data['id']) || !isset($data['type'])) {
            return 'File´s ´data´ must be null or a resource identifier.';
        }

        if ($data['type'] !== \JsonApi\Schemas\ContentTermsOfUse::TYPE) {
            return 'Wrong `data.type`.';
        }
    }
}
