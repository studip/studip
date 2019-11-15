<?php
/**
 * FileManager.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * The FileManager class contains methods that faciliate the management of files
 * and folders. Furthermore its methods perform necessary additional checks
 * so that files and folders are managed in a correct manner.
 *
 * It is recommended to use the methods of this class for file and folder
 * management instead of writing own methods.
 */
class FileManager
{
    //FILE HELPER METHODS

    /**
     * Removes special characters from the file name (and by that cleaning
     * the file name) so that the file name which is returned by this method
     * works on every operating system.
     *
     * @param string $file_name The file name that shall be "cleaned".
     * @param bool $shorten_name True, if the file name shall be shortened to
     *     31 characters. False, if the full length shall be kept (default).
     *
     * @return string The "cleaned" file name.
     */
    public static function cleanFileName($file_name = null, $shorten_name = false)
    {
        if(!$file_name) {
            //If you put an empty string in, you will get an empty string out!
            return $file_name;
        }

        $bad_characters = [
            ':', chr(92), '/', '"', '>', '<', '*', '|', '?',
            ' ', '(', ')', '&', '[', ']', '#', chr(36), '\'',
            '*', ';', '^', '`', '{', '}', '|', '~', chr(255)
        ];

        $replacement_characters = [
            '', '', '', '', '', '', '', '', '',
            '_', '', '', '+', '', '', '', '', '',
            '', '-', '', '', '', '', '-', '', ''
        ];

        //All control characters shall be deleted:
        for($i = 0; $i < 0x20; $i++) {
            $bad_characters[] = chr($i);
            $replacement_characters[] = '';
        }

        $clean_file_name = str_replace(
            $bad_characters,
            $replacement_characters,
            $file_name
        );

        if($clean_file_name[0] == '.') {
            $clean_file_name = mb_substr($clean_file_name, 1, mb_strlen($clean_file_name));
        }

        if($shorten_name === true) {
            //If we have to shorten the file name we have to split it up
            //into file name and extension.

            $tmp_file_name = pathinfo($clean_file_name, PATHINFO_FILENAME);
            $file_extension = pathinfo($clean_file_name, PATHINFO_EXTENSION);

            $clean_file_name = mb_substr($tmp_file_name, 0, 28)
                . '.'
                . $file_extension;

        }

        return $clean_file_name;
    }


    /**
     * Returns the icon name for a given mime type.
     *
     * @param string $mime_type The mime type whose icon is requested.
     *
     * @return string The icon name for the mime type.
     */
    public static function getIconNameForMimeType($mime_type = null)
    {
        $application_category_icons = [
            'file-pdf'     => ['pdf'],
            'file-ppt'     => ['powerpoint','presentation'],
            'file-excel'   => ['excel', 'spreadsheet', 'csv'],
            'file-word'    => ['word', 'wordprocessingml', 'opendocument.text', 'rtf'],
            'file-archive' => ['zip', 'rar', 'arj', '7z'],
        ];

        if (!$mime_type) {
            //No mime type given: We can only assume it is a generic file.
            return 'file-generic';
        }

        list($category, $type) = explode('/', $mime_type, 2);

        switch($category) {
            case 'image':
                return 'file-pic';
            case 'audio':
                return 'file-audio';
            case 'video':
                return 'file-video';
            case 'text':
                if ($type === 'csv') {
                    //CSV files:
                    return 'file-excel';
                }
                //other text files:
                return 'file-text';
            case 'application':
                //loop through all application category icons
                //and return the icon name that matches the regular expression
                //for an application mime type:
                foreach ($application_category_icons as $icon_name => $type_name) {
                    if (preg_match('/' . implode($type_name, '|') . '/i', $type)) {
                        return $icon_name;
                    }
                }
        }

        //If code execution reaches this point, no special mime type icon
        //was detected.
        return 'file-generic';
    }

    /**
     * Returns the icon for a given mime type.
     *
     * @param string $mime_type  The mime type whose icon is requested.
     * @param string $role       The requested role
     * @param array  $attributes Optional additional attributes
     *
     * @return Icon The icon for the mime type.
     */
    public static function getIconForMimeType(
        $mime_type = null,
        $role = Icon::ROLE_CLICKABLE,
        $attributes = []
    )
    {
        $icon = self::getIconNameForMimeType($mime_type);
        return Icon::create($icon, $role, $attributes);
    }

    /**
     * Returns the icon for a given file ref.
     *
     * @param FileRef|stdClass $ref        The file ref whose icon is requested.
     * @param string           $role       The requested role
     * @param array            $attributes Optional additional attributes
     * @return Icon                        The icon for the file ref.
     */
    public static function getIconForFileRef($ref, $role = Icon::ROLE_CLICKABLE, array $attributes = [])
    {
        if ($ref->is_link) {
            return Icon::create('link-extern', $role, $attributes);
        }
        return self::getIconForMimeType($ref->mime_type);
    }

    /**
     * Builds a download URL for the file archive of an archived course.
     *
     * @param ArchivedCourse $archived_course An archived course whose file
     *     archive is requested.
     * @param bool $protected_archive True, if the protected file archive
     *     is requested. False, if the "readable for everyone" file archive
     *     is requested (default).
     *
     * @return string The download link for the file or an empty string on failure.
     */
    public static function getDownloadURLForArchivedCourse(
        ArchivedCourse $archived_course,
        $protected_archive = false
    )
    {
        $file_id = $protected_archive
                 ? $archived_course->archiv_protected_file_id
                 : $archived_course->archiv_file_id;

        if ($file_id) {
            $file_name = sprintf(
                '%s-%s.zip',
                $protected_archive ? _('Geschützte Dateisammlung') : _('Dateisammlung'),
                mb_substr($archived_course->name, 0, 200)
            );

            //file_id is set: file archive exists
            return URLHelper::getURL('sendfile.php', [
                'type'           => 1,
                'file_id'        => $file_id,
                'file_name'      => $file_name,
                'force_download' => true, //because archive files are ZIP files
            ], true);
        }

        //file_id is empty: no file archive available
        return '';
    }

    /**
     * Builds a download link for the file archive of an archived course.
     *
     * @param ArchivedCourse $archived_course An archived course whose file
     *     archive is requested.
     * @param bool $protected_archive True, if the protected file archive
     *     is requested. False, if the "readable for everyone" file archive
     *     is requested (default).
     *
     * @return string The download link for the file or an empty string on failure.
     */
    public static function getDownloadLinkForArchivedCourse(
        ArchivedCourse $archived_course,
        $protected_archive = false
    )
    {
        return htmlReady(
            self::getDownloadURLForArchivedCourse(
                $archived_course,
                $protected_archive
            )
        );
    }

    /**
     * Builds a download link for temporary files.
     */
    public static function getDownloadLinkForTemporaryFile(
        $temporary_file_name = null,
        $download_file_name = null
    )
    {
        return htmlReady(
            self::getDownloadURLForTemporaryFile(
                $temporary_file_name,
                $download_file_name
            )
        );
    }


    /**
     * Builds a download URL for temporary files.
     */
    public static function getDownloadURLForTemporaryFile(
        $temporary_file_name = null,
        $download_file_name = null
    )
    {
        return URLHelper::getURL('sendfile.php', [
            'token'          => Token::create(),
            'type'           => 4,
            'file_id'        => $temporary_file_name,
            'file_name'      => $download_file_name,
            'force_download' => true, //because temporary files have a reason for their name
        ], true);
    }

    //FILE METHODS

