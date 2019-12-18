<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;

class SubfoldersCreate extends RangeFoldersCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$parent = \Folder::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        $folder = $this->validateAndCreate($request, $parent);

        return $this->getCreatedResponse($folder);
    }

    protected function validateAndCreate(Request $request, \Folder $parent)
    {
        $rangeType = $parent->range_type;
        $range = $parent->$rangeType;

        if (!Authority::canShowFileArea($user = $this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        return $this->validateAndCreateSubfolder($range, $user, $json, $parent);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if ($err = $this->validateFolderResourceObject($json, null, false)) {
            return $err;
        }
    }
}
