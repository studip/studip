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

        PageLayout::setTitle(_('Meine Dateien'));
        PageLayout::setHelpKeyword('Basis.Dateien');

        $this->user = User::findCurrent();
        $this->last_visitdate = time();

        if (Navigation::hasItem('/files_dashboard/files')) {
            Navigation::activateItem('/files_dashboard/files');
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Helper method for filling the sidebar with actions.
     */
    private function buildSidebar(FolderType $folder, $view = true)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');


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

        if ($folder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Datei hinzufügen'),
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

    /**
     * Displays the files in tree view.
     */
    public function index_action($topFolderId = '')
    {
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
    }

    /**
     * Displays the files in flat view
     **/
    public function flat_action()
    {
        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->user->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

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
                                $result = FileManager::moveFileRef($source, $destination_folder, $user);
                            } else {
                                $result = FileManager::copyFileRef($source, $destination_folder, $user);
                            }
                            if (!is_array($result)) {
                                $count_files += 1;
                            }
                        }
                    }
                } else {
                    if ($source = FileRef::find($fileref)) {
                        if ($copymode === 'move') {
                            $result = FileManager::moveFileRef($source, $destination_folder, $user);
                        } else {
                            $result = FileManager::copyFileRef($source, $destination_folder, $user);
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
            PageLayout::postError(_('Es ist ein Fehler aufgetreten.'), $errors);
        }

        $dest_range = $destination_folder->range_id;

        switch ($destination_folder->range_type) {
            case 'course':
                return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'institute':
                return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/' . $destination_folder->getId() . '?cid=' . $dest_range));
            case 'user':
                return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $destination_folder->getId()));
            default:
                if ($destination_plugin) {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/system/' . $destination_plugin->getPluginId() .'/'. $destination_folder->getId()));
                } else {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $destination_folder->getId()));
                }
        }

    }

    private function countChildren (FolderType $folder)
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
}
