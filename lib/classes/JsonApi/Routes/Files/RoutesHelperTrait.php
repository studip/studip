<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Providers\JsonApiConfig as C;
use JsonApi\Schemas\FileRef as FileRefSchema;
use JsonApi\Schemas\Folder as FolderSchema;
use JsonApi\Schemas\ContentTermsOfUse as ContentTermsOfUseSchema;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

trait RoutesHelperTrait
{
    protected function validateResourceIdentifier($json, $type, $newResource = true)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        // type
        if (self::arrayGet($json, 'data.type')
            !== $type
        ) {
            return 'Missing `type` member of document´s `data`.';
        }

        // id
        if ($newResource && self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }
    }

    protected function validateFileRefResourceObject($json, \FileRef $fileRef = null)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        // Resource Identifier
        if ($err = $this->validateResourceIdentifier($json, FileRefSchema::TYPE, null === $fileRef)) {
            return $err;
        }

        // Attributes
        if ($err = $this->validateFileRefAttributes($json, $fileRef)) {
            return $err;
        }

        // Relationship: terms-of-use
        if (self::arrayHas($json, 'data.relationships.terms-of-use')) {
            $license = self::arrayGet($json, 'data.relationships.terms-of-use');
            if ($err = $this->validateResourceIdentifier($license, ContentTermsOfUseSchema::TYPE, false)) {
                return $err;
            }
            $termsId = self::arrayGet($license, 'data.id');
            if (!\ContentTermsOfUse::find($termsId)) {
                return 'Invalid `terms-of-use` specified.';
            }
        } else {
            return 'Missing `terms-of-use` relationship.';
        }
    }

    private function validateFileRefAttributes($json)
    {
        // Attributes
        if (!self::arrayHas($json, 'data.attributes')) {
            return 'Missing `attributes` member of document´s `data`.';
        }

        // Attribute: name
        $name = self::arrayGet($json, 'data.attributes.name');
        if (!$name || 0 === mb_strlen(trim($name))) {
            return '`name` must not be empty.';
        }
    }

    protected function validateFolderAttributes($json, \FolderType $folder = null, $needsParent = false)
    {
        // Attributes needed to create a new folder
        if (!$folder) {
            if (!self::arrayHas($json, 'data.attributes')) {
                return 'Missing `attributes` member of document´s `data`.';
            }

            if (!self::arrayHas($json, 'data.attributes.name')) {
                return 'Missing `data.name`.';
            }
        }

        // Attribute: name must not be empty if present
        if (self::arrayHas($json, 'data.attributes.name')
            && !mb_strlen(trim(self::arrayGet($json, 'data.attributes.name', '')))) {
            return '`name` must not be empty.';
        }

        // Relationship: parent
        if (self::arrayHas($json, 'data.relationships.parent')) {
            $parent = self::arrayGet($json, 'data.relationships.parent');
            if ($err = $this->validateResourceIdentifier($parent, FolderSchema::TYPE, false)) {
                return $err;
            }
        } elseif ($needsParent) {
            return 'Missing `parent` relationship.';
        }
    }

    protected function validateFolderResourceObject($json, \FolderType $folder = null, $needsParent = false)
    {
        if ($err = $this->validateResourceIdentifier($json, FolderSchema::TYPE, null === $folder)) {
            return $err;
        }

        if ($err = $this->validateFolderAttributes($json, $folder, $needsParent)) {
            return $err;
        }
    }

    protected function editFolder(\FolderType $folder, \User $user, $name = null, $description = null)
    {
        // Since name must not be empty we have to check if it validates to false
        // (which can happen with emtpy strings). Description on the other hand
        // can be null which means it shoudln't be changed.
        // If description is an empty string it shall be changed to an empty string
        // if it had a filled string as value.
        if (!$name && null !== $description) {
            //neither name nor description are set: nothing to do, no error:
            return $folder;
        }

        //check if folder is not a top folder:
        if (!$folder->parent_id) {
            //folder is a top folder which cannot be edited!
            return [sprintf(
                _('Ordner %s ist ein Hauptordner, der nicht bearbeitet werden kann!'),
                $folder->name
            )];
        }

        if (!$folder->isWritable($user->id)) {
            return [sprintf(
                _('Unzureichende Berechtigungen zum Bearbeiten des Ordners %s'),
                $folder->name
            )];
        }

        //ok, user has write permissions for this folder:
        //edit name or description or both

        $data = $folder->getEditTemplate();

        if (!is_array($data)) {
            $data = [];
        }

        if ($name) {
            //get the parent folder to check for duplicate names
            //and set the folder name to an unique name:
            $data['name'] = $name;
        }

        if (null !== $description) {
            $data['description'] = $description;
        }

        $folder->setDataFromEditTemplate($data);
        if ($folder->store()) {
            //folder successfully edited
            return $folder;
        }

        return [sprintf(
            _('Fehler beim Speichern des Ordners %s'),
            $folder->name
        )];
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function handleUpload(Request $request, \FolderType $folder)
    {
        $uploadedFile = $this->getUploadedFile($request);
        $user = $this->getUser($request);
        $tmpFilename = $this->moveUploadedFile($this->getTmpPath(), $uploadedFile);

        $data = [
            'name' => $this->getFilename($request, $uploadedFile),
            'type' => $uploadedFile->getClientMediaType(),
            'size' => $uploadedFile->getSize(),
            'user_id' => $user->id,
            'tmp_name' => $tmpFilename,
            'description' => '',
            'content_terms_of_use_id' => 0,
        ];

        $file = \StandardFile::create($data);

        if ($error = $folder->validateUpload($file,$user->id)) {
            throw new BadRequestException($error);
        }

        $file = $folder->addFile($file);
        if (!$file) {
            throw new InternalServerError();
        }

        return $file->getFileRef();
    }

    protected function getUploadedFile($request)
    {
        $files = $this->getUploadedFiles($request);

        if (0 === count($files)) {
            throw new BadRequestException('File upload required.');
        }

        if (count($files) > 1) {
            throw new BadRequestException('Multiple file upload not possible.');
        }

        $uploadedFile = reset($files);
        if (UPLOAD_ERR_OK !== $uploadedFile->getError()) {
            throw new BadRequestException('Upload error.');
        }

        return $uploadedFile;
    }

    protected function getUploadedFiles($request)
    {
        $files = [];
        foreach ($request->getUploadedFiles() as $item) {
            if (!is_array($item)) {
                $files[] = $item;
            } else {
                foreach ($item as $file) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string       $directory directory to which the file is moved
     * @param UploadedFile $uploaded  file uploaded file to move
     *
     * @return string filename of moved file
     */
    protected function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory.DIRECTORY_SEPARATOR.$filename);

        return $directory.DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTmpPath()
    {
        return $GLOBALS['TMP_PATH'];
    }

    protected function getTmpFile()
    {
        return tempnam($this->getTmpPath(), 'jsonapi');
    }

    protected function getFilename($request, $uploadedFile)
    {
        return $request->hasHeader('slug')
            ? rawurldecode(reset($request->getHeader('Slug')))
            : $uploadedFile->getClientFilename();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function redirectToFileRef(Response $response, \FileRef $fileRef)
    {
        $pathinfo = $this->getSchema($fileRef)->getSelfSubLink($fileRef)->getSubHref();
        $old = \URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = \URLHelper::getURL($this->container->get(C::JSON_URL_PREFIX).$pathinfo, [], true);
        \URLHelper::setBaseURL($old);

        return $response->withRedirect($url, 201);
    }
}
