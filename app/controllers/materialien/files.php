<?php

/**
 * files.php - controller class for related files
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */
class Materialien_FilesController extends MVVController
{
    public $filter = [];
    private $show_sidebar_search = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->filter = $this->sessGet('filter', []);

        // set navigation
        Navigation::activateItem($this->me . '/materialien/files');

        $this->action = $action;
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Dokumente'));

        $this->initPageParams();
        $this->initSearchParams();

        $search_result = $this->getSearchResult('MvvFile');
        if ($search_result) {
            $ranges = [];
            $refs = [];
            $this->filter = array_merge(
                ['mvv_files.mvvfile_id' => $search_result],
                (array) $this->filter
            );
        }

        // set default semester filter
        if (!isset($this->filter['start_sem.beginn'], $this->filter['end_sem.ende'])) {
            $sem_time_switch = Config::get()->SEMESTER_TIME_SWITCH;
            // switch semester according to time switch
            // (n weeks before next semester)
            $current_sem = Semester::findByTimestamp(
                time() + $sem_time_switch * 7 * 24 * 3600
            );
            if ($current_sem) {
                $this->filter['start_sem.beginn'] = $current_sem->beginn;
                $this->filter['end_sem.ende']     = $current_sem->beginn;
            }
        }

        if (Request::option('range_id')) {
            $this->filter = ['mvv_files.range_id' => Request::option('range_id')];
            $this->sortby = 'position';
            $this->range_id = Request::option('range_id');
        }

        // show only files assigned to objects where the responsible institute is
        // in the list of users own institutes
        if (!$this->filter['mvv_studiengang.institut_id']) {
            $this->filter['mvv_studiengang.institut_id'] = MvvPerm::getOwnInstitutes();
        }