    /**
     * This is a helper method that checks an uploaded file for errors
     * which appeared during upload.
     */
    public static function checkUploadedFileStatus($uploaded_file)
    {
        $errors = [];
        if ($uploaded_file['error'] === UPLOAD_ERR_INI_SIZE) {
            $errors[] = sprintf(_('Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: %s.'), 'upload_max_filesize=' . ini_get('upload_max_filesize'));
        } elseif ($uploaded_file['error'] > 0) {
            $errors[] = sprintf(
                _('Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: %s.'),
                $uploaded_file['error']
            );
        }
        return $errors;
    }

    /**
     * Handles uploading one or more files
     *
     * @param uploaded_files A two-dimensional array with file data for all uploaded files.
     *     The array has the following structure in the second dimension:
     *      [
     *          'name': The name of the file
     *          'error': An integer telling if there were errors. 0, if no errors occured.
     *          'type': The uploaded file's mime type.
     *          'tmp_name': Name of the temporary file that was created right after the upload.
     *          'size': Size of the uploaded file in bytes.
     *      ]
     * @param folder the folder where the files are inserted
     * @param user_id the ID of the user who wants to upload files
     *
     * @return mixed[] Array with the created file objects and error strings
     */
    public static function handleFileUpload(Array $uploaded_files, FolderType $folder, $user_id)
    {
        $result = [];
        $error  = [];

        //check if user has write permissions for the folder:
        if (!$folder->isWritable($user_id)) {
            $error[] = _('Keine Schreibrechte für Zielordner!');
            return compact('error');
        }

        //Check if uploaded files[name] is an array.
        //This check is necessary to find out, if $uploaded_files is a
        //two-dimensional array. Each index of the first dimension
        //contains an array attribute for uploaded files, one entry per file.
        if (is_array($uploaded_files['name'])) {
            $error = [];
            foreach ($uploaded_files['name'] as $key => $filename) {

                $filetype = $uploaded_files['type'][$key] ?: get_mime_type($filename);
                $tmpname  = $uploaded_files['tmp_name'][$key];
                $size     = $uploaded_files['size'][$key];

                $uploaded_file = [
                    'name'     => $filename,
                    'type'     => $filetype,
                    'tmp_name' => $tmpname,
                    'url'      => "",
                    'size'     => $size,
                    'user_id'  => $user_id,
                    'error'    => $uploaded_files['error'][$key]
                ];
                $upload_errors = self::checkUploadedFileStatus($uploaded_file);
                if ($upload_errors) {
                    $error = array_merge($error, $upload_errors);
                    continue;
                }

                //validate the upload by looking at the folder where the
                //uploaded file shall be stored:
                if ($folder_error = $folder->validateUpload(
                    [
                        'name' => $filename,
                        'size' => $size
                    ],
                    $user_id)
                ) {
                    $error[] = $folder_error;
                    continue;
                }

                $new_reference = $folder->createFile($uploaded_file);
                if (!$new_reference){
                    $error[] = _('Ein Systemfehler ist beim Upload aufgetreten.');
                } else {
                    $result['files'][] = $new_reference;
                }

            }
        }
        return array_merge($result, compact('error'));
    }

    //FILEREF METHODS

    /**
     * This method handles updating the File a FileRef is pointing to.
     *
     * The parameters $source, $user and $uploaded_file_data are required
     * for this method to work.
     *
     * @param FileRef $source The file reference pointing to a file that
     *     shall be updated.
     * @param User $user The user who wishes to update the file.
     * @param Array $uploaded_file_data The data of the uploaded new version
     *     of the file that is going to be updated.
     * @param bool $update_filename True, if the file name of the File and the
     *     FileRef shall be set to the name of the uploaded new version
     *     of the file. False otherwise.
     * @param bool $update_other_references If other FileRefs pointing to the
     *     File that is going to be updated shall be updated too, set this
     *     to True. In case only the FileRef $source and its file shall be
     *     updated, set this to False. In the latter case the File will be
     *     copied and the copy gets updated.
     *
     * @return FileRef|string[] On success the updated $source FileRef is returned.
     *     On failure an array with error messages is returned.
     */
    public static function updateFileRef(
        FileRef $source,
        User $user,
        $uploaded_file_data = [],
        $update_filename = false,
        $update_other_references = false
    )
    {
        $errors = [];

        // Do some checks:
        $folder = $source->getFolderType();
        if (!$folder || !$folder->isFileEditable($source->id, $user->id)) {
            $errors[] = sprintf(
                _('Sie sind nicht dazu berechtigt, die Datei %s zu aktualisieren!'),
                $source->name
            );
            return $errors;
        }

        // Check if $uploaded_file_data has valid data in it:
        $upload_error = $folder->validateUpload($uploaded_file_data, $user->id);
        if ($upload_error) {
            $errors[] = $upload_error;
            return $errors;
        }

        // Ok, checks are completed: We can start updating the file.

        // If we don't update other file references that point to the File instance
        // we must first copy the file and then link the $source FileRef to the
        // new file:

        $data_file = null;

        if (!$source->file) {
            if (!$update_other_references) {
                if (!$update_filename) {
                    $uploaded_file_data['name'] = $source->name;
                } else {
                    if(!$folder->deleteFile($source->getId())){
                        $errors[] = _('Aktualisierte Datei konnte nicht ins Stud.IP Dateisystem übernommen werden!');
                        return $errors;
                    }
                }
                $new_reference = $folder->createFile($uploaded_file_data);
                if(!$new_reference){
                    $errors[] = _('Aktualisierte Datei konnte nicht ins Stud.IP Dateisystem übernommen werden!');
                    return $errors;
                }
            }
            return $new_reference;
        }


        if ($update_other_references) {
            // We want to update all file references. In that case we can just
            // use the $source FileRef's file directly.
            $data_file = $source->file;

            $connect_success = $data_file->connectWithDataFile($uploaded_file_data['tmp_name']);
            if (!$connect_success) {
                $errors[] = _('Aktualisierte Datei konnte nicht ins Stud.IP Dateisystem übernommen werden!');
                return $errors;
            }
            // moving the file was successful:
            // update the File object:
            $data_file->size      = filesize($data_file->getPath());
            $data_file->mime_type = get_mime_type($uploaded_file_data['name']);
            if ($update_filename) {
                $data_file->name = $uploaded_file_data['name'];
            }
            $data_file->user_id = $user->id;
            $data_file->store();
        } else {
            // If we want to keep the old version of the file in all other
            // File references we must create a new File object and link
            // the $source FileRef to it:

            $upload_errors = self::checkUploadedFileStatus($uploaded_file_data);

            if ($upload_errors) {
                $errors = array_merge($errors, $upload_errors);
            }

            $data_file = new File();
            $data_file->user_id = $user->id;
            $data_file->storage = 'disk';
            $data_file->id      = $data_file->getNewId();

            $connect_success = $data_file->connectWithDataFile($uploaded_file_data['tmp_name']);
            if (!$connect_success) {
                $errors[] = _('Aktualisierte Datei konnte nicht ins Stud.IP Dateisystem übernommen werden!');
                return $errors;
            }

            // moving the file was successful:
            // update the File object:
            $data_file->size      = filesize($data_file->getPath());
            $data_file->mime_type = get_mime_type($uploaded_file_data['name']);
            if ($update_filename) {
                $data_file->name = $uploaded_file_data['name'];
            }
            $data_file->store();

            $source->file = $data_file;
            $source->store();
        }

        if ($update_filename) {
            $source->name = $uploaded_file_data['name'];
            if ($source->isDirty()) {
                $source->store();
            }

            //We must find all FileRefs that point to $data_file
            //and change their name, too:

            $other_file_refs = FileRef::findBySql('file_id = :file_id AND id <> :source_id', [
                'file_id' => $source->file_id,
                'source_id' => $source->id
            ]);

            foreach ($other_file_refs as $other_file_ref) {
                $other_file_ref->name = $uploaded_file_data['name'];
                if ($other_file_ref->isDirty()) {
                    $other_file_ref->store();
                }
            }
        }

        // Update author
        FileRef::findEachBySQL(
            function ($ref) use ($user) {
                $ref->user_id = $user->id;
                $ref->store();
            },
            'file_id = :file_id',
            ['file_id' => $source->file_id]
         );

        //Everything went fine: Return the updated $source FileRef:
        return $source;
    }


