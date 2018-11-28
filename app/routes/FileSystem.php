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
        //check if the file_id references a file reference object:
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $user_id = \User::findCurrent()->id;

        //check if the file reference is placed inside a folder.
        //(must be present to check for permissions)
        if (!$file_ref->folder) {
            $this->halt(500, 'File reference has no folder!');
        }

        $folder_type = $file_ref->folder->getTypedFolder();
        if (!$folder_type) {
            $this->halt(500, "File reference's folder has no folder type!");
        }

        //check if the current user has the permissions to read this file reference:
        if (!$folder_type->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this file reference!');
        }

        $extended_data = \Request::int('extended', 0);
        //the current user may read the file reference:
        $result = $file_ref->toRawArray();


        //add permissions for the user who called this REST route to the result:

        //this code wouldn't be executed if the FileRef wasn't readable (see above):
        $result['is_readable'] = true;

        $result['is_downloadable'] = $folder_type->isFileDownloadable($file_ref->id, $user_id);
        $result['is_editable'] = $folder_type->isFileEditable($file_ref->id, $user_id);
        $result['is_writable'] = $folder_type->isFileWritable($file_ref->id, $user_id);
        //Shortcuts:
        $result['size'] = $file_ref->file->size;
        $result['mime_type'] = $file_ref->file->mime_type;
        $result['storage'] = $file_ref->file->storage;
        if ($result['storage'] === "url") {
            $result['url'] = $file_ref->file->url;
        }

        //maybe the user wants not just only the FileRef object's data
        //but also data from related objects:
        if ($extended_data) {
            //In case more data are requested we can add a File,
            //Folder and ContentTermsOfUse object

            //folder does exist (since we checked for its existence above)
            $result['folder'] = $file_ref->folder->toRawArray();

            //file and owner might not exist, so we have to check that:
            if($file_ref->file) {
                $result['file'] = $file_ref->file->toRawArray();
            }
            if($file_ref->owner) {
                $result['owner'] = $file_ref->owner->toRawArray();
            }

            //$result['license'] = $file_ref->license; //to be activated when licenses are defined

            if($file_ref->terms_of_use) {
                $result['terms_of_use'] =
                    $file_ref->terms_of_use->toRawArray();
            }
        }

        return $result;
    }

    /**
     * Get the data of a file by the ID of an associated FileRef object
     *
     * @get /file/:file_ref_id/download
     */
    public function getFileRefData($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        if (!$file_ref->folder) {
            $this->halt(500, 'File reference is not bound to a folder!');
        }

        $folder_type = $file_ref->folder->getTypedFolder();
        if (!$folder_type) {
            $this->halt(500, "Cannot find folder type of the file reference's folder!");
            return;
        }

        $user_id = \User::findCurrent()->id;

        //check if the current user has the permissions to read this file reference:
        if ($folder_type->isReadable($user_id) && $folder_type->isFileDownloadable($file_ref_id, $user_id)) {
            //if this code is executed we can read the file's data

            //check if file exists:
            if (!$file_ref->file) {
                $this->halt(500, 'File reference has no associated file object!');
            }

            $data_path = $file_ref->file->getPath();
            if (!file_exists($data_path)) {
                $this->notFound("File was not found in the operating system's file system!");
            }

            $this->lastModified($file_ref->file->chdate);
            $this->sendFile($data_path, ['filename' => $file_ref->name]);
        }
    }

    /**
     * Update file data using a FileReference to it.
     *
     * @post /file/:file_ref_id/update
     */
    public function updateFileData($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $user = \User::findCurrent();

        //We only update the first file:
        $uploaded_file = array_shift($this->data['_FILES']);

        //FileManager::updateFileRef handles the whole file upload
        //and does all the necessary security checks:
        $result = \FileManager::updateFileRef(
            $file_ref,
            $user,
            $uploaded_file,
            true,
            false
        );

        if ($result instanceof \FileRef) {
            $file_ref_data = $result->toRawArray();
            //Shortcuts:
            $file_ref_data['size'] = $result->file->size;
            $file_ref_data['mime_type'] = $result->file->mime_type;
            $file_ref_data['storage'] = $result->file->storage;
            if ($file_ref_data['storage'] === "url") {
                $file_ref_data['url'] = $file_ref->file->url;
            }
            return $file_ref_data;
        } else {
            $this->halt(500, 'Error while updating a file reference: ' . implode(' ', $result));
        }
    }

    /**
     * Edit a file reference.
     *
     * @put /file/:file_ref_id
     */
    public function editFileRef($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $user = \User::findCurrent();

        $errors = \FileManager::editFileRef(
            $file_ref,
            $user,
            $this->data['name'],
            $this->data['description'],
            $this->data['content_term_of_use_id'],
            $this->data['license']
        );

        if (!empty($errors)) {
            $this->halt(500, 'Error while editing a file reference: ' . implode(' ', $errors));
        }

        $file_ref_data = $file_ref->toRawArray();
        //Shortcuts:
        $file_ref_data['size'] = $file_ref->file->size;
        $file_ref_data['mime_type'] = $file_ref->file->mime_type;
        $file_ref_data['storage'] = $file_ref->file->storage;
        if ($file_ref_data['storage'] === "url") {
            $file_ref_data['url'] = $file_ref->file->url;
        }
        return $file_ref_data;
    }

    /**
     * Copies a file reference.
     *
     * @post /file/:file_ref_id/copy/:destination_folder_id
     */
    public function copyFileRef($file_ref_id, $destination_folder_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $destination_folder = \Folder::find($destination_folder_id);
        if (!$destination_folder) {
            $this->notFound('Destination folder not found!');
        }

        $destination_folder = $destination_folder->getTypedFolder();
        if (!$destination_folder) {
            $this->halt(500, 'Cannot find folder type of destination folder!');
            return;
        }

        $user = \User::findCurrent();

        $errors = \FileManager::copyFileRef($file_ref, $destination_folder, $user);

        if (!empty($errors)) {
            $this->halt(500, 'Error while copying a file reference: ' . implode(' ', $errors));
        }

        $file_ref_data = $file_ref->toRawArray();
        //Shortcuts:
        $file_ref_data['size'] = $file_ref->file->size;
        $file_ref_data['mime_type'] = $file_ref->file->mime_type;
        $file_ref_data['storage'] = $file_ref->file->storage;
        if ($file_ref_data['storage'] === "url") {
            $file_ref_data['url'] = $file_ref->file->url;
        }
        return $file_ref_data;
    }

    /**
     * Moves a file reference.
     *
     * @post /file/:file_ref_id/move/:destination_folder_id
     */
    public function moveFileRef($file_ref_id, $destination_folder_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $destination_folder = \Folder::find($destination_folder_id);
        if (!$destination_folder) {
            $this->notFound('Destination folder not found!');
        }

        $destination_folder = $destination_folder->getTypedFolder();
        if (!$destination_folder) {
            $this->halt(500, 'Cannot find folder type of destination folder!');
            return;
        }

        $user = \User::findCurrent();

        $errors = \FileManager::moveFileRef($file_ref, $destination_folder, $user);

        if (!empty($errors)) {
            $this->halt(500, 'Error while moving a file reference: ' . implode(' ', $errors));
        }

        $file_ref_data = $file_ref->toRawArray();
        //Shortcuts:
        $file_ref_data['size'] = $file_ref->file->size;
        $file_ref_data['mime_type'] = $file_ref->file->mime_type;
        $file_ref_data['storage'] = $file_ref->file->storage;
        if ($file_ref_data['storage'] === "url") {
            $file_ref_data['url'] = $file_ref->file->url;
        }
        return $file_ref_data;
    }

    /**
     * Deletes a file reference.
     *
     * @delete /file/:file_ref_id
     */
    public function deleteFileRef($file_ref_id)
    {
        $file_ref = \FileRef::find($file_ref_id);
        if (!$file_ref) {
            $this->notFound('File reference not found!');
        }

        $user = \User::findCurrent();

        $errors = \FileManager::deleteFileRef($file_ref, $user);

        if (!empty($errors)) {
            $this->halt(500, 'Error while deleting a file reference: ' . implode(' ', $errors));
        }

        $this->halt(200, 'OK');
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
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder_type = $folder->getTypedFolder();
        if (!$folder_type) {
            $this->halt(500, 'Folder type not found!');
        }

        $user_id = \User::findCurrent()->id;

        $result['is_visible']  = $folder_type->isVisible($user_id);
        $result['is_readable'] = $folder_type->isReadable($user_id);
        $result['is_writable'] = $folder_type->isWritable($user_id);

        //If the folder isn't readable by the user (given by user_id)
        //the result parameter is_readable is set to false.
        if ($result['is_readable']) {

            $result = array_merge($result, $folder->toRawArray());
            //The field "data_content" must be handled differently
            //than the other fields since it contains JSON data.
            $data_content = json_decode($folder->data_content);
            $result['data_content'] = $data_content;

            $subfolders = $folder->subfolders;

            if ($subfolders) {
                $result['subfolders'] = [];
                foreach ($subfolders as $subfolder) {
                    $subfolder_type = $subfolder->getTypedFolder();
                    if ($subfolder_type->isVisible($user_id)) {
                        //Here we must also take special care of the
                        //"data_content" field.
                        $subfolder_data = $subfolder->toRawArray();
                        $subfolder_data['data_content'] = json_decode(
                            $subfolder->data_content
                        );
                        $result['subfolders'][] = $subfolder_data;
                    }
                }
            }

            $file_refs = $folder_type->getFiles();
            if ($file_refs) {
                $result['file_refs'] = [];
                foreach ($file_refs as $file_ref) {
                    $file_ref_data = $file_ref->toRawArray();
                    //Shortcuts:
                    $file_ref_data['size'] = $file_ref->file->size;
                    $file_ref_data['mime_type'] = $file_ref->file->mime_type;
                    $file_ref_data['storage'] = $file_ref->file->storage;
                    if ($file_ref_data['storage'] === "url") {
                        $file_ref_data['url'] = $file_ref->file->url;
                    }

                    $result['file_refs'][] = $file_ref_data;
                }
            }

        }

        return $result;
    }

    /**
     * Creates a new folder inside of another folder and returns the new object on success.
     * @post /folder/:parent_folder_id/new_folder
     */
    public function createNewFolder($parent_folder_id)
    {
        $parent_folder = \Folder::find($parent_folder_id);
        if (!$parent_folder) {
            $this->notFound('Parent folder not found!');
        }

        $parent_folder = $parent_folder->getTypedFolder();
        if (!$parent_folder) {
            $this->halt(500, 'Parent folder has an invalid folder type!');
        }

        $user = \User::findCurrent();

        if (!$parent_folder->isWritable($user->id)) {
            $this->halt(403, 'You are not permitted to create a subfolder in the parent folder!');
        }

        $result = \FileManager::createSubFolder(
            $parent_folder,
            $user,
            'StandardFolder', //to be extended
            $this->data['name'],
            $this->data['description']
        );

        if (!$result instanceof FolderType) {
            $this->halt(500, 'Error while creating a folder: ' . implode(' ', $result));
        }

        $folder = Folder::find($result->getId());
        $folder_data = $folder->toRawArray();
        $folder_data['data_content'] = json_decode($folder->data_content);
        return $folder_data;
    }

    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/files
     */
    public function getFileRefsOfFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Folder type not found!');
        }

        $user_id = \User::findCurrent()->id;

        if (!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }

        $file_refs = \FileRef::findBySql(
            'folder_id = :folder_id ORDER BY name ASC LIMIT :limit OFFSET :offset',
            [
                'folder_id' => $folder->id,
                'limit' => $this->limit,
                'offset' => $this->offset
            ]
        );

        $total = \FileRef::countBySql('folder_id = :folder_id', [
            'folder_id' => $folder->id
        ]);

        $result = [];
        if ($file_refs) {
            foreach ($file_refs as $file_ref) {
                $file_ref_data = $file_ref->toRawArray();
                //Shortcuts:
                $file_ref_data['size'] = $file_ref->file->size;
                $file_ref_data['mime_type'] = $file_ref->file->mime_type;
                $file_ref_data['storage'] = $file_ref->file->storage;
                if ($file_ref_data['storage'] === "url") {
                    $file_ref_data['url'] = $file_ref->file->url;
                }

                $result[] = $file_ref_data;
            }
        }

        return $this->paginated($result, $total, ['folder_id' => $folder->id]);
    }


    /**
     * Get a list with all FileRef objects of a folder.
     * @get /folder/:folder_id/subfolders
     */
    public function getSubfoldersOfFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Folder type not found!');
        }

        $user_id = \User::findCurrent()->id;

        if(!$folder->isReadable($user_id)) {
            $this->halt(403, 'You are not permitted to read this folder!');
        }

        $subfolders = \Folder::findBySql(
            'parent_id = :parent_id ORDER BY name ASC LIMIT :limit OFFSET :offset',
            [
                'parent_id' => $folder->id,
                'limit'     => $this->limit,
                'offset'    => $this->offset,
            ]
        );

        $total = \Folder::countBySql('parent_id = :parent_id', [
            'parent_id' => $folder->id
        ]);

        $result = [];
        if ($subfolders) {
            foreach ($subfolders as $subfolder) {
                $subfolder_type = $subfolder->getTypedFolder();
                if ($subfolder_type->isVisible($user_id)) {
                    //Here we must also take special care of the
                    //"data_content" field.
                    $subfolder_data = $subfolder->toRawArray();
                    $subfolder_data['data_content'] = json_decode(
                        $subfolder->data_content
                    );
                    $result[] = $subfolder_data;
                }
            }
        }

        return $this->paginated($result, $total, ['folder_id' => $folder->id]);
    }

    /**
     * Get a list with permissions a user (or the current user) has for a folder.
     * @get /folder/:folder_id/permissions
     * @get /folder/:folder_id/permissions/:user_id
     */
    public function getFolderPermissions($folder_id, $user_id = null)
    {
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Folder type not found!');
        }

        if (!$user_id) {
            //user_id is not set: use the ID of the current user
            $user_id = \User::findCurrent()->id;
        }

        // read permissions of the user and return them:
        return [
            'folder_id'   => $folder->id,
            'user_id'     => $user_id,
            'is_visible'  => $folder->isVisible($user_id),
            'is_readable' => $folder->isReadable($user_id),
            'is_writable' => $folder->isWritable($user_id),
        ];
    }

    /**
     * Allows editing the name or the description (or both) of a folder.
     *
     * @put /folder/:folder_id
     */
    public function editFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Folder has an invalid folder type!');
        }

        $result = \FileManager::editFolder($folder, \User::findCurrent(), $this->data['name'], $this->data['description']);

        if (!($result instanceof FolderType)) {
            $this->halt(500, 'Error while editing a folder: ' . implode(' ', $errors));
        }

        //We must get the result folder from the database to get updated data.
        //Furthermore we can't use the FolderType object since it
        //doesn't have the toRawArray method.
        $result_folder = Folder::find($result->getId());
        $folder_data = $result_folder->toRawArray();
        $folder_data['data_content'] = json_decode($result_folder->data_content);
        return $folder_data;
    }

    /**
     * Copies a folder into another folder.
     *
     * @post /folder/:folder_id/copy/:destination_folder_id
     */
    public function copyFolder($folder_id, $destination_folder_id)
    {
        $folder = \Folder::find($folder_id);
        $destination_folder = \Folder::find($destination_folder_id);

        if (!$folder) {
            $this->notFound('Source folder not found!');
        }
        if (!$destination_folder) {
            $this->notFound('Destination folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Source folder type not found!');
        }

        $destination_folder = $destination_folder->getTypedFolder();
        if (!$destination_folder) {
            $this->halt(500, 'Destination folder type not found!');
        }

        $user = \User::findCurrent();

        $result = \FileManager::copyFolder($folder, $destination_folder, $user);

        if (!$result instanceof FolderType) {
            $this->halt(500, 'Error while copying a folder: ' . implode(' ', $result));
        }

        //We must get the result folder from the database to get updated data.
        //Furthermore we can't use the FolderType object since it
        //doesn't provide us with the toRawArray method.
        $result_folder = Folder::find($result->getId());
        $folder_data = $result_folder->toRawArray();
        $folder_data['data_content'] = json_decode($result_folder->data_content);
        return $folder_data;
    }


    /**
     * Move a folder into another folder.
     * @post /folder/:folder_id/move/:destination_folder_id
     */
    public function moveFolder($folder_id, $destination_folder_id)
    {
        $folder = \Folder::find($folder_id);
        $destination_folder = \Folder::find($destination_folder_id);

        if (!$folder) {
            $this->notFound('Source folder not found!');
        }
        if (!$destination_folder) {
            $this->notFound('Destination folder not found!');
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            $this->halt(500, 'Folder has an invalid folder type!');
        }

        $destination_folder = $destination_folder->getTypedFolder();
        if (!$destination_folder) {
            $this->halt(500, 'Destination folder has an invalid folder type!');
        }

        $user = \User::findCurrent();

        $errors = \FileManager::moveFolder($folder, $destination_folder, $user);

        if (!empty($errors)) {
            $this->halt(500, 'Error while moving a folder: ' . implode(' ', $errors));
        }

        //We must get the result folder from the database to get updated data.
        //Furthermore we can't use the FolderType object since it
        //doesn't provide us with the toRawArray method.
        $result_folder = Folder::find($folder->getId());
        $folder_data = $result_folder->toRawArray();
        $folder_data['data_content'] = json_decode($result_folder->data_content);
        return $folder_data;
    }


    /**
     * Deletes a folder.
     *
     * @delete /folder/:folder_id
     */
    public function deleteFolder($folder_id)
    {
        $folder = \Folder::find($folder_id);
        if (!$folder) {
            $this->notFound('Folder not found!');
        }

        $folder_type = $folder->getTypedFolder();
        if (!$folder_type) {
            $this->halt(500, 'Folder type of folder not found!');
        }

        $user = \User::findCurrent();

        $errors = \FileManager::deleteFolder($folder_type, $user);

        if (!empty($errors)) {
            $this->halt(500, 'Error while deleting a folder: ' . implode(' ', $errors));
        }

        $folder_data = $folder->toRawArray();
        $folder_data['data_content'] = json_decode($folder->data_content);
        return $folder_data;
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

        $total = \ContentTermsOfUse::countBySql('1');

        $result = [];
        foreach ($objects as $object) {
            $result[] = $object->toRawArray();
        }

        return $this->paginated($result, $total);
    }


}
