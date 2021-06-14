<?php
/**
 * file.php - controller to display files in a course
 *
 * This controller contains actions related to single files.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.0
 */
class FileController extends AuthenticatedController
{
    protected $allow_nobody = true;

    function validate_args(&$args, $types = NULL)
    {
        reset($args);
    }

    /**
     * This is a helper method that decides where a redirect shall be made
     * in case of error or success after an action was executed.
     */
    public function redirectToFolder($folder)
    {
        switch ($folder->range_type) {
            case 'course':
            case 'institute':
                $this->relocate($folder->range_type . '/files/index/' . $folder->getId(), ['cid' => $folder->range_id]);
               break;
            case 'user':
                $this->relocate('files/index/' . $folder->getId(), ['cid' => null]);
                break;
            case 'Resource':
                $this->relocate(
                    'resources/resource/files/'
                  . $folder->range_id . '/'
                  . $folder->getId()
                );
                break;
            default:
                //Plugins should not be available in the flat view.
                $this->relocate('files/system/' . $folder->range_type . '/' . $folder->getId(), ['cid' => null]);
                break;
        }
    }


    public function upload_window_action()
    {
        // just send the template
    }

    public function upload_action($folder_id)
    {
        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/upload/") + strlen("dispatch.php/file/upload/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $folder = $plugin->getFolder($folder_id);
        } else {
            $folder = FileManager::getTypedFolder($folder_id);
        }

        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));

        if (!$folder || !$folder->isWritable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            if (is_array($_FILES['file'])) {
                $validatedFiles = FileManager::handleFileUpload(
                    $_FILES['file'],
                    $folder,
                    $GLOBALS['user']->id
                );

                if (count($validatedFiles['error']) > 0) {
                    $this->response->add_header(
                        'X-Filesystem-Changes',
                        json_encode(['message' => null])
                    );
                    // error during upload: display error message:
                    $this->render_json([
                        'message' => (string) MessageBox::error(
                            _('Beim Hochladen ist ein Fehler aufgetreten '),
                            array_map('htmlready', $validatedFiles['error'])
                        ),
                    ]);

                    return;
                }

                //all files were uploaded successfully:
                if (count($validatedFiles['files']) > 0) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Es wurden %s Dateien hochgeladen'),
                            count($validatedFiles['files'])
                        ),
                        array_map(function ($file) {
                            return htmlReady($file->getFilename());
                        }, $validatedFiles['files']),
                        true
                    );
                }
            } else {
                $this->response->add_header('X-Filesystem-Changes', json_encode(['message' => null]));
                $this->render_json([
                    'message' => (string) MessageBox::error(
                        _('Ein Systemfehler ist beim Upload aufgetreten.')
                    )
                ]);
                return;
            }

            if (Request::isXhr()) {
                $changes = ['added_files' => null];
                $output  = ['added_files' => []];

                if (count($validatedFiles['files']) === 1
                    && strtolower(substr($validatedFiles['files'][0]->getFilename(), -4)) === '.zip'
                    && ($folder->range_id === $GLOBALS['user']->id || Seminar_Perm::get()->have_studip_perm('tutor', $folder->range_id)))
                {
                    $ref_ids = [];
                    foreach ($validatedFiles['files'] as $file) {
                        $ref_ids[] = $file->getId();
                    }
                    $changes['redirect'] = $this->url_for('file/unzipquestion', [
                        'file_refs' => $ref_ids
                    ]);
                } elseif (in_array($folder->range_type, ['course', 'institute', 'user'])) {
                    $ref_ids = [];
                    foreach ($validatedFiles['files'] as $file) {
                        $ref_ids[] = $file->getId();
                    }
                    $changes['redirect'] = $this->url_for("file/edit_license/{$folder->getId()}", [
                        'file_refs' => $ref_ids,
                    ]);
                } else {
                    $changes['close_dialog'] = true;
                }

                $this->current_folder = $folder;
                foreach ($validatedFiles['files'] as $file) {
                    $output['added_files'][] = FilesystemVueDataManager::getFileVueData($file, $folder);
                }

                $this->response->add_header(
                    'X-Filesystem-Changes',
                    json_encode($changes)
                );
                $this->render_json($output);
            }
        }

        $this->folder_id = $folder_id;
    }


    public function unzipquestion_action()
    {
        $this->file_refs      = FileRef::findMany(Request::getArray('file_refs'));
        $this->file_ref       = $this->file_refs[0];
        $this->current_folder = $this->file_ref->folder->getTypedFolder();

        if (Request::isPost()) {
            $changes = [];

            if (Request::submitted('unzip')) {
                //unzip!
                $this->file_refs = FileArchiveManager::extractArchiveFileToFolder(
                    $this->file_ref,
                    $this->current_folder,
                    $GLOBALS['user']->id
                );

                $ref_ids = [];

                foreach ($this->file_refs as $file_ref) {
                    $ref_ids[] = $file_ref->id;
                }

                //Delete the original zip file:
                $changes['removed_files'] = [$this->file_ref->id];
                $this->file_ref->delete();
            } else {
                $ref_ids = [$this->file_ref->getId()];
            }

            $this->flash->set('file_refs', $ref_ids);

            if (Request::isXhr()) {
                $topFolder = null;

                $changes['redirect'] = $this->url_for("file/edit_license/{$this->current_folder->getId()}");
                $changes['added_files'] = null;
                $changes['added_folders'] = null;

                $payload = [
                    'add_files'   => [],
                    'add_folders' => [],
                ];
                $added_folders = [];
                foreach ($this->file_refs as $fileref) {
                    if ($fileref->folder->id === $this->current_folder->id) {
                        $payload['added_files'][] = FilesystemVueDataManager::getFileVueData($fileref->getFileType(), $this->current_folder);
                    } elseif (
                        $fileref->folder->parentfolder->id === $this->current_folder->id
                        && !in_array($fileref->folder->id, $added_folders)
                    ) {
                        if ($topFolder === null) {
                            $topFolder = $this->current_folder;
                            while ($topFolder->parentfolder !== null) {
                                $topFolder = $topFolder->parentFolder;
                            }
                        }

                        $payload['added_folders'][] = FilesystemVueDataManager::getFolderVueData($fileref->getFolderType(), $this->current_folder);

                        $added_folders[] = $fileref->folder->id;
                    }
                }

                $this->response->add_header(
                    'X-Filesystem-Changes',
                    json_encode($changes)
                );
                $this->render_json($payload);
            } else {
                $this->redirect("file/edit_license/{$this->current_folder->getId()}");
            }
        }
    }

    /**
     * Displays details about a file or a folder.
     *
     * @param string $file_area_object_id A file area object like a Folder or a FileRef.
     */
    public function details_action($file_area_object_id = null)
    {
        $this->include_navigation = Request::get('file_navigation', false);
        //check if the file area object is a FileRef:
        if (Request::get("from_plugin")) {
            $file_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/details/") + strlen("dispatch.php/file/details/"));
            if (strpos($file_id, "?") !== false) {
                $file_id = substr($file_id, 0, strpos($file_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $this->file = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");
        } else {
            $file_ref = FileRef::find($file_area_object_id);
            if ($file_ref) {
                $this->file = $file_ref->getFileType();
            }
        }

        if ($this->file) {
            //file system object is a FileRef
            PageLayout::setTitle($this->file->getFilename());

            //Check if file is downloadable for the current user:
            $this->show_preview    = false;
            $this->is_downloadable = false;

            $this->is_standard_file = get_class($this->file) === StandardFile::class;

            // NOTE: The following can only work properly for folders which are
            // stored in the database, since remote folders
            // (for example owncloud/nextcloud folders) are not stored in the database.
            $folder = $this->file->getFolderType();
            if (!$folder->isVisible(User::findCurrent()->id)) {
                throw new AccessDeniedException();
            }
            $this->is_downloadable = $this->file->isDownloadable(User::findCurrent()->id);
            $this->is_editable     = $this->file->isEditable(User::findCurrent()->id);
            $this->file_info_template = $this->file->getInfoTemplate($this->is_downloadable);

            //load the previous and next file in the folder,
            //if the folder is of type FolderType.
            $this->previous_file_ref_id = false;
            $this->next_file_ref_id     = false;
            if ($this->include_navigation && $folder->isReadable(User::findCurrent()->id)) {
                $current_file_ref_id = null;
                foreach ($folder->getFiles() as $folder_file) {
                    $last_file_ref_id = $current_file_ref_id;
                    $current_file_ref_id = $folder_file->getId();

                    if ($folder_file->getId() === $this->file->getId()) {
                        $this->previous_file_ref_id = $last_file_ref_id;
                    }

                    if ($last_file_ref_id === $this->file->getId()) {
                        $this->next_file_ref_id = $folder_file->getId();
                        //at this point we have the ID of the previous
                        //and the next file ref so that we can exit
                        //the foreach loop:
                        break;
                    }
                }
            }
            $this->fullpath = FileManager::getFullPath($folder);

            $this->render_action('file_details');
        } else {
            //file area object is not a FileRef: maybe it's a folder:
            if (Request::get("from_plugin")) {
                $this->folder = $plugin->getFolder($file_id);
            } else {
                $this->folder = FileManager::getTypedFolder($file_area_object_id);
            }
            if (!$this->folder || !$this->folder->isVisible($GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }

            //The file system object is a folder.
            //Calculate the files and the folder size:
            $this->folder_size = 0;
            $this->folder_file_amount = 0;
            foreach ($this->folder->getFiles() as $file) {
                $this->folder_file_amount++;
                $this->folder_size += $file->getSize();
            }
            PageLayout::setTitle($this->folder->name);
            $this->render_action('folder_details');
        }
    }

    /**
     * The action for editing a file reference.
     */
    public function edit_action($file_ref_id)
    {
        if (Request::get("from_plugin")) {
            $file_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/edit/") + strlen("dispatch.php/file/edit/"));
            if (strpos($file_id, "?") !== false) {
                $file_id = substr($file_id, 0, strpos($file_id, "?"));
            }
            $file_ref_id = $file_id;
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }

            $this->file = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");

        } else {
            $this->file_ref = FileRef::find($file_ref_id);
            $this->file = $this->file_ref->getFileType();
        }

        $this->folder = $this->file->getFoldertype();

        if (!$this->folder || !$this->folder->isFileEditable($this->file->getId(), $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->content_terms_of_use_entries = ContentTermsOfUse::findAll();
        $this->show_force_button = false;

        if (Request::isPost()) {
            //form was sent
            CSRFProtection::verifyUnsafeRequest();
            $this->errors = [];

            $force_save = Request::submitted('force_save');
            $this->name = trim(Request::get('name'));
            $this->description = Request::get('description');
            $this->content_terms_of_use_id = Request::get('content_terms_of_use_id');

            //Check if the FileRef is unmodified:
            if (($this->name == $this->file_ref->name) &&
                ($this->description == $this->file_ref->description) &&
                ($this->content_terms_of_use_id == $this->file_ref->content_terms_of_use_id)) {
                $this->redirectToFolder($this->folder);
                return;
            }
            //Check if the file extension has changed:
            $old_file_extension = pathinfo($this->file_ref->name, PATHINFO_EXTENSION);
            $new_file_extension = pathinfo($this->name, PATHINFO_EXTENSION);
            if ($old_file_extension !== $new_file_extension && !$force_save) {
                if (!$new_file_extension) {
                    PageLayout::postWarning(
                        sprintf(
                            _('Die Dateiendung "%1$s" wird entfernt. Soll die Datei trotzdem gespeichert werden?'),
                            htmlReady($old_file_extension)
                        )
                    );
                } elseif (!$old_file_extension) {
                    PageLayout::postWarning(
                        sprintf(
                            _('Die Dateiendung wird auf "%1$s" gesetzt. Soll die Datei trotzdem gespeichert werden?'),
                            htmlReady($new_file_extension)
                        )
                    );
                } else {
                    PageLayout::postWarning(
                        sprintf(
                            _('Die Dateiendung wird von "%1$s" auf "%2$s" geändert. Soll die Datei trotzdem gespeichert werden?'),
                            htmlReady($old_file_extension),
                            htmlReady($new_file_extension)
                        )
                    );
                }
                $this->show_force_button = true;
                return;
            }

            if (Request::get("from_plugin")) {
                $result = $this->folder->editFile(
                    $file_ref_id,
                    $this->name,
                    $this->description,
                    $this->content_terms_of_use_id
                );
            } else {
                $result = FileManager::editFileRef(
                    $this->file_ref,
                    User::findCurrent(),
                    $this->name,
                    $this->description,
                    $this->content_terms_of_use_id
                );
            }

            if (!$result instanceof FileRef) {
                $this->errors = array_merge($this->errors, $result);
            }


            if ($this->errors) {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Ändern der Datei %s!'),
                        htmlReady($this->file_ref->name)
                    ),
                    $this->errors
                );
            } else {
                PageLayout::postSuccess(_('Änderungen gespeichert!'));
                $this->redirectToFolder($this->folder);
            }
        }

        $this->name = $this->file->getFilename();
        $this->description = $this->file->getDescription();
        $this->content_terms_of_use = $this->file->getTermsOfUse();
    }

    /**
     * The action for sharing a file on the oer campus
     */
    public function share_oer_action($file_ref_id)
    {
        $this->file_ref = FileRef::find($file_ref_id);
        $this->file = $this->file_ref->getFileType();

        $this->folder = $this->file->getFoldertype();

        if (!$this->folder || !$this->folder->isFileEditable($this->file->getId(), $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $_SESSION['NEW_OER'] = [
            'name' => $this->file->getFilename(),
            'filename' => $this->file->getFilename(),
            'description' => $this->file->getDescription(),
            'player_url' => null,
            'tags' => [],
            'tmp_name' => $this->file->getPath(),
            'content_type' => $this->file->getMimeType(),
            'image_tmp_name' => null
        ];

        $this->redirect("oer/mymaterial/edit");
    }

    public function edit_urlfile_action($file_ref_id)
    {
        $this->file_ref = FileRef::find($file_ref_id);
        $this->file = $this->file_ref->getFileType();
        $this->folder = $this->file_ref->foldertype;

        if (!$this->folder || !$this->folder->isFileEditable($this->file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->content_terms_of_use_entries = ContentTermsOfUse::findAll();
        $this->show_force_button = false;

        if (Request::isPost()) {
            //form was sent
            CSRFProtection::verifyUnsafeRequest();
            $this->file_ref['name'] = trim(Request::get('name'));
            $this->file_ref['description'] = trim(Request::get('description'));
            $this->file_ref['content_terms_of_use_id'] = Request::get('content_terms_of_use_id');
            $this->file_ref->file['metadata']['url'] = Request::get('url');
            $this->file_ref->file['metadata']['access_type'] = Request::get('access_type');
            $this->file_ref->file->store();
            $this->file_ref->store();

            PageLayout::postSuccess(_('Änderungen gespeichert!'));
            $this->redirectToFolder($this->folder);
        }

        $this->name = $this->file_ref->name;
        $this->url = $this->file_ref->file['metadata']['url'];
        $this->description = $this->file_ref->description;
        $this->content_terms_of_use_id = $this->file_ref->content_terms_of_use_id;
    }

    /**
     * This action is responsible for updating a file reference.
     */
    public function update_action($file_ref_id)
    {
        if (Request::get("from_plugin")) {
            $file_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/update/") + strlen("dispatch.php/file/update/"));
            if (strpos($file_id, "?") !== false) {
                $file_id = substr($file_id, 0, strpos($file_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $this->file = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");
        } else {
            $this->file_ref = FileRef::find($file_ref_id);
            $this->file = $this->file_ref->getFileType();
        }
        $this->folder = $this->file->getFolderType();

        if (!$this->file->isEditable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->errors = [];

        if (Request::submitted('confirm')) {
            $update_filename = (bool) Request::get('update_filename', false);
            $update_all_instances = (bool) Request::get('update_all_instances', false);
            CSRFProtection::verifyUnsafeRequest();

            //Form was sent
            if (Request::isPost() && is_array($_FILES['file'])) {

                if ($this->file_ref) {
                    $result = FileManager::updateFileRef(
                        $this->file_ref,
                        User::findCurrent(),
                        $_FILES['file'],
                        $update_filename,
                        $update_all_instances
                    );
                } else {

                }

                if (!$result instanceof FileRef) {
                    $this->errors = array_merge($this->errors, $result);
                }

            } else {
                $this->errors[] = _('Es wurde keine neue Dateiversion gewählt!');
            }

            if ($this->errors) {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Aktualisieren der Datei %s!'),
                        htmlReady($this->file_ref->name)
                    ),
                    $this->errors
                );
            } else {
                PageLayout::postSuccess(
                    sprintf(
                        _('Datei %s wurde aktualisiert!'),
                        htmlReady($this->file_ref->name)
                    )
                );
            }
            $this->redirectToFolder($this->folder);
        }
    }

    public function choose_destination_action($copymode, $fileref_id = null)
    {
        PageLayout::setTitle(_('Ziel wählen'));

        if (empty($fileref_id)) {
            $fileref_id = Request::getArray('fileref_id');
        } elseif ($fileref_id === 'bulk') {
            $fileref_id = Request::optionArray('ids');
        }
        $this->copymode = $copymode;
        $this->fileref_id = $fileref_id;

        if (Request::get("from_plugin")) {

            if (is_array($fileref_id)) {
                $file_id = $fileref_id[0];
            } else {
                $file_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/choose_destination/".$copymode."/") + strlen("dispatch.php/file/choose_destination/".$copymode."/"));
                if (strpos($file_id, "?") !== false) {
                    $file_id = substr($file_id, 0, strpos($file_id, "?"));
                }
                $fileref_id = [$file_id];
            }
            $file_id = $fileref_id[0];
            $this->fileref_id = $fileref_id;

            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }

            if (!Request::get("isfolder")) {
                $this->file_ref = $plugin->getPreparedFile($file_id);
            } else {
                $this->parent_folder = $plugin->getFolder($file_id);
            }
        } else {

            if (is_array($fileref_id)) {
                $this->file_ref = FileRef::find($fileref_id[0]);
            } else {
                $this->file_ref = FileRef::find($fileref_id);

                $this->fileref_id = [$fileref_id];
            }
        }

        if ($this->file_ref && Request::submitted("from_plugin")) {
            $this->parent_folder = $this->file_ref->getFoldertype();
        } elseif ($this->file_ref) {
            $this->parent_folder = Folder::find($this->file_ref->folder_id);
            $this->parent_folder = $this->parent_folder->getTypedFolder();
        } elseif (!Request::submitted("from_plugin")) {
            $folder = Folder::find(is_array($fileref_id) ? $fileref_id[0] : $fileref_id);
            if ($folder) {
                $this->parent_folder = Folder::find($folder->parent_id);
                $this->parent_folder = $this->parent_folder->getTypedFolder();
            }
        } elseif (!$this->parent_folder) {
            throw new AccessDeniedException();
        }

        $this->plugin = Request::get('from_plugin');
    }


    public function download_folder_action($folder_id)
    {
        $user = User::findCurrent();

        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/download_folder/") + strlen("dispatch.php/file/download_folder/"));

            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $foldertype = $plugin->getFolder($folder_id);

        } else {
            $folder = Folder::find($folder_id);
            if ($folder) {
                $foldertype = $folder->getTypedFolder();
            }
        }
        if ($foldertype) {
            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');

            $result = FileArchiveManager::createArchive(
                [$foldertype],
                $user->id,
                $tmp_file,
                true,
                true,
                false,
                'UTF-8',
                true
            );

            if ($result) {
                $filename = $folder ? $folder->name : basename($tmp_file);

                //ZIP file was created successfully
                $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                    basename($tmp_file),
                    FileManager::cleanFileName("{$filename}.zip")
                ));
            } else {
                throw new Exception('Error while creating ZIP archive!');
            }
        } else {
            throw new Exception('Folder not found in database!');
        }
    }

    public function choose_folder_from_course_action()
    {
        PageLayout::setTitle(_('Zielordner von Veranstaltung wählen'));

        if (Request::get('course_id')) {
            $folder = Folder::findTopFolder(Request::get("course_id"));
            $this->redirect($this->url_for(
                'file/choose_folder/' . $folder->getId(), [
                    'from_plugin'  => Request::get('from_plugin'),
                    'fileref_id' => Request::getArray('fileref_id'),
                    'copymode'   => Request::get('copymode'),
                    'isfolder'   => Request::get('isfolder')
                ]
            ));
            return;
        }

        $this->plugin = Request::get('from_plugin');
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $query = "SELECT seminare.*, COUNT(semester_courses.semester_id) AS semesters
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id
                      GROUP BY seminare.Seminar_id
                      ";
            if (Config::get()->DEPUTIES_ENABLE) {
                $query .= " UNION
                    SELECT `seminare`.*, COUNT(semester_courses.semester_id) AS semesters
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
                    WHERE `deputies`.`user_id` = :user_id
                    GROUP BY seminare.Seminar_id";
            }
            $query .= " ORDER BY semesters = 0 DESC, start_time DESC, Name ASC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([':user_id' => $GLOBALS['user']->id]);
            $this->courses = [];

            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }
    }

    public function choose_folder_from_institute_action()
    {
        PageLayout::setTitle(_('Zielordner von Einrichtung wählen'));

        if (Request::get('Institut_id')) {
            $folder = Folder::findTopFolder(Request::get("Institut_id"));
            $this->redirect($this->url_for(
                'file/choose_folder/' . $folder->getId(), [
                    'from_plugin'  => Request::get('from_plugin'),
                    'fileref_id' => Request::getArray('fileref_id'),
                    'copymode'   => Request::get('copymode'),
                    'isfolder'   => Request::get('isfolder'),
                ]
            ));
            return;
        }

        if ($GLOBALS['perm']->have_perm('root')) {
            $sql = "SELECT DISTINCT Institute.Institut_id, Institute.Name
                    FROM Institute
                    LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id)
                    WHERE Institute.Name LIKE :input
                       OR Institute.Strasse LIKE :input
                       OR Institute.email LIKE :input
                       OR range_tree.name LIKE :input
                    ORDER BY Institute.Name";
        } else {
            $quoted_user_id = DBManager::get()->quote($GLOBALS['user']->id);
            $sql = "SELECT DISTINCT Institute.Institut_id, Institute.Name
                    FROM Institute
                    LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id)
                    LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id)
                    WHERE user_inst.user_id = {$quoted_user_id}
                      AND (
                          Institute.Name LIKE :input
                          OR Institute.Strasse LIKE :input
                          OR Institute.email LIKE :input
                          OR range_tree.name LIKE :input
                      )
                    ORDER BY Institute.Name";
        }

        $this->instsearch = SQLSearch::get($sql, _('Einrichtung suchen'), 'Institut_id');
        $this->plugin = Request::get('from_plugin');
    }

    public function choose_folder_action($folder_id = null)
    {
        PageLayout::setTitle(_('Zielordner wählen'));

        if (Request::isPost()) {
            //copy
            if (Request::get('to_plugin')) {
                $plugin = PluginManager::getInstance()->getPlugin(Request::get('to_plugin'));
                //$file = $plugin->getPreparedFile(Request::get("file_id"));
            } else {
                $folder = new Folder($folder_id);
                $this->to_folder_type = new StandardFolder($folder);
            }
        }

        if (Request::get('to_plugin')) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/choose_folder") + strlen("dispatch.php/file/choose_folder"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            if ($folder_id[0] === "/") {
                $folder_id = substr($folder_id, 1);
            }

            $this->filesystemplugin = PluginManager::getInstance()->getPlugin(Request::get('to_plugin'));
            if (Request::get('search') && $this->filesystemplugin->hasSearch()) {
                $this->top_folder = $this->filesystemplugin->search(
                    Request::get('search'),
                    Request::getArray('parameter')
                );
            } else {
                $this->top_folder = $this->filesystemplugin->getFolder($folder_id, true);
                if (is_a($this->top_folder, 'Flexi_Template')) {
                    $this->top_folder->select    = true;
                    $this->top_folder->to_folder = $this->to_folder;
                    $this->render_text($this->top_folder);
                }
            }
        } else {
            $this->top_folder = new StandardFolder(new Folder($folder_id));
            if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }
        }

        $this->top_folder_name = _('Hauptordner');

        //A top folder can have its parent-ID set to an emtpy string
        //or its folder_type is set to 'RootFolder'.
        if ($this->top_folder->parent_id == ''
            or $this->top_folder->folder_type == 'RootFolder') {
            //We have a top folder. Now we check if its range-ID
            //references a Stud.IP object and set the displayed folder name
            //to the name of that object.
            if ($this->top_folder->range_id) {
                $range_type = Folder::findRangeTypeById($this->top_folder->range_id);

                switch ($range_type) {
                    case 'course':
                        $course = Course::find($this->top_folder->range_id);
                        if ($course) {
                            $this->top_folder_name = $course->getFullName();
                        }
                        break;
                    case 'institute':
                        $institute = Institute::find($this->top_folder->range_id);
                        if ($institute) {
                            $this->top_folder_name = $institute->getFullName();
                        }
                        break;
                    case 'user':
                        $user = User::find($this->top_folder->range_id);
                        if ($user) {
                            $this->top_folder_name = $user->getFullName();
                        }
                        break;
                    case 'message':
                        $message = Message::find($this->top_folder->range_id);
                        if ($message) {
                            $this->top_folder_name = $message->subject;
                        }
                        break;
                    case 'resource': {
                        $resource = Resource::find($this->top_folder->range_id);
                        if ($resource) {
                            $resource = $resource->getDerivedClassInstance();
                            if ($resource) {
                                $this->top_folder_name = $resource->getFullName();
                            }
                        }
                    }
                }

            }
        }else {
            //$top_folder is not a top folder. We can use its name directly.
            $this->top_folder_name = $this->top_folder->name;
        }
    }


    public function add_from_library_action($folder_id = null)
    {
        PageLayout::setTitle(_('Suche im Bibliothekskatalog'));
        if (!Config::get()->LITERATURE_ENABLE) {
            throw new AccessDeniedException(_('Die Literaturverwaltung ist ausgeschaltet!'));
        }

        $this->top_folder = new StandardFolder(new Folder($folder_id));
        if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $course = $this->top_folder->getRangeObject();
        if (!($course instanceof Course) || !$GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
            throw new AccessDeniedException();
        }

        if (!LibrarySearchManager::catalogsConfigured()) {
            PageLayout::postError(
                _('In dieser Stud.IP-Installation sind keine Bibliothekskataloge aktiviert!')
            );
            return;
        }

        $this->folder_id = $folder_id;

        $plugin_manager = PluginManager::getInstance();
        $this->page_size = 30;
        $this->limit = 100; //the limit for items from each catalog
        $this->next_page = 0;
        $this->page = 0;
        $this->order_by = LibrarySearch::ORDER_BY_RELEVANCE;
        $this->global_stylesheet = $GLOBALS['LIBRARY_STYLESHEET_ID'];

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if (Request::submitted('search')) {
                $this->title = Request::get('title');
                $this->author = Request::get('author');
                $this->year = Request::get('year');
                $this->number = Request::get('number');
                $this->publication = Request::get('publication');
                $this->signature = Request::get('signature');
                $this->order_by = Request::get('order_by');

                if (!$this->title && !$this->author && !$this->year &&
                    !$this->number && !$this->publication && !$this->signature) {
                    PageLayout::postError(
                        _('Es muss mindestens ein Suchkriterium angegeben werden!')
                    );
                    return;
                }

                $this->library_plugins = $plugin_manager->getPlugins('LibraryPlugin');

                //Build the query parameter array:
                $search_parameters = [];
                if ($this->title) {
                    $search_parameters[LibrarySearch::TITLE] = $this->title;
                }
                if ($this->author) {
                    $search_parameters[LibrarySearch::AUTHOR] = $this->author;
                }
                if ($this->year) {
                    $search_parameters[LibrarySearch::YEAR] = $this->year;
                }
                if ($this->number) {
                    $search_parameters[LibrarySearch::NUMBER] = $this->number;
                }
                if ($this->publication) {
                    $search_parameters[LibrarySearch::PUBLICATION] = $this->publication;
                }
                if ($this->signature) {
                    $search_parameters[LibrarySearch::SIGNATURE] = $this->signature;
                }

                $this->search_id = md5(json_encode($search_parameters));

                $cache = StudipCacheFactory::getCache();

                $merged_results = LibrarySearchManager::search(
                    $search_parameters,
                    $this->order_by,
                    $this->limit
                );
                $this->total_results = count($merged_results);
                $cache_data = [
                    'search_params' => $search_parameters,
                    'results' => $merged_results
                ];
                $cache->write($this->search_id, $cache_data);
                if (count($merged_results) > $this->page_size) {
                    $this->next_page = 2;
                }
                $this->result_set = array_slice($merged_results, 0, $this->page_size);
                $this->pagination_link_closure = function($page_id) {
                    return URLHelper::getLink(
                        'dispatch.php/file/add_from_library/' . $this->top_folder->getId(),
                        [
                            'search_id' => $this->search_id,
                            'page' => $page_id,
                        ]
                    );
                };
                return;
            } elseif (Request::submitted('add_to_file_area')) {
                $search_id = Request::get('search_id');
                $result_id = Request::get('result_id');
                $this->redirect(
                    $this->url_for(
                        'file/create_library_file/' . $this->top_folder->getId(),
                        [
                            'search_and_item_id' => $search_id . '_' . $result_id,
                            'create_only' => '1'
                        ]
                    )
                );
            } elseif (Request::submitted('create_library_request')) {
                $search_id = Request::get('search_id');
                $result_id = Request::get('result_id');
                $plugin_id = Request::get('plugin_id');
                $this->redirect(
                    $this->url_for(
                        'file/create_library_file/' . $this->top_folder->getId(),
                        [
                            'search_and_item_id' => $search_id . '_' . $result_id,
                            'plugin_id' => $plugin_id
                        ]
                    )
                );
            }
        } elseif (Request::get('search_id')) {
            $this->library_plugins = $plugin_manager->getPlugins('LibraryPlugin');

            $this->search_id = Request::get('search_id');
            $this->page = Request::get('page');

            $cache = StudipCacheFactory::getCache();
            $cache_data = $cache->read($this->search_id);
            $results = $cache_data['results'];
            $this->total_results = count($results);
            $search_parameters = $cache_data['search_params'];
            $this->title = $search_parameters[LibrarySearch::TITLE];
            $this->author = $search_parameters[LibrarySearch::AUTHOR];
            $this->year = $search_parameters[LibrarySearch::YEAR];
            $this->number = $search_parameters[LibrarySearch::NUMBER];
            $this->publication = $search_parameters[LibrarySearch::PUBLICATION];
            $this->signature = $search_parameters[LibrarySearch::SIGNATURE];
            $offset = $this->page_size * $this->page;
            $this->result_set = array_slice($results, $offset, $this->page_size);
            $this->pagination_link_closure = function($page_id) {
                return URLHelper::getLink(
                    'dispatch.php/file/add_from_library/' . $this->top_folder->getId(),
                    [
                        'search_id' => $this->search_id,
                        'page' => $page_id,
                    ]
                );
            };
        }
    }


    public function create_library_file_action($folder_id = null)
    {
        PageLayout::setTitle(_('Bibliothekseintrag erstellen'));
        if (!Config::get()->LITERATURE_ENABLE) {
            throw new AccessDeniedException(_('Die Literaturverwaltung ist ausgeschaltet!'));
        }

        $this->top_folder = new StandardFolder(new Folder($folder_id));
        if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $course = $this->top_folder->getRangeObject();
        if (!($course instanceof Course) || !$GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
            throw new AccessDeniedException();
        }
        $create_only = Request::submitted('create_only');
        $plugin_id = Request::get('plugin_id');
        $search_and_item_id = Request::get('search_and_item_id');
        if (!$search_and_item_id) {
            PageLayout::postError(_('Es wurde kein Suchergebnis ausgewählt!'));
            return;
        }
        if (!$plugin_id && !$create_only) {
            throw new Exception('No plugin ID has been provided!');
        }
        $search_and_item_id = explode('_', $search_and_item_id);
        $search_id = $search_and_item_id[0];
        $item_id = $search_and_item_id[1];
        if (!$search_id) {
            throw new Exception('No search_id provided!');
        }

        if ($item_id) {
            $cache = StudipCacheFactory::getCache();
            $documents = $cache->read($search_id);
            $document = $documents['results'][$item_id];
            if (!($document instanceof LibraryDocument)) {
                throw new Exception('Library file not found in result cache!');
            }
            $file = LibraryFile::createFromLibraryDocument($document, $folder_id);
        } else {
            $cache = StudipCacheFactory::getCache();
            $search = $cache->read($search_id);
            if (!$search) {
                throw new Exception('Search not found in cache!');
            }
            $search_params = $search['search_params'];
            $document = new LibraryDocument();
            $document->search_params = $search_params;
            $file = LibraryFile::createFromLibraryDocument($document, $folder_id);
        }

        if ($create_only) {
            $this->redirect($this->url_for('file/edit_license/' . $this->top_folder->getId(), [
                'file_refs' => [$file->getFileRef()->getId()],
                're_location' => $this->url_for($this->top_folder->range_type . '/files/index/' . $this->top_folder->getId(), ['cid' => $this->top_folder->range_id])
            ]));
            return;
        }

        if ($plugin_id) {
            $plugin_manager = PluginManager::getInstance();
            $plugin = $plugin_manager->getPluginById($plugin_id);
            if (!($plugin instanceof LibraryPlugin)) {
                throw new Exception(sprintf('The plugin with the ID %s is not a LibraryPlugin!', $plugin_id));
            }

            if ($file instanceof LibraryFile) {
                //Redirect to the request page of the plugin.
                $this->redirect($plugin->getRequestURL($file->getId()));
            } else {
                throw new Exception('Library file could not be stored!');
            }
        }
    }


    public function getFolders_action()
    {
        $rangeId   = Request::get('range');
        $folders   = Folder::findBySQL('range_id = ?', [$rangeId]);
        $folderray = [];
        $pathes    = [];
        foreach ($folders as $folder) {
            $pathes[] = $folder->getPath();
            $folderray[][$folder->getPath()] = $folder->id;
        }
        array_multisort($pathes, SORT_ASC, SORT_STRING, $folderray);

        if (Request::isXhr()) {
            $this->render_json($folderray);
        } else {
            $this->render_nothing();
        }
    }


    /**
     * The action for deleting a file reference.
     */
    public function delete_action($file_ref_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::get("from_plugin")) {
            $file_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/delete/") + strlen("dispatch.php/file/delete/"));
            if (strpos($file_id, "?") !== false) {
                $file_id = substr($file_id, 0, strpos($file_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $filetype = $plugin->getPreparedFile($file_id);
            $folder = $filetype->getFolderType();
        } else {
            $file_ref = FileRef::find($file_ref_id);
            $folder = $file_ref->foldertype;
            $filetype = $file_ref->getFileType();
        }
        if (!$filetype) {
            throw new Trails_Exception(404, _('Datei nicht gefunden.'));
        }

        if (!$filetype->isWritable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        if ($filetype->delete()) {
            PageLayout::postSuccess(_('Datei wurde gelöscht.'));
        } else {
            PageLayout::postError(_('Datei konnte nicht gelöscht werden.'));
        }
        if (Request::submitted('from_flat_view')) {
            $this->redirectToFlatView($folder);
        } else {
            $this->redirectToFolder($folder);
        }
    }

    public function add_files_window_action($folder_id)
    {
        $this->folder_id   = $folder_id;

        $this->range = Context::getType();
        $this->upload_type = FileManager::getUploadTypeConfig(
            Context::getId(), $GLOBALS['user']->id
        );
        $config = Config::get();
        $this->show_library_functions = $config->LITERATURE_ENABLE;
        if ($this->show_library_functions) {
            $this->library_search_description = $config->LIBRARY_ADD_ITEM_ACTION_DESCRIPTION;
        }

        $this->plugin = Request::get('to_plugin');
    }

    public function choose_file_from_course_action($folder_id)
    {
        if (Request::get('course_id')) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/choose_file_from_course/") + strlen("dispatch.php/file/choose_file_from_course/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $folder = Folder::findTopFolder(Request::get('course_id'));
            $this->redirect($this->url_for(
                'file/choose_file/' . $folder->getId(), [
                    'to_plugin'    => Request::get('to_plugin'),
                    'to_folder_id' => $folder_id
                ]
            ));
            return;
        }

        $this->folder_id = $folder_id;
        $this->plugin = Request::get('to_plugin');
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $query = "SELECT seminare.*, COUNT(semester_courses.semester_id) AS semesters
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id
                      GROUP BY seminare.Seminar_id";
            if (Config::get()->DEPUTIES_ENABLE) {
                $query .= " UNION
                    SELECT `seminare`.*, COUNT(semester_courses.semester_id) AS semesters
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
                    WHERE `deputies`.`user_id` = :user_id
                    GROUP BY seminare.Seminar_id";
            }
            $query .= " ORDER BY semesters = 0 DESC, start_time DESC, Name ASC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(['user_id' => $GLOBALS['user']->id]);

            $this->courses = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }
    }

    public function choose_file_action($folder_id = null)
    {
        if (Request::get('to_plugin')) {
            $to_plugin = PluginManager::getInstance()->getPlugin(Request::get('to_plugin'));
            $this->to_folder_type = $to_plugin->getFolder(Request::get('to_folder_id', ''));
        } else {
            if (!Request::get('to_folder_id')) {
                throw new Exception('target folder_id must be set.');
            }
            $folder = new Folder(Request::option('to_folder_id', ''));
            $this->to_folder_type = $folder->getTypedFolder();
        }

        if (Request::isPost()) {
            //copy
            if (Request::get('from_plugin')) {
                $plugin = PluginManager::getInstance()->getPlugin(Request::get('from_plugin'));
                $file = $plugin->getPreparedFile(Request::get('file_id'), true);
            } else {
                $from_file_ref = FileRef::find(Request::get('file_id'));
                $file = $from_file_ref->getFileType();
            }

            $newfile = FileManager::copyFile(
                $file,
                $this->to_folder_type,
                User::findCurrent()
            );

            if (Request::isXhr()) {
                $this->current_folder = $this->to_folder_type;
                $this->marked_element_ids = [];

                $plugins = PluginManager::getInstance()->getPlugins('FileUploadHook');

                $redirects = [];
                foreach ($plugins as $plugin) {
                    $url = $plugin->getAdditionalUploadWizardPage($newfile);
                    if ($url) {
                        $redirects[] = $url;
                    }
                }
                $payload = [
                    'html'     => FilesystemVueDataManager::getFileVueData($newfile, $this->current_folder),
                    'redirect' => $redirects[0],
                    'url'      => $this->generateFilesUrl($this->current_folder, $newfile),
                ];

                $this->response->add_header(
                    'X-Dialog-Execute',
                    'STUDIP.Files.addFile'
                );
                return $this->render_json($payload);
            } else {
                PageLayout::postSuccess(_('Datei wurde hinzugefügt.'));
                return $this->redirectToFolder($this->to_folder_type);
            }

            /*if ($file_ref instanceof FileRef) {
                if (in_array($this->to_folder_type->range_type, ['course', 'institute']) && !$file_ref->content_terms_of_use_id) {
                    $this->redirect($this->url_for(
                        "file/edit_license/{$this->to_folder_type->getId()}",
                        ['file_refs' => [$file_ref->id]]
                    ));
                    return;
                } elseif (Request::isXhr()) {
                    $this->file = $file_ref->getFileType();
                    $this->current_folder = $this->to_folder_type;
                    $this->marked_element_ids = [];

                    $plugins = PluginManager::getInstance()->getPlugins('FileUploadHook');

                    $redirects = [];
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($file_ref);
                        if ($url) {
                            $redirects[] = $url;
                        }
                    }
                    $payload = [
                        'html'     => FilesystemVueDataManager::getFileVueData($this->file, $this->current_folder),
                        'redirect' => $redirects[0],
                        'url'      => $this->generateFilesUrl($this->current_folder, $this->file_ref),
                    ];

                    $this->response->add_header(
                        'X-Dialog-Execute',
                        'STUDIP.Files.addFile'
                    );
                    return $this->render_json($payload);
                } else {
                    PageLayout::postSuccess(_('Datei wurde hinzugefügt.'));
                    return $this->redirectToFolder($this->to_folder_type);
                }
            } else {
                if (is_array($file_ref)) {
                    $error = $file_ref;
                }
                if(!is_array($error)) {
                    $error = [$error];
                }
                PageLayout::postError(_('Konnte die Datei nicht hinzufügen.'), array_map('htmlReady', $error));
            }*/
        }

        if (Request::get('from_plugin')) {
            $this->filesystemplugin = PluginManager::getInstance()->getPlugin(Request::get('from_plugin'));
            PageLayout::setTitle(sprintf(
                _('Dokument hinzufügen von %s'),
                $this->filesystemplugin->getPluginName()
            ));

            if (Request::get('search') && $this->filesystemplugin->hasSearch()) {
                $this->top_folder = $this->filesystemplugin->search(Request::get('search'), Request::getArray('parameter'));
            } else {
                $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/choose_file/") + strlen("dispatch.php/file/choose_file/"));
                if (strpos($folder_id, "?") !== false) {
                    $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
                }
                $this->top_folder = $this->filesystemplugin->getFolder($folder_id, true);
                if (is_a($this->top_folder, 'Flexi_Template')) {
                    $this->top_folder->select    = true;
                    $this->top_folder->to_folder = $this->to_folder;
                    $this->render_text($this->top_folder->render());
                }
            }
        } else {
            //Load the folder by its ID.
            $folder = new Folder($folder_id);
            $folder_type = $folder->folder_type;
            //Check if the specified folder type is a FolderType implementation.
            if (is_a($folder_type, 'FolderType', true)) {
                //Get an instance of the FolderType implementation
                //and use it in the code below this point.
                $this->top_folder = new $folder_type($folder);
                if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                    throw new AccessDeniedException();
                }
            }
        }

        $this->to_folder_name = _('Hauptordner');

        //A top folder can have its parent-ID set to an empty string
        //or its folder_type set to 'RootFolder'.
        if ($this->to_folder_type->parent_id == ''
            or $this->to_folder_type->folder_type == 'RootFolder') {
            //We have a top folder. Now we check if its range-ID
            //references a Stud.IP object and set the displayed folder name
            //to the name of that object.
            if ($this->to_folder_type->range_id) {
                $range_type = Folder::findRangeTypeById($this->to_folder_type->range_id);

                switch ($range_type) {
                    case 'course': {
                        $course = Course::find($this->to_folder_type->range_id);
                        if ($course) {
                            $this->to_folder_name = $course->getFullName();
                        }
                        break;
                    }
                    case 'institute': {
                        $institute = Institute::find($this->to_folder_type->range_id);
                        if ($institute) {
                            $this->to_folder_name = $institute->getFullName();
                        }
                        break;
                    }
                    case 'user': {
                        $user = User::find($this->to_folder_type->range_id);
                        if ($user) {
                            $this->to_folder_name = $user->getFullName();
                        }
                        break;
                    }
                    case 'message': {
                        $message = Message::find($this->to_folder_type->range_id);
                        if ($message) {
                            $this->to_folder_name = $message->subject;
                        }
                        break;
                    }
                }
            }
        } else {
            //The folder is not a top folder. We can use its name directly.
            $this->to_folder_name = $this->to_folder_type->name;
        }
    }

    public function edit_license_action($folder_id = null)
    {
        $this->re_location = Request::get('re_location');
        $file_ref_ids = Request::getArray('file_refs');
        if (!$file_ref_ids) {
            //In case the file ref IDs are not set in the request
            //they may still be set in the flash object of the controller:
            $file_ref_ids = $this->flash->get('file_refs');
        }
        $this->file_refs = FileRef::findMany($file_ref_ids);
        $this->folder = $this->file_refs[0]->folder;
        $this->show_description_field = Config::get()->ENABLE_DESCRIPTION_ENTRY_ON_UPLOAD;
        if ($folder_id == 'bulk') {
            $this->show_description_field = false;
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            if (($folder_id == 'bulk') && !Request::submitted('accept')) {
                $file_ref_ids = Request::getArray('ids');
                $this->file_refs = FileRef::findMany($file_ref_ids);
            } else {
                $description = Request::get('description');
                $success_files = [];
                $error_files = [];
                foreach ($this->file_refs as $file_ref) {
                    //Check if the user may change the license of the file:
                    $folder = $file_ref['folder'];
                    if (!$folder) {
                        //We have no way of determining whether the user may change
                        //the license.
                        $error_files[] = sprintf(_('Die Datei "%s" ist ungültig!'), $file_ref['name']);
                        continue;
                    }

                    $file_ref['content_terms_of_use_id'] = Request::option('content_terms_of_use_id');
                    if ($this->show_description_field && $description) {
                        $file_ref['description'] = $description;
                    }
                    if ($file_ref->isDirty()) {
                        if ($file_ref->store()) {
                            $success_files[] = $file_ref['name'];
                        } else {
                            $error_files[] = sprintf(_('Fehler beim Speichern der Datei "%s"!'), $file_ref['name']);
                        }
                    } else {
                        $success_files[] = $file_ref['name'];
                    }

                    $this->file = $file_ref->getFileType();
                    $this->current_folder = $file_ref->folder->getTypedFolder();
                    $this->marked_element_ids = [];
                    $payload['html'][] = FilesystemVueDataManager::getFileVueData($this->file, $this->current_folder);
                }
                if (Request::isXhr() && !$this->re_location) {
                    $payload = ['html' => []];
                    foreach ($this->file_refs as $file_ref) {
                        $folder = $file_ref->folder->getTypedFolder();

                        // Skip files not in current folder (during archive extract)
                        if ($folder_id && $folder_id !== $folder->getId()) {
                            continue;
                        }

                        $payload['html'][] = FilesystemVueDataManager::getFileVueData(
                            $file_ref->getFileType(),
                            $file_ref->folder->getTypedFolder()
                        );
                    }

                    $plugins = PluginManager::getInstance()->getPlugins('FileUploadHook');
                    $redirect = null;
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($file_ref);
                        if ($url) {
                            $redirect = $url;
                            break;
                        }
                    }

                    if ($redirect) {
                        $this->redirect($redirect);
                        return;
                    }

                    $payload['url'] = $this->generateFilesUrl(
                        $this->folder,
                        $file_ref
                    );

                    if ($folder_id == 'bulk' && $this->file_refs) {
                        if ($success_files) {
                            if (count($success_files) == 1) {
                                PageLayout::postSuccess(sprintf(
                                    _('Die Lizenz der Datei "%s" wurde geändert.'),
                                    htmlReady($this->file_refs[0]->name)
                                ));
                            } else {
                                sort($success_files);
                                PageLayout::postSuccess(
                                    _('Die Lizenzen der folgenden Dateien wurden geändert:'),
                                    array_map('htmlReady', $success_files)
                                );
                            }
                        }
                        if ($error_files) {
                            if (count($error_files) == 1) {
                                PageLayout::postError(sprintf(
                                    _('Die Lizenz der Datei "%s" konnte nicht geändert werden!'),
                                    htmlReady($this->file_refs[0]->name)
                                ));
                            } else {
                                PageLayout::postError(
                                    _('Die Lizenzen der folgenden Dateien konnten nicht geändert werden:'),
                                    array_map('htmlReady', $error_files)
                                );
                            }
                        }
                    }

                    $this->response->add_header(
                        'X-Dialog-Execute',
                        'STUDIP.Files.addFile'
                    );
                    $this->render_json($payload);
                    return;
                } else {
                    PageLayout::postSuccess(_('Datei wurde bearbeitet.'));
                    if ($this->re_location) {
                        return $this->relocate(URLHelper::getURL($this->re_location));;
                    } else {
                        return $this->redirectToFolder($this->folder);
                    }
                }
            }
        }

        PageLayout::setTitle(sprintf(
            ngettext(
                'Lizenz auswählen',
                'Lizenz auswählen: %s Dateien',
                count($this->file_refs)
            ),
            count($this->file_refs)
        ));

        $this->licenses = ContentTermsOfUse::findBySQL("1 ORDER BY position ASC, id ASC");
    }

    public function add_url_action($folder_id)
    {
        $this->content_terms_of_use_entries = ContentTermsOfUse::findAll();
        if (Request::get("to_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/add_url/") + strlen("dispatch.php/file/add_url/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("to_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $this->top_folder = $plugin->getFolder($folder_id);
        } else {
            $this->top_folder = FileManager::getTypedFolder($folder_id);
        }
        URLHelper::addLinkParam('to_plugin', Request::get('to_plugin'));
        if (!$this->top_folder || !$this->top_folder->isWritable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $url = trim(Request::get('url'));
            $url_parts = parse_url($url);
            if (filter_var($url, FILTER_VALIDATE_URL) !== false && in_array($url_parts['scheme'], ['http', 'https','ftp'])) {
                $this->file = $this->top_folder->addFile(URLFile::create([
                    'name' => Request::get('name'),
                    'url' => $url,
                    'access_type' => Request::get('access_type', "redirect"),
                    'content_terms_of_use_id' => Request::get('content_terms_of_use_id')
                ]));

                if ($this->file) {
                    $payload = [];

                    $payload['html'][] = FilesystemVueDataManager::getFileVueData($this->file, $this->top_folder);

                    $plugins = PluginManager::getInstance()->getPlugins('FileUploadHook');

                    $redirects = [];
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($this->file);
                        if ($url) {
                            $redirects[] = $url;
                        }
                    }
                    if (count($redirects) > 0) {
                        $payload['html'] = $redirects[0];
                    }

                    $this->response->add_header(
                        'X-Dialog-Execute',
                        'STUDIP.Files.addFile'
                    );
                    $this->render_json($payload);
                }
            } else {
                PageLayout::postError(_('Die angegebene URL ist ungültig.'));
            }
        }
    }

    /**
     * Action for creating a new folder.
     */
    public function new_folder_action($folder_id)
    {
        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/new_folder/") + strlen("dispatch.php/file/new_folder/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $parent_folder = $plugin->getFolder($folder_id);
        } else {
            $parent_folder = FileManager::getTypedFolder($folder_id);
        }

        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));
        if (!$parent_folder || !$parent_folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->parent_folder_id = $parent_folder->getId();

        $folder_types = FileManager::getAvailableFolderTypes($parent_folder->range_id, $GLOBALS['user']->id);

        $this->name = Request::get('name');
        $this->description = Request::get('description');
        $this->folder_types = [];
        $this->show_confirmation_button = false;

        foreach ($folder_types as $folder_type) {
            $folder_type_instance = new $folder_type(
                ['range_id' => $parent_folder->range_id,
                 'range_type' => $parent_folder->range_type,
                 'parent_id' => $parent_folder->getId()]
            );
            $this->folder_types[] = [
                'class'    => $folder_type,
                'instance' => $folder_type_instance,
                'name'     => $folder_type::getTypeName(),
                'icon'     => $folder_type_instance->getIcon('clickable')
            ];
        }

        if (Request::submitted('create') || Request::submitted('force_creation')) {
            CSRFProtection::verifyUnsafeRequest();

            $force_creation = Request::submitted('force_creation');

            // Get class name of folder type and check if the class
            // is a subclass of FolderType before initialising it:
            $folder_type = Request::get('folder_type', 'StandardFolder');
            if (!is_subclass_of($folder_type, 'FolderType')) {
                throw new Exception(
                    _('Der gewünschte Ordnertyp ist ungültig!')
                );
            }
            $request = Request::getInstance();
            $request->offsetSet('parent_id', $folder_id);
            $new_folder = new $folder_type(
                ['range_id' => $parent_folder->range_id,
                 'range_type' => $parent_folder->range_type,
                 'parent_id' => $parent_folder->getId()]
            );
            $result = $new_folder->setDataFromEditTemplate($request);

            if ($result instanceof FolderType) {
                $new_folder->user_id = User::findCurrent()->id;
                if ($result instanceof CourseDateFolder && !$force_creation) {
                    // Check if there is already a folder for the
                    // selected course date:
                    $course_date = $result->getDate();
                    if ($course_date instanceof CourseDate
                        && count($course_date->folders) > 0
                    ) {
                        PageLayout::postWarning(sprintf(
                            _('Für den Termin am %s existiert bereits ein Sitzungs-Ordner. Möchten Sie trotzdem einen weiteren Sitzungs-Ordner erstellen?'),
                            htmlReady($course_date->getFullname())
                        ));
                        $this->show_confirmation_button = true;
                        $this->folder = $new_folder ?: new StandardFolder();
                        return;
                    }
                }
                if ($parent_folder->createSubfolder($new_folder)) {
                    PageLayout::postSuccess(_('Der Ordner wurde angelegt.'));
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->render_nothing();
                } else {
                    PageLayout::postError(
                        _('Fehler beim Anlegen des Ordners!')
                    );
                }
            } else {
                PageLayout::postMessage($result);
            }
        }
        $this->folder = $new_folder ?: new StandardFolder();
    }

    /**
     * Action for editing an existing folder, referenced by its ID.
     *
     * @param $folder_id string The ID of the folder that shall be edited.
     */
    public function edit_folder_action($folder_id)
    {
        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/edit_folder/") + strlen("dispatch.php/file/edit_folder/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $folder = $plugin->getFolder($folder_id);
        } else {
            $folder = FileManager::getTypedFolder($folder_id);
        }
        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));
        if (!$folder || !$folder->isEditable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        $parent_folder = $folder->getParent();
        $folder_types = FileManager::getAvailableFolderTypes($parent_folder->range_id, $GLOBALS['user']->id);
        $this->name = Request::get('name', $folder->name);
        $this->description = Request::get('description', $folder->description);

        $this->folder = $folder;
        $this->folder_template = $folder->getEditTemplate();

        $this->folder_types = [];

        if (!is_a($folder, 'VirtualFolderType')) {
            foreach ($folder_types as $folder_type) {
                $folder_type_instance = new $folder_type(
                    [
                        'range_id' => $parent_folder->range_id,
                        'range_type' => $parent_folder->range_type,
                        'parent_id' => $parent_folder->getId()
                    ]
                );
                $this->folder_types[] = [
                    'class'    => $folder_type,
                    'instance' => $folder_type_instance,
                    'name'     => $folder_type::getTypeName(),
                    'icon'     => $folder_type_instance->getIcon('clickable')
                ];
            }
        }


        if (Request::submitted('edit')) {
            CSRFProtection::verifyUnsafeRequest();
            if (!is_a($folder, 'VirtualFolderType')) {
                $folder_type = Request::get('folder_type', get_class($folder));
                if (!is_subclass_of($folder_type, 'FolderType') || !class_exists($folder_type)) {
                    throw new InvalidArgumentException(_('Unbekannter Ordnertyp!'));
                }
                if ($folder_type !== get_class($folder)) {
                    $folder = new $folder_type($folder);
                }
            }
            $request = Request::getInstance();
            $request->offsetSet('parent_id', $folder->getParent()->getId());
            $result = $folder->setDataFromEditTemplate($request);
            if ($result instanceof FolderType) {
                if ($folder->store()) {
                    PageLayout::postSuccess(_('Der Ordner wurde bearbeitet.'));
                }
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            } else {
                PageLayout::postMessage($result);
            }
        }
    }

    public function delete_folder_action($folder_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/delete_folder/") + strlen("dispatch.php/file/delete_folder/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $folder = $plugin->getFolder($folder_id);
        } else {
            $folder = FileManager::getTypedFolder($folder_id);
        }
        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));
        if (!$folder || !$folder->isEditable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $parent_folder = $folder->getParent();

        if ($folder->delete()) {
            PageLayout::postSuccess(_('Ordner wurde gelöscht!'));
        } else {
            PageLayout::postError(_('Ordner konnte nicht gelöscht werden!'));
        }
        $this->redirectToFolder($parent_folder);
    }

    /**
     * This action allows downloading, copying, moving and deleting files and folders in bulk.
     */
    public function bulk_action($folder_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::get("from_plugin")) {
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/bulk/") + strlen("dispatch.php/file/bulk/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
            }
            $plugin = PluginManager::getInstance()->getPlugin(Request::get("from_plugin"));
            if (!$plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $parent_folder = $plugin->getFolder($folder_id);
        } else {
            $parent_folder = FileManager::getTypedFolder($folder_id);
        }

        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));
        if (!$parent_folder || !$parent_folder->isReadable($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        //check, if at least one ID was given:
        $ids = Request::getArray('ids');

        if (empty($ids)) {
            $this->redirectToFolder($parent_folder);
            return;
        }

        //check, which action was chosen:

        if (Request::submitted('download')) {
            //bulk downloading:
            $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');
            $user = User::findCurrent();
            $use_dos_encoding = strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false;

            //collect file area objects by looking at their IDs:
            $file_area_objects = [];
            foreach ($ids as $id) {

                if (Request::get("from_plugin")) {
                    $fa_object = $plugin->getFolder($id);
                    if (!$fa_object) {
                        $fa_object = $plugin->getPreparedFile($id, true);
                    }
                    if ($fa_object) {
                        $file_area_objects[] = $fa_object;
                    }
                } else {
                //check if the ID references a FileRef:
                    $filesystem_item = FileRef::find($id);
                    if (!$filesystem_item) {
                        //check if the ID references a Folder:
                        $filesystem_item = Folder::find($id);
                        if ($filesystem_item) {
                            $file_area_objects[] = $filesystem_item->getTypedFolder();
                        }
                    } else {
                        $file_area_objects[] = $filesystem_item;
                    }
                }
            }

            if (count($file_area_objects) === 1 && is_a($file_area_objects[0], 'FileRef')) {
                //we have only one file to deliver, so no need for zipping it:
                $this->redirect($file_area_objects[0]->getDownloadURL('force_download'));
                return;
            }

            //create a ZIP archive:
            try {
                $result = FileArchiveManager::createArchive(
                    $file_area_objects,
                    $user->id,
                    $tmp_file,
                    true,
                    true,
                    false,
                    $use_dos_encoding ? 'CP850' : 'UTF-8',
                    true
                );
            }  catch (FileArchiveManagerException $fame) {
                PageLayout::postError(_('Es ist ein Fehler aufgetreten.'), [$fame->getMessage()]);
                $this->redirectToFolder($parent_folder);
                return;
            }

            if ($result) {
                if (count($file_area_objects) === 1 && $file_area_objects[0] instanceof FolderType) {
                    $zip_file_name = $file_area_objects[0]->name;
                } else {
                    $zip_file_name = $parent_folder->name;
                }
                //ZIP file was created successfully
                $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                    basename($tmp_file),
                    ($zip_file_name ?: basename($tmp_file)) . '.zip'
                ));
            } else {
                throw new Exception('Error while creating ZIP archive!');
            }
        } elseif (Request::submitted('copy')) {
            //bulk copying
            $this->flash['fileref_id'] = Request::getArray('ids');
            $this->redirect($this->url_for('file/choose_destination/copy/flash'));
        } elseif (Request::submitted('move')) {
            //bulk moving
            $this->flash['fileref_id'] = Request::getArray('ids');
            $this->redirect($this->url_for('file/choose_destination/move/flash'));
        } elseif (Request::submitted('delete')) {
            //bulk deleting
            $errors = [];
            $count_files = 0;
            $count_folders = 0;

            $user = User::findCurrent();
            $selected_elements = Request::getArray('ids');
            foreach ($selected_elements as $element) {

                if (Request::get("from_plugin")) {
                    $foldertype = $plugin->getFolder($element);
                    if (!$foldertype) {
                        $file_ref = $plugin->getPreparedFile($element, true);
                    }
                } else {
                    $file_ref = FileRef::find($element);
                    if(!$file_ref) {
                        $foldertype = FileManager::getTypedFolder($element);
                    }
                }

                if ($file_ref) {
                    $current_folder = $file_ref->getFolderType();
                    $result = $current_folder ? $current_folder->deleteFile($element) : false;
                    if ($result && !is_array($result)) {
                        $count_files += 1;
                    }
                } elseif ($foldertype) {
                    $folder_files = count($foldertype->getFiles());
                    $folder_subfolders = count($foldertype->getSubfolders());
                    $result = FileManager::deleteFolder($foldertype, $user);
                    if (!is_array($result)) {
                        $count_folders += 1;
                        $count_files += $folder_files;
                        $count_folders += $folder_subfolders;
                    }
                }
                if (is_array($result)) {
                    $errors = array_merge($errors, $result);
                }
            }

            if (empty($errors) || $count_files > 0 || $count_folders > 0) {
                if ($count_files == 1 || $count_folders == 1) {
                    if ($count_folders) {
                        PageLayout::postSuccess(_('Der Ordner wurde gelöscht!'));
                    } else {
                        PageLayout::postSuccess(_('Die Datei wurde gelöscht!'));
                    }
                } elseif ($count_files > 0 && $count_folders > 0) {
                    PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner und %s Dateien gelöscht!'), $count_folders, $count_files));
                } elseif ($count_files > 0) {
                    PageLayout::postSuccess(sprintf(_('Es wurden  %s Dateien gelöscht!'), $count_files));
                } else {
                    PageLayout::postSuccess(sprintf(_('Es wurden %s Ordner gelöscht!'), $count_folders));
                }
            } else {
                PageLayout::postError(_('Es ist ein Fehler aufgetreten.'), array_map('htmlReady', $errors));
            }

            $this->redirectToFolder($parent_folder);
        }
    }

    public function open_folder_action($folder_id)
    {
        $folder = FileManager::getTypedFolder($folder_id, Request::get('from_plugin'));
        URLHelper::addLinkParam('from_plugin', Request::get('from_plugin'));
        if (!$folder || !$folder->isVisible($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        $this->redirectToFolder($folder);
    }

    private function generateFilesUrl($folder, $fileRef)
    {
        require_once 'app/controllers/files.php';

        return \FilesController::getRangeLink($folder) . '#fileref_' . $fileRef->id;
    }
}
