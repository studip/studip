<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays one StructuralElement.
 */
class StructuralElementsShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        'ancestors',
        'containers',
        'containers.blocks',
        'containers.blocks.edit-blocker',
        'containers.blocks.editor',
        'containers.blocks.owner',
        'containers.blocks.user-data-field',
        'containers.blocks.user-progress',
        'course',
        'descendants',
        'descendants.containers',
        'descendants.containers.blocks',
        'editor',
        'owner',
        'parent',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = StructuralElement::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowStructuralElement($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        $last = \UserConfig::get($GLOBALS['user']->id)->getValue('COURSEWARE_LAST_ELEMENT');

        if ($resource->user) {
            $last['global'] = $args['id'];
        } else if ($resource->course) {
            $last[$resource->course->id] = $args['id'];
        } else {
            throw new RecordNotFoundException();
        }

        \UserConfig::get($GLOBALS['user']->id)->store('COURSEWARE_LAST_ELEMENT', $last);

        return $this->getContentResponse($resource);
    }
}