    /**
     * This method handles editing file reference attributes.
     *
     * Checks that have to be made during the editing of a file reference are placed
     * in this method so that a controller can simply call this method
     * to change attributes of a file reference.
     *
     * At least one of the three parameters name, description and
     * content_terms_of_use_id must be set. Otherwise this method
     * will do nothing.
     *
     * @param FileRef file_ref The file reference that shall be edited.
     * @param User user The user who wishes to edit the file reference.
     * @param string|null name The new name for the file reference
     * @param string|null description The new description for the file reference.
     * @param string|null content_terms_of_use_id The ID of the new ContentTermsOfUse object.
     * @param string|null url The new URL for the file to link to.
     *     This is only regarded if the file_ref points to an URL instead
     *     of a file stored by Stud.IP.
     *
     * @return FileRef|string[] The edited FileRef object on success, string array with error messages on failure.
     */
    public static function editFileRef(
        FileRef $file_ref,
        User $user,
        $name = null,
        $description = null,
        $content_terms_of_use_id = null,
        $url = null
    )
    {
        if (!$name && !$description && !$content_terms_of_use_id) {
            //nothing to do, no errors:
            return $file_ref;
        }

        if (!$file_ref->folder) {
            return [_('Dateireferenz ist keinem Ordner zugeordnet!')];
        }

        $folder_type = $file_ref->folder->getTypedFolder();
        if (!$folder_type) {
            return [_('Ordnertyp konnte nicht ermittelt werden!')];
        }

        if (!$folder_type->isFileEditable($file_ref, $user->id)) {
            return [sprintf(
                _('Ungenügende Berechtigungen zum Bearbeiten der Datei %s!'),
                $file_ref->name
            )];
        }

        // check if name is set and is different from the current name
        // of the file reference:
        if ($name && $name !== $file_ref->name) {
            // name is special: we have to check if files/folders in
            // the file_ref's folder have the same name. If so, we must
            // make it unique.
            $folder = $file_ref->folder;

            if (!$folder) {
                return [sprintf(
                    _('Verzeichnis von Datei %s nicht gefunden!'),
                    $file_ref->name
                )];
            }

            $file_ref->name = $name;
        }

        if ($description !== null) {
            //description may be an empty string which is allowed here
            $file_ref->description = $description;
        }

        if ($content_terms_of_use_id !== null) {
            $content_terms_of_use = ContentTermsOfUse::find($content_terms_of_use_id);
            if (!$content_terms_of_use) {
                return [sprintf(
                    _('Inhalts-Nutzungsbedingungen mit ID %s nicht gefunden!'),
                    $content_terms_of_use_id
                )];
            }

            $file_ref->content_terms_of_use_id = $content_terms_of_use->id;
        }

        if ($file_ref->isLink() && $url !== null) {
            $file_ref->file->setURL($url);
            if ($file_ref->file->isDirty()) {
                $file_ref->file->store();
            }
            if ($file_ref->file->file_url->isDirty()) {
                $file_ref->file->file_url->store();
            }
        }

        if (!$file_ref->isDirty() || $file_ref->store()) {
            //everything went fine
            return $file_ref;
        }

        //error while saving the changes!
        return [sprintf(
            _('Fehler beim Speichern der Änderungen bei Datei %s'),
            $file_ref->name
        )];
    }

    /**
     * This method handles copying a file to a new folder.
     *
     * If the user (given by $user) is the owner of the file (by looking at the user_id
     * in the file reference) we can just make a new reference to that file.
     * Else, we must copy the file and its content.
     *
     * The file name is altered when a file with the identical name exists in
     * the destination folder. In that case, only the name in the FileRef object
     * of the file is altered and the File object's name is unchanged.
     *
     * @param FileRef $source The file reference for the file that shall be copied.
     * @param FolderType $destination_folder The destination folder for the file.
     * @param User $user The user who wishes to copy the file.
     *
     * @return FileRef|string[] The copied FileRef object on success or an array with error messages on failure.
     */
    public static function copyFileRef(FileRef $source, FolderType $destination_folder, User $user)
    {
        // first we have to make sure if the user has the permissions to read the source folder
        // and the permissions to write to the destination folder:

        $source_folder = $source->getFolderType();
        if (!$source_folder) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }

