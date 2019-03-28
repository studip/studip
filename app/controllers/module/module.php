<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once 'lib/classes/exportdocument/ExportPDF.class.php';

class Module_ModuleController extends MVVController
{
    public $filter = [];
    protected $show_sidebar_search = false;
    protected $show_sidebar_filter = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/module/module');
        $this->filter = $this->sessGet('filter', []);
        $this->action = $action;
    }

    public function index_action()
    {
        $this->initPageParams();
        $this->initSearchParams('module');
        $search_result = $this->getSearchResult('Modul');

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

        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        if (!$this->filter['mvv_modul_inst.institut_id']) {
            unset($this->filter['mvv_modul_inst.institut_id']);
        }
        $this->filter = array_merge(
            [
                'mvv_modul.modul_id'         => $search_result,
                'mvv_modul_inst.gruppe'      => 'hauptverantwortlich',
                'mvv_modul_inst.institut_id' => MvvPerm::getOwnInstitutes()
            ],
            $this->filter
        );
        $this->sortby = $this->sortby ?: 'code';
        $this->order  = $this->order ?: 'ASC';

        //get data
        $this->module = Modul::getAllEnriched(
                $this->sortby,
                $this->order,
                self::$items_per_page,
                self::$items_per_page * (($this->page ?: 1) - 1),
                $this->filter);

        if (!empty($this->filter)) {
            $this->search_result['Modul'] = $this->module->pluck('id');
        }
        $title = _('Verwaltung der Module - Alle Module');
        if (count($this->module) === 0) {
            if (count($this->filter) || $this->search_term) {
                PageLayout::postInfo(_('Es wurden keine Module gefunden.'));
            } else {
                PageLayout::postInfo(_('Es wurden noch keine Module angelegt.'));
            }
        }
        $this->count = Modul::getCount($this->filter);
        $title .= ' - ' . sprintf(ngettext('%s Modul', '%s Module', $this->count), $this->count);
        $this->show_sidebar_search = true;
        $this->show_sidebar_filter = true;
        $this->setSidebar();
        PageLayout::setTitle($title);
    }

    public function modul_action($modul_id = null, $institut_id = null)
    {
        $own_institutes = MvvPerm::getOwnInstitutes();

        if (!isset($this->modul)) {
            $this->modul = Modul::find($modul_id);
            if (!$this->modul) {
                $this->modul = new Modul();
                $this->modul->setNewId();
            }
        }
        if ($institut_id) {
            $institut = new Institute($institut_id);
            if (!$institut->isNew()) {
                $this->institut_id = $institut->getId();
            }
        }

        $this->setSidebar();
        $sidebar = Sidebar::get();

        if ($this->modul->isNew()) {
            PageLayout::setTitle(_('Neues Modul anlegen'));
            $success_message = ('Das Modul "%s" wurde angelegt.');
            $this->display_language = $this->modul->getDefaultLanguage();
            $this->deskriptor = $this->modul->getDeskriptor($this->display_language, true);
            $this->reset_search('Modul');
            if (!$modul_id) {
                PageLayout::postInfo(sprintf(
                    _('Sie legen ein neues Modul an. Das Modul muss zunächst in der Ausgabesprache <em>%s</em> angelegt werden.'),
                    $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['name']
                ));
            }
        } else {
            $this->display_language = Request::option(
                'display_language',
                $this->modul->getDefaultLanguage()
            );
            
            $this->deskriptor = $this->modul->getDeskriptor($this->display_language, true);
            $this->translations = $this->deskriptor->getAvailableTranslations();
            if (!in_array($this->display_language, $this->translations)) {
                PageLayout::setTitle(
                    sprintf(
                        _('Modul: <em>%s</em> in der Ausgabesprache <em>%s</em> neu anlegen.'),
                        $this->modul->getDisplayName(),
                        $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['name']
                    )
                );
            } else {
                PageLayout::setTitle(sprintf(
                    _('Modul: %s bearbeiten'), htmlReady($this->modul->getDisplayName())
                ));
            }
            
            $success_message = _('Das Modul "%s" wurde geändert.');
            // language selector as sidebar widget
            $template_factory = $this->get_template_factory();
            $sidebar_template = $template_factory->render('shared/deskriptor_language', [
                'modul'   => $this->modul,
                'sprache' => $this->display_language,
                'link'    => $this->url_for('/modul', $this->modul->id, $this->institut_id),
                'url'     => $this->url]
            );
    
            $widget  = new SidebarWidget();
            $widget->setTitle(_('Ausgabesprache'));
            $widget->addElement(new WidgetElement($sidebar_template));
            $sidebar->addWidget($widget, 'language');

            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Download der Modulbeschreibung'),
                $this->url_for('module/download/details', $this->modul->id, $this->display_language),
                Icon::create('file-word')
            );
            $action_widget->addLink(
                _('Modulbeschreibung als PDF'),
                $this->url_for('module/download/details/' . $this->modul->id . '/' . $this->display_language, ['pdf' => '1']),
                Icon::create('file-pdf')
            );
            $action_widget->addLink(
                _('Vergleich mit anderem Modul'),
                $this->url_for('/diff_select', $this->modul->id),
                Icon::create('learnmodule'),
                ['data-dialog' => 'size=auto']
            );

            if ($this->modul->stat === 'planung' && MvvPerm::haveFieldPermStat($this->modul)) {
                $action_widget->addLink(
                    _('Modul genehmigen'),
                    $this->url_for('/approve', $this->modul->id),
                    Icon::create('accept'),
                    ['data-dialog' => 'size=auto;buttons=false']
                );
            }
    
            $action_widget->addLink(
                _('Log-Einträge dieses Moduls'),
                $this->url_for('shared/log_event/show/Modul/' . $this->modul->id,
                    ['object2_type' => 'ModulDeskriptor', 'object2_id' => $this->deskriptor->id]
                ),
                Icon::create('log')
            )->asDialog();

            // list all variants
            $variants = $this->modul->getVariants();
            if (count($variants)) {
                $widget = new SidebarWidget();
                $widget->setTitle(_('Varianten'));
                $widget->addElement(new WidgetElement(
                    $template_factory->render('shared/modul_variants',
                        [
                            'variants' => $variants,
                            'link'     => $this->url_for('/modul')
                        ])
                ));
                $sidebar->addWidget($widget, 'variants');
            }
        }
        $this->semester = Semester::getAll();
        $this->def_lang = $this->display_language === $this->modul->getDefaultLanguage();
        ModuleManagementModel::setContentLanguage($this->display_language);
        if (!$this->def_lang) {
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Alle Texte der Originalfassung anzeigen'),
                '#', Icon::create('consultation'),
                ['class' => 'mvv-show-all-original']
            );
        }

        $this->language = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['content_language'];
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->def_lang) {
                $this->modul->variante = Request::option('modul_item');
                $this->modul->flexnow_modul = trim(Request::get('flexnow_modul'));
                $this->modul->code = trim(Request::get('code'));
                $this->modul->start = Request::option('start');
                $this->modul->end = Request::option('end') ?: null;
                $this->modul->beschlussdatum = strtotime(trim(Request::get('beschlussdatum')));
                $this->modul->assignLanguagesOfInstruction(Request::optionArray('language_items'));
                $this->modul->dauer = trim(Request::get('dauer'));
                if (Request::get('kap_unbegrenzt')) {
                    $this->modul->kapazitaet = '';
                } else {
                    $kapazitaet = trim(Request::get('kapazitaet'));
                    $this->modul->kapazitaet = $kapazitaet === '' ? null : $kapazitaet;
                }
                $this->modul->kp = Request::int('kp');
                $this->modul->wl_selbst = Request::int('wl_selbst');
                $this->modul->wl_pruef = Request::int('wl_pruef');
                $this->modul->pruef_ebene = Request::option('pruef_ebene');
                $this->modul->faktor_note = StringToFloat(trim(Request::get('faktor_note')));
                $this->modul->stat = Request::option('status', $GLOBALS['MVV_MODUL']['STATUS']['default']);
                $this->modul->kommentar_status = trim(Request::get('kommentar_status'));
                $this->modul->assignInstitutes(Request::optionArray('institutes_items'));
                $this->modul->assignResponsibleInstitute(Request::option('responsible_item'));
                $this->modul->fassung_nr = Request::int('fassung_nr');
                $this->modul->fassung_typ = Request::option('fassung_typ');
                $this->modul->version = trim(Request::get('version'));
                foreach ($GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'] as $key => $foo) {
                    $grouped_users[$key] = Request::optionArray('users_items_' . $key);
                }
                $this->modul->assignUsers($grouped_users);
                $this->modul->verantwortlich = trim(Request::get('verantwortlich'));
            }
            
            $deskriptor_fields = ['bezeichnung', 'verantwortlich',
                'voraussetzung', 'kompetenzziele', 'inhalte', 'literatur',
                'links', 'kommentar', 'turnus', 'kommentar_kapazitaet',
                'kommentar_wl_selbst', 'kommentar_wl_pruef', 'kommentar_sws',
                'kommentar_note', 'pruef_vorleistung', 'pruef_leistung',
                'pruef_wiederholung', 'ersatztext'];
            
            foreach ($deskriptor_fields as $deskriptor_field) {
                if ($this->deskriptor->isI18nField($deskriptor_field)) {
                    $this->deskriptor->$deskriptor_field->setLocalized(
                        trim(Request::get($deskriptor_field)),
                        $this->language
                    );
                } else {
                    $this->deskriptor->setValue(
                        $deskriptor_field,
                        trim(Request::get($deskriptor_field))
                    );
                }
            }
            
            // update datafields
            foreach (Request::getArray('datafields') as $df_key => $df_value) {
                $df = $this->deskriptor->datafields->findOneBy('datafield_id', $df_key);
                if ($df) {
                    $tdf = $df->getTypedDatafield();
                    $tdf->setContentLanguage($this->language);
                    $tdf->setValueFromSubmit($df_value);
                    $tdf->store();
                }
            }
            if (Request::submitted('store')) {
                try {
                    $this->modul->verifyPermission();
                    $stored = $this->modul->store();
                } catch (InvalidValuesException $e) {
                    PageLayout::postError(htmlReady($e->getMessage()));
                }
                if ($stored !== false) {
                    PageLayout::postSuccess(sprintf(
                        $success_message,
                        htmlReady($this->modul->getDisplayName())
                    ));
                    $this->redirect($this->url_for('/index'));
                    return;
                }
            }
        }

        $query = "SELECT modul_id, CONCAT(mmd.bezeichnung, ' (', code, ')') as name FROM mvv_modul mm
                LEFT JOIN mvv_modul_deskriptor mmd USING(modul_id)
                WHERE (code LIKE :input OR mmd.bezeichnung LIKE :input)
                AND mm.modul_id <> " . DBManager::get()->quote($this->modul->id ?: ''). " ORDER BY name ASC";
        $sql_search_modul = new SQLSearch($query, _('Modul suchen'));
        $this->qs_id_module = md5(serialize($sql_search_modul));
        $this->search_modul =
                QuickSearch::get('modul', $sql_search_modul)
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();
    
        $sql_search_user = new PermissionSearch(
            'user',
            _('Person suchen'),
            'user_id',
            ['permission' => words('tutor dozent admin'), 'exclude_user' => '']
        );
        $this->qs_id_users = md5(serialize($sql_search_user));
        $qs_users = QuickSearch::get('users', $sql_search_user);
        $this->qs_frame_id_users = $qs_users->getId();
        $this->search_users = $qs_users
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $query = 'SELECT DISTINCT Institute.Institut_id, Institute.Name AS name
                    FROM Institute
                    LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id)
                    WHERE (Institute.Name LIKE :input OR range_tree.name LIKE :input )';
        if (count($own_institutes)) {
            $query .= 'AND Institut_id IN('
                    . DBManager::get()->quote($own_institutes) . ') ';
        }
        $query .= ' ORDER BY Institute.Name';
        $sql_search_responsible = new SQLSearch($query, _('Einrichtung suchen'),
                'Institut_id');
        $this->qs_id_responsible = md5(serialize($sql_search_responsible));
        $qs_responsible = QuickSearch::get('responsible', $sql_search_responsible);
        $this->qs_frame_id_responsible = $qs_responsible->getId();
        $this->search_responsible = $qs_responsible
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $query = 'SELECT DISTINCT Institute.Institut_id, Institute.Name AS name
                FROM Institute
                LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id)
                WHERE (Institute.Name LIKE :input
                OR range_tree.name LIKE :input )
                ORDER BY Institute.Name';
        $sql_search_institutes = new SQLSearch($query, _('Einrichtung suchen'),
                'Institut_id');
        $this->qs_id_institutes = md5(serialize($sql_search_institutes));
        $qs_institutes = QuickSearch::get('institutes', $sql_search_institutes);
        $this->qs_frame_id_institutes = $qs_institutes->getId();
        $this->search_institutes = $qs_institutes
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $this->cancel_url = $this->url_for('/index');

        $this->render_template('module/module/modul', $this->layout);
    }

    /**
     * Deletes a descriptor from module
     *
     * @param type $deskriptor_id
     * @throws Trails_Exception
     */
    public function delete_modul_deskriptor_action($deskriptor_id, $language)
    {
        $deskriptor = ModulDeskriptor::find($deskriptor_id);
        if (is_null($deskriptor)) {
            throw new Trails_Exception(404, _('Unbekannter Deskriptor.'));
        }
        $def_lang = $deskriptor->modul->getDefaultLanguage();
        if ($language === $def_lang) {
            throw new Trails_Exception(403, _('Ein Deskriptor in der Original-Sprache kann nicht gelöscht werden.'));
        }
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($deskriptor->deleteTranslation($language)) {
                PageLayout::postSuccess(sprintf(
                    _('Der Deskriptor "%s" des Moduls "%s" wurde gelöscht!'),
                    htmlReady($deskriptor->getDisplayName()),
                    htmlReady($deskriptor->modul->getDisplayName())
                ));
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    public function assignments_action($modul_id)
    {
        $this->modul = Modul::find($modul_id);
        PageLayout::setTitle(_('Zuordnungen'));
    }

    public function description_action($modul_id)
    {
        $response = $this->relay('shared/modul/description/' . $modul_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            $this->render_nothing();
        }
    }

    public function approve_action($modul_id)
    {
        $this->modul_id = $modul_id;
        $this->modul = Modul::find($modul_id);

        if (Request::submitted('approval')) {
            $modul = Modul::get($modul_id);
            $modul->stat = 'genehmigt';
            $stored = $modul->store(false);
            if ($stored) {
                PageLayout::postSuccess(sprintf(
                    _('Modul "%s" genehmigt!'),
                    htmlReady($this->modul->getDisplayName())
                ));
                $this->redirect($this->url_for('/details/' . $modul_id));
                return;
            }
        }
        PageLayout::setTitle(_('Modul genehmigen'));
        $this->setSidebar();
        $this->render_template('module/module/approve', $this->layout);
    }

    public function copy_form_action($modul_id)
    {
        // find the latest version of the given module
        $modul = Modul::find($modul_id);
        $this->modul = reset(Modul::findBySql('LEFT JOIN semester_data '
                . 'ON start = semester_id '
                . "WHERE modul_id = ? OR quelle = ? AND stat = 'genehmigt' "
                . 'ORDER BY beginn DESC LIMIT 1',
                [$modul->id, $modul->quelle]));
        if (!$this->modul) {
            PageLayout::postError(_('Unbekanntes Modul.'));
        } else {
            $this->perm = MvvPerm::get($this->modul);
            $this->submit_url = $this->url_for('/copy/' . $this->modul->id);
            $this->cancel_url = $this->url_for('/index');
        }
        PageLayout::setTitle(_('Modul kopieren'));
    }


    public function copy_action($modul_id)
    {
        $modul = Modul::find($modul_id);
        if (!$modul) {
            PageLayout::postError(_('Unbekanntes Modul.'));
        } else {
            $perm = MvvPerm::get($modul);
            if (Request::submitted('copy')) {
                CSRFProtection::verifyUnsafeRequest();
                // Assign the copied module to the same versions of studycourses
                // as the original?
                $copy_assignments_to_versions = Request::get('copy_assignments');
                $copy = $modul->copy(true, $copy_assignments_to_versions);
                // Kopie kennzeichnen (eindeutiger Code und Titel des Moduls in
                // allen Deskriptoren
                $copy->code = $modul->code;
                $copy->beschlussdatum = null;
                $current = Semester::find($copy->end);
                $next = Semester::findNext($current->beginn);
                if (is_array($next) && !empty($next)) {
                    $next = array_pop($next);
                }
                $copy->start = $next->id;
                // get end semester from selection
                $end_sem = Semester::find(Request::option('end_sem'));
                $copy->end = $next->beginn > $end_sem->beginn ? '' : $end_sem->id;

                // quelle: Klammerung von (Gültigkeit-) Versionen desselben Moduls
                // Gießen: Modul-ID des ursprünglichen Moduls
                $copy->quelle = $modul->quelle ?: $modul->id;
                //UOL Version um 1 hochzählen
                $copy->version = $copy->version + 1;
                // don't show the new Modul
                $copy->stat = 'planung';

                $store = false;
                try {
                    $copy->verifyPermission();
                    // UOL: Don't validate
                    $copy->store(false);
                    PageLayout::postSuccess(sprintf(
                        _('Das Modul "%s" und alle zugehörigen Modulteile wurden kopiert!'),
                        htmlReady($modul->getDisplayName())
                    ));
                } catch (InvalidValuesException $e) {
                    PageLayout::postError(
                        _('Das Modul konnte nicht kopiert werden!') . ' ' . htmlReady($e->getMessage())
                    );
                } catch (Exception $e) {
                    PageLayout::postError(_('Beim Kopieren trat ein Fehler auf!'));
                }
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * Retrieves all fields from descriptor in the original language and
     * returns them as a json object.
     */
    public function show_original_action()
    {
        if (Request::isXhr()) {
            if (Request::option('type') === 'modulteil') {
                $parent = Modulteil::find(Request::option('id'));
                $formatted_fields = [
                    'voraussetzung',
                    'kommentar',
                    'kommentar_kapazitaet',
                    'kommentar_wl_praesenz',
                    'kommentar_wl_bereitung',
                    'kommentar_wl_selbst',
                    'kommentar_wl_pruef',
                    'pruef_vorleistung pruef_leistung'];
            } else {
                $parent = Modul::find(Request::option('id'));
                $formatted_fields = [
                    'voraussetzung',
                    'kompetenzziele',
                    'inhalte',
                    'literatur',
                    'links',
                    'kommentar',
                    'kommentar_kapazitaet',
                    'kommentar_wl_selbst',
                    'kommentar_wl_pruef',
                    'pruef_vorleistung',
                    'pruef_leistung',
                    'pruef_wiederholung ersatztext'
                ];
            }
            if ($parent) {
                $deskriptor_array = [];
                $deskriptor = $parent->getDeskriptor();
                foreach ($deskriptor->toArray() as $key => $value) {
                    if ($deskriptor->isI18nField($key) && MVVController::trim($value->original()) !== '') {
                        if (in_array($key, $formatted_fields)) {
                            $deskriptor_array[$key]['value'] = formatReady($value->original());
                        } else {
                            $deskriptor_array[$key]['value'] = $value->original();
                        }
                        $deskriptor_array[$key]['empty'] = 0;
                    } else {
                        $deskriptor_array[$key]['value'] = _('Keine Angabe in Originalfassung.');
                        $deskriptor_array[$key]['empty'] = 1;
                    }
                }

                // datafields
                foreach ($deskriptor->datafields as $entry) {
                    if ($entry->lang === '') {
                        $df = $entry->getTypedDatafield();
                        $value = $df->getDisplayValue();
                        $df_id = 'datafields_' . $entry->datafield_id;
                        if (MVVController::trim($value)) {
                            $deskriptor_array[$df_id]['value'] = $value;
                            $deskriptor_array[$df_id]['empty'] = 0;
                        } else {
                            $deskriptor_array[$df_id]['value'] = _('Keine Angabe in Originalfassung.');
                            $deskriptor_array[$df_id]['empty'] = 1;
                        }
                    }
                }
                $this->render_json($deskriptor_array);
            } else {
                $this->render_nothing();
            }
        } else {
            $this->redirect($this->url_for('/index'));
        }
    }

    public function modulteil_action($modulteil_id = null)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (!$this->modulteil) {
            $this->modul = Modul::find(Request::option('modul_id'));
            if (!$this->modul) {
                PageLayout::postError(_('Unbekanntes Modul.'));
                $this->redirect($this->url_for('/index'));
                return;
            }
            $this->modulteil = new Modulteil();
            $this->modulteil->modul_id = $this->modul->id;
        } else {
            $this->modul = $this->modulteil->modul;
        }
    
        $this->formen = [];
        foreach ($GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'] as $key => $form) {
            if ($form['parent'] === '') {
                if ($form['visible'] || is_null($form['visible'])) {
                    $this->formen[$key]['group'] = [
                        'key'  => $key,
                        'name' => $form['name']
                    ];
                }
            } else {
                if ($form['visible']) {
                    $this->formen[$form['parent']]['options'][] = [
                        'key'  => $key,
                        'name' => $form['name']
                    ];
                }
            }
        }
        $this->setSidebar();

        if ($this->modulteil->isNew()) {
            PageLayout::setTitle(_('Neuen Modulteil anlegen'));
            $success_message = ('Der Modulteil "%s" wurde angelegt.');
            $this->display_language = $this->modulteil->getDefaultLanguage();
            $this->deskriptor = $this->modulteil->getDeskriptor($this->display_language, true);
            PageLayout::postInfo(sprintf(
                _('Sie legen einen neuen Modulteil für das Modul <em>%s</em> an. Der Modulteil muss zunächst in der Ausgabesprache <em>%s</em> angelegt werden.'),
                htmlReady($this->modul->getDisplayName()),
                htmlReady($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['name'])
            ));
        } else {
            $this->display_language = Request::option('display_language', $this->modulteil->getDefaultLanguage());
            $this->deskriptor = $this->modulteil->getDeskriptor($this->display_language, true);
            $this->translations = $this->deskriptor->getAvailableTranslations();
    
            if (!in_array($this->display_language, $this->translations)) {
                PageLayout::setTitle(sprintf(
                    _('Modulteil: <em>%s</em> in der Ausgabesprache <em>%s</em> neu anlegen.'),
                    $this->modulteil->getDisplayName(),
                    $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['name']
                ));
            } else {
                PageLayout::setTitle(sprintf(_('Modulteil: %s'), htmlReady($this->modulteil->getDisplayName())));
            }
            $success_message = _('Der Modulteil "%s" wurde geändert.');
            // sidebar widget for selecting language
            $template_factory = $this->get_template_factory();
            $sidebar = Sidebar::get();
            $widget = new ListWidget();
            $widget->setTitle(_('Ausgabesprache'));
            $widget_element = new WidgetElement(
                $template_factory->render('shared/deskriptor_language',
                    [
                        'modul'   => $this->modulteil,
                        'sprache' => $this->display_language,
                        'link'    => $this->url_for('/modulteil', $this->modulteil->id),
                        'url'     => $this->url
                    ]
                )
            );
            $widget->addElement($widget_element);
            $sidebar->addWidget($widget, 'languages');
        }

        $this->def_lang = $this->display_language === $this->modulteil->getDefaultLanguage();

        if (!$this->def_lang) {
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Alle Texte der Originalfassung anzeigen'),
                '#',
                Icon::create('consultation'),
                ['class' => 'mvv-show-all-original']
            );
        }

        $this->language = $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['content_language'];
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->def_lang) {
                $this->modulteil->modul_id = Request::option('modul_id');
                $this->modulteil->flexnow_modul = trim(Request::get('flexnow_modul'));
                $this->modulteil->nummer = Request::int('nummer');
                $this->modulteil->num_bezeichnung = Request::option('num_bezeichnung');
                $this->modulteil->lernlehrform = Request::option('lernlehrform');
                $this->modulteil->semester = Request::option('semester');
                if (Request::get('kap_unbegrenzt')) {
                    $this->modulteil->kapazitaet = '';
                } else {
                    $kapazitaet = trim(Request::get('kapazitaet'));
                    $this->modulteil->kapazitaet = $kapazitaet === '' ? null : $kapazitaet;
                }
                $this->modulteil->kp = Request::int('kp');
                $this->modulteil->sws = Request::int('sws', 0);
                $this->modulteil->wl_praesenz = Request::int('wl_praesenz', 0);
                $this->modulteil->wl_bereitung = Request::int('wl_bereitung', 0);
                $this->modulteil->wl_selbst = Request::int('wl_selbst', 0);
                $this->modulteil->wl_pruef = Request::int('wl_pruef', 0);
                $this->modulteil->anteil_note = Request::int('anteil_note', 0);
                $this->modulteil->ausgleichbar = Request::int('ausgleichbar', 0);
                $this->modulteil->pflicht = Request::int('pflicht', 0);
                $this->modulteil->assignLanguagesOfInstruction(Request::optionArray('language_items'));
            }

            $deskriptor_fields = ['bezeichnung', 'voraussetzung', 'kommentar',
                'kommentar_kapazitaet', 'kommentar_wl_praesenz',
                'kommentar_wl_bereitung', 'kommentar_wl_selbst',
                'kommentar_wl_pruef', 'pruef_vorleistung', 'pruef_leistung',
                'kommentar_pflicht'];
            
            foreach ($deskriptor_fields as $deskriptor_field) {
                if ($this->deskriptor->isI18nField($deskriptor_field)) {
                    $this->deskriptor->$deskriptor_field->setLocalized(
                        trim(Request::get($deskriptor_field)),
                        $this->language
                    );
                } else {
                    $this->deskriptor->setValue($deskriptor_field, trim(Request::get($deskriptor_field)));
                }
            }

            // update datafields
            foreach (Request::getArray('datafields') as $df_key => $df_value) {
                $df = $this->deskriptor->datafields->findOneBy('datafield_id', $df_key);
                if ($df) {
                    $tdf = $df->getTypedDatafield();
                    $tdf->setContentLanguage($this->language);
                    $tdf->setValueFromSubmit($df_value);
                    $tdf->store();
                }
            }

            // check permission (add a Modulteil to Modul) if Modulteil is new
            if ($this->modulteil->isNew()) {
                $perm = MvvPerm::get($this->modul);
                if (!$perm->haveFieldPermModulteile(MvvPerm::PERM_CREATE)) {
                    throw new Exception(_('Keine Berechtigung.'));
                }
            }

            try {
                $this->modulteil->verifyPermission();
                $stored = $this->modulteil->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf(
                        $success_message,
                        htmlReady($this->modulteil->getDisplayName())
                    ));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/details/' . $this->modulteil->modul_id));
                return;
            }
        }
        if ($this->display_language !== $this->modulteil->getDefaultLanguage() && $this->deskriptor->isNew()) {
            PageLayout::postInfo(sprintf(
                _('Neue Beschreibung zum Modulteil "%s" in der Ausgabesprache %s anlegen.'),
                htmlReady($this->modulteil->getDisplayName()),
                htmlReady($GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['values'][$this->display_language]['name'])
            ));
        }
        $this->cancel_url = $this->url_for('/details/' .  $this->modulteil->modul_id);

        $action_widget = Sidebar::get()->getWidget('actions');
        $action_widget->addLink(
            _('Log-Einträge dieses Modulteils'),
            $this->url_for('shared/log_event/show/Modulteil/' . $this->modulteil->id,
                ['object2_type' => 'ModulteilDeskriptor', 'object2_id' => $this->deskriptor->id]
            ),
            Icon::create('log')
        )->asDialog();

        $this->render_template('module/module/modulteil', $this->layout);
    }

    /**
     * Deletes a descriptor from Modulteil
     *
     * @param type $deskriptor_id
     * @throws Trails_Exception
     */
    public function delete_modulteil_deskriptor_action($deskriptor_id, $language)
    {
        $deskriptor = ModulteilDeskriptor::find($deskriptor_id);
        if (is_null($deskriptor)) {
            throw new Trails_Exception(404, _('Unbekannter Deskriptor.'));
        }
        $def_lang = $deskriptor->modulteil->getDefaultLanguage();
        if ($language === $def_lang) {
            throw new Trails_Exception(403, _('Ein Deskriptor in der Original-Sprache kann nicht gelöscht werden.'));
        }
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($deskriptor->deleteTranslation($language)) {
                PageLayout::postSuccess(sprintf(
                    _('Der Deskriptor "%s" des Modulteils "%s" wurde gelöscht!'),
                    htmlReady($deskriptor->getDisplayName()),
                    htmlReady($deskriptor->modulteil->getDisplayName())
                ));
            }
        }
        $this->redirect($this->url_for('/details/' . Request::option('modul_id')));
    }

    public function copy_modulteil_action($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        if (!$modulteil) {
            PageLayout::postError(_('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        }
        $copy_modulteil = $modulteil->copy();
        $copy_modulteil->store();
        PageLayout::postInfo(sprintf(
            _('Der Modulteil "%s" wurde kopiert. Klicken Sie auf "übernehmen", um Änderungen an der Kopie zu speichern.'),
            htmlReady($copy_modulteil->getDisplayName())
        ));
        $this->redirect($this->url_for('/modulteil/' . $copy_modulteil->id));
    }

    public function modulteil_lvg_action($modulteil_id)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError(_('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->modulteil_id = $this->modulteil->getId();
            $this->search_lvgruppe($this->modulteil->getId());
            if (Request::isXhr()) {
                $this->render_template('module/module/modulteil_lvg', null);
            } else {
                $this->modul = Modul::get($this->modulteil->modul_id);
                $this->modul_id = $this->modul->getId();
                $this->perform_relayed('details/' . $this->modulteil->modul_id . '/' . $this->modulteil->id);
            }
        }
    }

    private function search_lvgruppe($modulteil_id)
    {
        //Quicksearch
        $query = 'SELECT lvgruppe_id, name
                FROM mvv_lvgruppe WHERE name LIKE :input
                AND lvgruppe_id NOT IN (
                    SELECT lvgruppe_id FROM mvv_lvgruppe_modulteil WHERE modulteil_id = ' . DBManager::get()->quote($modulteil_id) .
            ')';
        $search = new SQLSearch($query, _('LV-Gruppe suchen'));
        $this->qs_search_id = md5(serialize($search));
        $this->search = QuickSearch::get(
            'lvgruppe_id_' . $modulteil_id, $search
        )->setInputStyle('width: 240px')
            ->noSelectbox();
    }

    public function add_lvgruppe_action($modulteil_id)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError(_('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->lvgruppe = Lvgruppe::find(
                Request::option('lvgruppe_id_' . $this->modulteil->getId())
            );
            if (!$this->lvgruppe) {
                PageLayout::postError(_('Unbekannte Lehrveranstaltungsgruppe.'));
            } else {
                $this->lvg_modulteil = LvgruppeModulteil::get(
                    [
                        $this->lvgruppe->getId(),
                        $this->modulteil->getId()
                    ]
                );
                if ($this->lvg_modulteil->isNew()) {
                    $this->lvg_modulteil->store();
                    PageLayout::postSuccess(sprintf(
                        _('Die Lehrveranstaltungsgruppe "%s" wurde dem Modulteil "%s" zugeordnet.'),
                        htmlReady($this->lvgruppe->getDisplayName()),
                        htmlReady($this->modulteil->getDisplayName())
                    ));
                } else {
                    PageLayout::postInfo(sprintf(
                        _('Die bestehende Zuordnung der Lehrveranstaltungsgruppe "%s" zum Modulteil "%s" wurde nicht geändert.'),
                        htmlReady($this->lvgruppe->getDisplayName()),
                        htmlReady($this->modulteil->getDisplayName())
                    ));
                }
            }
            $this->redirect($this->url_for('/details/' . $this->modulteil->modul_id . '/' . $this->modulteil->id));
        }
    }

    public function delete_lvgruppe_action($modulteil_id, $lvgruppe_id)
    {
        $lvg_modulteil = LvgruppeModulteil::find([$lvgruppe_id, $modulteil_id]);
        if (!$lvg_modulteil) {
            PageLayout::postError(_('Unbekannte Zuordnung.'));
        } else {
            $lvgruppe = Lvgruppe::find($lvgruppe_id);
            $modulteil = Modulteil::find($modulteil_id);
            if (Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();
                PageLayout::postSuccess(sprintf(
                    _('Die Lehrveranstaltungsgruppe "%s" wurde vom Modulteil "%s" entfernt.'),
                    htmlReady($lvgruppe->getDisplayName()),
                    htmlReady($modulteil->getDisplayName())
                ));
                $lvg_modulteil->delete();
            }
        }
        $this->redirect($this->url_for('/details/' . $modulteil->modul_id . '/' . $modulteil->id));
    }

    public function lvgruppe_action($modulteil_id, $lvgruppe_id = null)
    {
        $this->modulteil = Modulteil::find($modulteil_id);
        if (is_null($this->modulteil)) {
            PageLayout::postError(_('Unbekannter Modulteil.'));
            $this->redirect($this->url_for('/index'));
            return;
        } else {
            $this->lvgruppe = Lvgruppe::get($lvgruppe_id);
            if ($this->lvgruppe->isNew()) {
                $this->lvgruppe = new Lvgruppe();
                PageLayout::setTitle(_('Neue Lehrveranstaltungsgruppe anlegen'));
                $success_message = _('Die Lehrveranstaltungsgruppe "%s" wurde angelegt.');
                $this->headline = _('Neue Lehrveranstaltungsgruppe anlegen.');
            } else {
                PageLayout::setTitle(_('Lehrveranstaltungsgruppe bearbeiten'));
                $success_message = _('Die Lehrveranstaltungsgruppe "%s" wurde geändert.');
                $this->headline = sprintf(_('Lehrveranstaltungsgruppe "%s" bearbeiten.'),
                    $this->lvgruppe->getDisplayName());
            }
            $this->cancel_url = $this->url_for('/index');
            $this->submit_url = $this->url_for('/lvgruppe' . '/' . $this->modulteil->id . '/' . $this->lvgruppe->id);
            if (Request::submitted('store')) {
                CSRFProtection::verifyUnsafeRequest();
                $stored = false;
                $this->lvgruppe->name = trim(Request::get('name'));
                $this->lvgruppe->alttext = Request::i18n('alttext')->trim();

                try {
                    $stored = $this->lvgruppe->store();
                    if ($stored) {
                        $this->lvg_modulteil = LvgruppeModulteil::get(
                            [
                                $this->lvgruppe->getId(),
                                $this->modulteil->getId()
                            ]
                        );
                        $stored_modulteil = $this->lvg_modulteil->store();
                    }
                } catch (InvalidValuesException $e) {
                    PageLayout::postError($e->getMessage());
                }
                if ($stored !== false && $stored_modulteil !== false) {
                    if ($stored) {
                        PageLayout::postSuccess(sprintf(
                            $success_message,
                            htmlReady($this->lvgruppe->getDisplayName())
                        ));
                    } else {
                        PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                    }
                    $this->relocate('/details/' . $this->modulteil->modul_id . '/' . $this->modulteil->id);
                    return;
                }
            }
        }
        $this->render_template('lvgruppen/lvgruppen/lvgruppe');
    }

    public function delete_action($modul_id)
    {
        $modul = Modul::get($modul_id);
        if ($modul->isNew()) {
             PageLayout::postError(_('Unbekanntes Modul.'));
        } else {
            if (Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();
                PageLayout::postSuccess(sprintf(
                    _('Modul "%s" gelöscht!'),
                    htmlReady($modul->getDisplayName())
                ));
                $modul->delete();
            }
        }
        $this->redirect($this->url_for('/details/' . $modul->id));
    }

    public function delete_modulteil_action($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        $modul_id = $modulteil->modul_id;
        if (!$modulteil) {
             PageLayout::postError(_('Unbekannter Modulteil.'));
        } else {
            if (Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();
                PageLayout::postSuccess(sprintf(
                    _('Modulteil "%s" gelöscht!'),
                    htmlReady($modulteil->getDisplayName())
                ));
                $modulteil->delete();
            }
        }
        $this->redirect($this->url_for('/details/' . $modul_id));
    }

    public function details_action($modul_id, $modulteil_id = null)
    {
        $this->modul = Modul::get($modul_id);
        $this->modul_id = $this->modul->isNew() ? null : $this->modul->getId();
        if ($modulteil_id) {
            $modulteil = Modulteil::get($modulteil_id);
            $this->modulteil_id = $modulteil->isNew() ? null : $modulteil->getId();
            $this->search_lvgruppe($this->modulteil_id);
        }
        if (Request::isXhr()) {
            $this->details_url = '/modulteil_lvg/';
        } else {
            $this->perform_relayed('index');
        }
    }

    public function diff_select_action($modul_id)
    {
        $this->modul = Modul::find($modul_id);
        if ($this->modul) {
            // Modulsuche
            $query = "SELECT
                    mm.modul_id,
                    CONCAT(mmd.bezeichnung, ', ', IF(ISNULL(mm.code), '', mm.code),
                    IF(ISNULL(sd1.name), '', CONCAT(', ', IF(ISNULL(sd2.name),
                    CONCAT('ab ', sd1.name),CONCAT(sd1.name, ' - ', sd2.name))))) AS modul_name
                    FROM mvv_modul mm LEFT JOIN mvv_modul_deskriptor mmd
                    ON mm.modul_id = mmd.modul_id
                    LEFT JOIN semester_data sd1 ON mm.start = sd1.semester_id
                    LEFT JOIN semester_data sd2 ON mm.end = sd2.semester_id
                    WHERE (mm.code LIKE :input
                    OR mmd.bezeichnung LIKE :input)
                    AND mm.modul_id <> "
                   . DBManager::get()->quote($this->modul->getId())
                   . " ORDER BY modul_name";
            $sql_search_modul = new SQLSearch($query, _('Modul suchen'), 'modul');
            $this->qs_id_module = md5(serialize($sql_search_modul));
            $this->search_modul = QuickSearch::get('old_module_id', $sql_search_modul)
                    ->setInputStyle('width: 240px');

            $this->quelle = $this->modul->modul_quelle;
            $this->variante = $this->modul->modul_variante;
        } else {
            PageLayout::postError(_('Unbekanntes Modul!'));
            $this->relocate('/index');
        }
    }

    public function diff_action($new_id = null, $old_id = null)
    {
        $new_module = Modul::find(Request::option('new_id', $new_id));
        $old_module = Modul::find(Request::option('old_module_id', $old_id));

        if (!$new_module || !$old_module) {
            PageLayout::postError(_('Unbekanntes Modul!'));
            if ($new_module) {
                $this->redirect($this->url_for('/diff_select/' . $new_module->id));
            } else {
                $this->redirect($this->url_for('/index'));
            }
        } else {
            if (Request::isXhr()) {
                $this->response->add_header(
                    'X-Location',
                    $this->url_for('/diff/' . $new_module->id . '/' . $old_module->id)
                );
            }
            $type_new = 1;
            $count_modulteile = count($new_module->modulteile);
            if ($count_modulteile === 0) {
                $type_new = 3;
            } else if ($count_modulteile === 1) {
                $type_new = 2;
            }

            $type_old = 1;
            $count_modulteile = count($old_module->modulteile);
            if ($count_modulteile === 0) {
                $type_old = 3;
            } else if ($count_modulteile === 1) {
                $type_old = 2;
            }

            PageLayout::setTitle(_('Vergleichsansicht'));
            PageLayout::addStylesheet('print.css');
            $factory = $this->get_template_factory();
            $template = $factory->open('module/module/diff');
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
            $template->set_attributes([
                'new_module' => $new_module,
                'old_module' => $old_module,
                'type_new'   => $type_new,
                'type_old'   => $type_old,
                'plugin'   => $this->plugin
            ]);

            $this->render_text($template->render());
        }
    }

    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('module');
            $this->reset_page();
        } else {
            $this->reset_search('module');
            $this->reset_page();
            // responsible Institutes
            $this->filter['mvv_modul_inst.gruppe'] = 'hauptverantwortlich';
            if (!$this->filter['mvv_modul_inst.institut_id']) {
                // only institutes the user has an assigned MVV role
                $this->filter['mvv_modul_inst.institut_id'] = MvvPerm::getOwnInstitutes();
            }

            // set default semester filter
            if (!isset($this->filter['start_sem.beginn']) || !isset($this->filter['end_sem.ende'])) {
                $sem_time_switch = Config::get()->SEMESTER_TIME_SWITCH;
                // switch semester according to time switch
                // (n weeks before next semester)
                $current_sem = Semester::findByTimestamp(
                    time() + $sem_time_switch * 7 * 24 * 3600
                );
                if ($current_sem) {
                    $this->filter['start_sem.beginn'] = $current_sem->beginn;
                    $this->filter['end_sem.ende'] = $current_sem->beginn;
                }
            }
    
            $this->do_search(
                'Modul',
                trim(Request::get('modul_suche_parameter')),
                Request::get('modul_suche'), $this->filter
            );
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('module');
        $this->reset_page();
    }

    public function sort_action()
    {
        $target = explode('_', Request::option('list_id'));
        $success = false;
        if ($target[0] === 'modulteil') {
            $success = $this->sort_lvgruppen($target[1]);
        } else {
            $success = $this->sort_modulteile($target[0]);
        }
        if ($success) {
            $this->set_status(204);
        } else {
            $this->set_status(400);
        }
        $this->render_nothing();
    }

    /**
     * sorts Modulteile
     */
    private function sort_modulteile($modul_id)
    {
        $modul = Modul::find($modul_id);
        if ($modul) {
            $orderedIds = Request::getArray('newOrder');
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $modulteil_id) {
                    $modulteil = $modul->modulteile->find($modulteil_id);
                    if ($modulteil->position !== $i) {
                        $modulteil->position = $i;
                        $modulteil->store(false);
                    }
                    $i++;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * sorts LVGruppen
     */
    private function sort_lvgruppen($modulteil_id)
    {
        $modulteil = Modulteil::find($modulteil_id);
        if ($modulteil) {
            $orderedIds = Request::getArray('newOrder');
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    list($foo, $lvgruppe_id) = explode('_', $id);
                    $lvgruppe_modulteil = LvgruppeModulteil::find(
                        [
                            $lvgruppe_id,
                            $modulteil->getId()
                        ]
                    );
                    if ($lvgruppe_modulteil && $lvgruppe_modulteil->position !== $i) {
                        $lvgruppe_modulteil->position = $i;
                        $lvgruppe_modulteil->store(false);
                    }
                    $i++;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
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

        // module status
        $this->filter['mvv_modul.stat'] = trim(Request::get('status_filter')) ? Request::option('status_filter') : null;

        // responsible Institutes
        $this->filter['mvv_modul_inst.gruppe'] = 'hauptverantwortlich';
        if (trim(Request::get('institut_filter'))) {
            $this->filter['mvv_modul_inst.institut_id'] = Request::option('institut_filter');
        } else {
            // only institutes the user has an assigned MVV role
            $this->filter['mvv_modul_inst.institut_id'] = MvvPerm::getOwnInstitutes();
        }

        // store filter
        $this->reset_page();
        $this->sessSet('filter', $this->filter);
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
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        $widget  = new ViewsWidget();
        $widget->addLink(
            _('Liste der Module'),
            $this->url_for('module/module/index')
        )->setActive(get_called_class() === 'Module_ModuleController');
        $widget->addLink(
            _('Gruppiert nach verantwortlichen Einrichtungen'),
            $this->url_for('module/institute/index')
        )->setActive(get_called_class() === 'Module_InstituteController');
        $sidebar->addWidget($widget, 'views');

        $widget  = new ActionsWidget();
        $widget->setTitle(_('Aktionen'));
        if (MvvPerm::havePermCreate('Modul')) {
            $widget->addLink(
                _('Neues Modul anlegen'),
                $this->url_for('/modul'),
                Icon::create('file+add')
            );
        }
        $sidebar->addWidget($widget, 'actions');

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
        }
        if ($this->show_sidebar_filter) {
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie die Module verwalten. Ein Modul kann ein oder mehrere Modulteil(e) haben.').'</br>'));
        $widget->addElement(new WidgetElement(_('Module können nur gelöscht werden, wenn Sie noch keinem Studiengang zugeordnet sind.')));
        $helpbar->addWidget($widget);
        $this->sidebar_rendered = true;
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        // Nur Module von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        if (!$this->filter['mvv_modul_inst.institut_id']) {
            unset($this->filter['mvv_modul_inst.institut_id']);
        }
        $modul_filter = array_merge(
            [
                'mvv_modul_inst.gruppe'      => 'hauptverantwortlich',
                'mvv_modul_inst.institut_id' => MvvPerm::getOwnInstitutes()
            ],
            $this->filter
        );
        
        $template_factory = $this->get_template_factory();
        $template = $template_factory->open('shared/filter');

        // Status
        $modul_ids = Modul::findByFilter($modul_filter);
        $template->set_attribute('status', Modul::findStatusByIds($modul_ids));
        $template->set_attribute('selected_status', $this->filter['mvv_modul.stat']);
        $template->set_attribute('status_array', $GLOBALS['MVV_MODUL']['STATUS']['values']);

        // Institutes
        $template->set_attribute('institute', Modul::getAllAssignedInstitutes('name', 'ASC', $modul_filter));
        $template->set_attribute('institute_count', 'count_objects');
        $template->set_attribute('selected_institut', $this->filter['mvv_modul_inst.institut_id']);

        // Semesters
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');
        $selected_semester = $semesters->findOneBy('beginn', $this->filter['start_sem.beginn']);


        $template->set_attribute('semester', $semesters);
        $template->set_attribute('selected_semester', $selected_semester->id);
        $template->set_attribute('default_semester', Semester::findCurrent()->id);

        $template->set_attribute('action', $this->url_for('/set_filter'));
        $template->set_attribute('action_reset', $this->url_for('/reset_filter'));

        $filter_template = $template->render();

        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget, 'filters');
    }

    /**
     * adds the search function to the sidebar
     */
    private function sidebar_search()
    {
        $query = "
            SELECT DISTINCT `mvv_modul`.`modul_id`,
                    CONCAT(`mvv_modul_deskriptor`.`bezeichnung`, ' (', `code`, ')')
                        AS `name` FROM `mvv_modul`
                LEFT JOIN `mvv_modul_deskriptor`
                    ON `mvv_modul`.`modul_id` = `mvv_modul_deskriptor`.`modul_id`
                LEFT JOIN `mvv_modul_inst`
                    ON `mvv_modul_deskriptor`.`modul_id` = `mvv_modul_inst`.`modul_id`
                LEFT JOIN semester_data as start_sem
                    ON (mvv_modul.start = start_sem.semester_id)
                LEFT JOIN semester_data as end_sem
                    ON (mvv_modul.end = end_sem.semester_id)
            WHERE (`code` LIKE :input OR `mvv_modul_deskriptor`.`bezeichnung` LIKE :input) "
                . ModuleManagementModel::getFilterSql($this->filter) . '
            ORDER BY `name` ASC';
        $search_term =
                $this->search_term ? $this->search_term : _('Modul suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(
            _('Modul suchen'),
            'modul_suche',
            true,
            new SQLSearch($query, $search_term, 'modul_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');
    }
}
