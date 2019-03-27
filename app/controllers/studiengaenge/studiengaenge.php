<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Studiengaenge_StudiengaengeController extends MVVController
{
    public $filter = [];
    protected $show_sidebar_search = false;
    protected $show_sidebar_filter = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        Navigation::activateItem($this->me . '/studiengaenge/studiengaenge');
        $this->filter = $this->sessGet('filter', []);
        $this->action = $action;
    }

    public function after_filter($action, $args) {
        parent::after_filter($action, $args);
    }

    public function index_action($studiengang_id = null)
    {
        PageLayout::setTitle(_('Verwaltung der Studiengänge'));

        $this->initPageParams();
        $this->initSearchParams();

        $search_result = $this->getSearchResult('Studiengang');

        // set default semester filter
        if (!isset($this->filter['start_sem.beginn']) || !isset($this->filter['end_sem.ende'])) {
            $sem_time_switch = Config::get()->getValue('SEMESTER_TIME_SWITCH');
            // switch semester according to time switch
            // (n weeks before next semester)
            $current_sem = Semester::findByTimestamp(time()
                    + $sem_time_switch * 7 * 24 * 3600);
            if ($current_sem) {
                $this->filter['start_sem.beginn'] = $current_sem->beginn;
                $this->filter['end_sem.ende'] = $current_sem->beginn;
            }
        }

        // Nur Studiengänge von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $this->filter = array_merge(
            [
                'mvv_studiengang.studiengang_id' => $search_result,
                'mvv_studiengang.institut_id'    => MvvPerm::getOwnInstitutes()
            ],
            (array)$this->filter
        );
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';

        // get data
        $this->studiengaenge = Studiengang::getAllEnriched(
            $this->sortby, $this->order, $this->filter,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1)
        );
        
        if (count($this->studiengaenge) == 0) {
            if (count($this->filter) || $this->search_term) {
                $this->msg = _('Es wurden keine Studiengänge gefunden.');
            } else {
                $this->msg =_('Es wurden noch keine Studiengänge angelegt.');
            }
        }

        if (!isset($this->studiengang_id)) {
            $this->studiengang_id = null;
        }

        // show details
        if ($studiengang_id) {
            $this->set_studiengangteile($studiengang_id);
        }

        $this->count = Studiengang::getCount($this->filter);
        $this->show_sidebar_search = true;

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(
            new WidgetElement(_('Auf diesen Seiten können Sie die Studiengänge verwalten.').'</br>')
        );
        $widget->addElement(
            new WidgetElement(_('Studiengänge bestehen aus einem Abschluss und einem oder mehreren Studiengangteilen.'))
        );
        $helpbar->addWidget($widget);
    }

    public function studiengang_action($studiengang_id = null, $parent_id = null)
    {
        $this->existing_studycourses = Studiengang::getAllEnriched();
        $this->abschluesse = Abschluss::getAll();
        $this->studiengang = Studiengang::get($studiengang_id);
        $this->semester = Semester::getAll();
        $this->dokumente = $this->studiengang->document_assignments;
        $this->parent_id = $parent_id;
        
        if ($this->studiengang->isNew()) {
            $this->studiengang->setNewId();
            PageLayout::setTitle(_('Neuen Studiengang anlegen'));
            $success_message = ('Der Studiengang "%s" wurde angelegt.');
            $quicksearchText = _('Studiengangsbezeichnung suchen');
        } else {
            PageLayout::setTitle(sprintf(
                _('Studiengang: %s bearbeiten'),
                htmlReady($this->studiengang->getDisplayName())
            ));
            $success_message = _('Der Studiengang "%s" wurde geändert.');
            $quicksearchText = $this->studiengang->name;
        }
        
        $this->sessSet('dokument_target', [$this->studiengang->getId(), 'Studiengang']);
        
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->studiengang->isNew()) {
                $this->reset_search('Studiengang');
            }

            //Special handling for names and short names:
            //These can be copied from a "fach" object, if such an object
            //has been selected via the quick search element.
            //In such a case the studiengang_id parameter contains a MD5 sum
            //instead of text.

            $fach_id = Request::get('fach_id');
            $this->fach = null;

            //check, if fach_id contains a MD5 sum:
            if (preg_match('/[a-f0-9]{32}/', $fach_id)) {
                //We have a MD5 sum of a "fach":
                //Lookup the "fach" object in the database:
                $this->fach = Fach::find($fach_id);
            }
            
            if ($this->fach) {
                //"fach" object exists: use its value
                //for the names and short names of the "studiengang"
                $this->studiengang->name = $this->fach->name;
                $this->studiengang->name_kurz = $this->fach->name_kurz;
            } else {
                //No "fach" object has been found:
                //Use the entered names and short names
                $this->studiengang->name = Request::i18n('name')->trim();
                $this->studiengang->name_kurz = Request::i18n('name_kurz')->trim();
            }
    
            $this->studiengang->abschluss_id     = Request::option('abschluss_id');
            $this->studiengang->beschreibung     = Request::i18n('beschreibung')->trim();
            $this->studiengang->institut_id      = Request::option('institut_item');
            $this->institut                      = Institute::find($this->studiengang->institut_id);
            $this->studiengang->typ              = Request::option('stg_typ');
            $this->studiengang->start            = Request::option('start');
            $this->studiengang->end              = Request::option('end') ?: null;
            $this->studiengang->beschlussdatum   = strtotime(trim(Request::get('beschlussdatum')));
            $this->studiengang->fassung_nr       = Request::int('fassung_nr');
            $this->studiengang->fassung_typ      = Request::option('fassung_typ');
            $this->studiengang->stat             = Request::option('status');
            $this->studiengang->kommentar_status = trim(Request::get('kommentar_status'));
            $this->studiengang->schlagworte      = trim(Request::get('schlagworte'));
    
            MvvDokument::updateDocuments($this->studiengang,
                Request::optionArray('dokumente_items'),
                Request::getArray('dokumente_properties')
            );
            
            $quicksearchText = $this->studiengang->name;

            $this->studiengang->verifyPermission();

            try {
                $stored = $this->studiengang->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->studiengang->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index/'. $this->studiengang->id));
                return;
            }
        }
        //Quicksearch
        // Faecher
        $query = 'SELECT fach_id, name FROM '
                . 'fach WHERE name LIKE :input ORDER BY name ASC';
        $search = new SQLSearch($query, $quicksearchText, 'fach_id');
        $this->search = QuickSearch::get('fach_id', $search)
                    ->defaultValue($this->studiengang->name,
                        $this->studiengang->name)
                    ->noSelectbox()
                    ->fireJSFunctionOnSelect('MVV.Search.insertFachName')
                    ->render();

        // Dokumente
        $this->search_dokumente =
                MvvDokument::getQuickSearch($this->dokumente->pluck('dokument_id'));

        // Einrichtung
        $this->institut = Fachbereich::find($this->studiengang->institut_id);
        $search = new StandardSearch('Institut_id');
        $this->search_institutes_id = md5(serialize($search));
        $this->search_institutes = QuickSearch::get('institut_id', $search)
            ->fireJSFunctionOnSelect('MVV.Search.addSelected')
            ->noSelectbox();

        if ($this->parent_id) {
            $this->cancel_url = $this->url_for('/index/' . $this->parent_id . '/' . $studiengang_id);
        } else {
            $this->cancel_url = $this->url_for('/index/' .  $studiengang_id);
        }

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Die Reihenfolge der Studiengangteile können Sie durch ziehen ändern.')));
        $helpbar->addWidget($widget);

        $this->setSidebar();
        if (!$this->studiengang->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Download des Studienganges'),
                $this->url_for('/export/' . $this->studiengang->getId()),
                Icon::create('file-word')
            );
            $action_widget->addLink(
                _('Studiengang als PDF'),
                $this->url_for('/export/' . $this->studiengang->getId(), ['pdf' => '1']),
                Icon::create('file-pdf')
            );
            if ($this->studiengang->stat == 'planung' && MvvPerm::haveFieldPermStat($this->studiengang)) {
                $action_widget->addLink(_('Studiengang genehmigen'),
                    $this->url_for('/approve/' . $this->studiengang->getId()),
                    Icon::create('accept'),
                    ['data-dialog' => 'buttons=false']
                );
            }
            $action_widget->addLink(
                _('Log-Einträge dieses Studienganges'),
                $this->url_for('shared/log_event/show/Studiengang/' . $this->studiengang->getId()),
                Icon::create('log')
            )->asDialog();
        }

        $this->render_template('studiengaenge/studiengaenge/studiengang', $this->layout);
    }

    /**
     * Deletes one Studiengang.
     *
     * @param string $studiengang_id The ID of the Studiengang
     */
    public function delete_action($studiengang_id)
    {
        $studiengang = Studiengang::find($studiengang_id);
        if (!$studiengang) {
             PageLayout::postError(_('Unbekannter Studiengang.'));
        } else {
            if (Request::isPost()) {
                if (Request::submitted('delete')) {
                    CSRFProtection::verifyRequest();
                    PageLayout::postSuccess(sprintf(
                        _('Studiengang "%s" gelöscht!'),
                        htmlReady($studiengang->name)
                    ));
                    $studiengang->delete();
                }
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * List of Studiengänge grouped by Abschlusskategorien
     */
    public function kategorien_action()
    {
        PageLayout::setTitle(_('Studiengänge gruppiert nach Abschluss-Kategorien'));

        $this->initPageParams('kategorien');

        // Nur Kategorien anzeigen, denen Studiengänge zugeordnet sind an deren
        // verantwortlichen Einrichtungen der User eine Rolle hat...
        $perm_institutes = MvvPerm::getOwnInstitutes();
        $filter = [];
        
        if (count($perm_institutes)) {
            $filter['ms.institut_id'] = $perm_institutes;
        }
    
        $this->abschluss_kategorien = AbschlussKategorie::getAllEnriched(
            $this->sortby, $this->order, null, null, $filter
        );

        $this->setSidebar();
    }

    protected function set_details_studiengang($studiengang_id)
    {
        $this->studiengang = Studiengang::getEnriched($studiengang_id);
        $this->studiengang_id = $this->studiengang->id;
        if ($this->studiengang->typ === 'mehrfach') {
            $this->bez_stgteile = StgteilBezeichnung::findByStudiengang(
                $this->studiengang->getId()
            );
            $this->stgteile_bez = StgteilBezeichnung::getAllSorted();
            Request::set('stgteil_id_parameter',  $this->flash['qs_stgteil']);
            $query = "SELECT stgteil_id, CONCAT(mf.name, ' ', mst.kp, ' cp' "
                    . "' (', mst.zusatz, ')') AS stgteil_name FROM "
                    . 'mvv_stgteil mst INNER JOIN fach mf USING(fach_id) '
                    . 'WHERE (mst.zusatz LIKE :input '
                    . 'OR mf.name LIKE :input) '
                    . "AND stgteil_id NOT IN('"
                    . join("','", $this->bez_stgteile->pluck('id'))
                    . "') ORDER BY stgteil_name";
            $search = new SQLSearch($query, _('Studiengangteil suchen'),
                    'stgteil_id');
            $this->qs_search_stgteil_id = md5(serialize($search));
            $this->search_stgteil = QuickSearch::get('stg_id_' . $this->studiengang->id, $search);
        }
    }

    protected function set_studiengangteile($studiengang_id, $stgteil_bez_id = null)
    {
        $this->set_details_studiengang($studiengang_id);
        $this->stgteil_bez_id = $stgteil_bez_id;
        $this->add_stgteil();
        if ($stgteil_bez_id) {
            $this->stg_bez = StgteilBezeichnung::find($stgteil_bez_id);
            $this->stgteile = StudiengangTeil::findByStudiengangStgteilBez($studiengang_id,
            $stgteil_bez_id);
            $this->stg_stgbez_id = implode('_', [$this->studiengang->id, $this->stg_bez->id]);
        } else {
            $this->stgteile = StudiengangTeil::findByStudiengang($studiengang_id);
            $this->stg_stgbez_id = $this->studiengang->id;
        }
        $query = "SELECT stgteil_id, CONCAT(mf.name, ' ', mst.kp, ' cp' "
                . "' (', mst.zusatz, ')') AS stgteil_name FROM "
                . 'mvv_stgteil mst INNER JOIN fach mf USING(fach_id) '
                . 'WHERE (mst.zusatz LIKE :input '
                . 'OR mf.name LIKE :input) '
                . "AND stgteil_id NOT IN('"
                . join("','", $this->stgteile->pluck('id'))
                . "') ORDER BY stgteil_name";
        $search = new SQLSearch($query, _('Studiengangteil suchen'));
        $this->qs_search_id = md5(serialize($search));
        $this->search = QuickSearch::get('stg_id_'. $this->stg_stgbez_id, $search)
                ->fireJSFunctionOnSelect('MVV.Search.submitSelected');
    }

    public function details_studiengang_action($studiengang_id, $stgteil_bez_id = null)
    {
        $this->set_studiengangteile($studiengang_id, $stgteil_bez_id);
        $this->parent_id = Request::option('parent_id');
        $this->studiengang_id = $studiengang_id;
        
        if (Request::isXhr()) {
            if ($this->studiengang->typ === 'einfach' || $this->stg_bez) {
                $this->render_template('studiengaenge/studiengaenge/studiengangteile');
            } else {
                $this->render_template('studiengaenge/studiengaenge/stgteil_bezeichnungen');
            }
        } else {
            $this->perform_relayed('index');
        }
    }

    protected function add_stgteil()
    {
        if ($this->studiengang) {
            if (Request::submitted('add_stgteil')) {
                if ($this->studiengang->typ === 'mehrfach') {
                    if (Request::option('level') === 'stg') {
                        $stgteil = StudiengangTeil::getEnriched(
                            Request::option('stg_id_' . $this->studiengang->id)
                        );
                    } else {
                        $stgteil = StudiengangTeil::getEnriched(
                            Request::option('stg_id_' . $this->studiengang->id . '_' . $this->bez_stgteile->id)
                        );
                    }
                } else {
                    $stgteil = StudiengangTeil::getEnriched(
                        Request::option('stg_id_' . $this->studiengang->id)
                    );
                    $stgteil_bez = null;
                }
                if (!$stgteil->isNew()) {
                    CSRFProtection::verifyUnsafeRequest();
                    $stg_stgteil = new StudiengangStgteil();
                    $stg_stgteil->setId(
                        [$this->studiengang->id, $stgteil->id, $stgteil_bez ? $this->bez_stgteile->id : '']
                    );
                    
                    if ($stg_stgteil->store()) {
                        if ($this->studiengang->typ === 'mehrfach') {
                            PageLayout::postSuccess(sprintf(
                                _('Der Studiengangteil "%s" wurde dem Studiengang "%s" als "%s" hinzugefügt.'),
                                $stgteil->getDisplayName(),
                                $this->studiengang->name,
                                $this->stgteil_bez->name
                            ));
                        } else {
                            PageLayout::postSuccess(sprintf(
                                _('Der Studiengangteil "%s" wurde dem Studiengang "%s" hinzugefügt.'),
                                $stgteil->getDisplayName(),
                                $this->studiengang->name
                            ));
                        }
                    } else {
                        PageLayout::postError(sprintf(
                            _('Der Studiengangteil "%s" wurde bereits zugordnet.'),
                            $stgteil->getDisplayName()
                        ));
                    }
                }
            }
        } else {
            PageLayout::postError(_('Unbekannter Studiengang.'));
        }
    }

    public function add_stgteil_action($studiengang_id)
    {
        $studiengang = Studiengang::find($studiengang_id);
        if ($studiengang) {
            $this->studiengang_id = $studiengang->getId();
            if ($studiengang->typ === 'mehrfach') {
                $stgteil_bez = StgteilBezeichnung::find(Request::option('stgteil_bez_id'));
                if (Request::option('level') === 'stg') {
                    $stgteil = StudiengangTeil::getEnriched(
                        Request::option('stg_id_' . $studiengang->id)
                    );
                } else {
                    $stgteil = StudiengangTeil::getEnriched(
                        Request::option('stg_id_' . $studiengang->id . '_' . $stgteil_bez->id)
                    );
                }
                if (!$stgteil_bez) {
                    PageLayout::postError(_('Bitte Studiengangteil-Bezeichnung auswählen!'));
                    $this->redirect($this->url_for('/details_studiengang/' . $studiengang->id . '/' . $stgteil->id));
                    return;
                }
            } else {
                $stgteil = StudiengangTeil::getEnriched(
                    Request::option('stg_id_' . $studiengang->id)
                );
                $stgteil_bez = null;
            }
            if (Request::isPost()) {
                if (!$stgteil->isNew()) {
                    CSRFProtection::verifyUnsafeRequest();
                    $stg_stgteil = new StudiengangStgteil();
                    $stg_stgteil->setId(
                        [$studiengang->getId(), $stgteil->getId(), $stgteil_bez ? $stgteil_bez->getId() : '']
                    );
                    if ($stg_stgteil->store()) {
                        if ($studiengang->typ === 'mehrfach') {
                            PageLayout::postSuccess(sprintf(
                                _('Der Studiengangteil "%s" wurde dem Studiengang "%s" als "%s" hinzugefügt.'),
                                htmlReady($stgteil->getDisplayName()),
                                htmlReady($studiengang->name),
                                htmlReady($stgteil_bez->name)
                            ));
                        } else {
                            PageLayout::postSuccess(sprintf(
                                _('Der Studiengangteil "%s" wurde dem Studiengang "%s" hinzugefügt.'),
                                htmlReady($stgteil->getDisplayName()),
                                htmlReady($studiengang->name)
                            ));
                        }
                    } else {
                        PageLayout::postError(sprintf(
                            _('Der Studiengangteil "%s" wurde bereits zugordnet.'),
                            htmlReady($stgteil->getDisplayName())
                        ));
                    }
                } else {
                    PageLayout::postError(_('Unbekannter Studiengangteil.'));
                    $this->redirect($this->url_for('/details_studiengang/' . $studiengang->id));
                    return;
                }
            }
            $this->redirect(
                $this->url_for('/details_studiengang/' . $studiengang->id . '/' . $stgteil_bez->id)
            );
            return;
        } else {
            PageLayout::postError(_('Unbekannter Studiengang.'));
            $this->redirect($this->url_for('/index'));
        }
    }

    public function delete_stgteilmf_action($studiengang_id, $stgteil_id, $stgteil_bez_id)
    {
        $this->stg_stgteil = StudiengangStgteil::getEnriched(
            [$studiengang_id, $stgteil_id, $stgteil_bez_id]
        );
        
        $studiengang = Studiengang::find($studiengang_id);
        
        if ($studiengang) {
            $stgbez_id = $this->stg_stgteil->stgteil_bez_id;
            if ($this->stg_stgteil->isNew()) {
                PageLayout::postError(_('Unbekannter Studiengangteil.'));
            } else {
                if (Request::isPost()) {
                    CSRFProtection::verifyRequest();
                    if (!MvvPerm::haveFieldPermStudiengangteile($studiengang, MvvPerm::PERM_CREATE)) {
                        throw new Trails_Exception(403);
                    }
                    $stgteil_name = $this->stg_stgteil->stgteil_name;
                    $stgbez_name = $this->stg_stgteil->stgbez_name;
                    if ($this->stg_stgteil->delete()) {
                        PageLayout::postSuccess(sprintf(
                            _('Die Zuordnung des Studiengangteils "%s" als "%s" zum Studiengang "%s" wurde gelöscht.'),
                            htmlReady($stgteil_name),
                            htmlReady($stgbez_name),
                            htmlReady($studiengang->name)
                        ));
                    } else {
                        PageLayout::postError(_('Der Studiengangteil konnte nicht gelöscht werden.'));
                    }
                }
            }
            $this->redirect(
                $this->url_for('/details_studiengang/' .  $studiengang->id . '/' . $stgbez_id)
            );
        } else {
            PageLayout::postError(_('Unbekannter Studiengang.'));
            $this->redirect($this->url_for('/index'));
        }
    }

    public function delete_stgteil_action($studiengang_id, $stgteil_id)
    {
        $studiengang = Studiengang::find($studiengang_id);
        $stg_stgteil = StudiengangStgteil::getEnriched(
            [$studiengang->getId(), $stgteil_id, '']
        );
        $this->delete_stgteil($studiengang, $stg_stgteil);
        $this->redirect(
            $this->url_for('/details_studiengang/' . $studiengang_id . '/' .  $stg_stgteil->stgbez_id)
        );
    }

    private function delete_stgteil($studiengang, $stg_stgteil)
    {
        if ($studiengang) {
            $stgbez_id = $stg_stgteil->stgteil_bez_id;
            if ($stg_stgteil->isNew()) {
                PageLayout::postError(_('Unbekannter Studiengangteil.'));
            } else {
                if (Request::isPost()) {
                    CSRFProtection::verifyUnsafeRequest();
                    if (!MvvPerm::haveFieldPermStudiengangteile($studiengang,
                            MvvPerm::PERM_CREATE)) {
                        throw new Trails_Exception(403);
                    }
                    $stgteil_name = $stg_stgteil->stgteil_name;
                    $stgbez_name = $stg_stgteil->stgbez_name;
                    if ($stg_stgteil->delete()) {
                        if ($stgbez_id) {
                            PageLayout::postSuccess(sprintf(
                                _('Die Zuordnung des Studiengangteils "%s" als "%s" zum Studiengang "%s" wurde gelöscht.'),
                                htmlReady($stgteil_name),
                                htmlReady($stgbez_name),
                                htmlReady($studiengang->name)
                            ));
                        } else {
                            PageLayout::postSuccess(sprintf(
                                _('Die Zuordnung des Studiengangteils "%s" zum Studiengang "%s" wurde gelöscht.'),
                                htmlReady($stgteil_name),
                                htmlReady($studiengang->name)
                            ));
                        }
                    } else {
                        PageLayout::postError(_('Der Studiengangteil konnte nicht gelöscht werden.'));
                    }
                }
            }
        } else {
            PageLayout::postError(_('Unbekannter Studiengang.'));
            $this->redirect($this->url_for('/index'));
        }
    }

    /**
     * execute the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('Studiengang');
            $this->reset_page();
        } else {
            $this->reset_search('Studiengang');
            $this->reset_page();
            $this->do_search(
                'Studiengang',
                trim(Request::get('studiengang_suche_parameter')),
                Request::get('studiengang_suche'),
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
        $this->reset_search('Studiengang');
        $this->reset_page();
    }

    /**
     * sorts studiengaenge
     */
    public function sort_action()
    {
        $target = explode('_', Request::option('list_id'));
        $ids = Request::getArray('newOrder');
        if ($target[0] === 'stgteilbez') {
            $studiengang_id = $target[1];
            // check permission
            $perm = MvvPerm::get(new Studiengang($studiengang_id));
            if (!$perm->haveFieldPerm('studiengangteile', MvvPerm::PERM_WRITE)) {
                throw new AccessDeniedException();
            }
            $bez_id = $target[2];
            $stgteile = StudiengangStgteil::findByStudiengangStgteilBez(
                $studiengang_id, $bez_id
            );
            if (is_array($ids)) {
                $i = 1;
                foreach ($ids as $id) {
                    if ($bez_id) {
                        $obj_ids = explode('_', $id);
                        $id = implode('_', [$obj_ids[0], $obj_ids[2], $obj_ids[1]]);
                    } else {
                        $id = $id . '_';
                    }
                    $one_stgteil = $stgteile->find($id);
                    if ($one_stgteil && $one_stgteil->position !== $i) {
                        $one_stgteil->position = $i;
                        $one_stgteil->store();
                    }
                    $i++;
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    public function dokumente_properties_action($dokument_id)
    {
        $target = $this->sessGet('dokument_target');
        if ($target) {
            $this->redirect('materialien/dokumente/ref_properties/' . $dokument_id . '/' . join('/', $target));
        }
    }

    public function fach_data_action()
    {
        $fach = Fach::find(Request::option('fach_id'));
        if ($fach) {
            $this->render_json(
                array_map(function ($v) {
                    return trim($v) == '' ? null : $v;
                }, $fach->toArray())
            );
        } else {
            $this->set_status(404, 'Not Found');
            $this->render_nothing();
        }
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
        // Status
        if (mb_strlen(Request::option('status_filter'))) {
            $this->filter['mvv_studiengang.stat'] = Request::option('status_filter');
        }
        // Abschluss
        if (mb_strlen(Request::get('abschluss_filter'))) {
            $this->filter['abschluss.abschluss_id'] = Request::get('abschluss_filter');
        }
        // Abschluss-Kategorie
        if (mb_strlen(Request::get('kategorie_filter'))) {
            $this->filter['mvv_abschl_zuord.kategorie_id'] = Request::option('kategorie_filter');
        }
        if (mb_strlen(Request::get('kategorie_filter'))) {
            $this->filter['mvv_abschl_zuord.kategorie_id'] = Request::option('kategorie_filter');
        }
        // Verantwortliche Einrichtung
        if (mb_strlen(Request::get('institut_filter'))) {
            $this->filter['mvv_studiengang.institut_id'] = Request::option('institut_filter');
        }
        // Zugeordnete Fachbereiche
        if (mb_strlen(Request::get('fachbereich_filter'))) {
            $this->filter['mvv_fach_inst.institut_id'] = Request::option('fachbereich_filter');
        }

        // store filter
        $this->sessSet('filter', $this->filter);
        $this->reset_page();
        $this->redirect($this->url_for('/index'));
    }

    public function reset_filter_action()
    {
        $this->filter = [];
        $this->reset_page();
        // current semester is set in index_action()
        unset($this->filter['start_sem.beginn']);
        unset($this->filter['end_sem.ende']);
        $this->sessSet('filter', $this->filter);
        $this->redirect($this->url_for('/index'));
    }

    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');
        $widget  = new ViewsWidget();
        $widget->addLink(
            _('Liste der Studiengänge'),
            URLHelper::getURL('dispatch.php/studiengaenge/studiengaenge')
        )->setActive(get_called_class() == 'Studiengaenge_StudiengaengeController');
        $widget->addLink(
            _('Gruppierung nach Fachbereichen'),
            URLHelper::getURL('dispatch.php/studiengaenge/fachbereiche')
        )->setActive(get_called_class() == 'Studiengaenge_FachbereicheController');
        $widget->addLink(
            _('Gruppierung nach Abschlüssen'),
            URLHelper::getURL('dispatch.php/studiengaenge/abschluesse')
        )->setActive(get_called_class() == 'Studiengaenge_AbschluesseController');
        $widget->addLink(
            _('Gruppierung nach Abschluss-Kategorien'),
            URLHelper::getURL('dispatch.php/studiengaenge/kategorien')
        )->setActive(get_called_class() == 'Studiengaenge_KategorienController');
        $sidebar->addWidget($widget);
        $widget  = new ActionsWidget();
        if (MvvPerm::havePermCreate('Studiengang')) {
            $widget->addLink(
                _('Neuen Studiengang anlegen'),
                $this->url_for('/studiengang'),
                Icon::create('file+add')
            );
        }
        $sidebar->addWidget($widget);

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
            $this->sidebar_filter();
        }
        $this->sidebar_rendered = true;
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();
        $studiengang_ids = Studiengang::findByFilter($this->filter);

        if ($this->search_result['Studiengang']) {
            $studiengang_ids = array_intersect($studiengang_ids, $this->search_result['Studiengang']);
        }

        // Semesters
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');

        $filter_template = $template_factory->render('shared/filter',
            [
                'semester'             => $semesters,
                'selected_semester'    => $semesters->findOneBy('beginn', $this->filter['start_sem.beginn'])->id,
                'default_semester'     => Semester::findCurrent()->id,
                'status'               => Studiengang::findStatusByIds($studiengang_ids),
                'selected_status'      => $this->filter['mvv_studiengang.stat'],
                'status_array'         => $GLOBALS['MVV_STUDIENGANG']['STATUS']['values'],
                'kategorien'           => AbschlussKategorie::findByStudiengaenge($studiengang_ids),
                'selected_kategorie'   => $this->filter['mvv_abschl_zuord.kategorie_id'],
                'abschluesse'          => Abschluss::findByStudiengaenge($studiengang_ids),
                'selected_abschluss'   => $this->filter['abschluss.abschluss_id'],
                'institute'            => Studiengang::getAllAssignedInstitutes(
                    ['mvv_studiengang.studiengang_id' => $studiengang_ids]
                ),
                'selected_institut'    => $this->filter['mvv_studiengang.institut_id'],
                'fachbereiche'         => Fach::getAllAssignedInstitutes($studiengang_ids),
                'selected_fachbereich' => $this->filter['mvv_fach_inst.institut_id'],
                'action'               => $this->url_for('/set_filter'),
                'action_reset'         => $this->url_for('/reset_filter')
            ]
        );
        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle('Filter');
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget, 'filter');
    }

    /**
     * adds the search funtion to the sidebar
     */
    private function sidebar_search()
    {
        $query = 'SELECT DISTINCT mvv_studiengang.studiengang_id, '
                . 'IF(LENGTH(mvv_studiengang.name_kurz), '
                . 'CONCAT(mvv_studiengang.name_kurz, " (", mvv_abschl_kategorie.name, ")"), '
                . 'CONCAT(mvv_studiengang.name, " (", mvv_abschl_kategorie.name, ")")) AS name '
                . 'FROM mvv_studiengang '
                . 'LEFT JOIN abschluss USING(abschluss_id)'
                . 'LEFT JOIN mvv_abschl_zuord USING(abschluss_id) '
                . 'LEFT JOIN mvv_abschl_kategorie USING(kategorie_id) ';
        if ($this->filter) {
            $query .= 'LEFT JOIN mvv_stg_stgteil USING(studiengang_id) '
                . 'LEFT JOIN mvv_stgteil USING(stgteil_id) '
                . 'LEFT JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN semester_data start_sem '
                . 'ON (mvv_studiengang.start = start_sem.semester_id) '
                . 'LEFT JOIN semester_data end_sem '
                . 'ON (mvv_studiengang.end = end_sem.semester_id) ';
            $query .= 'WHERE (mvv_studiengang.name LIKE :input '
                . 'OR mvv_studiengang.name_kurz LIKE :input) ';
            $query .= ModuleManagementModel::getFilterSql($this->filter, false);
        }

        $search_term =
                $this->search_term ? $this->search_term : _('Studiengang suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(
            _('Studiengang suchen'),
            'studiengang_suche',
            true,
            new SQLSearch($query, $search_term, 'studiengang_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');

    }

    public function approve_action($studiengang_id)
    {
        $this->studiengang_id = $studiengang_id;
        $this->studiengang =  Studiengang::find($studiengang_id);

        if (!$this->studiengang) {
            PageLayout::postError(_('Unbekannter Studiengang!'));
            $this->relocate('/index');
            return;
        }

        $this->institut = Fachbereich::find($this->studiengang->institut_id);

        if (Request::submitted('approval')) {
            CSRFProtection::verifyUnsafeRequest();
            $studiengang = Studiengang::get($studiengang_id);
            $studiengang->stat = 'genehmigt';
            $studiengang->verifyPermission();
            $stored = $studiengang->store(false);
            if ($stored) {
                PageLayout::postSuccess(sprintf(
                    _('Studiengang "%s" genehmigt!'),
                    htmlReady($studiengang->getDisplayName())
                ));
                $this->relocate('/index');
                return;
            }
        }
        PageLayout::setTitle($this->studiengang->getDisplayName());
        $this->render_template('studiengaenge/studiengaenge/approve', $this->layout);
    }

    public function export_action ($studiengang_id)
    {
        $studiengang = Studiengang::find($studiengang_id ?: Request::option('studiengang_id'));

        if (!$studiengang) {
            PageLayout::postError(_('Unbekannter Studiengang!'));
            $this->redirect($this->url_for('/index'));
        } else {
            if (Request::isXhr()) {
                $this->relocate('/export/' . $studiengang->id);
            }

            $factory = $this->get_template_factory();
            $template = $factory->open('studiengaenge/studiengaenge/export');
            $template->set_attributes(['studiengang' => $studiengang]);

            $as_pdf = Request::int('pdf');

            if ($as_pdf) {
                $template->set_attribute('image_style', 'height: 6px; width: 8px;');

                $doc = new ExportPDF();
                $doc->addPage();
                $doc->SetFont('helvetica', '', 8);
                $doc->writeHTML($template->render(), false, false, true);
                $doc->Output($studiengang->getDisplayName() . '.pdf', 'D');

                $this->render_nothing();
            } else {
                $content = $template->render();
                $this->response->add_header('Content-type', 'application/msword');
                $this->response->add_header('Content-Disposition', 'attachment; ' . encode_header_parameter('filename', FileManager::cleanFileName($studiengang->getDisplayName()). '.doc'));
                $this->render_text($content);
            }
        }
    }
}
