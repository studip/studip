<?php
/**
 * This class implements REST routes for the new Stud.IP file system.
 *
 * @author Moritz Strohm <strohm@data-quest.de>
 * @license GNU General Public License Version 2 or later
 *
 * Partially based upon the Files.php source code from Jan-Hendrik Willms
 * (tleilax+studip@gmail.com) and mluzena@uos.de which is also
 * licensed under the terms of the GNU General Public License Version 2
 * or later.
 */

namespace RESTAPI\Routes;

class FileSystem extends \RESTAPI\RouteMap
{
    // FILE REFERENCE AND FILE ROUTES:

    /**
     * Get a file reference object (metadata)
     * @get /file/:file_ref_id
     */
    public function getFileRef($file_ref_id)
    {
        return $this->filerefToJSON(
            $this->requireFileRef($file_ref_id),
            (bool) \Request::int('extended')
        );
    }

    /**
     * Get the data of a file by the ID of an associated FileRef object
     *
     * @get /file/:file_ref_id/download
     */
    public function getFileRefData($file_ref_id)
    {
        $file_ref = $this->requireFileRef($file_ref_id);

        // check if the current user has the permissions to read this file reference:
        $user = \User::findCurrent();
        if (!$file_ref->folder->getTypedFolder()->isFileDownloadable($file_ref_id, $user->id)) {
            $this->error(403, "You may not download the file reference with the id {$file_ref_id}");
        }

        // check if file exists:
        if (!$file_ref->file) {
            $this->error(500, 'File reference has no associated file object!');
        }

        $data_path = $file_ref->file->getPath();
        if (!file_exists($data_path)) {
            $this->error(500, "File was not found in the operating system's file system!");
        }

        $this->lastModified($file_ref->file->chdate);
        $this->sendFile($data_path, ['filename' => $file_ref->name]);
    }

    /**
     * Update file data using a FileReference to it.
     *
     * @post /file/:file_ref_id/update
     */
    public function updateFileData($file_ref_id)
    {
        // We only update the first file:
        $uploaded_file = array_shift($this->data['_FILES']);

        // FileManager::updateFileRef handles the whole file upload
        // and does all the necessary security checks:
        $result = \FileManager::updateFileRef(
            $this->requireFileRef($file_ref_id),
            \User::findCurrent(),
            $uploaded_file,
            true,
            false
        );

        if (!$result instanceof \FileRef) {
            $this->error(500, 'Error while updating a file reference: ' . implode(' ', $result));
        }

        return $this->filerefToJSON($result);
    }

    /**
     * Edit a file reference.
     *
     * @put /file/:file_ref_id
     */
    public function editFileRef($file_ref_id)
    {
        $result = \FileManager::editFileRef(
            $this->requireFileRef($file_ref_id),
            \User::findCurrent(),
            $this->data['name'],
            $this->data['description'],
            $this->data['content_term_of_use_id'],
            $this->data['license']
        );

        if (!$result instanceof \FileRef) {
            $this->error(500, 'Error while editing a file reference: ' . implode(' ', $result));
        }

        return $this->filerefToJSON($result);
    }

    /**
     * Copies a file reference.
     *
     * @post /file/:file_ref_id/copy/:destination_folder_id
     */
    public function copyFileRef($file_ref_id, $destination_folder_id)
    {
        $result = \FileManager::copyFileRef(
            $this->requireFileRef($file_ref_id),
            $this->requireFolder($destination_folder_id)->getTypedFolder(),
            \User::findCurrent()
        );

        if (!$result instanceof \FileRef) {
            $this->error(500, 'Error while copying a file reference: ' . implode(' ', $result));
        }

        return $this->filerefToJSON($result);
    }

    /**
     * Moves a file reference.
     *
     * @post /file/:file_ref_id/move/:destination_folder_id
     */
    public function moveFileRef($file_ref_id, $destination_folder_id)
    {
        $result = \FileManager::moveFileRef(
            $this->requireFileRef($file_ref_id),
            $this->requireFolder($destination_folder_id)->getTypedFolder(),
             \User::findCurrent()
        );

        if (!$result instanceof \FileRef) {
            $this->error(500, 'Error while moving a file reference: ' . implode(' ', $result));
        }

        return $this->filerefToJSON($result);
    }

    /**
     * Deletes a file reference.
     *
     * @delete /file/:file_ref_id
     */
    public function deleteFileRef($file_ref_id)
    {
        $result = \FileManager::deleteFileRef(
            $this->requireFileRef($file_ref_id),
            \User::findCurrent()
        );

        if (!$result instanceof \FileRef) {
            $this->error(500, 'Error while deleting a file reference: ' . implode(' ', $result));
        }

        $this->halt(200);
    }

