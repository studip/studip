<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Filesystem\PublicFolder;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use JsonApi\Routes\Files\RoutesHelperTrait as FilesRoutesHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use StandardFile;

class StructuralElementsImageUpload extends NonJsonApiController
{
    use CoursewareInstancesHelper, FilesRoutesHelper;

    public function invoke(Request $request, Response $response, $args)
    {
        if (!($structuralElement = StructuralElement::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUploadStructuralElementsImage($this->getUser($request), $structuralElement)) {
            throw new AuthorizationFailedException();
        }

        $instance = $this->findInstanceWithRange($structuralElement['range_type'], $structuralElement['range_id']);
        $publicFolder = PublicFolder::findOrCreateTopFolder($instance);

        $fileRef = $this->handleUpload($request, $publicFolder, $structuralElement);

        // remove existing image
        if ($structuralElement->image) {
            $structuralElement->image->getFileType()->delete();
        }

        // refer to newly uploaded image
        $structuralElement->image_id = $fileRef->id;
        $structuralElement->store();

        return $response->withStatus(201);
    }

    protected function handleUpload(Request $request, PublicFolder $folder, StructuralElement $structuralElement)
    {
        $uploadedFile = $this->getUploadedFile($request);
        $user = $this->getUser($request);
        $tmpFilename = $this->moveUploadedFile($this->getTmpPath(), $uploadedFile);
        $name = sprintf(
            'structural-element-%s.%s',
            $structuralElement->id,
            mb_strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION))
        );

        $data = [
            'name' => $name,
            'type' => $uploadedFile->getClientMediaType(),
            'size' => $uploadedFile->getSize(),
            'user_id' => $user->id,
            'tmp_name' => $tmpFilename,
            'description' => '',
            'content_terms_of_use_id' => 0,
        ];

        $file = StandardFile::create($data);

        if ($error = $folder->validateUpload($file, $user->id)) {
            throw new BadRequestException($error);
        }

        $file = $folder->addFile($file);
        if (!$file) {
            throw new InternalServerError();
        }

        return $file->getFileRef();
    }
}
