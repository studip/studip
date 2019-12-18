<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileRefsContentUpdate extends NonJsonApiController
{
    use RoutesHelperTrait;

    public function invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        $uploadedFile = $this->getUploadedFile($request);
        $fileRef = $this->updateFileRefWithUpload($request, $fileRef, $uploadedFile);

        return $this->redirectToFileRef($response, $fileRef);
    }

    protected function updateFileRefWithUpload($request, $fileRef, $uploadedFile)
    {
        $user = $this->getUser($request);
        $updateFilename = true;
        $updateAllInstances = true;
        $tmpFilename = $this->moveUploadedFile($this->getTmpPath(), $uploadedFile);

        $file = [
            'name' => $this->getFilename($request, $uploadedFile),
            'type' => $uploadedFile->getClientMediaType(),
            'size' => $uploadedFile->getSize(),
            'user_id' => $user->id,
            'tmp_name' => $tmpFilename,
            'description' => $fileRef->description,
            'content_terms_of_use_id' => $fileRef->content_terms_of_use_id,
        ];

        $result = \FileManager::updateFileRef(
            $fileRef,
            $user,
            $file,
            $updateFilename,
            $updateAllInstances
        );

        if (!$result instanceof \FileRef) {
            throw new BadRequestException('Fehler beim Aktualisieren der Datei.');
        }

        return $result;
    }
}
