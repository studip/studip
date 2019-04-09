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
    private function redirectToFolder($folder)
    {
        switch ($folder->range_type) {
            case 'course':
            case 'institute':
                $this->relocate($folder->range_type . '/files/index/' . $folder->getId(), ['cid' => $folder->range_id]);
               break;
            case 'user':
                $this->relocate('files/index/' . $folder->getId(), ['cid' => null]);
                break;
            default:
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
                    // error during upload: display error message:
                    $this->render_json(['message' => (string)MessageBox::error(
                        _('Beim Hochladen ist ein Fehler aufgetreten '),
                        array_map('htmlready', $validatedFiles['error'])
                    )]);

                    return;
                }

                //all files were uploaded successfully:
                $storedFiles = [];
                $default_license = ContentTermsOfUse::findDefault();

                foreach ($validatedFiles['files'] as $fileref) {
                    //If no terms of use is set for the file ref
                    //we must set it to a default terms of use
                    //and update the fileref.
                    if (!$fileref->content_terms_of_use_id
                        and $default_license) {
                        $fileref->content_terms_of_use_id = $default_license->id;
                        if ($fileref->isDirty()) {
                            $fileref->store();
                        }
                    }
                    $storedFiles[] = $fileref;
                }
                if (count($storedFiles) > 0 && !Request::isXhr()) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Es wurden %s Dateien hochgeladen'),
                            count($storedFiles)
                        ),
                        array_map('htmlready', $storedFiles)
                    );
                }
            } else {
                $this->render_json(['message' => (string)MessageBox::error(
                    _('Ein Systemfehler ist beim Upload aufgetreten.')

                )]);
                return;
            }

            if (Request::isXhr()) {
                $output = ['new_html' => []];

                if (count($storedFiles) === 1
                        && (strtolower(substr($storedFiles[0]['name'], -4)) === ".zip")
                        && ($folder->range_id === $GLOBALS['user']->id || Seminar_Perm::get()->have_studip_perm('tutor', $folder->range_id))) {
                    $ref_ids = [];
                    foreach ($storedFiles as $file_ref) {
                        $ref_ids[] = $file_ref->getId();
                    }
                    $output['redirect'] = $this->url_for('file/unzipquestion', [
                        'file_refs' => $ref_ids
                    ]);
                } elseif (in_array($folder->range_type, ['course', 'institute', 'user'])) {
                    $ref_ids = [];
                    foreach ($storedFiles as $file_ref) {
                        $ref_ids[] = $file_ref->getId();
                    }
                    $output['redirect'] = $this->url_for('file/edit_license', [
                        'file_refs' => $ref_ids
                    ]);
                }

                foreach ($storedFiles as $fileref) {
                    $this->file_ref           = $fileref;
                    $this->current_folder     = $folder;
                    $this->marked_element_ids = [];

                    $output['new_html'][] = ['html' => $this->render_template_as_string('files/_fileref_tr')];
                }

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
            if (Request::submitted('unzip')) {
                //unzip!
                $file_refs = FileArchiveManager::extractArchiveFileToFolder(
                    $this->file_ref,
                    $this->current_folder,
                    $GLOBALS['user']->id
                );

                $ref_ids = [];

                foreach ($file_refs as $file_ref) {
                    $ref_ids[] = $file_ref->id;
                }

                //Delete the original zip file:
                $this->file_ref->delete();
            } else {
                $ref_ids = [$this->file_ref->getId()];
            }

            $this->flash->set('file_refs', $ref_ids);
            $this->redirect(
                $this->url_for('file/edit_license')
            );
        }
    }

    /**
     * Displays details about a file or a folder.
     *
     * @param string $file_area_object_id A file area object like a Folder or a FileRef.
     */
    public function details_action($file_area_object_id = null, $include_navigation = false)
    {
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
            $this->file_ref = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");
        } else {
            $this->file_ref = FileRef::find($file_area_object_id);
        }

        if ($this->file_ref) {
            //file system object is a FileRef
            PageLayout::setTitle($this->file_ref->name);

            //Check if file is downloadable for the current user:
            $this->show_preview    = false;
            $this->is_downloadable = false;

            // NOTE: The following can only work properly for folders which are
            // stored in the database, since remote folders
            // (for example owncloud/nextcloud folders) are not stored in the database.
            $folder = $this->file_ref->foldertype;
            if (!$folder->isVisible(User::findCurrent()->id)) {
                throw new AccessDeniedException();
            }
            $this->is_downloadable = $folder->isFileDownloadable($this->file_ref->id, User::findCurrent()->id);
            $this->is_editable     = $folder->isFileEditable($this->file_ref->id, User::findCurrent()->id);

            //load the previous and next file in the folder,
            //if the folder is of type FolderType.
            $this->previous_file_ref_id = false;
            $this->next_file_ref_id     = false;
            if ($include_navigation && $folder->isReadable(User::findCurrent()->id)) {
                $current_file_ref_id = null;
                foreach ($folder->getFiles() as $folder_file_ref) {
                    $last_file_ref_id = $current_file_ref_id;
                    $current_file_ref_id = $folder_file_ref->id;

                    if ($folder_file_ref->id === $this->file_ref->id) {
                        $this->previous_file_ref_id = $last_file_ref_id;
                    }

                    if ($last_file_ref_id === $this->file_ref->id) {
                        $this->next_file_ref_id = $folder_file_ref->id;
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

            //file system object is a Folder
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

            $this->file_ref = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");

        } else {
            $this->file_ref = FileRef::find($file_ref_id);
        }
        $this->folder = $this->file_ref->foldertype;

        if (!$this->folder || !$this->folder->isFileEditable($this->file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->content_terms_of_use_entries = ContentTermsOfUse::findAll();
        if (Request::isPost()) {
            //form was sent
            CSRFProtection::verifyUnsafeRequest();
            $this->errors = [];

            $new_name = trim(Request::get('name'));
            $new_description = Request::get('description');
            $new_content_terms_of_use_id = Request::get('content_terms_of_use_id');

            //Check if the FileRef is unmodified:
            if (($new_name == $this->file_ref->name) &&
                ($new_description == $this->file_ref->description) &&
                ($new_content_terms_of_use_id == $this->file_ref->content_terms_of_use_id)) {
                //The FileRef is unmodified. We can redirect to the folder
                //where the FileRef is stored in.
                $this->redirectToFolder($this->folder);
                return;
            }

            if (Request::get("from_plugin")) {
                $result = $this->folder->editFile($file_ref_id, $new_name, $new_description, $new_content_terms_of_use_id);
            } else {
                $result = FileManager::editFileRef(
                    $this->file_ref,
                    User::findCurrent(),
                    $new_name,
                    $new_description,
                    $new_content_terms_of_use_id
                );
            }

            if (!$result instanceof FileRef) {
                $this->errors = array_merge($this->errors, $result);
            }


            if ($this->errors) {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Ändern der Datei %s!'),
                        $this->file_ref->name
                    ),
                    $this->errors
                );
            } else {
                PageLayout::postSuccess(_('Änderungen gespeichert!'));
                $this->redirectToFolder($this->folder);
            }

        }
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
            $this->file_ref = $plugin->getPreparedFile($file_id);
            $this->from_plugin = Request::get("from_plugin");
        } else {
            $this->file_ref = FileRef::find($file_ref_id);
        }
        $this->folder = $this->file_ref->foldertype;

        if (!$this->folder || !$this->folder->isFileEditable($this->file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->errors = [];

        if (Request::submitted('confirm')) {
            $update_filename = (bool) Request::get('update_filename', false);
            $update_all_instances = (bool) Request::get('update_all_instances', false);
            CSRFProtection::verifyUnsafeRequest();

            //Form was sent
            if (Request::isPost() && is_array($_FILES['file'])) {

                $result = FileManager::updateFileRef(
                    $this->file_ref,
                    User::findCurrent(),
                    $_FILES['file'],
                    $update_filename,
                    $update_all_instances
                );

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
                        $this->file_ref->name
                    ),
                    $this->errors
                );
            } else {
                PageLayout::postSuccess(
                    sprintf(
                        _('Datei %s wurde aktualisiert!'),
                        $this->file_ref->name
                    )
                );
            }
            $this->redirectToFolder($this->folder);
        }
    }

    public function choose_destination_action($copymode, $fileref_id = null)
    {

        if (empty($fileref_id)) {
            $fileref_id = Request::getArray('fileref_id');
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
                $refs = $fileref_id;
                $this->file_ref = FileRef::find($refs[0]);
            } else {
                $this->file_ref = FileRef::find($fileref_id);

                $this->fileref_id = [$fileref_id];
            }
        }

        if ($this->file_ref && Request::submitted("from_plugin")) {
            $this->parent_folder = $this->file_ref->foldertype;
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

        if (Request::isXhr()) {
            $this->response->add_header('X-Title', rawurlencode(_('Ziel wählen')));
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

            $use_dos_encoding = version_compare(PHP_VERSION, '5.6', '<=') || strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false;

            $result = FileArchiveManager::createArchive(
                [$foldertype],
                $user->id,
                $tmp_file,
                true,
                true,
                false,
                $use_dos_encoding ? 'CP850' : 'UTF-8',
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
            $query = "SELECT seminare.*
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id";
            if (Config::get()->DEPUTIES_ENABLE) {
                $query .= " UNION
                    SELECT `seminare`.*
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    WHERE `deputies`.`user_id` = :user_id";
            }
            $query .= " ORDER BY duration_time = -1, start_time DESC, Name ASC";
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
            $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/file/choose_folder/") + strlen("dispatch.php/file/choose_folder/"));
            if (strpos($folder_id, "?") !== false) {
                $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
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
                    case 'course': {
                        $course = Course::find($this->top_folder->range_id);
                        if ($course) {
                            $this->top_folder_name = $course->getFullName();
                        }
                        break;
                    }
                    case 'institute': {
                        $institute = Institute::find($this->top_folder->range_id);
                        if ($institute) {
                            $this->top_folder_name = $institute->getFullName();
                        }
                        break;
                    }
                    case 'user': {
                        $user = User::find($this->top_folder->range_id);
                        if ($user) {
                            $this->top_folder_name = $user->getFullName();
                        }
                        break;
                    }
                    case 'message': {
                        $message = Message::find($this->top_folder->range_id);
                        if ($message) {
                            $this->top_folder_name = $message->subject;
                        }
                        break;
                    }
                }

            }
        }
        else {
            //$top_folder is not a top folder. We can use its name directly.
            $this->top_folder_name = $this->top_folder->name;
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
            $file_ref = $plugin->getPreparedFile($file_id);
        } else {
            $file_ref = FileRef::find($file_ref_id);
        }
        if (!$file_ref) {
            throw new Trails_Exception(404, _('Datei nicht gefunden.'));
        }

        $folder = $file_ref->foldertype;
        if (!$folder || !$folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        if ($folder->deleteFile($file_ref->id)) {
            PageLayout::postSuccess(_('Datei wurde gelöscht.'));
        } else {
            PageLayout::postError(_('Datei konnte nicht gelöscht werden.'));
        }
        $this->redirectToFolder($folder);
    }

    public function add_files_window_action($folder_id)
    {
        $this->folder_id   = $folder_id;

        $this->upload_type = FileManager::getUploadTypeConfig(
            Context::getId(), $GLOBALS['user']->id
        );

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
            $query = "SELECT seminare.*
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id";
            if (Config::get()->DEPUTIES_ENABLE) {
                $query .= " UNION
                    SELECT `seminare`.*
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    WHERE `deputies`.`user_id` = :user_id";
            }
            $query .= " ORDER BY duration_time = -1, start_time DESC, Name ASC";
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
        if (!Request::get('to_folder_id')) {
            throw new Exception('target folder_id must be set.');
        }
        if (Request::get('to_plugin')) {
            $to_plugin = PluginManager::getInstance()->getPlugin(Request::get('to_plugin'));
            $this->to_folder_type = $to_plugin->getFolder(Request::get('to_folder_id'));
        } else {
            $folder = new Folder(Request::option('to_folder_id'));
            $this->to_folder_type = new StandardFolder($folder);
        }

        if (Request::isPost()) {
            //copy
            if (Request::get('from_plugin')) {
                $plugin = PluginManager::getInstance()->getPlugin(Request::get('from_plugin'));
                $filedata = $file = $plugin->getPreparedFile(Request::get('file_id'), true);
                if (!isset($file['tmp_name'])) {
                    if ($file->path_to_blob) {
                        //Cloud-file
                        $fileobject = [
                            'name' => $file['name'],
                            'tmp_name' => $file->path_to_blob,
                            'type' => $file->mime_type ?: get_mime_type($file['name']),
                            'size' => $file->size
                        ];
                        $file = $fileobject;
                    } elseif($file['url']) {
                        //URL-file
                        $fileobject = new File();
                        $fileobject->url = $file['url'];
                        $fileobject->name = $file['name'];
                        $fileobject->url_access_type = $file['url_access_type'] ?: 'redirect';

                        $meta = FileManager::fetchURLMetadata($file['url']);
                        if ($meta['response_code'] === 200) {
                            if (!$fileobject->name) {
                                $fileobject->name = $meta['filename'] ?: 'unknown';
                            }
                            $fileobject->size = $meta['Content-Length'];
                            $fileobject->mime_type = mb_strstr($meta['Content-Type'], ';', true);
                        }

                        $file = $fileobject;
                    }
                }
            } else {
                $file = FileRef::find(Request::get('file_id'))->file;
            }

            $error = $this->to_folder_type->validateUpload($file, $GLOBALS['user']->id);
            if (!$error) {
                //do the copy
                $file_ref = $this->to_folder_type->createFile($file);
                if ($filedata['content_terms_of_use_id']) {
                    $file_ref['content_terms_of_use_id'] = $filedata['content_terms_of_use_id'];
                }
                if (in_array($this->to_folder_type->range_type, ['course', 'institute'])) {
                    $this->redirect($this->url_for('file/edit_license', ['file_refs' => [$file_ref->id]]));
                    return;
                } elseif (Request::isXhr()) {
                    $this->file_ref = $file_ref;
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
                        'html'     => $this->render_template_as_string("files/_fileref_tr"),
                        'redirect' => $redirects[0],
                        'url'      => $this->generateFilesUrl($folder, $this->file_ref),
                    ];

                    $this->response->add_header(
                        'X-Dialog-Execute',
                        'STUDIP.Files.addFile'
                    );
                    $this->render_json($payload);
                } else {
                    PageLayout::postSuccess(_('Datei wurde hinzugefügt.'));
                    $redirect = 'files/index/' . $folder_id;
                    if ($this->to_folder_type->range_type === 'course') {
                        $redirect = 'course/' . $redirect;
                    }
                    $this->redirect($redirect);
                }
            } else {
                PageLayout::postError(_('Konnte die Datei nicht hinzufügen.'), [$error]);
            }
        }

        if (Request::get('from_plugin')) {
            $this->filesystemplugin = PluginManager::getInstance()->getPlugin(Request::get('from_plugin'));
            PageLayout::setTitle(sprintf(
                _('Datei hinzufügen von %s'),
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
            $this->top_folder = new StandardFolder(new Folder($folder_id));
            if (!$this->top_folder->isReadable($GLOBALS['user']->id)) {
                throw new AccessDeniedException();
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

    public function edit_license_action()
    {
        $file_ref_ids = Request::getArray('file_refs');
        if (!$file_ref_ids) {
            //In case the file ref IDs are not set in the request
            //they may still be set in the flash object of the controller:
            $file_ref_ids = $this->flash->get('file_refs');
        }
        $this->file_refs = FileRef::findMany($file_ref_ids);
        $this->folder = $this->file_refs[0]->folder;
        if (Request::isPost()) {
            foreach ($this->file_refs as $file_ref) {
                $file_ref['content_terms_of_use_id'] = Request::option('content_terms_of_use_id');
                $file_ref->store();
            }
            if (Request::isXhr()) {
                $payload = ['html' => []];
                foreach ($this->file_refs as $file_ref) {
                    $this->file_ref = $file_ref;
                    $this->current_folder = $file_ref->folder->getTypedFolder();
                    $this->marked_element_ids = [];
                    $payload['html'][] = $this->render_template_as_string('files/_fileref_tr');
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

                $this->response->add_header(
                    'X-Dialog-Execute',
                    'STUDIP.Files.addFile');
                $this->render_json($payload);
                return;
            } else {
                PageLayout::postSuccess(_('Datei wurde bearbeitet.'));
                //redirect:
            }
        }
        $this->licenses = ContentTermsOfUse::findBySQL("1 ORDER BY position ASC, id ASC");
    }

    public function add_url_action($folder_id)
    {
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
                if (Request::get('access_type') === 'redirect') {
                    if (in_array($url_parts['scheme'], ['http', 'https'])) {
                        $file = new File();
                        $file->url = $url;
                        $file->url_access_type = 'redirect';
                        $file->name = Request::get('name');
                        if (!$file->name) {
                            $meta = FileManager::fetchURLMetadata($url);
                            if ($meta['response_code'] === 200) {
                                $file->name = $meta['filename'] ?: 'unknown';
                                $file->mime_type = mb_strstr($meta['Content-Type'], ';', true);
                            }
                        } else {
                            $file->mime_type = get_mime_type($file->name);
                        }
                    } else {
                        PageLayout::postError(_('Die angegebene URL muss mit http(s) beginnen.'));
                    }
                } elseif (Request::get('access_type') === 'proxy') {
                    $meta = FileManager::fetchURLMetadata($url);
                    if ($meta['response_code'] === 200) {
                        $file = new File();
                        $file->url = $url;
                        $file->url_access_type = 'proxy';
                        $file->name = Request::get('name');
                        if (!$file->name) {
                            $file->name = $meta['filename'] ?: 'unknown';
                        }
                        $file->mime_type = $meta['Content-Type'] ? mb_strstr($meta['Content-Type'], ';', true) : get_mime_type($file->name);
                        $file->size = $meta['Content-Length'];
                    } else {
                        PageLayout::postError(
                            _('Die angegebene URL kann nicht abgerufen werden.'),
                            [_('Fehlercode') . ':' . htmlReady($meta['response_code'])]
                        );
                    }
                }
                if ($file) {
                    $file['user_id'] = $GLOBALS['user']->id;

                    $this->file_ref = $this->top_folder->createFile($file);
                    $payload = [];

                    $this->current_folder = $this->top_folder;
                    $this->marked_element_ids = [];
                    $payload['html'][] = $this->render_template_as_string('files/_fileref_tr');

                    $plugins = PluginManager::getInstance()->getPlugins('FileUploadHook');

                    $redirects = [];
                    foreach ($plugins as $plugin) {
                        $url = $plugin->getAdditionalUploadWizardPage($this->file_ref);
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

        if (Request::submitted('create')) {
            CSRFProtection::verifyUnsafeRequest();

            //Get class name of folder type and check if the class
            //is a subclass of FolderType before initialising it:
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
            $use_dos_encoding = version_compare(PHP_VERSION, '5.6', '<=') || strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false;

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
            $this->redirect($this->url_for('file/choose_destination/copy', ['fileref_id' => Request::getArray('ids')]));
        } elseif (Request::submitted('move')) {
            //bulk moving
            $this->redirect($this->url_for('file/choose_destination/move', ['fileref_id' => Request::getArray('ids')]));
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
                PageLayout::postError(_('Es ist ein Fehler aufgetreten!'), array_map('htmlReady', $errors));
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