    /**
     * Upload file to given folder.
     * file data has to be attached as multipart/form-data
     *
     * @post /file/:folder_id
     */
    public function uploadFile($folder_id)
    {
        $typed_folder = $this->requireFolder($folder_id)->getTypedFolder();
        if (isset($this->data['_FILES'])) {
            $file_data = array_map(function ($a) {
                return is_array($a) ? $a : [$a];
            }, array_shift($this->data['_FILES']));
        }
        if (is_array($file_data)) {
            $validated_files = \FileManager::handleFileUpload(
                $file_data,
                $typed_folder,
                $this->requireUser()->id
            );

            if (count($validated_files['error']) > 0) {
                $this->error(500, 'Error while uploading files: ' . implode(' ', $validated_files['error']));
            }

            $uploaded_files = \SimpleCollection::createFromArray($validated_files['files']);
            $default_license = \ContentTermsOfUse::findDefault();
            $uploaded_files->setValue('content_terms_of_use_id', $default_license->id);
            $uploaded_files->store();
            if (count($uploaded_files) === 1) {
                $result = $this->filerefToJSON($uploaded_files->first());
            } else {
                $result = $uploaded_files->map(function ($f) {
                    return $this->filerefToJSON($f);
                });
            }
            $this->halt(201, [], $result);
        } else {
            $this->error(400, 'No files found in request.');
        }
    }

    // FOLDER ROUTES:

    /**
     * Returns a list of defined folder types, separated by range type.
     * @get /studip/file_system/folder_types
     */
    public function getDefinedFolderTypes()
    {
        return \FileManager::getFolderTypes();
    }

    /**
     * Get a folder object with its file references, subdirectories and the permissions for the user who has made the API call.
     * @get /folder/:folder_id
     */
    public function getFolder($folder_id)
    {
        return $this->folderToJSON(
            $this->requireFolder($folder_id),
            true
        );
    }

    /**
     * Creates a new folder inside of another folder and returns the new object on success.
     * @post /folder/:parent_folder_id/new_folder
     */
    public function createNewFolder($parent_folder_id)
    {
        $user   = \User::findCurrent();
        $parent = $this->requireTypedFolder($parent_folder_id);

        if (!$parent->isWritable($user->id)) {
            $this->error(403, 'You are not permitted to create a subfolder in the parent folder!');
        }

        $result = \FileManager::createSubFolder(
            $parent,
            $user,
            'StandardFolder', //to be extended
            $this->data['name'],
            $this->data['description']
        );

        if (!$result instanceof \FolderType) {
            $this->error(500, 'Error while creating a folder: ' . implode(' ', $result));
        }

        return $this->folderToJSON(
            $this->requireFolder($result->getId())
        );
    }

    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/files
     */
    public function getFileRefsOfFolder($folder_id)
    {
        $folder = $this->requireFolder($folder_id);

        $query = "folder_id = :folder_id ORDER BY name ASC";
        $parameters[':folder_id'] = $folder->id;

        if ($limit || $offset) {
            $query .= " LIMIT :limit OFFSET :offset";
            $parameters[':limit'] = $limit;
            $parameters[':offset'] = $offset;
        }

        $file_refs = \FileRef::findAndMapBySql(function (\FileRef $ref) {
            return $this->filerefToJSON($ref);
        }, $query, $parameters);

        return $this->paginated(
            $file_refs,
            \FileRef::countByFolder_id($folder->id),
            ['folder_id' => $folder->id]
        );
    }


    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/subfolders
     */
    public function getSubfoldersOfFolder($folder_id)
    {
        $user   = $this->requireUser();
        $folder = $this->requireFolder($folder_id);

        $query = "parent_id = :parent_id ORDER BY name ASC";
        $parameters = [':parent_id' => $folder->id];

        if ($this->limit || $this->offset) {
            $query .= " LIMIT :limit OFFSET :offset";
            $parameters[':limit']  = $this->limit;
            $parameters[':offset'] = $this->offset;
        }

        $subfolders = \Folder::findAndMapBySql(function (\Folder $subfolder) use ($user) {
            $type = $subfolder->getTypedFolder();
            if (!$type || !$type->isVisible($user->id)) {
                return false;
            }
            return $this->folderToJSON($subfolder);
        }, $query, $parameters);

        return $this->paginated(
            array_filter($subfolders),
            \Folder::countByParent_id($folder_id),
            ['folder_id' => $folder_id]
        );
    }

    /**
     * Get a list with permissions the current user has for a folder.
     * @get /folder/:folder_id/permissions
     */
    public function getFolderPermissions($folder_id)
    {
        $user   = $this->requireUser();
        $folder = $this->requireFolder($folder_id);

        // read permissions of the user and return them:
        return array_merge([
            'folder_id'   => $folder->id,
            'user_id'     => $user->id,
        ], $this->folderPermissionsToJSON($folder));
    }

