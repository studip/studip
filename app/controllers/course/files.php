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
class Course_FilesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        checkObjectModule('documents');
        object_set_visit_module('documents');

        if (!Context::isCourse()) {
            throw new CheckObjectException(_('Es wurde keine passende Veranstaltung gefunden.'));
        }
        $this->course = Context::get();
        $this->last_visitdate = object_get_visit($this->course->id, 'documents');

        PageLayout::setHelpKeyword('Basis.Dateien');
        PageLayout::setTitle(Context::get()->getFullname() . ' - ' . _('Dateien'));

        Navigation::activateItem('/course/files');
    }

    private function buildSidebar($index = 'index')
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
                URLHelper::getUrl('dispatch.php/file/new_folder/' . $this->topFolder->getId()),
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

        if ($this->topFolder->isWritable($GLOBALS['user']->id)) {
            $uploadArea = new LinksWidget();
            $uploadArea->setTitle(_("Dateien hochladen"));
            $uploadArea->addElement(new WidgetElement(
                    $this->render_template_as_string('files/upload-drag-and-drop'))
            );
            $sidebar->addWidget($uploadArea);
        }

        $views = new ViewsWidget();
        $views->addLink(
            _('Ordneransicht'),
            $this->url_for('course/files/index'),
            null,
            [],
            'index'
        )->setActive(true);
        $views->addLink(
            _('Alle Dateien'),
            $this->url_for('course/files/flat'),
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
            $folder = Folder::findTopFolder($this->course->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        if (!$this->topFolder->isVisible($GLOBALS['user']->id) || $this->topFolder->range_id !== $this->course->id) {
            throw new AccessDeniedException();
        }
        $this->buildSidebar('index');
        $this->render_template('files/index.php', $this->layout);
    }

    /**
     * Displays the files in flat view
     **/
    public function flat_action()
    {
        $sidebar = Sidebar::get();

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Dateien herunterladen'),
            $this->url_for('course/files/newest_files'),
            Icon::create('download', 'clickable'),
            ['cid' => $this->course->id]
        );
        if ($GLOBALS['user']->id !== 'nobody') {
            $sidebar->addWidget($actions);
        }

        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->course->id);
        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));
        $this->files = SimpleCollection::createFromArray($this->files)->orderBy('chdate desc');
        $this->range_type = 'course';
        $this->render_template('files/flat.php', $this->layout);
    }

    /**
     * Packs all files in this course which are considered new
     * to the current user into a ZIP file and send it to the user.
     */
    public function newest_files_action()
    {
        $user = User::findCurrent();

        //Get the course's top folder:
        $folder = Folder::findTopFolder($this->course->id);
        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->top_folder = $folder->getTypedFolder();

        //Get all files of the course:
        list($files, $folders) = array_values(
            FileManager::getFolderFilesRecursive(
                $this->top_folder,
                $user->id,
                true
            )
        );

        $last_visitdate = $this->last_visitdate;

        //Only select those files which are newer than the last visit date:
        $relevant_file_refs = [];

        foreach ($files as $file_ref) {
            if ($file_ref->chdate > $this->last_visitdate) {
                $relevant_file_refs[] = $file_ref;
            }
        }

        if (!$relevant_file_refs) {
            //There are no new files: Display an info message
            //and return to the flat file view of the course:
            PageLayout::postInfo(
                _('Es sind keine neuen Dateien in dieser Veranstaltung verfügbar!')
            );

            $this->redirect(
                'course/files/flat',
                [
                    'cid' => $this->course->id
                ]
            );
            return;
        }

        $tmp_file = tempnam($GLOBALS['TMP_PATH'], 'doc');

        //Create a ZIP archive from all relevant files the user may see:
        $archive = FileArchiveManager::createArchiveFromFileRefs(
            $relevant_file_refs,
            $user,
            $tmp_file,
            true
        );

        if ($archive) {
            //ZIP file has been created successfully.
            //Redirect to the ZIP file download URL:
            $this->redirect(
                FileManager::getDownloadURLForTemporaryFile(
                    basename($tmp_file),
                    $this->top_folder->name . '.zip'
                )
            );
        } else {
            throw new Exception('Error while creating ZIP archive!');
        }
    }
}
