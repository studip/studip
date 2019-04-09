<?php
/**
 * files.php - controller to display files in a course
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


class Institute_FilesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        checkObjectModule('documents');
        $this->institute = Institute::findCurrent();
        object_set_visit_module('documents');

        PageLayout::setHelpKeyword("Basis.Dateien");
        PageLayout::setTitle($this->institute->getFullname() . " - " . _("Dateien"));

        $this->last_visitdate = object_get_visit($this->institute->id, 'documents');
        Navigation::activateItem('/course/files');
    }

    private function buildSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/files-sidebar.png');

        $actions = new ActionsWidget();

        if ($this->topFolder->isEditable($GLOBALS['user']->id) && $this->topFolder->parent_id) {
            $actions->addLink(
                _("Ordner bearbeiten"),
                $this->url_for("file/edit_folder/".$this->topFolder->getId()),
                Icon::create("edit", "clickable"),
                ['data-dialog' => 1]
            );
        }

        if ($this->topFolder && $this->topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl(
                    'dispatch.php/file/new_folder/' . $this->topFolder ->getId()
                ),
                Icon::create('folder-empty+add', 'clickable')
            )->asDialog();
        }
        if ($this->topFolder && $this->topFolder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Datei hinzufügen'),
                '#',
                Icon::create('file+add', 'clickable'),
                ['onclick' => "STUDIP.Files.openAddFilesWindow(); return false;"]
            );
        }

        $sidebar->addWidget($actions);

        $views = new ViewsWidget();
        $views->addLink(
            _('Ordneransicht'),
            $this->url_for('institute/files/index'),
            null,
            [],
            'index'
        )->setActive(true);
        $views->addLink(
            _('Alle Dateien'),
            $this->url_for('institute/files/flat'),
            null,
            [],
            'flat'
        );

        $sidebar->addWidget($views);
    }

    /**
     * Displays the files in tree view
     **/
    public function index_action($topFolderId = '')
    {
        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->institute->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        if (!$this->topFolder->isVisible($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }

        $this->buildSidebar();

        $this->render_template('files/index.php', $this->layout);
    }

    /**
     * Displays the files in flat view
     **/
    public function flat_action()
    {
        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->institute->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));

        $this->range_type = 'institute';
        $this->render_template('files/flat.php', $this->layout);
    }
}
