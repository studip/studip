<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\Block;
use Courseware\Container;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Copy a courseware container in an courseware structural element
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */

class ContainersCopy extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody()['data'];

        $container = \Courseware\Container::find($data['container']['id']);
        $element = \Courseware\StructuralElement::find($data['parent_id']);
        if (!Authority::canCreateContainer($user = $this->getUser($request), $element)) {
            throw new AuthorizationFailedException();
        }

        $new_container = $this->copyContainer($user, $container, $element);
    }

    private function copyContainer(\User $user, \Courseware\Container $remote_container, \Courseware\StructuralElement $element)
    {
        $container = $remote_container->copy($user, $element);

        return $container;
    }
}