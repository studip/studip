<?php

namespace JsonApi\Routes\Wiki\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Routes\Wiki\Authority;
use JsonApi\Routes\RelationshipsController;
use JsonApi\Routes\Wiki\HelperTrait;
use JsonApi\Errors\BadRequestException;

class ParentPage extends RelationshipsController
{
    use HelperTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $parent = $related->parent;

        return $this->getIdentifiersResponse($parent);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $parentPage = is_null($json['data'])
                    ? null
                    : $this->validateParentPage($related, $json);
        $this->replaceParentPage($related, $parentPage);

        return $this->getCodeResponse(204);
    }

    private function replaceParentPage($related, $parentOrNull)
    {
        $related->ancestor = is_null($parentOrNull) ? '' : $parentOrNull->keyword;
        $related->store();
    }

    protected function findRelated(array $args)
    {
        return self::findWikiPage($args['id']);
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowWiki($this->getUser($request), $resource);

            case 'PATCH':
                return Authority::canUpdateParent($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        $item = self::arrayGet($json, 'data');

        if (!is_null($item)) {
            if (\JsonApi\Schemas\WikiPage::TYPE !== self::arrayGet($item, 'type')) {
                return 'Wrong `type` in document´s `data`.';
            }

            if (!self::arrayGet($item, 'id')) {
                return 'Missing `id` of document´s `data`.';
            }

            if (self::arrayHas($item, 'attributes')) {
                return 'Document must not have `attributes`.';
            }
        }
    }

    private function validateParentPage(\WikiPage $page, $json)
    {
        $resourceIdentifier = self::arrayGet($json, 'data');
        $parentPage = self::findWikiPage($resourceIdentifier['id']);

        if ($parentPage->range_id !== $page->range_id) {
            throw new BadRequestException('Both pages have to belong to the same range_id.');
        }

        if (!$page->isValidAncestor($parentPage->keyword)) {
            throw new BadRequestException('Page is not a valid parent page.');
        }

        return $parentPage;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, 'parent');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, 'parent');
    }
}