    /**
     * Allows editing the name or the description (or both) of a folder.
     *
     * @put /folder/:folder_id
     */
    public function editFolder($folder_id)
    {
        if (isset($this->data['name']) && !$this->data['name']) {
            $this->error(400, "The name for the folder with the id {$folder_id} must not be empty!");
        }

        $user = $this->requireUser();
        $typed_folder = $this->requireTypedFolder($folder_id);

        if (!$typed_folder->isEditable($user->id)) {
            $this->error(403, "You may not edit the folder with id {$folder_id}!");
        }

        if (!$typed_folder instanceof \StandardFolder) {
            $this->error(501, "Editing is only allowed for folders of type StandardFolder for now!");
        }

        if ($this->data['name']) {
            $typed_folder->name = $this->data['name'];
        }
        if (isset($this->data['description'])) {
            $typed_folder->description = $this->data['description'] ?: '';
        }

        if (!$typed_folder->store()) {
            $this->error(500, "Could not store folder with id {$folder_id}!");
        }

        return $this->folderToJSON(
            $this->requireFolder($folder_id)
        );
    }

    /**
     * Copies a folder into another folder.
     *
     * @post /folder/:folder_id/copy/:destination_folder_id
     */
    public function copyFolder($folder_id, $destination_folder_id)
    {
        $result = \FileManager::copyFolder(
            $this->requireTypedFolder($folder_id),
            $this->requireTypedFolder($destination_folder_id),
            \User::findCurrent()
        );

        if (!$result instanceof \FolderType) {
            $this->error(500, 'Error while copying a folder: ' . implode(' ', $result));
        }

        return $this->folderToJSON(
            $this->requireFolder($result->getId())
        );
    }


    /**
     * Move a folder into another folder.
     * @post /folder/:folder_id/move/:destination_folder_id
     */
    public function moveFolder($folder_id, $destination_folder_id)
    {
        $result = \FileManager::moveFolder(
            $this->requireTypedFolder($folder_id),
            $this->requireTypedFolder($destination_folder_id),
            \User::findCurrent()
        );

        if (!$result instanceof \FolderType) {
            $this->error(500, 'Error while moving a folder: ' . implode(' ', $result));
        }

        return $this->folderToJSON(
            $this->requireFolder($folder_id)
        );
    }


    /**
     * Deletes a folder.
     *
     * @delete /folder/:folder_id
     */
    public function deleteFolder($folder_id)
    {
        $result = \FileManager::deleteFolder(
            $this->requireTypedFolder($folder_id),
            \User::findCurrent()
        );

        if (!$result instanceof \FolderType) {
            $this->error(500, 'Error while deleting a folder: ' . implode(' ', $result));
        }

        $this->halt(200);
    }

    // RELATED OBJECT ROUTES:

    /**
     * Get a collection of all ContentTermsOfUse objects
     *
     * @get /studip/content_terms_of_use_list
     */
    public function getContentTermsOfUseList()
    {
        $objects = \ContentTermsOfUse::findBySql(
            '1 ORDER BY name ASC LIMIT :limit OFFSET :offset',
            ['limit'  => $this->limit, 'offset' => $this->offset]
        );

        return $this->paginated(
            array_map([$this, 'termsOfUseToJSON'], $objects),
            \ContentTermsOfUse::countBySql('1')
        );
    }

    // UTILITY METHODS

    /**
     * Requires a valid user object.
     * @return User object
     */
    private function requireUser()
    {
        return \User::findCurrent();
    }

    /**
     * Requires a valid file reference object
     * @param  mixed $id_or_object Either a file reference id or object
     * @return FileRef object
     */
    private function requireFileRef($id_or_object)
    {
        if ($id_or_object instanceof \FileRef) {
            $file_ref = $id_or_object;
        } else {
            //check if the file_id references a file reference object:
            $file_ref = \FileRef::find($id_or_object);
            if (!$file_ref) {
                $this->notFound("File reference with id {$id_or_object} not found!");
            }
        }

        // check if the file reference is placed inside a folder.
        // (must be present to check for permissions)
        if (!$file_ref->folder) {
            $this->error(500, "File reference with id {$file_ref->id} has no folder!");
        }

        $typed_folder = $file_ref->folder->getTypedFolder();
        if (!$typed_folder) {
            $this->error(500, "The folder of file reference with id {$file_ref->id} has no folder type!");
        }

        //check if the current user has the permissions to read this file reference:
        if (!$typed_folder->isReadable($this->requireUser()->id)) {
            $this->error(403, "You are not permitted to read the file reference with id {$file_ref->id}!");
        }

        return $file_ref;
    }