        $this->sortby = $this->sortby ?:  'mvv_files_filerefs.name';
        $this->order = $this->order ?: 'ASC';
        $this->dokumente = MvvFile::getAllEnriched(
            $this->sortby,
            $this->order,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1),
            $this->filter
        );

        if (MvvFile::countBySql() === 0) {
            PageLayout::postInfo(sprintf(
                _('Es wurden noch keine Dokumente angelegt. Klicken Sie %shier%s, um ein neues Dokument anzulegen.'),
                '<a data-dialog="size=auto" href="' . $this->link_for('/new_dokument') . '">',
                '</a>')
            );
        }
        if (!isset($this->dokument_id)) {
            $this->dokument_id = null;
        }

        $this->count = MvvFile::getCount($this->filter);

        $this->show_sidebar_search = true;
        $this->setSidebar();
    }

    public function range_action()
    {
        $this->sortby = 'position';
        $this->order = $this->order ?: 'ASC';
        if (Request::submitted('range_id')) {
            $this->range_id = Request::option('range_id');
            $this->range_type = Request::get('range_type');
            $this->dokumente = MvvFile::findByRange_id($this->range_id);
        } else {
            $this->dokumente = [];
        }
    }

    public function add_dokument_action($origin = 'index', $range_type = null, $range_id = null, $mvvfile_id = null)
    {
        PageLayout::setTitle(_('MVV Dokument'));
        $mvv_file = new MvvFile();
        $this->perm = MvvPerm::get($mvv_file);

        if (!$mvvfile_id) {
            $mvv_file->mvvfile_id = $mvv_file->getNewId();
        } else {
            $mvv_file = new MvvFile($mvvfile_id);
        }

        if (Request::submitted('store_document')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;

            foreach($GLOBALS['MVV_LANGUAGES']['values'] as $key => $entry) {
                if (Request::get('doc_url_'.$key)) {
                    $file = $this->upload_fileurl($mvvfile_id, Request::get('doc_url_'.$key));
                    if ($file) {
                        $mvvfile_fileref = new MvvFileFileref([$mvvfile_id, $key]);
                        $mvvfile_fileref->fileref_id = $file->getId();
                        $mvvfile_fileref->name = Request::get('doc_displayname_'.$key);
                        $mvvfile_fileref->store();
                    }
                } else {
                    if (Request::get('doc_displayname_'.$key)) {
                        $mvvfile_fileref = MvvFileFileref::find([$mvvfile_id, $key]);
                        if ($mvvfile_fileref) {
                            $mvvfile_fileref->name = Request::get('doc_displayname_'.$key);
                            $mvvfile_fileref->store();
                        }
                    }
                }
            }

            $mvv_file->year = Request::get('doc_year');
            $mvv_file->type = Request::get('doc_type');
            $mvv_file->category = Request::get('doc_cat');
            $mvv_file->tags = implode(';', Request::getArray('doc_tags'));
            $mvv_file->extern_visible = Request::get('doc_extvisible',0);

            try {
                $mvv_file->verifyPermission();
                $is_new = $mvv_file->isNew();
                $stored = $mvv_file->store();
            } catch (Exception $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                PageLayout::postSuccess(_('Das Dokument wurde gespeichert.'));

                if ($is_new) {
                    $range_ids = explode(',', $range_id);
                    foreach ($range_ids as $range_id) {
                        $mvv_file->addToRange($range_id, $range_type);
                    }
                }

                if ($origin == 'range') {
                    $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable("' . $range_id . '", "' . $range_type . '")');
                    $this->response->add_header('X-Dialog-Close', 1);
                    $this->render_nothing();
                } else {
                    $this->response->add_header('X-Dialog-Close', 1);
                    $this->response->add_header('X-Location', $this->url_for('/index'));
                }
                return;
            }
        }

        $this->origin = $origin;
        $this->range_id = $range_id;
        $this->range_type = $range_type;
        $this->mvvfile_id = $mvv_file->mvvfile_id;

        $this->doc_year = $mvv_file->year;
        $this->doc_type = $mvv_file->type;
        $this->doc_cat = $mvv_file->category;
        $this->doc_tags = $mvv_file->tags;
        $this->doc_extvisible = $mvv_file->extern_visible;
        foreach($mvv_file->file_refs as $mvvfile_ref) {
            $this->documents[$mvvfile_ref->file_language] = $mvvfile_ref;
        }
    }

    public function add_files_to_range_action($range_type, $range_id)
    {
        PageLayout::setTitle(_('MVV Dokument Zuordnen'));
        $pre_selected = [];
        foreach(MvvFile::findBySQL('JOIN mvv_files_ranges USING (mvvfile_id) WHERE range_id =?', [$range_id]) as $mvv_file) {
            $pre_selected[] = $mvv_file->mvvfile_id;
        }
        $this->files = MvvFile::findMany($pre_selected);

        $this->pre_selected = $pre_selected;
        $this->range_id = $range_id;
        $this->range_type = $range_type;

        if (Request::submitted('store')) {

            $selected = Request::getArray('files');
            $changes = 0;

            //add new selected files
            foreach ($selected as $add_file) {
                if (!in_array($add_file, $pre_selected )) {
                    $file = MvvFile::find($add_file);
                    $file->addToRange($range_id, $range_type);
                    $changes++;
                }
            }

            //remove deselected files
            foreach ($pre_selected as $rm_file) {
                if (!in_array($rm_file, $selected)) {
                    $file = MvvFile::find($add_file);
                    $file->removeFromRange($range_id);
                    $changes++;
                }
            }

            if ($changes > 0) {
                if ($changes > 1) {
                    PageLayout::postSuccess(sprintf(_('%d Änderungen an der Zuweisung der Dokumente wurden gespeichert.'), $changes));
                } else {
                    PageLayout::postSuccess(_('Die Änderung der Zuweisung der Dokumente wurde gespeichert.'));
                }
            }

            $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable("' . $range_id . '", "' . $range_type . '")');
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }
    }

    public function add_ranges_to_file_action($mvvfile_id, $range_type = null)
    {
        PageLayout::setTitle(_('MVV-Objekte Zuordnen'));
        $mvv_file = MvvFile::find($mvvfile_id);

        $this->range_type = $range_type?:$mvv_file->getRangeType();
        if (!$this->range_type) {
            $this->redirect($this->url_for('/select_range_type',$mvvfile_id));
            return;
        }
        $this->pre_selected = $mvv_file->ranges->pluck('range_id');
        $this->mvv_objects = $this->range_type::findMany($this->pre_selected);
        $this->mvvfile_id = $mvvfile_id;

        if (Request::submitted('store')) {

            $selected = Request::getArray('ranges');
            $changes = 0;

            //add new selected ranges
            foreach ($selected as $add_range) {
                if (!in_array($add_range, $this->pre_selected )) {
                    $mvv_file->addToRange($add_range, $this->range_type);
                    $changes++;
                }
            }

            //remove deselected ranges
            foreach ($this->pre_selected as $rm_range) {
                if (!in_array($rm_range, $selected)) {
                    $mvv_file->removeFromRange($rm_range);
                    $changes++;
                }
            }

            if ($changes > 0) {
                if ($changes > 1) {
                    PageLayout::postSuccess(sprintf(_('%d Änderungen an den Zuweisungen zum Dokument wurden gespeichert.'), $changes));
                } else {
                    PageLayout::postSuccess(_('Die Änderung der Zuweisung zum Dokument wurde gespeichert.'));
                }
            }

            $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable()');
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }
    }

    public function select_range_type_action($mvvfile_id)
    {
        PageLayout::setTitle(_('Art des MVV-Objektes wählen'));
        $this->allowed_object_types = ['AbschlussKategorie','Studiengang','StgteilVersion'];
        $this->mvvfile_id = $mvvfile_id;
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('materialien/files/add_ranges_to_file',$mvvfile_id, Request::get('range_type')));
        }
    }

    private function getTopFolder($mvvfile_id)
    {
        return MVVFolder::findTopFolder($mvvfile_id) ?: MVVFolder::createTopFolder($mvvfile_id);
    }

    private function upload_fileurl($mvvfile_id, $file_url)
    {
        $stg_top_folder = $this->getTopFolder($mvvfile_id);
        $url = trim($file_url);
        $url_parts = parse_url($url);
        if (filter_var($url, FILTER_VALIDATE_URL) !== false && in_array($url_parts['scheme'], ['http', 'https','ftp'])) {

            if (in_array($url_parts['scheme'], ['http', 'https'])) {
                $file = URLFile::create([
                    'url' => $url,
                    'name' => Request::get('name'),
                    'access_type' => "redirect",
                    ''
                ]);
            } else {
                PageLayout::postError(_('Die angegebene URL muss mit http(s) beginnen.'));
            }

            if ($file) {
                return $stg_top_folder->addFile($file);
            } else {
                $file->delete();
            }
        } else {
            PageLayout::postError(_('Die angegebene URL ist ungültig. Die URL muss mit http(s) beginnen.'));
        }

    }

    public function upload_attachment_action()
    {
        if ($GLOBALS['user']->id === "nobody") {
            throw new AccessDeniedException();
        }

        $file = $_FILES['file'];
        $output = [
            'name' => $file['name'],
            'size' => $file['size']];

        $mvvfile_id = Request::option('mvvfile_id');
        $output['mvvfile_id'] = $mvvfile_id;
        $range_id = Request::option('range_id', $mvvfile_id);
        $output['range_id'] = $range_id;
        $file_language = Request::option('file_language');

        $top_folder = $this->getTopFolder($mvvfile_id);

        $user = User::findCurrent();

        $file = StandardFile::create($_FILES['file']);
        $error = $top_folder->validateUpload($file, $GLOBALS['user']->id);
        if ($error != null) {
            $file->delete();
            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }
        $file = $top_folder->addFile($file);

        if (!$file instanceof FileType) {
            $error = _('Ein Systemfehler ist beim Upload aufgetreten.');

            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }

        $mvv_file_fileref = new MvvFileFileref([$mvvfile_id, $file_language]);
        $mvv_file_fileref->fileref_id = $file->getId();
        $mvv_file_fileref->store();

        $output['document_id'] = $file->getId();

        $output['icon'] = Icon::create(
            FileManager::getIconNameForMimeType(
                $file->getMimeType()
            ),
            'clickable'
        )->asImg(['class' => "text-bottom"]);

        $this->render_json($output);
    }

    public function delete_attachment_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $attachment = FileRef::find(Request::option('fileref_id'));
        if ($attachment) {
            if($attachment->delete()) {
                MvvFileFileref::deleteBySQL('mvvfile_id =? AND fileref_id =?',
                [
                    Request::option('mvvfile_id'),
                    Request::option('fileref_id')
                ]);
                PageLayout::postSuccess(_('Datei gelöscht.'));
            }
        }
        $this->render_nothing();
    }

    public function delete_range_action($mvvfile_id, $range_id)
    {
        CSRFProtection::verifyRequest();

        if ($mvvfile_range = MvvFileRange::find([$mvvfile_id, $range_id])) {
            $vacant = $mvvfile_range->position;
            $range_type = $mvvfile_range->range_type;
            if ($mvvfile_range->delete()) {
                foreach (MvvFileRange::findBySQL('range_id = ? ORDER BY position ASC',[$range_id]) as $other_range) {
                    if ($other_range->position > $vacant) {
                        $tmp = $other_range->position;
                        $other_range->position = $vacant;
                        $other_range->store();
                        $vacant = $tmp;
                    }
                }
                PageLayout::postSuccess(_('Die Dokument Zuweisung wurde gelöscht.'));
            }
        }
        $this->range_id = $range_id;
        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable("' . $range_id . '", "' . $range_type . '")');
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }
    }

    public function delete_fileref_action($mvvfile_id, $fileref_id)
    {
        CSRFProtection::verifyRequest();

        if ($mvv_file = MvvFile::find($mvvfile_id)) {
            $vacant = $mvv_file->position;
            $range_id = $mvv_file->range_id;
            if ($mvv_file->delete()) {
                foreach (MvvFile::findBySQL('range_id = ? ORDER BY position ASC',[$range_id]) as $other_file) {
                    if ($other_file->position > $vacant) {
                        $tmp = $other_file->position;
                        $other_file->position = $vacant;
                        $other_file->store();
                        $vacant = $tmp;
                    }
                }
                PageLayout::postSuccess(_('Das Dokument wurde gelöscht.'));
            }
        }
        $this->range_id = $range_id;
        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable("' . $range_id . '")');
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }
    }

    public function delete_all_dokument_action($mvvfile_id)
    {
        CSRFProtection::verifyRequest();

        MvvFile::deleteBySQL('mvvfile_id =?', [$mvvfile_id]);
        MvvFileRange::deleteBySQL('mvvfile_id =?', [$mvvfile_id]);
        MvvFileFileref::deleteBySQL('mvvfile_id =?', [$mvvfile_id]);

        PageLayout::postSuccess(_('Das Dokument und alle seine Zuweisungen wurden gelöscht.'));

        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Document.reload_documenttable("' . $range_id . '")');
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
            return;
        }
    }

    public function details_action($mvvfile_id = null)
    {
        PageLayout::setTitle(_('Dokument Details'));

        $mvv_file = MvvFile::find($mvvfile_id);
        if (!$mvv_file) {
            throw new Trails_Exception(404);
        }

        $this->doc_year = $mvv_file->year;
        $this->doc_type = $mvv_file->type;
        $this->doc_cat = $mvv_file->category;
        $this->doc_tags = $mvv_file->tags;
        $this->doc_extvisible = $mvv_file->extern_visible;
        $this->relations = $mvv_file->getRelations();

        $files = [];
        foreach ($mvv_file->file_refs as $mvv_fileref) {
            $files[$mvv_fileref->file_language] = $mvv_fileref;
        }
        $this->documents = $files;

        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }


    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('dokumente');
            $this->reset_page();
        } else {
            $this->reset_search('dokumente');
            $this->reset_page();
            $this->do_search(
                'MvvFile',
                trim(Request::get('dokument_suche')),
                Request::get('dokument_suche'),
                $this->filter
            );
        }

        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('MvvFile');
        $this->perform_relayed('index');
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
        $this->filter = [];

        //Document name
        $this->sessSet('search_term', Request::get('dokument_suche'));

        if (trim(Request::get('name_filter'))) {
            $this->filter['searchnames'] = trim(Request::get('name_filter'));
        }
        // Semester
        $semester_id = Request::option('semester_filter', 'all');
        if ($semester_id !== 'all') {
            $semester = Semester::find($semester_id);
            $this->filter['start_sem.beginn'] = $semester->beginn;
            $this->filter['end_sem.ende'] = $semester->beginn;
        } else {
            $this->filter['start_sem.beginn'] = -1;
            $this->filter['end_sem.ende'] = -1;
        }

        // responsible Institutes
        $own_institutes = MvvPerm::getOwnInstitutes();
        $institut_filter = Request::option('institut_filter');
        if ($institut_filter) {
            if (count($own_institutes) && !in_array($institut_filter, $own_institutes)) {
                throw new AccessDeniedException();
            }
            $this->filter['mvv_studiengang.institut_id'] = $institut_filter;
        } else {
            // only institutes the user has an assigned MVV role
            $this->filter['mvv_studiengang.institut_id'] = $own_institutes;
        }

        // filtered by object type (Zuordnungen)
        $this->filter['mvv_files_ranges.range_type']
                = mb_strlen(Request::get('zuordnung_filter'))
                ? Request::option('zuordnung_filter') : null;
        if ($this->filter['mvv_files_ranges.range_type']) {
            $this->sessSet('filter', $this->filter);
        } else {
            $this->sessRemove('filter');
        }
        // store filter
        $this->reset_page();
        $this->sessSet('filter', $this->filter);
        $this->redirect($this->url_for('/index'));
    }

    public function reset_filter_action()
    {
        $this->filter = [];
        $this->reset_search();
        $this->sessRemove('filter');
        $this->perform_relayed('index');
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();

        $widget  = new ActionsWidget();
        if (MvvPerm::get('MvvFile')->havePermCreate()) {
            $widget->addLink(
                _('Neues Dokument anlegen'),
                $this->url_for('/new_dokument'),
                Icon::create('file+add')
            )->asDialog('size=auto');
        }
        $sidebar->addWidget($widget);

        if ($this->show_sidebar_search) {
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite können Sie Dokumente verwalten, die mit Studiengängen, Studiengangteilen usw. verknüpft sind.')));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }


    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();

        // Nur Dateien von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        if (!$this->filter['mvv_studiengang.institut_id']) {
            unset($this->filter['mvv_studiengang.institut_id']);
        }
        $own_institutes = MvvPerm::getOwnInstitutes();
        $institute_filter = array_merge(
            [
                'mvv_studiengang.institut_id' => $own_institutes
            ],
            $this->filter
        );
        unset($institute_filter['searchnames']);

        $semesters = new SimpleCollection(array_reverse(Semester::getAll()));
        $filter_template = $template_factory->render('shared/filter', [
            'name_search'        => true,
            'selected_name'      => $this->filter['searchnames'],
            'name_caption'       => _('Name, Kategorie, Schlagwort'),
            'semester'           => $semesters,
            'selected_semester'  => $semesters->findOneBy('beginn', $this->filter['start_sem.beginn'])->id,
            'default_semester'   => Semester::findCurrent()->id,
            'institute'          => MvvFile::getAllAssignedInstitutes('name', 'ASC', $institute_filter),
            'institute_count'    => 'count_objects',
            'selected_institut'  => $this->filter['mvv_studiengang.institut_id'],
            'zuordnungen'        => MvvFile::getAllRelations($this->search_result['MvvFile']),
            'selected_zuordnung' => $this->filter['mvv_files_ranges.range_type'],
            'action'             => $this->url_for('/set_filter'),
            'action_reset'       => $this->url_for('/reset_filter')]
        );

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle('Filter');
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget,"filter");
    }

    public function dispatch_action($class_name, $id)
    {
        switch (mb_strtolower($class_name)) {
            case 'fach':
                $this->redirect('fachabschluss/faecher/fach/' . $id);
                break;
            case 'abschlusskategorie':
                $this->redirect('fachabschluss/kategorien/kategorie/' . $id);
                break;
            case 'abschluss':
                $this->redirect('fachabschluss/abschluesse/abschluss/' . $id);
                break;
            case 'studiengangteil':
                $this->redirect('studiengaenge/studiengangteile/stgteil/' . $id);
                break;
            case 'studiengang':
                $this->redirect('studiengaenge/studiengaenge/studiengang/' . $id);
                break;
            case 'stgteilversion':
                $version = StgteilVersion::get($id);
                if ($version->isNew()) {
                    $this->flash_set('error', dgettext('MVVPlugin', 'Unbekannte Version'));
                    $this->redirect('studiengaenge/studiengaenge');
                }
                $this->redirect('studiengaenge/studiengangteile/version/'
                        . join('/', [$version->stgteil_id, $version->getId()]));
                break;
            default:
                $this->redirect('studiengaenge/studiengaenge/');
        }
    }

    public function sort_action($range_type, $range_id)
    {
        if (Request::submitted('order')) {
            $ordered = json_decode(Request::get('ordering'), true);
            if (is_array($ordered)) {
                $ok = false;
                foreach ($ordered as $p => $mvvfile_id) {
                    if ($mvv_file_fileref = MvvFileRange::find([$mvvfile_id, $range_id])) {
                        $mvv_file_fileref->position = $p + 1;
                        $ok += $mvv_file_fileref->store();
                    }
                }
                if (Request::isXhr()) {
                    header('X-Dialog-Close: 1');
                    exit;
                }
            }
        }
        $this->range_id = $range_id;
        $this->range_type = $range_type;
        $this->mvv_files = MvvFile::findBySQL('JOIN mvv_files_ranges USING (mvvfile_id) WHERE range_id = ? ORDER BY position ASC', [$range_id]);
        PageLayout::setTitle(_('Reihenfolge ändern'));
    }

    /**
     * Search for abschlusskategorie by given search term.
     */
    public function search_abschlusskategorie_action()
    {
        $sword = Request::get('term');

        $query = "SELECT `kategorie_id`, `name`
             FROM `mvv_abschl_kategorie`
             WHERE (`name` LIKE :keyword
                    OR `name_kurz` LIKE :keyword)
            ORDER BY `name` ASC
            LIMIT 10";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([
            ':keyword'     => '%' . $sword . '%'
        ]);

        $res = ['results' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $abschlusskat_id) {
            $abschlusskat = AbschlussKategorie::find($abschlusskat_id);
            $res['results'][] = [
                'id'   => $abschlusskat->id,
                'text' => $abschlusskat->getDisplayName()
            ];
        }

        $this->render_text(json_encode($res));
    }

    /**
     * Search for studiengang by given search term.
     */
    public function search_studiengang_action()
    {
        $sword = Request::get('term');

        $query = "SELECT `studiengang_id`, `name`
             FROM `mvv_studiengang`
             WHERE (`name` LIKE :keyword
                    OR `name_kurz` LIKE :keyword)
                    AND `stat` IN (:stat)
            ORDER BY `name` ASC
            LIMIT 10";

        $stat = array_keys(array_filter(
            $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'],
            function ($v) {
                return $v['public'];
            }
        ));

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([
            ':keyword'     => '%' . $sword . '%',
            ':stat'        => $stat
        ]);

        $res = ['results' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $studiengang_id) {
            $studiengang = Studiengang::find($studiengang_id);
            $res['results'][] = [
                'id'   => $studiengang->id,
                'text' => $studiengang->getDisplayName()
            ];
        }

        $this->render_text(json_encode($res));
    }

    /**
     * Search for studiengang by given search term.
     */
    public function search_modul_action()
    {
        $sword = Request::get('term');

        $query = "SELECT `mvv_modul`.`modul_id`, `mvv_modul`.`code`
             FROM `mvv_modul`
             INNER JOIN `mvv_modul_deskriptor` USING(`modul_id`)
             WHERE (`mvv_modul`.`code` LIKE :keyword
                    OR `mvv_modul_deskriptor`.`bezeichnung` LIKE :keyword)
                    AND `mvv_modul`.`stat` IN (:stat)
            ORDER BY `mvv_modul`.`code` ASC
            LIMIT 10";

        $stat = array_keys(array_filter(
            $GLOBALS['MVV_MODUL']['STATUS']['values'],
            function ($v) {
                return $v['public'];
            }
        ));

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([
            ':keyword'     => '%' . $sword . '%',
            ':stat'        => $stat
        ]);

        $res = ['results' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $modul_id) {
            $modul = Modul::find($modul_id);
            $res['results'][] = [
                'id'   => $modul->id,
                'text' => $modul->getDisplayName()
            ];
        }

        $this->render_text(json_encode($res));
    }

    /**
     * Search for file by given search term.
     */
    public function search_file_action()
    {
        $sword = Request::get('term');

        $query = "SELECT `mvv_files`.`mvvfile_id`, `mvv_files_filerefs`.`name`
             FROM `mvv_files`
             INNER JOIN `mvv_files_filerefs` USING(`mvvfile_id`)
             INNER JOIN `file_refs` ON (`file_refs`.`id` = `mvv_files_filerefs`.`fileref_id`)
             WHERE (`mvv_files_filerefs`.`name` LIKE :keyword
                    OR `file_refs`.`name` LIKE :keyword)
            ORDER BY `mvv_files_filerefs`.`name` ASC
            LIMIT 10";

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([
            ':keyword'     => '%' . $sword . '%'
        ]);

        $res = ['results' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $mvvfile_id) {
            $mvvfile = MvvFile::find($mvvfile_id);
            $res['results'][] = [
                'id'   => $mvvfile->id,
                'text' => $mvvfile->getDisplayName()
            ];
        }

        $this->render_text(json_encode($res));
    }

    public function new_dokument_action()
    {
        PageLayout::setTitle(_('Art des MVV-Objektes wählen'));
        $this->allowed_object_types = ['AbschlussKategorie','Studiengang'];
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('materialien/files/select_range', Request::get('range_type')));
        }
    }

    public function select_range_action($range_type)
    {
        PageLayout::setTitle(_('MVV-Objekt wählen'));
        $this->range_type = $range_type;
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('materialien/files/add_dokument', 'index', $range_type, implode(',', Request::getArray('range_id'))));
        }
    }
}
