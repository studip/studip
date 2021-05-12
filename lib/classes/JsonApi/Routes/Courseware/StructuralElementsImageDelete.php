<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StructuralElementsImageDelete extends NonJsonApiController
{
    public function invoke(Request $request, Response $response, $args)
    {
        if (!($structuralElement = StructuralElement::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteStructuralElementsImage($this->getUser($request), $structuralElement)) {
            throw new AuthorizationFailedException();
        }

        if ($structuralElement->image) {
            $structuralElement->image->getFileType()->delete();
        }

        $structuralElement->image_id = null;
        $structuralElement->store();

        return $response->withStatus(204);
    }
}