        if (!$source_folder->isReadable($user->id) || !$destination_folder->isWritable($user->id)) {
            //the user is not permitted to read the source folder
            //or to write to the destination folder!
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Kopieren der Datei %s in Ordner %s!'),
                    $source->name,
                    $destination_folder->name
                )
            ];
        }

        $source_plugin = PluginManager::getInstance()->getPlugin($source_folder->range_id);
        if (!$source_plugin) {
            $source_plugin = PluginManager::getInstance()->getPlugin($source_folder->range_type);;
        }
        $destination_plugin = PluginManager::getInstance()->getPlugin($destination_folder->range_id);
        if (!$destination_plugin) {
            $destination_plugin = PluginManager::getInstance()->getPlugin($destination_folder->range_type);;
        }

        if (!$destination_plugin && !$source_plugin) {
            //We copy a file from a Stud.IP file area to another
            //Stud.IP file area. No file system plugin is involved.
            if ($source->user_id === $user->id) {
                //The user is the owner of the file:
                //We can simply make a new reference to it
                $new_reference = new FileRef();
                $new_reference->file_id     = $source->file_id;
                $new_reference->folder_id   = $destination_folder->getId();
                $new_reference->name        = $source->name;
                $new_reference->description = $source->description;
                $new_reference->user_id     = $user->id;
                $new_reference->content_terms_of_use_id = $source->content_terms_of_use_id;

                if ($new_reference->store()) {
                    return $new_reference;
                }
            } else {
                //The user is not the owner of the file:
                //We must also copy the data (the File object).

                $copied_file = new File();
                $copied_file->user_id = $user->id;
                $copied_file->mime_type = $source->file->mime_type;
                $copied_file->name = $source->name;
                $copied_file->size = $source->file->size;
                $copied_file->storage = $source->file->storage;
                $copied_file->author_name = $user->getFullName('no_title');
                $copied_file->id = $copied_file->getNewId();
                if ($copied_file->storage === 'disk') {
                    //We must copy the physical data.
                    $copy_path = $GLOBALS['TMP_PATH'] . '/' . $copied_file->id;
                    if (!copy($source->file->getPath(), $copy_path)) {
                        return [
                            _('Daten konnten nicht kopiert werden!')
                        ];
                    }
                    if ($copied_file->connectWithDataFile($copy_path)) {
                        @unlink($copy_path);
                    } else {
                        return [
                            _('Daten konnten nicht kopiert werden!')
                        ];
                    }
                } else {
                    //We can simply copy the URL field's value.
                    $copied_file->url = $source->file->url;
                }

                if (!$copied_file->store()) {
                    return [
                        _('Daten konnten nicht kopiert werden!')
                    ];
                }

                //We finished copying the real data.
                //Now we must copy the FileRef:
                $copied_reference = new FileRef();
                $copied_reference->file_id = $copied_file->id;
                $copied_reference->folder_id = $destination_folder->getId();
                $copied_reference->name = $source->name;
                $copied_reference->description = $source->description;
                $copied_reference->user_id = $user->id;
                $copied_reference->content_terms_of_use_id = $source->content_terms_of_use_id;

                if ($copied_reference->store()) {
                    return $copied_reference;
                }
            }

            return[_('Neue Referenz kann nicht erzeugt werden!')];

        } else {

            if ($source_plugin) {
                $prepared_file_ref = $source_plugin->getPreparedFile($source->id, true);

                $file_meta = [
                    'name'     => $prepared_file_ref->name,
                    'error'    => [0],
                    'type'     => $prepared_file_ref->mime_type,
                    'tmp_name' => $prepared_file_ref->path_to_blob,
                    'size'     => $prepared_file_ref->size,
                ];
                $result = $destination_folder->createFile($file_meta);

                if (is_a($result, "MessageBox")) {
                    return [$result->message];
                } else {
                    return $result;
                }

            } else {

                if (!$source->path_to_blob && $source->file_id) {
                    $source->path_to_blob = File::find($source->file_id)->getPath();
                }

                $file_meta = [
                    'name'     => $source->name,
                    'error'    => [0],
                    'type'     => $source->mime_type,
                    'tmp_name' => $source->path_to_blob,
                    'url'      => $source->file->url,
                    'size'     => $source->size,
                ];

                if ($source->file->url) {
                    if ($destination_plugin) {
                        $new_reference = $destination_folder->createFile($file_meta);
                    } else {
                        $copied_file = new File();
                        $copied_file->setData($source->file);
                        $copied_file->name    = $source->name;
                        $copied_file->user_id = $user->id;
                        $copied_file->setId($copied_file->getNewId());
                        $copied_file->store();

                        $fileurl = new FileURL();
                        $fileurl->setData($source->file->file_url->toArray());
                        $fileurl['file_id'] = $copied_file->getId();
                        $fileurl->store();

                        $new_reference = new FileRef();
                        $new_reference->file_id     = $copied_file->id;
                        $new_reference->folder_id   = $destination_folder->getId();
                        $new_reference->name        = $source->name;
                        $new_reference->description = $source->description;
                        $new_reference->user_id     = $user->id;
                    }
                } else {
                    $file_meta = [
                        'name'     => [$source->name],
                        'error'    => [0],
                        'type'     => [$source->mime_type],
                        'tmp_name' => [$source->path_to_blob],
                        'url'      => [$source->file->url],
                        'size'     => [$source->size],
                    ];
                    $fcopy = self::handleFileUpload($file_meta, $destination_folder, $user->id);
                    $new_reference = $fcopy['files'][0];
                }

                $new_reference->content_terms_of_use_id = $source->content_terms_of_use_id;

            }
            if ($destination_plugin) {
                return $new_reference;
            }

            if ($new_reference->store()) {
                return $new_reference;
            } else {
                $new_reference->delete();
                return [$error ?: _('Daten konnten nicht kopiert werden!')];
            }
        }

    }

    /**
     * This method handles moving a file to a new folder.
     *
     * @param FileRef $source The file reference for the file that shall be moved.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to move the file.
     *
     * @return FileRef|string[] $source FileRef object on success, Array with error messages on failure.
     */
    public static function moveFileRef(FileRef $source, FolderType $destination_folder, User $user)
    {
        $source_folder = $source->getFolderType();
        if (!$source_folder) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }

        if (!$source_folder->isReadable($user->id) || !$destination_folder->isWritable($user->id)) {
            //the user is not permitted to read the source folder
            //or to write to the destination folder!
            return [
                sprintf(
                    _('Ungenügende Berechtigungen zum Kopieren der Datei %s in Ordner %s!'),
                    $source->name,
                    $destination_folder->name
                )
            ];
        }

        $source_plugin = PluginManager::getInstance()->getPlugin($source_folder->range_id);
        if (!$source_plugin) {
            $source_plugin = PluginManager::getInstance()->getPlugin($source_folder->range_type);;
        }
        $destination_plugin = PluginManager::getInstance()->getPlugin($destination_folder->range_id);
        if (!$destination_plugin) {
            $destination_plugin = PluginManager::getInstance()->getPlugin($destination_folder->range_type);;
        }

        if (!$source_plugin && !$destination_plugin) {

            $source->folder_id = $destination_folder->id;
            if ($source->store()) {
                return $source;
            }
            return [_('Datei konnte nicht gespeichert werden.')];

        } else {
            $copy = self::copyFileRef($source, $destination_folder, $user);
            if (!is_array($copy)) {
                $source_folder->deleteFile($source->getId());
            }
            return $copy;
        }

    }

    /**
     * This method handles deletign a file reference.
     *
     * @param FileRef file_ref The file reference that shall be deleted
     * @param User user The user who wishes to delete the file reference.
     *
     * @return FileRef|string[] The FileRef object that was deleted from the database on success
     * or an array with error messages on failure.
     */
    public static function deleteFileRef(FileRef $file_ref, User $user)
    {
        $folder_type = $file_ref->getFolderType();

        if (!$folder_type) {
            return [_('Ordnertyp des Quellordners konnte nicht ermittelt werden!')];
        }

        if (!$folder_type->isFileWritable($file_ref->id, $user->id)) {
            return [sprintf(
                _('Ungenügende Berechtigungen zum Löschen der Datei %s in Ordner %s!'),
                $file_ref->name
            )];
        }

        if ($folder_type->deleteFile($file_ref->getId())) {
            return $file_ref;
        }

        return [_('Dateireferenz konnte nicht gelöscht werden.')];
    }

    // FOLDER METHODS

    /**
     * Handles the sub folder creation routine.
     *
     * @param FolderType $destination_folder The folder where the subfolder shall be linked.
     * @param User $user The user who wishes to create the subfolder.
     * @param string $folder_type_class_name The FolderType class name for the new folder
     * @param string $name The name for the new folder
     * @param string $description The description of the new folder
     *
     * @returns FolderType|string[] Either the FolderType object of the
     *     new folder or an Array with error messages.
     *
     */
    public static function createSubFolder(
        FolderType $destination_folder,
        User $user,
        $folder_type_class_name = null,
        $name = null,
        $description = null
    )
    {
        $errors = [];

        if (!$folder_type_class_name) {
            // folder_type_class_name is not set: we can't create a folder!
            return [_('Es wurde kein Ordnertyp angegeben!')];
        }

        // check if folder_type_class_name has a valid class:
        if (!is_subclass_of($folder_type_class_name, 'FolderType')) {
            return [sprintf(
                _('Die Klasse %s ist nicht von FolderType abgeleitet!'),
                $folder_type_class_name
            )];
        }

        if (!$name) {
            //name is not set: we can't create a folder!
            return [_('Es wurde kein Ordnername angegeben!')];
        }

        $sub_folder = new Folder();
        $sub_folder_type = new $folder_type_class_name($sub_folder);

        //set name and description of the new folder:
        $sub_folder->name = $name;
        if ($description) {
            $sub_folder->description = $description;
        }

        // check if the sub folder type is creatable in a StandardFolder,
        // if the destination folder is a StandardFolder:
        if (!in_array($folder_type_class_name, ['InboxFolder', 'OutboxFolder'])) {
            if (!$folder_type_class_name::availableInRange($destination_folder->range_id, $user->id))
            {
                $errors[] = sprintf(
                    _('Ein Ordner vom Typ %s kann nicht in einem Ordner vom Typ %s erzeugt werden!'),
                    get_class($sub_folder_type),
                    'StandardFolder'
                );
            }
        }

        if (!$destination_folder->isSubfolderAllowed($user->id)) {
            $errors[] = _('Sie sind nicht dazu berechtigt, einen Unterordner zu erstellen!');
        }

        // we can return here if we have found errors:
        if (!empty($errors)) {
            return $errors;
        }

        // check if all necessary attributes of the sub folder are set
        // and if they aren't set, set them here:

        // special case for inbox and outbox folders: these folder types
        // get a custom ID instead of a generic one, so it has to be set here!
        if ($folder_type_class_name === 'InboxFolder') {
            $sub_folder->id = md5('INBOX_' . $user->id);
        } elseif ($folder_type_class_name === 'OutboxFolder') {
            $sub_folder->id = md5('OUTBOX_' . $user->id);
        }

        $sub_folder->user_id     = $user->id;
        $sub_folder->range_id    = $destination_folder->range_id;
        $sub_folder->parent_id   = $destination_folder->getId();
        $sub_folder->range_type  = $destination_folder->range_type;
        $sub_folder->folder_type = get_class($sub_folder_type);
        $sub_folder->store();

        return $sub_folder_type; //no errors
    }

    /**
     * This method handles copying folders, including
     * copying the subfolders and files recursively.
     *
     * @param FolderType $source_folder The folder that shall be copied.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to copy the folder.
     *
     * @return FolderType|string[] The copy of the source_folder FolderType object on success
     * or an array with error messages on failure.
     */
    public static function copyFolder(FolderType $source_folder, FolderType $destination_folder, User $user)
    {
        $new_folder = null;

        if (!$destination_folder->isWritable($user->id)) {
            return [sprintf(
                _('Unzureichende Berechtigungen zum Kopieren von Ordner %s in Ordner %s!'),
                $source_folder->name,
                $destination_folder->name
            )];
        }

        //we have to check, if the source folder is a folder from a course.
        //If so, then only users with status dozent or tutor (or root) in that course
        //may copy the folder!
        if (!$source_folder->isReadable($user->id)) {
            return [sprintf(
                _('Unzureichende Berechtigungen zum Kopieren von Veranstaltungsordner %s in Ordner %s!'),
                $source_folder->name,
                $destination_folder->name
            )];
        }



        //The user has the permissions to copy the folder.

        //Now we must check if a folder is to be copied inside itself
        //or one of its subfolders. This is not allowed since it
        //leads to infinite recursion.

        $recursion_error = false;

        //First we check if the source folder is the destination folder:
        if ($destination_folder->getId() == $source_folder->getId()) {
            $recursion_error = true;
        }

        //After that we search the hierarchy of the destination folder
        //for the ID of the source folder:
        $parent = $destination_folder->getParent();
        while ($parent) {
            if ($parent->getId() == $source_folder->getId()) {
                $recursion_error = true;
                break;
            }
            $parent = $parent->getParent();
        }

        if ($recursion_error) {
            return [
                _('Ein Ordner kann nicht in sich selbst oder einen seiner Unterordner kopiert werden!')
            ];
        }


        //We must copy the source folder first.
        //The copy must be the same folder type like the destination folder.
        //Therefore we must first get the destination folder's FolderType class.
        $new_folder_class = get_class($source_folder);
        $destination_folder_type = in_array($new_folder_class, self::getAvailableFolderTypes($destination_folder->range_id, $user->id))
            ? $new_folder_class
            : "StandardFolder";
        $new_folder = new $destination_folder_type();
        $new_folder->name = $source_folder->name;
        $new_folder->user_id = $user->id;
        $new_folder = $destination_folder->createSubfolder($new_folder);

        //now we go through all subfolders and copy them:
        foreach ($source_folder->getSubfolders() as $sub_folder) {
            $result = self::copyFolder($sub_folder, $new_folder, $user);
            if (!$result instanceof FolderType) {
                return $result;
            }
        }

        //now go through all files and copy them, too:
        foreach ($source_folder->getFiles() as $file_ref) {
            if (!($file_ref instanceof FileRef)) {
                $file_ref = FileRef::build((array) $file_ref, false);
                $file_ref->setFolderType('foldertype', $source_folder);
            }
            $result = self::copyFileRef($file_ref, $new_folder, $user);
            if (!$result instanceof FileRef) {
                return $result;
            }
        }

        return $new_folder;
    }

    /**
     * This method handles moving folders, including
     * subfolders and files.
     *
     * @param FolderType $source_folder The folder that shall be moved.
     * @param FolderType $destination_folder The destination folder.
     * @param User $user The user who wishes to move the folder.
     *
     * @return FolderType|string[] The moved folder's FolderType object on success
     * or an array with error messages on failure.
     */
    public static function moveFolder(FolderType $source_folder, FolderType $destination_folder, User $user)
    {
        // Leave early, if folder was not actually moved
        if ($source_folder->parent_id === $destination_folder->id) {
            return $source_folder;
        }

        if (!$destination_folder->isWritable($user->id)) {
            return [sprintf(
                _('Unzureichende Berechtigungen zum Verschieben von Ordner %s in Ordner %s!'),
                $source_folder->name,
                $destination_folder->name
            )];
        }

        //Check if the destination folder is a subfolder of the source folder
        //or if destination and source folder are identical:
        $recursion_error = false;

        if ($destination_folder->getId() == $source_folder->getId()) {
            $recursion_error = true;
        }

        $parent = $destination_folder->getParent();
        while ($parent) {
            if ($parent->getId() == $source_folder->getId()) {
                $recursion_error = true;
                break;
            }
            $parent = $parent->getParent();
        }

        if ($recursion_error) {
            return [
                _('Ein Ordner kann nicht in sich selbst oder einen seiner Unterordner verschoben werden!')
            ];
        }

        $new_folder = null;
        if ($source_folder instanceof StandardFolder) {
            //Standard folders just have to be put below the
            //destination folder.
            $new_folder = $destination_folder->createSubfolder($source_folder);

            $array_walker = function ($folder) use (&$array_walker, $destination_folder, $user) {
                $type = get_class($folder);
                if (!$type::availableInRange($destination_folder->range_id, $user->id)) {
                    $folder = new StandardFolder($folder);
                }
                $folder->range_id   = $destination_folder->range_id;
                $folder->range_type = $destination_folder->range_type;
                $folder->store();
                array_walk($folder->getSubFolders(), $array_walker);

            };
            array_walk($new_folder->getSubfolders(), $array_walker);
            return $new_folder;

        } else {
            //It is a plugin folder which needs special treatment.
            $new_folder = $destination_folder->createSubfolder(
                $source_folder
            );
        }
        if (!is_a($new_folder, "FolderType")) {
            return [_('Fehler beim Verschieben des Ordners.')];
        } else {
            //now we go through all subfolders and move them:
            foreach ($source_folder->getSubfolders() as $sub_folder) {
                $result = self::moveFolder($sub_folder, $new_folder, $user);
                if (!$result instanceof FolderType) {
                    //error
                    return $result;
                }
            }

            //now go through all files and move them, too:
            foreach ($source_folder->getFiles() as $file_ref) {
                if (!($file_ref instanceof FileRef)) {
                    $file_ref = FileRef::build((array) $file_ref, false);
                    $file_ref->setFolderType('foldertype', $source_folder);
                }
                $result = self::moveFileRef($file_ref, $new_folder, $user);
                if (!$result instanceof FileRef) {
                    //error
                    return $result;
                }
            }

            $source_folder->delete();
            return $new_folder;
        }
    }

    /**
     * This method helps with deleting a folder.
     *
     * @param FolderType $folder The folder that shall be deleted.
     * @param User $user The user who wishes to delete the folder.
     *
     * @return FolderType|string[] The deleted folder's FolderType object on success
     * or an array with error messages on failure.
     */
    public static function deleteFolder(FolderType $folder, User $user)
    {
        if (!$folder->isEditable($user->id)) {
            return [sprintf(
                    _('Unzureichende Berechtigungen zum Löschen von Ordner %s!'),
                    $folder->name
                )
            ];
        }

        if ($folder->delete()) {
            //everything went fine!
            return $folder;
        }

        //error occured!
        return [sprintf(
            _('Fehler beim Löschvorgang von Ordner %s!'),
            $folder->name
        )];
    }


    /**
     * returns the available folder types,
     * There are several types of folders in Stud.IP. This method returns
     * all available folder types.
     *
     * @return array with strings representing the class names of available folder types.
     *
     */
    public static function getFolderTypes()
    {
        $result = [];
        foreach (scandir(__DIR__) as $filename) {
            $path = pathinfo($filename);
            if ($path['extension'] === 'php') {
                class_exists($path['filename']);
            }
        }
        $result = [];
        foreach (get_declared_classes() as $declared_class) {
            if (!is_a($declared_class, 'FolderType', true)) {
                continue;
            }
            $reflected_ft = new ReflectionClass($declared_class);
            $ft_sorter = $reflected_ft->getStaticPropertyValue('sorter', PHP_INT_MAX);
            if ($ft_sorter == 0 && $declared_class != 'StandardFolder') {
                $ft_sorter = PHP_INT_MAX;
            }
            $result[$declared_class] = $ft_sorter;
        }
        asort($result, SORT_NUMERIC);
        return array_keys($result);
    }

    /**
     * returns the available folder types, for given context and user
     *
     * @param string|SimpleORMap $range_id_or_object
     * @param string $user_id
     * @return array with strings representing the class names of available folder types.
     *
     */
    public static function getAvailableFolderTypes($range_id_or_object, $user_id)
    {
        $result = [];
        foreach (self::getFolderTypes() as $type) {
            if ($type::availableInRange($range_id_or_object, $user_id)) {
                $result[] = $type;
            }
        }
        return $result;
    }

    /**
     * Copies the content of a folder (files and subfolders) into a given
     * path in the operating system's file system.
     *
     * @param FolderType folder The folder whose content shall be copied.
     * @param string path The path in the operating system's file system
     *     where the content shall be copied into.
     * @param string user_id The user who wishes to copy the content.
     * @param string min_perms If set, the selection of subfolders and files
     *     is limited to those which are visible for users having
     *     the minimum permissions.
     * @param bool ignore_perms If set to true, files are copied without checking
     *     the minimum permissions or the permissions of the user given by user_id.
     * @return bool True on success, false on error.
     */
    public static function copyFolderContentIntoPath(
        FolderType $folder,
        $path = null,
        $user_id = 'nobody',
        $min_perms = 'nobody',
        $ignore_perms = false
    )
    {
        if (!$path) {
            return false;
        }

        // loop through all subfolders, create a directory for each subfolder
        // and call this method recursively:
        foreach ($folder->getSubfolders() as $subfolder) {
            if ($subfolder->isReadable($user_id) || $ignore_perms)
            {
                //User has permissions to read the folder or permission checks
                //are ignored.

                $subfolder_path = $path . '/' . $subfolder->name;
                mkdir($subfolder_path, 0700);
                $success = self::copyFolderContentIntoPath(
                    $subfolder,
                    $subfolder_path,
                    $user_id,
                    $min_perms
                );

                if (!$success) {
                    return false;
                }
            }
        }

        // loop through all files and copy them to the folder path:
        foreach ($folder->getFiles() as $file_ref) {
            if ($folder->isFileDownloadable($file_ref, $user_id) || $ignore_perms) {
                //The user (given by user_id) has the required permissions
                //to download the file or the permission checks are
                //ignored.

                $file_path = $path . '/' . $file_ref->name;
                $success = copy($file_ref->file->getPath(), $file_path);
                if (!$success) {
                    return false;
                }
            }
        }

        //Everything went fine.
        return true;
    }

    /**
     * Counts the number of files inside a folder and its subfolders.
     * The search result can be limited to the files belonging to one user
     * and/or to the files which are readable for one user.
     *
     * @param FolderType $folder The folder whose files shall be counted.
     * @param bool $count_subfolders True, if files subfolders shall also
     *     be counted, too (default). False otherwise.
     * @param string $owner_id Optional user-ID to count only files of one
     *     user specified by the ID.
     * @param string $user_id Optional user-ID to count only files the user
     *     (specified by this user-ID) can read.
     *
     * @return int The amount of files inside the folder (and its subfolders).
     */
    public static function countFilesInFolder(
        FolderType $folder,
        $count_subfolders = true,
        $owner_id = null,
        $user_id = null
    )
    {
        $num_files = 0;

        if ($owner_id === null) {
            //If the owner_id is not set we can simply count the number of all files.
            $folder_files = $folder->getFiles();
            if ($user_id === null) {
                //If the user_id is also not set we can simply count all files in this folder:
                $num_files = count($folder_files);
            } else {
                //$user_id is set: We must check for each file if it is readable for the user
                //specified by $user_id:
                if ($folder->isReadable($user_id)) {
                    foreach ($folder_files as $folder_file) {
                        if ($folder->isFileDownloadable($folder_file->id, $user_id)) {
                            $num_files++;
                        }
                    }
                }
            }
        } else {
            //If the owner_id is set we must check who owns the file
            //and count only those files whose user_id matches the owner_id specified.
            foreach ($folder->getFiles() as $file) {
                if ($file->user_id === $owner_id) {
                    if ($user_id) {
                        //user-ID is set: only if the file is downloadable
                        //it will be counted!
                        if ($folder->isFileDownloadable($file->id, $user_id)) {
                            $num_files++;
                        }
                    } else {
                        //No user-ID set: we can count the file
                        $num_files++;
                    }
                }
            }
        }

        if ($count_subfolders) {
            //If files in subfolders shall be counted too,
            //we must call this method recursively.
            foreach ($folder->getSubFolders() as $subfolder) {
                $num_files += self::countFilesInFolder(
                    $subfolder,
                    $count_subfolders,
                    $owner_id,
                    $user_id
                );
            }
        }

        return $num_files;
    }



    /**
     * Creates a list of files and subfolders of a folder.
     *
     * @param FolderType $top_folder The folder whose content shall be retrieved.
     * @param string $user_id The ID of the user who wishes to get all
     *     files and subfolders of a folder.
     * @param bool $check_file_permissions Set to true, if file permissions
     *     shall be checked. Defaults to false.
     * @return mixed[] A mixed array with FolderType and FileRef objects.
     */
    public static function getFolderFilesRecursive(
        FolderType $top_folder,
        $user_id,
        $check_file_permissions = false
    )
    {
        $files = [];
        $folders = [];
        $array_walker = function ($top_folder) use (
            &$array_walker, &$folders, &$files, $user_id, $check_file_permissions
        ) {
            if ($top_folder->isVisible($user_id)) {
                $folders[$top_folder->getId()] = $top_folder;
                if ($top_folder->isReadable($user_id)) {

                    if ($check_file_permissions) {
                        //We must check for each file if it is downloadable for the user
                        //specified by user_id:
                        $top_folder_file_refs = $top_folder->getFiles();
                        foreach ($top_folder_file_refs as $file_ref) {
                            if ($top_folder->isFileDownloadable($file_ref->id, $user_id)) {
                                $files[] = $file_ref;
                            }
                        }
                    } else {
                        $files = array_merge($files, $top_folder->getFiles());
                    }
                    array_walk($top_folder->getSubFolders(), $array_walker);
                }
            }
        };

        $top_folders = [$top_folder];
        array_walk($top_folders, $array_walker);
        return compact('files', 'folders');
    }

    /**
     * Creates a list of readable subfolders of a folder.
     *
     * @param FolderType $top_folder
     * @param string $user_id
     * @return FolderType[] assoc array ID => FolderType
     */
    public static function getReadableFolders(FolderType $top_folder, $user_id)
    {
        $folders = [];
        $array_walker = function ($top_folder) use (&$array_walker, &$folders,$user_id) {
            if ($top_folder->isReadable($user_id)) {
                $folders[$top_folder->getId()] = $top_folder;
                array_walk($top_folder->getSubFolders(), $array_walker);
            }
        };

        $top_folders = [$top_folder];
        array_walk($top_folders, $array_walker);
        return $folders;
    }

    /**
     * Creates a list of unreadable subfolders of a folder.
     * @deprecated use getReadableFolders() instead
     *
     * @param FolderType $top_folder
     * @param string $user_id
     * @return FolderType[] assoc array ID => FolderType
     */
    public static function getUnreadableFolders(FolderType $top_folder, $user_id)
    {
        $folders = [];
        $array_walker = function ($top_folder) use (&$array_walker, &$folders,$user_id) {
            if (!$top_folder->isReadable($user_id)) {
                $folders[$top_folder->getId()] = $top_folder;
            }
            array_walk($top_folder->getSubFolders(), $array_walker);
        };

        $top_folders = [$top_folder];
        array_walk($top_folders, $array_walker);
        return $folders;
    }

    /**
     * Returns a FolderType instance for a given folder-ID.
     * This method can also get FolderType instances which are defined
     * in a file system plugin.
     *
     * @param $id The ID of a Folder object.
     * @param null $pluginclass The name of a Plugin's main class.
     * @return FolderType|null A FolderType object if it can be retrieved
     *     using the Folder-ID (and by option the plugin class name)
     *     or null in case no FolderType object can be created.
     */
    public static function getTypedFolder($id, $pluginclass = null)
    {
        if ($pluginclass === null) {
            $folder = Folder::find($id);
            if ($folder) {
                return $folder->getTypedFolder();
            }
        } else {
            $plugin = PluginManager::getInstance()->getPlugin($pluginclass);
            if ($plugin instanceof FilesystemPlugin) {
                $folder = $plugin->getFolder($id);
                if ($folder instanceof FolderType) {
                    return $folder;
                }
            }
        }
        return null;
    }

    /**
     * Retrieves additional data for an URL by looking at the HTTP header.
     *
     * @param string $url The URL from which additional data shall be fetched.
     * @param int $level The amount of redirects that have already been walked through.
     *     The $level parameter is only useful when this method calls itself recursively.
     *
     * @return array An array with additional data retrieved from the HTTP header.
     */
    public static function fetchURLMetadata($url, $level = 0)
    {
        if ($level > 5) {
            return ['response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400];
        }

        $url_parts = @parse_url($url);
        // filter out localhost and reserved or private IPs
        if (mb_stripos($url_parts['host'], 'localhost') !== false
            || mb_stripos($url_parts['host'], 'loopback') !== false
            || (filter_var($url_parts['host'], FILTER_VALIDATE_IP) !== false
                && (mb_strpos($url_parts['host'], '127') === 0
                    || filter_var(
                        $url_parts['host'],
                        FILTER_VALIDATE_IP,
                        FILTER_FLAG_IPV4
                        | FILTER_FLAG_NO_PRIV_RANGE
                        | FILTER_FLAG_NO_RES_RANGE
                    ) === false)
               )
        ) {
            return ['response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400];
        }

        // URL links to an ftp server
        if ($url_parts['scheme'] === 'ftp') {
            if (preg_match('/[^a-z0-9_.-]/i', $url_parts['host'])) { // exists umlauts ?
                $IDN = new idna_convert();
                $out = $IDN->encode($url_parts['host']); // false by error
                $url_parts['host'] = $out ?: $url_parts['host'];
            }

            $ftp = @ftp_connect($url_parts['host'],$url_parts['port'] ?: 21, 10);
            if (!$ftp) {
                return ['response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502];
            }
            if (!$url_parts['user']) {
                $url_parts['user'] = 'anonymous';
            }
            if (!$url_parts['pass']) {
                $mailclass = new StudipMail();
                $url_parts['pass'] = $mailclass->getSenderEmail();
            }
            if (!@ftp_login($ftp, $url_parts["user"], $url_parts["pass"])) {
                ftp_quit($ftp);
                return ['response' => 'HTTP/1.0 403 Forbidden', 'response_code' => 403];
            }
            $parsed_link['Content-Length'] = ftp_size($ftp, $url_parts['path']);
            ftp_quit($ftp);
            if ($parsed_link['Content-Length'] != -1) {
                $parsed_link['HTTP/1.0 200 OK'] = 'HTTP/1.0 200 OK';
                $parsed_link['response_code'] = 200;
            } else {
                return ['response' => 'HTTP/1.0 404 Not Found', 'response_code' => 404];
            }
            $parsed_link['filename']     = basename($url_parts['path']);
            $parsed_link['Content-Type'] = get_mime_type($parsed_link['filename']);
            return $parsed_link;
        }

        // "Normal" url
        if (!empty($url_parts['path'])) {
            $documentpath = $url_parts['path'];
        } else {
            $documentpath = '/';
        }
        if (!empty($url_parts['query'])) {
            $documentpath .= '?' . $url_parts['query'];
        }
        $host = $url_parts['host'];
        $port = $url_parts['port'];
        $scheme = mb_strtolower($url_parts['scheme']);
        if (!in_array($scheme, ['http', 'https'])) {
            return ['response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400];
        }
        if ($scheme === 'https') {
            $ssl = true;
            if (empty($port)) {
                $port = 443;
            }
        } else {
            $ssl = false;
        }
        if (empty($port)) {
            $port = 80;
        }
        if (preg_match('/[^a-z0-9_.-]/i', $host)) { // exists umlauts ?
            $IDN = new idna_convert();
            $out = $IDN->encode($host); // false by error
            $host = $out ?: $host;
        }
        $socket = @fsockopen(($ssl ? 'ssl://' : '') . $host, $port, $errno, $errstr, 10);
        if (!$socket) {
            return ['response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502];
        }

        $urlString = "GET {$documentpath} HTTP/1.0\r\nHost: {$host}\r\n";
        if ($url_parts['user'] && $url_parts['pass']) {
            $pass = $url_parts['pass'];
            $user = $url_parts['user'];
            $urlString .= "Authorization: Basic " . base64_encode("{$user}:{$pass}") . "\r\n";
        }
        $urlString .= sprintf("User-Agent: Stud.IP v%s File Crawler\r\n", $GLOBALS['SOFTWARE_VERSION']);
        $urlString .= "Connection: close\r\n\r\n";
        fputs($socket, $urlString);
        stream_set_timeout($socket, 5);
        $response = '';
        do {
            $response .= fgets($socket, 128);
            $info = stream_get_meta_data($socket);
        } while (!feof($socket) && !$info['timed_out'] && mb_strlen($response) < 1024);
        fclose($socket);

        $raw_header = explode("\n", trim($response));
        if (!preg_match("~^HTTP/[^\s]*\s(.*?)\s~", $raw_header[0], $status)) {
            return ['response' => 'HTTP/1.0 502 Bad Gateway', 'response_code' => 502];
        }

        $header = [
            'response_code' => (int)$status[1],
            'response'      => trim($raw_header[0]),
        ];

        for ($i = 0; $i < count($raw_header); $i += 1) {
            $parts = null;
            if (!trim($raw_header[$i])) {
                break;
            }
            $matches = preg_match('/^\S+:/', $raw_header[$i], $parts);
            if ($matches){
                $key   = trim(mb_substr($parts[0],0,-1));
                $value = trim(mb_substr($raw_header[$i], mb_strlen($parts[0])));
                $header[$key] = $value;
            } else {
                $header[trim($raw_header[$i])] = trim($raw_header[$i]);
            }
        }

        // Anderer Dateiname?
        $disposition_header = $header['Content-Disposition'] ?: $header['content-disposition'];
        if ($disposition_header) {
            $header_parts = explode(';', $disposition_header);
            foreach ($header_parts as $part) {
                $part = trim($part);
                list($key, $value) = explode('=', $part, 2);
                if (mb_strtolower($key) === 'filename') {
                    $header['filename'] = trim($value, '"');
                }
            }
        } else {
            $header['filename'] = basename($url_parts['path']);
        }

        // Weg über einen Locationheader:
        $location_header = $header['Location'] ?: $header['location'];
        if (in_array($header['response_code'], [300, 301, 302, 303, 305, 307]) && $location_header) {
            if (mb_strpos($location_header, 'http') !== 0) {
                $location_header = $url_parts['scheme'] . '://' . $url_parts['host'] . '/' . $location_header;
            }
            $header = self::fetchURLMetadata($location_header, $level + 1);
        }
        return $header;
    }

    /**
     * Returns an INBOX folder for the given user.
     *
     * @param User user The user whose inbox folder is requested.
     * @return FolderType|null Returns the inbox folder on success, null on failure.
     */
    public static function getInboxFolder(User $user)
    {
        $top_folder = Folder::findTopFolder($user->id, 'user');
        if (!$top_folder) {
            return null;
        }

        $top_folder = $top_folder->getTypedFolder();
        if (!$top_folder) {
            return null;
        }

        $inbox_folder = Folder::find(md5('INBOX_' . $user->id));

        if (!$inbox_folder) {
            //inbox folder doesn't exist: create it, if necessary.
            //We need an inbox folder if there is at least one received
            //message with at least one attachment.

            $inbox_folder = FileManager::createSubFolder(
                $top_folder,
                $user,
                'InboxFolder',
                'Inbox',
                InboxFolder::getTypeName()
            );

            if ($inbox_folder instanceof InboxFolder) {
                return $inbox_folder;
            }

            return null;
        }

        return $inbox_folder->getTypedFolder();
    }

    /**
     * Returns a FolderType object for the outbox folder of the given user.
     *
     * @param User user The user whose outbox folder is requested.
     * @return FolderType|null Returns the inbox folder on success, null on failure.
     */
    public static function getOutboxFolder(User $user)
    {
        $top_folder = Folder::findTopFolder($user->id, 'user');
        if (!$top_folder) {
            return null;
        }

        $top_folder = $top_folder->getTypedFolder();
        if (!$top_folder) {
            return null;
        }

        $outbox_folder = Folder::find(md5('OUTBOX_' . $user->id));

        if (!$outbox_folder) {
            //inbox folder doesn't exist: create it, if necessary.
            //We need an inbox folder if there is at least one received
            //message with at least one attachment.

            $outbox_folder = FileManager::createSubFolder(
                $top_folder,
                $user,
                'OutboxFolder',
                'Outbox',
                OutboxFolder::getTypeName()
            );

            if ($outbox_folder instanceof OutboxFolder) {
                return $outbox_folder;
            }

            return null;
        }

        return $outbox_folder->getTypedFolder();
    }

    /**
     * returns config array for upload types and sizes
     *
     * @param $range_id string id of Course Institute User
     * @param null $user_id string
     * @return array
     */
    public static function getUploadTypeConfig($range_id, $user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = $GLOBALS['user']->id;
        }
        $range_object = get_object_by_range_id($range_id);
        $active_upload_type = null;
        if ($range_object instanceof Course) {
            $status = $GLOBALS['perm']->get_studip_perm($range_id, $user_id);
            $active_upload_type = $range_object->status;
        } elseif ($range_object instanceof Institute) {
            $status = $GLOBALS['perm']->get_studip_perm($range_id, $user_id);
            $active_upload_type = 'institute';
        } else {
            $status = $GLOBALS['perm']->get_perm($user_id);
            $active_upload_type = "personalfiles";
        }

        if (!isset($GLOBALS['UPLOAD_TYPES'][$active_upload_type])) {
            $active_upload_type = 'default';
        }

        $upload_type = $GLOBALS['UPLOAD_TYPES'][$active_upload_type];
        return [
            'type' => $upload_type['type'],
            'file_types' => $upload_type['file_types'],
            'file_size' => $upload_type['file_sizes'][$status]
        ];
    }

    /**
     * Create URL to a folder
     * @param FolderType $folder  the folder
     * @param bool $include_root
     * @return array array of FolderType
     */
    public static function getFullPath(FolderType $folder, $include_root = true)
    {
        $path = [$folder->getId() => $folder];
        $current = $folder;
        while ($current = $current->getParent()) {
            if ($current instanceof RootFolder && !$include_root) continue;
            $path[$current->getId()] = $current;
        }
        return array_reverse($path, true);
    }

    /**
     * Create URL to a folder
     * @param FolderType|Folder $folder  the folder
     * @return string URL to the folder's range
     */
    public static function getFolderURL($folder)
    {
        if (!$folder->range_type) {
            return null;
        }

        switch ($folder->range_type) {
            case 'course':
                $url = URLHelper::getURL(
                    'dispatch.php/course/files/index/'.$folder->id,
                    ['cid' => $folder->range_id]
                );
                break;

            case 'institute':
                $url = URLHelper::getURL(
                    'dispatch.php/institute/files/index/'.$folder->id,
                    ['cid' => $folder->range_id]
                );
                break;

            case 'message':
                $url = URLHelper::getURL('dispatch.php/messages/overview/'.$folder->range_id,
                    ['cid' => null]);
                break;

            case 'user':
                $url = URLHelper::getURL('dispatch.php/files/index/'.$folder->id,
                    ['cid' => null]);
                break;

            default:
                $url = URLHelper::getURL(
                    'dispatch.php/files/system/'.$folder->range_type.'/'.$folder->id,
                    ['cid' => null]
                );
        }
        return $url;
    }

    /**
     * Create link to a folder
     * @param FolderType|Folder $folder  the folder
     * @return string link to the folder's range
     */
    public static function getFolderLink($folder)
    {
        return htmlReady(self::getFolderURL($folder));
    }
}
