<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class SubfilerefsIndex extends JsonApiController
{

    protected $allowedIncludePaths = ['file', 'owner', 'parent', 'range', 'terms-of-use'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$folder = \FileManager::getTypedFolder($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFolder($this->getUser($request), $folder)) {
            throw new AuthorizationFailedException();
        }

        $fileRefs = $folder->file_refs->getArrayCopy();
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($fileRefs, $offset, $limit),
            count($fileRefs)
        );
    }
}
