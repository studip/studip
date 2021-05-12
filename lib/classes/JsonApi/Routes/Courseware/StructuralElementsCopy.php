<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Copy an courseware structural element in an courseware structural element
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */

class StructuralElementsCopy extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody()['data'];

        $remote_element = \Courseware\StructuralElement::find($data['element']['id']);
        $parent_element = \Courseware\StructuralElement::find($data['parent_id']);
        if (!Authority::canCreateContainer($user = $this->getUser($request), $parent_element)) {
            throw new AuthorizationFailedException();
        }

        $new_container = $this->copyElement($user, $remote_element, $parent_element);
    }

    private function copyElement(\User $user, \Courseware\StructuralElement $remote_element, \Courseware\StructuralElement $parent_element)
    {
        $new_element = $remote_element->copy($user, $parent_element);

        return $new_element;
    }
}