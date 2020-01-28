<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FileRefsUpdate extends JsonApiController
{
    use RoutesHelperTrait, ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateFileRef($user = $this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request, $fileRef);

        $this->updateFileRef($fileRef, $json, $user);

        $fileRef->restore();

        return $this->getContentResponse($fileRef);
    }

    private function updateFileRef(\FileRef $fileRef, array $json, \User $user)
    {
        $getTrimmed = function ($key, $default = '') use ($json) {
            return trim(self::arrayGet($json, $key, $default));
        };

        $name = $getTrimmed('data.attributes.name', $fileRef->name);
        $description = $getTrimmed('data.attributes.description', $fileRef->description);
        $termsId = $getTrimmed(
            'data.relationships.terms-of-use.data.id',
            $fileRef->content_terms_of_use_id
        );

        if ($fileRef->name === $name
            && $fileRef->description === $description
            && $fileRef->content_terms_of_use_id === $termsId
        ) {
            return;
        }

        $result = \FileManager::editFileRef($fileRef, $user, $name, $description, $termsId);

        if (!$result instanceof \FileRef) {
            throw new JsonApiException(array_map(function ($error) {
                return new Error('Bad Request Error', null, 400, null, null, $error);
            }, $result), 400);
        }
    }

    protected function validateResourceDocument($json, $fileRef)
    {
        if ($err = $this->validateFileRefResourceObject($json, $fileRef)) {
            return $err;
        }
    }
}