    /**
     * Converts a file reference object to JSON.
     * @param  FileRef $ref      File reference object
     * @param  boolean $extended Extended output? (includes folder, owner and terms of use)
     * @return array representation for json encoding
     */
    private function filerefToJSON(\FileRef $ref, $extended = false)
    {
        $user = $this->requireUser();
        $typed_folder = $ref->folder->getTypedFolder();

        $result = array_merge($ref->toRawArray(), [
            'size'      => (int) $ref->file->size,
            'mime_type' => $ref->file->mime_type,
            'storage'   => $ref->file->storage,

            'is_readable'     => $typed_folder->isReadable($user->id),
            'is_downloadable' => $typed_folder->isFileDownloadable($ref->id, $user->id),
            'is_editable'     => $typed_folder->isFileEditable($ref->id, $user->id),
            'is_writable'     => $typed_folder->isFileWritable($ref->id, $user->id),
        ]);

        $result['downloads'] = (int) $result['downloads'];
        $result['mkdate']    = (int) $result['mkdate'];
        $result['chdate']    = (int) $result['chdate'];

        if ($result['storage'] === 'url') {
            $result['url'] = $ref->file->url;
        }

        if ($extended) {
            //folder does exist (since we checked for its existence above)
            $result['folder'] = $this->folderToJSON($ref->folder);

            if ($ref->owner) {
                $result['owner'] = User::getMiniUser($this, $ref->owner);
            }

            //$result['license'] = $file_ref->license; //to be activated when licenses are defined

            if ($ref->terms_of_use) {
                $result['terms_of_use'] = $this->termsOfUseToJSON($ref->terms_of_use);
            }
        }

        return $result;
    }

    /**
     * Requires a valid folder object
     * @param  mixed $id_or_object Either a folder id or object
     * @return Folder object
     */
    private function requireFolder($id_or_object)
    {
        if ($id_or_object instanceof \Folder) {
            $folder = $id_or_object;
        } else {
            $folder = \Folder::find($id_or_object);
            if (!$folder) {
                $this->notFound("Folder with id {$id_or_object} not found!");
            }
        }

        $typed_folder = $folder->getTypedFolder();
        if (!$typed_folder) {
            $this->error(500, "Cannot find folder type of folder with id {$folder->id}!");
            return;
        }

        if (!$typed_folder->isReadable($this->requireUser()->id)) {
            $this->error(403, "You are not allowed to read the contents of the folder with the id {$folder->id}!");
        }

        return $folder;
    }

    /**
     * Requires a valid typed folder object
     * @param  mixed $id_or_object Either a folder id or object
     * @return FolderType instance
     */
    private function requireTypedFolder($id_or_object)
    {
        return $this->requireFolder($id_or_object)->getTypedFolder();
    }

    /**
     * Converts a given folder to JSON.
     * @param  Folder  $folder   Folder object
     * @param  boolean $extended Extended output? (includes subfolders and file references)
     * @return array representation for json encoding
     */
    private function folderToJSON(\Folder $folder, $extended = false)
    {
        $result = $this->folderPermissionsToJSON($folder);

        if ($result['is_readable']) {
            $result = array_merge($folder->toRawArray(), $result);

            $result['mkdate'] = (int) $result['mkdate'];
            $result['chdate'] = (int) $result['chdate'];

            //The field "data_content" must be handled differently
            //than the other fields since it contains JSON data.
            $data_content = json_decode($folder->data_content);
            $result['data_content'] = $data_content;

            if ($extended) {
                $user = $this->requireUser();

                $result['subfolders'] = [];
                foreach ($folder->subfolders as $subfolder) {
                    if (!$subfolder->getTypedFolder()->isVisible($user->id)) {
                        continue;
                    }
                    $result['subfolders'][] = $this->folderToJSON($subfolder);
                }

                $result['file_refs'] = [];
                foreach ($folder->getTypedFolder()->getFiles() as $file_ref) {
                    $result['file_refs'][] = $this->filerefToJSON($file_ref);
                }
            }
        }

        return $result;
    }

    /**
     * Converts permissions of a folder to JSON.
     * @param  Folder $folder Folder object
     * @param  User   $user   User object to check permissions against
     * @return array representation for json encoding
     */
    private function folderPermissionsToJSON(\Folder $folder)
    {
        $user = $this->requireUser();
        $type = $folder = $folder->getTypedFolder();
        if (!$type) {
            $this->error(500, 'Folder type not found!');
        }

        return [
            'is_visible'  => $type->isVisible($user->id),
            'is_readable' => $type->isReadable($user->id),
            'is_writable' => $type->isWritable($user->id),
        ];
    }

    /**
     * Converts a terms of use object to JSON.
     * @param  ContentTermsOfUse $object Object
     * @return array representation for json encoding
     */
    private function termsOfUseToJSON(\ContentTermsOfUse $object)
    {
        $result = $object->toRawArray();

        $result['is_default'] = (bool) $result['is_default'];

        $result['mkdate'] = (int) $result['mkdate'];
        $result['chdate'] = (int) $result['chdate'];

        return $result;
    }
}
