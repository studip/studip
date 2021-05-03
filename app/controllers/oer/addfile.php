<?php

class Oer_AddfileController extends AuthenticatedController
{
    public function choose_file_action()
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

        if (Request::option("material_id")) {
            $material = OERMaterial::find(Request::option("material_id"));
            $uploaded_file = [
                'name' => $material['filename'],
                'type' => $material['content_type'],
                'content_terms_of_use_id' => "FREE_LICENSE",
                'description' => $material['description']
            ];
            if ($material['host_id']) {
                $tmp_name = $GLOBALS['TMP_PATH']."/oer_".$material->getId();
                file_put_contents($tmp_name, file_get_contents($material->getDownloadUrl()));
                $uploaded_file['tmp_name'] = $tmp_name;
                $uploaded_file['type'] = filesize($tmp_name);
            } else {
                $uploaded_file['tmp_name'] = $material->getFilePath();
                $uploaded_file['size'] = filesize($material->getFilePath());
            }

            $standardfile = StandardFile::create($uploaded_file);

            if ($standardfile->getSize()) {
                $error = $this->to_folder_type->validateUpload($standardfile, User::findCurrent()->id);
                if ($error && is_string($error)) {
                    if ($tmp_name) {
                        @unlink($tmp_name);
                    }
                    return [$error];
                }

                $newfile = $this->to_folder_type->addFile($standardfile);
                if ($tmp_name) {
                    @unlink($tmp_name);
                }
                if (!$newfile) {
                    PageLayout::postError(_('Daten konnten nicht kopiert werden!'));
                }
                PageLayout::postSuccess(_('Datei wurde hinzugefügt.'));
            } else {
                if ($tmp_name) {
                    @unlink($tmp_name);
                }
                PageLayout::postError(_('Daten konnten nicht kopiert werden!'));
            }
            PageLayout::postSuccess(_('Datei wurde hinzugefügt.'));
            return $this->redirectToFolder($this->to_folder_type);
        }

        $tag_matrix_entries_number = 9;
        $this->best_nine_tags = OERTag::findBest($tag_matrix_entries_number);


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
            case 'user':
                $this->relocate('files/flat', ['cid' => null]);
                break;
            default:
                //Plugins should not be available in the flat view.
                $this->relocate('files/system/' . $folder->range_type . '/' . $folder->getId(), ['cid' => null]);
                break;
        }
    }
}
