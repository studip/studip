<?php
/**
 * files.php - controller to display personal files of a user
 *
 * The FilesController controller provides actions for the personal file area.
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

class FilesController extends AuthenticatedController
{
    public function validate_args(&$args, $types = NULL)
    {
        reset($args);
    }

    /**
     * Create a link to a folder's range.
     * @param \FolderType $folder  the folder
     * @return string the link to the folder's range
     */
    public static function getRangeLink($folder)
    {
        return FileManager::getFolderLink($folder);
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.Dateien');

        $this->user = User::findCurrent();
        $this->last_visitdate = time();

        //Actions in this controller are not accessible for nobody.
        if ($GLOBALS['user']->id == 'nobody') {
            throw new AccessDeniedException();
        }

        PageLayout::addHeadElement('script', ['type' => 'text/javascript'], sprintf(
            'STUDIP.Files.setUploadConstraints(%s);',
            json_encode([
                'filesize'   => $GLOBALS['UPLOAD_TYPES']['personalfiles']['file_sizes'][$this->user->perms],
                'type'       => $GLOBALS['UPLOAD_TYPES']['personalfiles']['type'],
                'file_types' => $GLOBALS['UPLOAD_TYPES']['personalfiles']['file_types'],
            ])
        ));
    }

    /**
     * Helper method for filling the sidebar with actions.
     */
    private function buildSidebar(FolderType $folder, $view = true)
    {
        $sidebar = Sidebar::get();

        $sources = new LinksWidget();
        $sources->setTitle(_("Dateiquellen"));
        $sources->addLink(
            _("Stud.IP-Dateien"),
            $this->url_for("files/index"),
            Icon::create("files", "clickable")
        );
        foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) {
            if ($plugin->isPersonalFileArea()) {
                $subnav = $plugin->getFileSelectNavigation();
                $sources->addLink(
                    $subnav->getTitle(),
                    URLHelper::getURL("dispatch.php/files/system/".$plugin->getPluginId()),
                    $subnav->getImage()
                );
            }
        }
        $sidebar->addWidget($sources);


        $actions = new ActionsWidget();

        if ($folder->isEditable($GLOBALS['user']->id) && $folder->parent_id) {
            $actions->addLink(
                _('Ordner bearbeiten'),
                $this->url_for('file/edit_folder/'.$folder->getId()),
                Icon::create("edit", "clickable"),
                ['data-dialog' => 1]
            );
        }

        if ($folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl('dispatch.php/file/new_folder/' . $folder->getId()),
                Icon::create('folder-empty+add', 'clickable'), ['data-dialog' => 1]
            );
        }

        $actions->addLink(
            _('Bildergalerie öffnen'),
            '#g',
            Icon::create('file-pic', 'clickable'),
            [
                'onClick' => "STUDIP.Files.openGallery(); return false;"
            ]
        );

        if ($folder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Dokument hinzufügen'),
                '#',
                Icon::create('file+add', 'clickable'),
                ['onClick' => "STUDIP.Files.openAddFilesWindow(); return false;"]
            );
        }

        $config_urls = [];
        foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) {
            $url = $plugin->filesystemConfigurationURL();
            if ($url) {
                $navigation = $plugin->getFileSelectNavigation();

                $config_urls[] = [
                    'name' => $navigation->getTitle(),
                    'icon' => $navigation->getImage(),
                    'url'  => $url,
                ];
            }
        }
        if (count($config_urls)) {
            if (count($config_urls) > 1) {
                $actions->addLink(
                    _('Dateibereiche konfigurieren'),
                    $this->url_for('files/configure'),
                    Icon::create('admin', 'clickable')
                )->asDialog();
            } else {
                $actions->addLink(
                    sprintf(_('%s konfigurieren'), $config_urls[0]['name']),
                    $config_urls[0]['url'],
                    $config_urls[0]['icon']
                )->asDialog();
            }
        }
        $sidebar->addWidget($actions);

        if ($folder->isWritable($GLOBALS['user']->id)) {
            $sidebar->addWidget(new TemplateWidget(
                _('Dateien hinzufügen'),
                $this->get_template_factory()->open('files/upload-drag-and-drop')
            ))->addLayoutCSSClass('hidden-medium-down');
        }

        if ($view) {
            $views = new ViewsWidget();
            $views->addLink(
                _('Ordneransicht'),
                $this->url_for('files/index'),
                null,
                [],
                'index'
            )->setActive(true);
            $views->addLink(
                _('Alle Dateien'),
                $this->url_for('files/flat'),
                null,
                [],
                'flat'
            );

            $sidebar->addWidget($views);
        }
    }


    protected function addFiltersToOverviewSidebar(array $filters = [])
    {
        if (!$filters) {
            return;
        }
        $sidebar = Sidebar::get();
        if (in_array('time_range', $filters)) {
            $template = $GLOBALS['template_factory']->open('sidebar/time-range-filter');
            $attributes = [];
            if (($this->begin instanceof DateTime) && ($this->end instanceof DateTime)) {
                $attributes['begin'] = $this->begin->format('d.m.Y');
                $attributes['end'] = $this->end->format('d.m.Y');
            }
        }
        if (in_array('course', $filters)) {
            //Find all courses of the user and add those courses to the course
            //list that have files in it.
            $user_courses = Course::findByUser($GLOBALS['user']->id);
            $this->course_ids = [];
            $courses_with_files = [];
            $user_courses = $user_courses ?: [];
            foreach ($user_courses as $course) {
                //Check if the course has files whose modification dates
                //lie in the specified time range:
                $db = DBManager::get();
                $sql = 'SELECT DISTINCT 1 FROM file_refs INNER JOIN `folders`
                    ON `file_refs`.`folder_id` = `folders`.`id`
                    WHERE `folders`.`range_id` =  :course_id';
                $sql_params = ['course_id' => $course->id];
                if (($this->begin instanceof DateTime) && ($this->end instanceof DateTime)) {
                    $sql .= ' AND `file_refs`.`chdate` BETWEEN :begin AND :end';
                    $sql_params['begin'] = $this->begin->getTimestamp();
                    $sql_params['end'] = $this->end->getTimestamp();
                }
                $stmt = $db->prepare($sql);
                $stmt->execute($sql_params);
                $files_exist = $stmt->fetchColumn();
                if ($files_exist) {
                    $courses_with_files[] = $course;
                }
            }
            if ($courses_with_files) {
                foreach ($courses_with_files as $course) {
                    $key = $course->id;
                    $course_options[$key] = $course->getFullName();
                }
                $attributes['course_options'] = $course_options;
            }
            $attributes['selected_course_id'] = $this->course_id;
        }
        $time_range_filter = new TemplateWidget(_('Filter'), $template, $attributes);
        $sidebar->addWidget($time_range_filter);
    }


    private function countChildren(FolderType $folder)
    {
        $file_count   = count($folder->getFiles());
        $folder_count = count($folder->getSubfolders());

        foreach ($folder->getSubfolders() as $subfolder) {
            $subs = $this->countChildren($subfolder);

            $file_count   += $subs[0];
            $folder_count += $subs[1];
        }

        return [$file_count, $folder_count];
    }


    protected function addViewsToOverview(string $current_view)
    {
        $sidebar = Sidebar::get();
        $views = new ViewsWidget();

        $views->addLink(
            _('Übersicht'),
            $this->url_for('files/overview'),
            null,
            [],
            'overview'
        )->setActive($current_view == 'overview');

        $views->addLink(
            _('Alle Dateien'),
            $this->url_for('files/overview', ['view' => 'all_files']),
            null,
            [],
            'all_files'
        )->setActive($current_view == 'all_files');

        $views->addLink(
            _('Persönlicher Dateibereich'),
            $this->url_for('files/overview', ['view' => 'my_uploaded_files']),
            null,
            [],
            'my_uploaded_files'
        )->setActive($current_view == 'my_uploaded_files');

        $views->addLink(
            _('Meine öffentlichen Dateien'),
            $this->url_for('files/overview', ['view' => 'my_public_files']),
            null,
            [],
            'my_public_files'
        )->setActive($current_view == 'my_public_files');

        $views->addLink(
            _('Meine Dateien mit ungeklärter Lizenz'),
            $this->url_for('files/overview', ['view' => 'my_uploaded_files_unknown_license']),
            null,
            [],
            'my_uploaded_files_unknown_license'
        )->setActive($current_view == 'my_uploaded_files_unknown_license');

        $sidebar->addWidget($views);
    }


    /**
     * Displays an overview page with widgets showing a current selection
     * of files. This is practically a new version of the former file dashboard.
     */
    public function overview_action()
    {
        if (Navigation::hasItem('/contents/files/overview')) {
            Navigation::activateItem('/contents/files/overview');
        }

        PageLayout::setTitle(_('Übersicht'));

        $this->begin = null;
        $this->end = null;
        $this->course_id = null;

        $user_id = $GLOBALS['user']->id;
        $this->show_download_column = Config::get()->DISPLAY_DOWNLOAD_COUNTER === 'always';

        $this->current_view = Request::get('view', 'overview');

        //Use the top folder of the user's file area for the link
        //to the file/bulk action. That action neets a folder-ID
        //to work properly.
        $folder = Folder::findTopFolder($this->user->id);
        $this->topFolder = null;
        if ($folder) {
            $this->topFolder = $folder->getTypedFolder();
            $this->vue_topfolder = FilesystemVueDataManager::getFolderVueData(
                $this->topFolder,
                $this->topFolder
            );
        }
        $this->form_action = $this->link_for('file/bulk/' . $folder->id);

        $course_did_change = false;
        if ($this->current_view != 'overview') {
            $tzdt = new DateTime();
            if (Request::submitted('filter')) {
                CSRFProtection::verifyUnsafeRequest();
                if (Request::get('begin') && Request::get('end')) {
                    $this->begin = Request::getDateTime('begin', 'd.m.Y');
                    $this->end = Request::getDateTime('end', 'd.m.Y');
                    if ($this->begin > $this->end) {
                        $this->begin = clone $this->end;
                    }
                    if ($this->begin instanceof DateTime) {
                        $this->begin->setTime(0,0,0);
                    }
                    if ($this->end instanceof DateTime) {
                        $this->end->setTime(23,59,59);
                    }

                    if (!is_array($_SESSION['files_overview'])) {
                        $_SESSION['files_overview'] = [];
                    }
                    $_SESSION['files_overview']['begin'] = $this->begin;
                    $_SESSION['files_overview']['end'] = $this->end;
                }
                if (Request::submitted('course_id')) {
                    $course_did_change = true;
                    $this->course_id = Request::get('course_id');
                    if (!is_array($_SESSION['files_overview'])) {
                        $_SESSION['files_overview'] = [];
                    }
                    $_SESSION['files_overview']['course_id'] = $this->course_id;
                }
            } else {
                $this->begin = $_SESSION['files_overview']['begin'];
                $this->end = $_SESSION['files_overview']['end'];
                $this->course_id = $_SESSION['files_overview']['course_id'];
            }
        }

        if ($this->course_id) {
            if (!$GLOBALS['perm']->have_studip_perm('user', $this->course_id)) {
                throw new AccessDeniedException();
            }
        }

        $this->addViewsToOverview($this->current_view);

        if ($this->current_view == 'overview') {
            $this->all_files_c = FileRef::countAll($GLOBALS['user']->id, $this->begin, $this->end);
            $all_file_refs = FileRef::findAll($GLOBALS['user']->id, $this->begin, $this->end, '', 100, 0);
            $this->all_files = [];
            $count_visible = 0;
            foreach ($all_file_refs as $file_ref) {
                $vue_data = FilesystemVueDataManager::getFileVueData(
                    $file_ref->getFileType(),
                    $this->topFolder
                );
                if (isset($vue_data['download_url'])) {
                    $this->all_files[] = $vue_data;
                    if (++$count_visible === 5) break;
                }
            }
            $this->public_files_c = FileRef::countPublicFiles($GLOBALS['user']->id, $this->begin, $this->end);
            $public_file_refs = FileRef::findPublicFiles($GLOBALS['user']->id, $this->begin, $this->end, 5, 0);
            $this->public_files = [];
            foreach ($public_file_refs as $file_ref) {
                $this->public_files[] = FilesystemVueDataManager::getFileVueData(
                    $file_ref->getFileType(),
                    $this->topFolder
                );
            }
            $this->uploaded_files_c = FileRef::countUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, '', false);
            $uploaded_file_refs = FileRef::findUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, '', false, 5, 0);
            $this->uploaded_files = [];
            foreach ($uploaded_file_refs as $file_ref) {
                $this->uploaded_files[] = FilesystemVueDataManager::getFileVueData(
                    $file_ref->getFileType(),
                    $this->topFolder
                );
            }
            $this->uploaded_unlic_files_c = FileRef::countUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, '', true);
            $uploaded_unlic_file_refs = FileRef::findUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, '', true, 5, 0);
            $this->uploaded_unlic_files = [];
            foreach ($uploaded_unlic_file_refs as $file_ref) {
                if ($file_ref->getFileType()->isVisible($GLOBALS['user']->id)) {
                    $this->uploaded_unlic_files[] = FilesystemVueDataManager::getFileVueData(
                        $file_ref->getFileType(),
                        $this->topFolder
                    );
                }
            }
            if (!$this->all_files_c) {
                $this->no_files = true;
            }
        } elseif ($this->current_view == 'all_files') {
            $this->table_title = _('Alle Dateien');
            $this->page_size = 25;
            $this->page = 1;
            if (!$course_did_change) {
                $this->page = Request::get('page') + 1;
            }
            if (($this->page < 1) || !$this->page) {
                $this->page = 1;
            }
            $offset = $this->page_size * ($this->page - 1);

            $this->addFiltersToOverviewSidebar(['time_range', 'course']);

            $this->file_ref_c = FileRef::countAll($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id);

            $pagination = Pagination::create(
                $this->file_ref_c,
                $this->page - 1,
                $this->page_size
            );
            $this->pagination_html = $pagination->asLinks(
                function ($page_id) {
                    return URLHelper::getLink(
                        'dispatch.php/files/overview',
                        [
                            'view' => 'all_files',
                            'page' => $page_id
                        ]
                    );
                }
            );
            //To optimise performance, the folders of the files are collected
            //in an array with all relevant files of the folder attached to it.
            //This way, we don't need to each file's folder separately and can
            //instead load it once.
            //Each item in the folders array has the following structure:
            //[
            //     'folder' => The folder object.
            //     'file_refs' => The file refs attached to it.
            //]
            $folders = [];
            $new_file_refs = FileRef::findAll($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id, $this->page_size, $offset);

            //Group the file refs by their folder:
            foreach ($new_file_refs as $file_ref) {
                if (!is_array($folders[$file_ref->folder_id])) {
                    $folders[$file_ref->folder_id] = [
                        'folder' => $file_ref->folder->getTypedFolder(),
                        'file_refs' => []
                    ];
                }
                $folders[$file_ref->folder_id]['file_refs'][] = $file_ref;
            }

            $this->files = [];
            foreach ($folders as $folder_data) {
                $folder = $folder_data['folder'];
                if (!$folder instanceof FolderType) {
                    //We cannot work with unknown folder types.
                    continue;
                }
                foreach ($folder_data['file_refs'] as $file_ref) {
                    //Check if the current user may download the file.
                    //If so, it is included in the new_files array.
                    if ($folder->isFileDownloadable($file_ref->id, $user_id)) {
                        $this->files[] = $file_ref->getFileType();
                    }
                }
            }
        } elseif ($this->current_view == 'my_public_files') {
            $this->table_title = _('Meine öffentlichen Dateien');
            $this->addFiltersToOverviewSidebar(['time_range']);
            $file_refs = FileRef::findPublicFiles($GLOBALS['user']->id, $this->begin, $this->end);
            $this->files = [];
            foreach ($file_refs as $file_ref) {
                $this->files[] = $file_ref->getFileType();
            }
        } elseif ($this->current_view == 'my_uploaded_files') {
            $this->addFiltersToOverviewSidebar(['time_range', 'course']);
            $this->table_title = _('Persönlicher Dateibereich');
            $file_refs = FileRef::findUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id);
            $this->files_c = FileRef::countUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id);
            $this->files = [];
            foreach ($file_refs as $file_ref) {
                $this->files[] = $file_ref->getFileType();
            }
        } elseif ($this->current_view == 'my_uploaded_files_unknown_license') {
            $this->addFiltersToOverviewSidebar(['time_range', 'course']);
            $this->table_title = _('Meine Dateien mit ungeklärter Lizenz');
            $file_refs = FileRef::findUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id, true);
            $this->files_c = FileRef::countUploadedFiles($GLOBALS['user']->id, $this->begin, $this->end, $this->course_id, true);
            $this->files = [];
            foreach ($file_refs as $file_ref) {
                $this->files[] = $file_ref->getFileType();
            }
        } else {
            PageLayout::postError(_('Die gewählte Ansicht ist nicht verfügbar!'));
        }

        $this->show_file_search = true;
    }


    /**
     * Displays the files from the personal file area in a tree view.
     */
    public function index_action($topFolderId = '')
    {
        if (Request::get("from_plugin")) {
            $this->redirect("files/index/".$topFolderId);
        }
        if (Navigation::hasItem('/contents/files/my_files')) {
            Navigation::activateItem('/contents/files/my_files');
        }

        PageLayout::setTitle(_('Persönliche Dateien'));

        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->user->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();
        if (!$this->topFolder->isVisible($GLOBALS['user']->id) || $this->topFolder->range_id !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }

        $this->buildSidebar($this->topFolder);

        //check for INBOX and OUTBOX folder:
        $inbox_folder  = FileManager::getInboxFolder($this->user);
        $outbox_folder = FileManager::getOutboxFolder($this->user);

        $this->show_file_search = true;
    }

    /**
     * Displays the files from the personal file area in a flat view
     **/
    public function flat_action()
    {
        if (Navigation::hasItem('/contents/files/my_files')) {
            Navigation::activateItem('/contents/files/my_files');
        }
        $sidebar = Sidebar::get();

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Bildergalerie öffnen'),
            '#g',
            Icon::create('file-pic', 'clickable'),
            [
                'onClick' => "STUDIP.Files.openGallery(); return false;",
                'v-if' => "hasFilesOfType('image')"
            ]
        );
        $sidebar->addWidget($actions);
        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->user->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();
        $this->form_action = $this->link_for('file/bulk/' . $this->topFolder->getId());
        $this->show_default_sidebar = true;
        $this->enable_table_filter = true;

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));
    }

    /**
     * Action to configure the different FileSystem-plugins
     */
    public function configure_action()
    {
        PageLayout::setTitle(_('Dateibereich zur Konfiguration auswählen'));

        $this->configure_urls = [];
        foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) {
            $url = $plugin->filesystemConfigurationURL();
            if ($url) {
                $navigation = $plugin->getFileSelectNavigation();

                $this->configure_urls[] = [
                    'name' => $navigation->getTitle(),
                    'icon' => $navigation->getImage(),
                    'url'  => $url,
                ];
            }
        }
    }

    public function system_action($plugin_id, $folder_id = null)
    {
        $this->plugin = PluginManager::getInstance()->getPluginById($plugin_id);
        if (!$this->plugin->isPersonalFileArea()) {
            throw new Exception(_('Dieser Bereich ist nicht verfügbar.'));
        }

        $navigation = $this->plugin->getFileSelectNavigation();
        PageLayout::setTitle($navigation->getTitle());

        URLHelper::addLinkParam('to_plugin', get_class($this->plugin));
        URLHelper::addLinkParam('from_plugin', get_class($this->plugin));

        $folder_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/files/system/".$this->plugin->getPluginId()) + strlen("dispatch.php/files/system/".$this->plugin->getPluginId()));
        if (strpos($folder_id, "?") !== false) {
            $folder_id = substr($folder_id, 0, strpos($folder_id, "?"));
        }

        $this->topFolder      = $this->plugin->getFolder($folder_id);
        $this->controllerpath = 'files/system/' . $plugin_id;

        if (!$this->topFolder) {
            PageLayout::postError(
                _('Ordner nicht gefunden!')
            );
        } else {
            $this->buildSidebar($this->topFolder, false);
        }
        $this->render_action('index');
    }

    public function copyhandler_action($destination_id)
    {
        $to_plugin = Request::get('to_plugin');
        $from_plugin    = Request::get('from_plugin');

        $fileref_id = Request::getArray('fileref_id');
        $copymode   = Request::get('copymode');

        $user = User::findCurrent();

        if ($to_plugin) {

            $destination_id = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "dispatch.php/files/copyhandler/") + strlen("dispatch.php/files/copyhandler/"));
            if (strpos($destination_id, "?") !== false) {
                $destination_id = substr($destination_id, 0, strpos($destination_id, "?"));
            }

            $destination_plugin = PluginManager::getInstance()->getPlugin($to_plugin);
            if (!$destination_plugin) {
                throw new Trails_Exception(404, _('Plugin existiert nicht.'));
            }
            $destination_folder = $destination_plugin->getFolder($destination_id);
        } else {
            $destination_folder = Folder::find($destination_id)->getTypedFolder();
        }

        $errors = [];

        $count_files   = 0;
        $count_folders = 0;

        $filerefs = $fileref_id;
        if (!empty($filerefs)) {

            foreach ($filerefs as $fileref) {

                if ($from_plugin) {
                    $source_plugin = PluginManager::getInstance()->getPlugin($from_plugin);
                    if (!$source_plugin) {
                        throw new Trails_Exception(404, _('Plugin existiert nicht.'));
                    }
                    if (Request::get("isfolder")) {
                        if ($source_folder = $source_plugin->getFolder($fileref)) {
                            if ($copymode === 'move') {
                                $result = FileManager::moveFolder($source_folder, $destination_folder, $user);
                            } else {
                                $result = FileManager::copyFolder($source_folder, $destination_folder, $user);
                            }
                            if (!is_array($result)) {
                                $count_folders += 1;
                                $children = $this->countChildren($result);
                                $count_files   += $children[0];
                                $count_folders += $children[1];
                            }
                        }
                    } else {
                        if ($source = $source_plugin->getPreparedFile($fileref, true)) {
                            if ($copymode === 'move') {
                                $result = FileManager::moveFile($source, $destination_folder, $user);
                            } else {
                                $result = FileManager::copyFile($source, $destination_folder, $user);
                            }
                            if (!is_array($result)) {
                                $count_files += 1;
                            }
                        }
                    }
                } else {
                    if ($source = FileRef::find($fileref)) {
                        if ($copymode === 'move') {
                            $result = FileManager::moveFile($source->getFileType(), $destination_folder, $user);
                        } else {
                            $result = FileManager::copyFile($source->getFiletype(), $destination_folder, $user);
                        }
                        if (!is_array($result)) {
                            $count_files += 1;
                        }
                    } elseif ($source = Folder::find($fileref)) {
                        $source_folder = $source->getTypedFolder();
                        if ($copymode === 'move') {
                            $result = FileManager::moveFolder($source_folder, $destination_folder, $user);
                        } else {
                            $result = FileManager::copyFolder($source_folder, $destination_folder, $user);
                        }
                        if (!is_array($result)) {
                            $count_folders += 1;

                            $children = $this->countChildren($result);
                            $count_files   += $children[0];
                            $count_folders += $children[1];
                        }
                    }
                }
                if (is_array($result)) {
                    $errors = array_merge($errors, $result);
                }
            }
        }

        if (empty($errors) || $count_files > 0 || $count_folders > 0) {
            if (count($filerefs) == 1) {
                if ($source_folder) {
                    if ($copymode == 'copy') {
                        PageLayout::postSuccess(_('Der Ordner wurde kopiert!'));
                    } else {
                        PageLayout::postSuccess(_('Der Ordner wurde verschoben!'));
                    }
                } else {
                    if ($copymode == 'copy') {
                        PageLayout::postSuccess(_('Die Datei wurde kopiert!'));
                    } else {
                        PageLayout::postSuccess(_('Die Datei wurde verschoben!'));
                    }
                }
            } else {
                if ($count_files > 0 && $count_folders > 0) {
                    if ($copymode === 'copy') {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Ordner und %u Dateien kopiert.'), $count_folders, $count_files));
                    } else {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Ordner und %u Dateien verschoben.'), $count_folders, $count_files));
                    }
                } elseif ($count_files > 0) {
                    if ($copymode === 'copy') {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Dateien kopiert.'), $count_files));
                    } else {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Dateien verschoben.'), $count_files));
                    }
                } else {
                    if ($copymode === 'copy') {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Ordner kopiert.'), $count_folders));
                    } else {
                        PageLayout::postSuccess(sprintf(_('Es wurden %u Ordner verschoben.'), $count_folders));
                    }
                }
            }
        } else {
            PageLayout::postError(_('Es ist ein Fehler aufgetreten.'), array_map('htmlReady', $errors));
        }

        $dest_range = $destination_folder->range_id;

        switch ($destination_folder->range_type) {
            case 'course':
                return $this->redirect(URLHelper::getURL('dispatch.php/course/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'institute':
                return $this->redirect(URLHelper::getURL('dispatch.php/institute/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'user':
                return $this->redirect(URLHelper::getURL('dispatch.php/files/index/' . $destination_folder->getId()));
            default:
                if ($destination_plugin) {
                    return $this->redirect(URLHelper::getURL('dispatch.php/files/system/' . $destination_plugin->getPluginId() .'/'. $destination_folder->getId()));
                } else {
                    return $this->redirect(URLHelper::getURL('dispatch.php/course/files/index/' . $destination_folder->getId()));
                }
        }

    }
}
