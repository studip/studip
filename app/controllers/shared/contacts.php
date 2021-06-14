<?php
/**
 * contacts.php - Shared_ContactsController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */
class Shared_ContactsController extends MVVController
{
    public $filter = [];
    private $show_sidebar_search = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->filter = $this->sessGet('filter', []);

        // set navigation
        Navigation::activateItem($this->me . '/contacts/index');

        $this->action = $action;

        if (Request::isXhr()) {
            $this->set_layout(null);
        }
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Ansprechpartner'));

        $this->initPageParams();
        $this->initSearchParams();
        $search_result = $this->getSearchResult('MvvContact');

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
        $this->sessSet('filter', $this->filter);

        if (Request::option('range_id')) {
            $this->range_id = Request::option('range_id');
            $this->filter['mvv_contacts_ranges.range_id'] = $this->range_id;
            $this->sortby = 'position';
        }

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        if (!$this->filter['mvv_modul_inst.institut_id']) {
            unset($this->filter['mvv_modul_inst.institut_id']);
        }
        if ($search_result) {
            $this->filter['mvv_contacts.contact_id'] = $search_result;
        }

        $own_institutes = MvvPerm::getOwnInstitutes();
        if ($this->filter['mvv_modul_inst.institut_id']) {
            if ($own_institutes) {
                $this->filter['mvv_modul_inst.institut_id']  = array_intersect(
                        $this->filter['mvv_modul_inst.institut_id'],
                        MvvPerm::getOwnInstitutes());
            }
        } else {
            $this->filter['mvv_modul_inst.institut_id'] = MvvPerm::getOwnInstitutes();
        }
        $this->filter['mvv_studiengang.institut_id'] = $this->filter['mvv_modul_inst.institut_id'];

        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $this->contacts = MvvContact::getAllEnriched(
            $this->sortby,
            $this->order,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1),
            $this->filter
        );

        $this->contact_id = Request::option('contact_id');
        if ($this->contact_id) {
            $contact_range = MvvContactRange::findOneBySQL('contact_id=?', [$this->contact_id]);
            if (!$contact_range) {
                throw new Trails_Exception(404);
            }
            $this->relations = $contact_range->getRelations($this->filter);
            $this->origin = 'index';
        }
        $this->count = MvvContact::getCount($this->filter);
        $this->show_sidebar_search = true;
        $this->setSidebar();
    }

    public function range_action()
    {
        PageLayout::setTitle(_('Verwaltung der Ansprechpartner'));

        $this->sortby = 'position';
        $this->order = $this->order ?: 'ASC';
        if (Request::submitted('range_id')) {
            $this->range_id = Request::option('range_id');
            $this->range_type = MvvContactRange::getRangeTypeByRangeId($this->range_id);
        }
        $this->contacts = MvvContactRange::findBySQL('range_id = ? ORDER BY position ASC', [$this->range_id]);
        if (!isset($this->contact_id)) {
            $this->contact_id = null;
        }
    }

    public function details_action($origin, $contact_id)
    {
        $contact_range = MvvContactRange::findOneBySQL('contact_id=?', [$contact_id]);
        if (!$contact_range) {
            throw new Trails_Exception(404);
        }

        $this->relations = $contact_range->getRelations($this->filter);
        $this->origin = $origin;

        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    public function new_ansprechpartner_action()
    {
        PageLayout::setTitle(_('Art des MVV-Objektes wählen'));
        $this->allowed_object_types = [
            'Modul',
            'Studiengang',
            'StudiengangTeil'
        ];
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('shared/contacts/select_range', Request::get('range_type')));
        }
    }

    public function select_range_action($range_type)
    {
        PageLayout::setTitle(_('MVV-Objekt wählen'));
        $this->range_type = $range_type;
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('shared/contacts/add_ansprechpartner','index', $range_type, implode(',', Request::getArray('range_id'))));
        }
    }

    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('contacts');
            $this->reset_page();
        } else {
            $this->reset_search('contacts');
            $this->reset_page();
            $this->do_search('MvvContact',
                    trim(Request::get('ansprechpartner_suche_parameter')),
                    Request::get('ansprechpartner_suche'), $this->filter);
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('MvvContact');
        $this->perform_relayed('index');
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
        $this->filter = [];

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

        // status (intern/extern)
        $this->filter['mvv_contacts.contact_status'] = trim(Request::get('status_filter')) ? Request::option('status_filter') : null;

        // responsible Institutes
        $own_institutes = MvvPerm::getOwnInstitutes();
        $institut_filter = Request::option('institut_filter');
        if ($institut_filter) {
            if (count($own_institutes) && !in_array($institut_filter, $own_institutes)) {
                throw new AccessDeniedException();
            }
            $this->filter['mvv_modul_inst.institut_id'] = $institut_filter;
            $this->filter['mvv_studiengang.institut_id'] = $institut_filter;
        } else {
            // only institutes the user has an assigned MVV role
            $this->filter['mvv_modul_inst.institut_id'] = $own_institutes;
            $this->filter['mvv_studiengang.institut_id'] = $own_institutes;
        }

        // category (object specific)
        if (Request::get('kategorie_filter')) {
            list($this->filter['mvv_contacts_ranges.category'],
                    $this->filter['mvv_contacts_ranges.range_type']) = explode('__@type__', Request::get('kategorie_filter'));
        } else {
            // filtered by object type (Zuordnungen)
            $this->filter['mvv_contacts_ranges.range_type'] = Request::option('zuordnung_filter');
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

        $widget = new ActionsWidget();
        if (MvvPerm::get('MvvContactRange')->havePermCreate()) {
            $widget->addLink(
                _('Neuen Ansprechpartner anlegen'),
                $this->url_for('/new_ansprechpartner'),
                Icon::create('headache+add')
            )->asDialog('size=auto');
        }
        $widget->addLink(
            _('Liste exportieren (CSV)'),
            $this->url_for('/export_csv'),
            Icon::create('download')
        );
        $sidebar->addWidget($widget);

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite können Sie Ansprechpartner verwalten, die mit Studiengängen, Studiengangteilen und Modulen verknüpft sind.')));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }

    /**
     * adds the search funtion to the sidebar
     */
    private function sidebar_search()
    {
        $ids = MvvContact::getIdsFiltered($this->filter);
        $query = "SELECT DISTINCT mvv_contacts.contact_id, IFNULL(CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname), IFNULL(Institute.Name, IFNULL(mvv_extern_contacts.name, ''))) AS `name`
            FROM mvv_contacts_ranges
                INNER JOIN mvv_contacts USING (contact_id)
                LEFT JOIN auth_user_md5 ON (contact_id = user_id)
                LEFT JOIN Institute ON (contact_id = Institut_id)
                LEFT JOIN mvv_extern_contacts ON (contact_id = extern_contact_id)
            WHERE (auth_user_md5.username LIKE :input
                OR auth_user_md5.Vorname LIKE :input
                OR auth_user_md5.Nachname LIKE :input
                OR mvv_extern_contacts.name LIKE :input)
                AND mvv_contacts_ranges.contact_id IN ('" . implode("','", $ids['contacts']) . "')
                AND mvv_contacts_ranges.category IN ('" . implode("','", $ids['categories']) . "')
                AND mvv_contacts_ranges.range_id IN ('" . implode("','", $ids['ranges']) . "')";
        $search_term = $this->search_term ? $this->search_term : _('Ansprechpartner suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(
            _('Ansprechpartner suchen'),
            'ansprechpartner_suche',
            true,
            new SQLSearch($query, $search_term, 'contact_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
       $template_factory = $this->get_template_factory();

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        if (!$this->filter['mvv_modul_inst.institut_id']) {
            unset($this->filter['mvv_modul_inst.institut_id']);
        }
        $own_institutes = MvvPerm::getOwnInstitutes();
        $institute_filter = array_merge(
            [
                'mvv_modul_inst.gruppe'       => 'hauptverantwortlich',
                'mvv_modul_inst.institut_id'  => $own_institutes,
                'mvv_studiengang.institut_id' => $own_institutes
            ],
            $this->filter
        );

        $semesters = new SimpleCollection(array_reverse(Semester::getAll()));
        $filter_template = $template_factory->render('shared/filter', [
            'semester'           => $semesters,
            'selected_semester'  => $semesters->findOneBy('beginn', $this->filter['start_sem.beginn'])->id,
            'default_semester'   => Semester::findCurrent()->id,
            'institute'          => MvvContact::getAllAssignedInstitutes('name', 'ASC', $institute_filter),
            'institute_count'    => 'count_objects',
            'selected_institut'  => $this->filter['mvv_modul_inst.institut_id'],
            'zuordnungen'        => MvvContact::getAllRelations($this->search_result['MvvContact']),
            'selected_zuordnung' => $this->filter['mvv_contacts_ranges.range_type'],
            'kategorien'         => $this->findCategoriesByIds(),
            'selected_kategorie' => "{$this->filter['mvv_contacts_ranges.category']}__@type__{$this->filter['mvv_contacts_ranges.range_type']}",
            'status'             => $this->findStatusByIds(),
            'selected_status'    => $this->filter['mvv_contacts.contact_status'],
            'status_array'       => ['intern' => ['name' => _('Intern')], 'extern' => ['name' =>_('Extern')]],
            'action'             => $this->url_for('/set_filter'),
            'action_reset'       => $this->url_for('/reset_filter')
        ]);

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle('Filter');
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget, 'filter');
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
            case 'modul':
                $this->redirect('module/module/modul/' . $id);
                break;
            default:
                $this->redirect('studiengaenge/studiengaenge/');
        }
    }

    public function add_ansprechpartner_action($origin = 'index', $range_type = null, $range_id = null, $user_id = null, $category = null)
    {
        PageLayout::setTitle(_('Ansprechpartner des Studienganges'));

        $this->extcontact_search_obj = new SQLSearch("SELECT extern_contact_id, mvv_extern_contacts.name "
                . "FROM mvv_extern_contacts "
                . "WHERE mvv_extern_contacts.name LIKE :input "
                . "ORDER BY mvv_extern_contacts.name ASC",
                _('Nutzer suchen'));

        $ext_contact = new MvvExternContact();
        $this->ext_contact = $ext_contact;

        if (Request::submitted('store_ansprechpartner')) {

            if (!$user_id) {
                if (Request::get('exansp_name')) {
                    $ext_contact->name = Request::i18n('exansp_name');
                    $ext_contact->vorname = Request::get('exansp_vorname');
                    $ext_contact->homepage = Request::i18n('exansp_web');
                    $ext_contact->mail = Request::get('exansp_mail');
                    $ext_contact->tel = Request::get('exansp_tel');
                    if ($ext_contact->store()) {
                        $user_id = $ext_contact->extern_contact_id;
                    }
                } else {
                    switch (Request::get('ansp_status')) {
                        case 'extern':
                            $user_id = Request::option('ansp_ext_user');
                            break;
                        case 'intern':
                            $user_id = Request::option('ansp_user');
                            break;
                        case 'institution':
                            $user_id = Request::option('ansp_inst');
                            break;
                        default:
                            $user_id = null;
                            break;
                    }
                }
                if (!$user_id) {
                    if (Request::isXhr()) {
                        header('X-Dialog-Close: 1');
                        exit;
                    } else {
                        return;
                    }
                }
            }

            $mvv_contact = MvvContact::find($user_id);
            if (!$mvv_contact) {
                $mvv_contact = new MvvContact();
                $mvv_contact->contact_id = $user_id;
            }

            $mvv_contact->contact_status = Request::get('ansp_status');
            $mvv_contact->alt_mail = Request::get('ansp_altmail');
            $ansp_type = Request::get('ansp_typ') ?: '';

            if (Request::get('contact_range_id')) {
                if($mvv_cr = MvvContactRange::find(Request::get('contact_range_id'))) {
                    $mvv_cr->type = $ansp_type;
                    $mvv_cr->category = Request::get('ansp_kat');
                    $contactrange_stored = $mvv_cr->store();
                }
            } else {
                $range_ids = explode(',', $range_id);
                $kat = Request::get('ansp_kat');
                foreach ($range_ids as $range_id) {
                    $range_added = $mvv_contact->addRange($range_id, $range_type, $ansp_type, $kat);
                }
            }

            $contact_stored = $mvv_contact->store();

            if ($range_added || $contact_stored || $contactrange_stored) {
                PageLayout::postSuccess(_('Der Ansprechpartner wurde gespeichert.'));
                if ($origin === 'range') {
                    $this->response->add_header('X-Dialog-Execute', 'STUDIP.MVV.Contact.reload_contacttable("' . $range_id . '", "' . $range_type . '")');
                    $this->response->add_header('X-Dialog-Close', 1);
                    $this->render_nothing();
                } else {
                    $this->response->add_header('X-Dialog-Close', 1);
                    $this->response->add_header('X-Location', $this->url_for('/index', ['contact_id' => $mvv_contact->id]));
                }
                return;
            }
        }

        if ($user_id && $category) {
            $contact_range = MvvContactRange::findOneBySQL("contact_id =? AND range_id =? AND category=?",[$user_id, $range_id, $category]);
            $this->ansp_status = $contact_range->contact->contact_status;
            $this->ansp_altmail = $contact_range->contact->alt_mail;
            $this->ansp_typ = $contact_range->type;
            $this->ansp_kat = $contact_range->category;
            $this->ansp_name = $contact_range->name;
        } else {
            $this->ansp_name = '';
        }

        $this->contact_range_id = ($contact_range) ? $contact_range->contact_range_id : '';
        $this->range_type = $range_type;
        $this->range_id = $range_id;
        $this->user_id = $user_id;
        $this->origin = $origin;
    }

    public function delete_range_action($range_id, $contact_id, $category)
    {
        CSRFProtection::verifyRequest();

        $range = MvvContactRange::findOneBySQL("contact_id =? AND range_id =? AND category=?",[$contact_id, $range_id, $category]);
        $contact = $range->contact;

        if (!($range && MvvPerm::get($contact)->haveFieldPerm('ranges', MvvPerm::PERM_CREATE))) {
            throw new AccessDeniedException();
        }
        if ($contact->deleteRange($range)) {
            PageLayout::postSuccess(_('Die Verknüpfung wurde gelöscht.'));
        }

        $this->range_id = $range_id;
        if (Request::isXhr()) {
            header('X-Dialog-Close: 1');
            exit;
        }
    }

    public function delete_all_ranges_action($contact_id = null)
    {
        CSRFProtection::verifyRequest();

        $contact = MvvContact::find($contact_id);
        if (!($contact && MvvPerm::get($contact)->haveFieldPerm('ranges', MvvPerm::PERM_CREATE))) {
            throw new AccessDeniedException();
        }

        if (MvvContactRange::deleteBySQL('contact_id = ?',[$contact_id])) {
            PageLayout::postSuccess(sprintf(
                _('Alle Verknüpfungen von %s gelöscht.'),
                htmlReady($contact->getDisplayName())
            ));
        }
        if (Request::isXhr()) {
            header('X-Dialog-Close: 1');
            exit;
        }
    }

    public function delete_extern_contact_action($user_id = null)
    {
        CSRFProtection::verifyRequest();

        if ($mvv_ext_contact = MvvExternContact::find($user_id)) {
            $mvv_ext_contact->delete();
            foreach (MvvContactRange::findBySQL('contact_id = ?',[$user_id]) as $mvv_contact_range) {
                $mvv_contact_range->delete();
            }
            PageLayout::postSuccess(sprintf(
                _('Externer Ansprechpartner %s wurde gelöscht.'),
                htmlReady($mvv_ext_contact->getDisplayName())
            ));
        }
        if (Request::isXhr()) {
            header('X-Dialog-Close: 1');
            exit;
        }
    }

    public function sort_action($range_id = null)
    {
        if (Request::submitted('order')) {
            $ordered = json_decode(Request::get('ordering'), true);
            if (is_array($ordered)) {
                $ok = false;
                foreach ($ordered as $p => $user_kat_id) {
                    $usr_kat_split = explode('_', $user_kat_id['id']);
                    if ($mvv_contact_range = MvvContactRange::findOneBySQL("contact_id =? AND range_id =? AND category=?",[$usr_kat_split[0], $range_id, $usr_kat_split[1]])) {
                        $mvv_contact_range->position = $p + 1;
                        $ok += $mvv_contact_range->store();
                    }
                }
                if (Request::isXhr()) {
                    header('X-Dialog-Close: 1');
                    exit;
                }
            }
        }
        $this->range_id = $range_id;
        $this->contacts = MvvContactRange::findBySQL('range_id = ? ORDER BY position ASC', [$range_id]);
        PageLayout::setTitle(_('Reihenfolge ändern'));
    }

    public function add_ranges_to_contact_action($user_id, $range_type = null)
    {
        PageLayout::setTitle(_('MVV-Objekte Zuordnen'));
        $mvv_contact = MvvContact::find($user_id);

        $this->range_type = $range_type;
        if (!$this->range_type) {
            $this->redirect($this->url_for('/select_range_type',$user_id));
            return;
        }

        $this->pre_selected = $mvv_contact->ranges->pluck('range_id');
        $this->mvv_objects = $range_type::findMany($this->pre_selected);
        $this->mvvcontact_id = $user_id;

        $this->selected_sem_end = $this->filter['end_sem.ende'];
        $this->selected_inst = $this->filter['mvv_studiengang.institut_id'];

        if (Request::submitted('store')) {
            $selected = Request::getArray('ranges');
            $category = Request::get('ansp_kat');
            $changes = 0;

            //add new selected ranges
            foreach ($selected as $add_range) {
                    if ($mvv_contact->addRange($add_range, $range_type, Request::get('ansp_typ', ''), $category)) {
                        $changes++;
                    }
            }

            if ($changes > 0) {
                PageLayout::postSuccess(
                        ngettext('Die Änderung der Zuweisung des Ansprechpartners wurde gespeichert.',
                        sprintf('%d Änderungen an der Zuweisung des Ansprechpartners wurden gespeichert.', $changes),
                        $changes));
            }
            $this->relocate('shared/contacts/index', ['contact_id' => $mvv_contact->id]);
            return;
        }
    }

    public function select_range_type_action($user_id)
    {
        PageLayout::setTitle(_('Art des MVV-Objektes wählen'));
        $this->allowed_object_types = [
            'Studiengang',
            'Modul',
            'StudiengangTeil'
        ];
        $this->mvvcontact_id = $user_id;
        if (Request::submitted('store')) {
            $this->redirect($this->url_for('shared/contacts/add_ranges_to_contact',$user_id, Request::get('range_type')));
        }
    }

    /**
     * Search for studiengang by given search term.
     */
    public function search_studiengang_action()
    {
        $term = str_replace('%', '', Request::get('term'));
        if (!trim($term)) {
            return [];
        }
        $stat = array_keys(array_filter(
            $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'],
            function ($v) {
                return $v['visible'];
            }
        ));
        $filter = [
            'mvv_studiengang.stat'        => $stat,
            'mvv_studiengang.institut_id' => $this->filter['mvv_modul_inst.institut_id'],
            'start_sem.beginn'            => $this->filter['start_sem.beginn'],
            'end_sem.ende'                => $this->filter['end_sem.ende']
        ];

        $term = '%' . $term . '%';
        $studycourses = Studiengang::getEnrichedByQuery('
            SELECT `mvv_studiengang`.*
            FROM `mvv_studiengang`
                LEFT JOIN `semester_data` `start_sem` ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                LEFT JOIN `semester_data` `end_sem` ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)
            WHERE (`mvv_studiengang`.`name` LIKE :term
                OR `mvv_studiengang`.`name_kurz` LIKE :term)
                ' . Studiengang::getFilterSql($filter) . '
            ORDER BY `name` ASC LIMIT 10', [':term' => $term]);

        $res = [];
        foreach ($studycourses as $studycourse) {
            $res['results'][] = [
                'id' => $studycourse->id,
                'text' => $studycourse->getDisplayName()
            ];
        }

        $this->render_json($res);
    }


    /**
     * Search for Studiengangteil by given search term.
     */
    public function search_stgteil_action()
    {
        $term = str_replace('%', '', Request::get('term'));
        if (!trim($term)) {
            return [];
        }
        $stat = array_keys(array_filter(
            $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'],
            function ($v) {
                return $v['visible'];
            }
        ));
        $filter = [
            'mvv_studiengang.institut_id' => $this->filter['mvv_modul_inst.institut_id'],
            'start_sem.beginn'            => $this->filter['start_sem.beginn'],
            'end_sem.ende'                => $this->filter['end_sem.ende']
        ];

        $term = '%' . $term . '%';
        $stgteile = StudiengangTeil::getEnrichedByQuery('
            SELECT `mvv_stgteil`.*
            FROM `mvv_stgteil`
                LEFT JOIN `mvv_stg_stgteil` USING (`stgteil_id`)
                LEFT JOIN `mvv_studiengang` USING (`studiengang_id`)
                LEFT JOIN `fach` USING (`fach_id`)
                LEFT JOIN `semester_data` `start_sem` ON (`mvv_studiengang`.`start` = `start_sem`.`semester_id`)
                LEFT JOIN `semester_data` `end_sem` ON (`mvv_studiengang`.`end` = `end_sem`.`semester_id`)
            WHERE (`fach`.`name` LIKE :term)
                ' . StudiengangTeil::getFilterSql($filter) . '
            ORDER BY `fach`.`name` ASC LIMIT 10', [':term' => $term]);

        $res = [];
        foreach ($stgteile as $stgteil) {
            $res['results'][] = [
                'id' => $stgteil->id,
                'text' => $stgteil->getDisplayName()
            ];
        }

        $this->render_json($res);
    }

    /**
     * Search for module by given search term.
     */
    public function search_modul_action()
    {
        $term = str_replace('%', '', Request::get('term'));
        if (!trim($term)) {
            return $this->render_json([]);
        }
        $stat = array_keys(array_filter(
            $GLOBALS['MVV_MODUL']['STATUS']['values'],
            function ($v) {
                return $v['visible'];
            }
        ));
        $filter = [
            'mvv_modul.stat'             => $stat,
            'mvv_modul_inst.institut_id' => $this->filter['mvv_modul_inst.institut_id'],
            'start_sem.beginn'           => $this->filter['start_sem.beginn'],
            'end_sem.ende'               => $this->filter['end_sem.ende']
        ];

        $term = '%' . $term . '%';
        $modules = Modul::getEnrichedByQuery("
                SELECT mvv_modul.*,
                    CONCAT(mvv_modul_deskriptor.bezeichnung, ' (', code, ')') AS name
                FROM mvv_modul
                    LEFT JOIN mvv_modul_deskriptor USING(modul_id)
                    LEFT JOIN mvv_modul_inst
                        ON (mvv_modul.modul_id = mvv_modul_inst.modul_id)
                    LEFT JOIN semester_data as start_sem
                        ON (mvv_modul.start = start_sem.semester_id)
                    LEFT JOIN semester_data as end_sem
                        ON (mvv_modul.end = end_sem.semester_id)
                WHERE (code LIKE :term OR mvv_modul_deskriptor.bezeichnung LIKE :term) "
                . Modul::getFilterSql($filter) . "
                ORDER BY name ASC LIMIT 10",
                [':term' => $term]
        );
        $res = [];
        foreach ($modules as $module) {
                $res['results'][] = [
                    'id' => $module->id,
                    'text' => $module->getDisplayName()
                ];
        }

        $this->render_json($res);
    }

    /**
     * Exports current list as CSV.
     */
    public function export_csv_action()
    {
        $ids = MvvContact::getIdsFiltered($this->filter);
        $stmt = DBManager::get()->prepare("SELECT
            IFNULL(IFNULL(`mec`.`name`, `Institute`.`Name`), CONCAT(`aum`.`Nachname`, ', ', `aum`.`Vorname`)) AS `name`,
            IFNULL(`aum`.`Email`, `mec`.`mail`) AS `email`,
            `mec`.`homepage` AS `homepage`,
            `mvv_contacts`.`contact_status` AS `status`,
            `mcr`.`range_type`, `mcr`.`type`, `mcr`.`category`,
            `mvv_contacts`.`alt_mail`,
            `mec`.`tel` AS `phone`,
            IFNULL(`mvv_modul`.`code`, `mvv_studiengang`.`name_kurz`) AS `shortname`,
            IFNULL(`mvv_modul_deskriptor`.`bezeichnung`, `mvv_studiengang`.`name`) AS `longname`
            FROM `mvv_contacts_ranges` AS `mcr`
            INNER JOIN `mvv_contacts` USING(`contact_id`)
            LEFT JOIN `auth_user_md5` AS `aum` ON `mvv_contacts`.`contact_id` = `aum`.`user_id`
            LEFT JOIN `mvv_extern_contacts` AS `mec` ON `mvv_contacts`.`contact_id` = `mec`.`extern_contact_id`
            LEFT JOIN `Institute` ON `mvv_contacts`.`contact_id` = `Institute`.`Institut_id`
            LEFT JOIN `mvv_modul` ON `mcr`.`range_id` = `mvv_modul`.`modul_id`
            LEFT JOIN `mvv_modul_deskriptor` USING(`modul_id`)
            LEFT JOIN `mvv_studiengang` ON `mcr`.`range_id` = `mvv_studiengang`.`studiengang_id`
            WHERE `mcr`.`contact_range_id` IN(:contacts_ranges)
            ORDER BY `name`, `range_type`, category");
        $stmt->execute($ids);

        $data = [];
        foreach ($stmt->fetchALL(PDO::FETCH_ASSOC) as $row) {
            $row['status'] = MvvContact::getStatusNames()[$row['status']];
            if ($row['range_type'] === 'Studiengang') {
                $row['type'] = $GLOBALS['MVV_CONTACTS']['TYPE']['values'][$row['type']]['name'];
                $row['category'] = $GLOBALS['MVV_STUDIENGANG']['PERSONEN_GRUPPEN']['values'][$row['category']]['name'];
            } else if ($row['range_type'] === 'Modul') {
                $row['type'] = '';
                $row['category'] = $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$row['category']]['name'];
            } else {
                $row['type'] = '';
                $row['category'] = $GLOBALS['MVV_STGTEIL']['PERSONEN_GRUPPEN']['values'][$row['category']]['name'];
            }
            $data[] = array_values($row);
        }
        $captions = [
            _('Name'),
            _('E-Mail'),
            _('Homepage'),
            _('Status'),
            _('Objekttyp'),
            _('Ansprechpartnertyp'),
            _('Kategorie'),
            _('E-Mail (alternativ)'),
            _('Telefon'),
            _('Code'),
            _('Objekname')
        ];

        $this->render_csv(
            array_merge([$captions], $data),
            'Contacts_Export.csv'
        );
    }

    private function findStatusByIds()
    {
        $ids = MvvContact::getIdsFiltered($this->filter);
        if (count($ids)) {
            $stmt = DBManager::get()->prepare('
                SELECT `contact_status`,
                COUNT(DISTINCT `mvv_contacts`.`contact_id`) AS `count_contacts`
                FROM `mvv_contacts_ranges`
                INNER JOIN `mvv_contacts` USING(`contact_id`)
                WHERE `contact_id` IN (:contacts)
                    AND `category` IN (:categories) AND `range_id` IN (:ranges)
                GROUP BY `contact_status`');
            $stmt->execute($ids);
        } else {
            $stmt = DBManager::get()->prepare('
                SELECT `contact_status`,
                COUNT(`contact_id`) AS `count_contacts`
                FROM `mvv_contacts`
                GROUP BY `contact_status`');
            $stmt->execute();
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
            $result[$status['contact_status']] = [
                'name' => MvvContact::getStatusNames()[$status['contact_status']],
                'count_objects' => $status['count_contacts']
            ];
        }
        return $result;
    }

    private function findCategoriesByIds()
    {
        $ids = MvvContact::getIdsFiltered($this->filter);
        if (count($ids)) {
            $stmt = DBManager::get()->prepare("
                SELECT `category`, `range_type`,
                COUNT(DISTINCT `mvv_contacts`.`contact_id`) AS `count_contacts`
                FROM `mvv_contacts_ranges`
                INNER JOIN `mvv_contacts` USING(`contact_id`)
                WHERE `contact_id` IN (:contacts)
                    AND `category` IN (:categories) AND `range_id` IN (:ranges)
                GROUP BY `category`, `range_type`");
            $stmt->execute($ids);
        } else {
            $stmt = DBManager::get()->prepare("
                SELECT `category`, `range_type`,
                COUNT(`contact_id`) AS `count_contacts`
                FROM `mvv_contacts_ranges`
                INNER JOIN `mvv_contacts_ranges` USING(`contact_id`)
                GROUP BY `category`, `range_type`");
            $stmt->execute();
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $range_categories = MvvContactRange::getCategoriesByRangetype($category['range_type']);
            $range_category = new stdClass();
            $range_category->id = $category['category'] . '__@type__' . $category['range_type'];
            $range_category->name = $range_categories[$category['category']]['name'];
            $range_category->count_objects = $category['count_contacts'];
            $result[$range_category->id] = $range_category;
        }
        return $result;
    }
}
