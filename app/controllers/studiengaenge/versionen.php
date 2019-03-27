<?php
/**
 * versionen.php - Studiengaenge_VersionenController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once dirname(__FILE__) . '/shared_version.php';

class Studiengaenge_VersionenController extends SharedVersionController
{

    public $chooser_filter = null;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/studiengaenge/versionen');
        $this->filter = $this->sessGet('filter', []);
        $this->action = $action;

        $this->chooser_filter = $this->sessGet(
            'chooser_filter',
            Request::optionArray('chooser')
        );
        URLHelper::bindLinkParam('chooser', $this->chooser_filter);
    }

    public function chooser_action()
    {
        $step = Request::option('step');
        switch ($step) {
            case 'index' :
                $this->chooser_filter['fachbereich'] =
                    Request::option('id', $this->chooser_filter['fachbereich']);
                $this->chooser_filter['stgteile'] = null;
                $this->chooser_faecher_fachbereich();
                $list = 'faecher_fachbereich';
                break;
            case 'faecher_fachbereich' :
                $this->chooser_filter['fach'] =
                    Request::option('id', $this->chooser_filter['fach']);
                $this->chooser_filter['stgteile'] = null;
                $this->chooser_stgteile_fach();
                $list = 'stgteile_fach';
                break;
            case 'stgteile_fach' :
                $this->chooser_filter['stgteile'] =
                    Request::option('id', $this->chooser_filter['stgteile']);
                $this->redirect($this->url_for('/index/' .  $this->chooser_filter['stgteile']));
                return;
            default :
                throw new Trails_Exception(400);
        }
        $this->name = $list;
        foreach ((array) $this->lists[$list]['elements'] as $key => $element) {
            $this->list['elements'][$key]['name'] = htmlReady($element['name']);
        }
        $this->list['headline'] = $this->lists[$list]['headline'];
        if ($this->lists[$list]['stop']) {
            $this->list['stop'] = 1;
        }
        $this->sessSet('chooser_filter', $this->chooser_filter);
        if (Request::isXhr()) {
            $this->render_template('shared/chooser_form');
        }
    }

    protected function chooser_kategorien_fachbereich()
    {
        $kategorien = AbschlussKategorie::findByFachbereich(
            $this->chooser_filter['fachbereich']
        );
        foreach ($kategorien as $kategorie) {
            $this->lists['kategorien']['elements'][$kategorie->id] = [
                'name' => $kategorie->name
            ];
        }
        $this->lists['kategorien']['headline'] = _('Abschluss-Kategorie');
        $this->lists['kategorien']['selected'] = $this->chooser_filter['kategorie'];
    }

    protected function chooser_faecher_fachbereich()
    {
        $faecher = Fach::findByFachbereich($this->chooser_filter['fachbereich'], true);
        foreach ($faecher as $fach) {
            $this->lists['faecher_fachbereich']['elements'][$fach->id] = ['name' => $fach->name];
        }
        $this->lists['faecher_fachbereich']['headline'] = _('Fach');
        $this->lists['faecher_fachbereich']['selected'] = $this->chooser_filter['fach'];
    }

    protected function chooser_studiengaenge_kategorie()
    {
        $studiengaenge = Studiengang::findByAbschlussKategorieFachbereich(
            $this->chooser_filter['kategorie'],
            $this->chooser_filter['fachbereich']
        );
        foreach ($studiengaenge as $studiengang) {
            $this->lists['studiengaenge']['elements'][$studiengang->id] = [
                'name' => $studiengang->name
            ];
        }
        $this->lists['studiengang']['headline'] = _('Studiengang');
        $this->lists['studiengang']['selected'] = $this->chooser_filter['studiengang'];
    }

    protected function chooser_stgabschnitte_studiengang()
    {
        $stgteile = StudiengangStgteil::findByStudiengang(
            $this->chooser_filter['studiengang']
        );
        foreach ($stgteile as $stgteil) {
            $this->lists['stg_abschnitte']['elements'][$stgteil->id] = [
                'name' => $stgteil->zusatz
            ];
        }
        $this->lists['stg_abschnitte']['headline'] = _('Studiengangteil');
        $this->lists['stg_abschnitte']['selected'] = $this->chooser_filter['stg_abschnitt'];
    }

    protected function chooser_faecher_stgteil()
    {
        $this->lists['faecher']['elements'] =
                Fach::toArrayByFachbereichStgteil(
                    $this->chooser_filter['fachbereich'],
                    $this->chooser_filter['stgteil']);
        $this->lists['faecher']['headline'] = _('Fach');
        $this->lists['faecher']['selected'] = $this->chooser_filter['fach'];
    }

    protected function chooser_stgteile()
    {
        $stgteile = StudiengangTeil::findByStudiengangStgteilBez(
            $this->chooser_filter['studiengang'],
            $this->chooser_filter['stgteil']
        );
        foreach ($stgteile as $stgteil) {
            $this->lists['stgteile']['elements'][$stgteil->id] = [
                'name' => $stgteil->getDisplayName()
            ];
        }
        $this->lists['stgteile']['stop'] = 1;
        $this->lists['stgteile']['headline'] = _('Studiengangteil');
        $this->lists['stgteile']['selected'] =
                $this->chooser_filter['stgteil'];
    }

    protected function chooser_stgteile_fach()
    {
        $stgteile = StudiengangTeil::findByFach(
            $this->chooser_filter['fach'], null, 'zusatz,kp', 'ASC'
        );
        foreach ($stgteile as $stgteil) {
            $this->lists['stgteile_fach']['elements'][$stgteil->id] = [
                'name' => $stgteil->getDisplayName()
            ];
        }
        $this->lists['stgteile_fach']['headline'] = _('Studiengangteil');
        $this->lists['stgteile_fach']['stop'] = 1;
        $this->lists['stgteile_fach']['selected'] = $this->chooser_filter['stgteil'];
    }

    protected function chooser_index()
    {
        $filter = ['mvv_fach_inst.institut_id' => MvvPerm::getOwnInstitutes()];
        $fachbereiche = StudiengangTeil::getAssignedFachbereiche('name', 'ASC', $filter);
        foreach ($fachbereiche as $key => $fachbereich) {
            $this->lists['index']['elements'][$key] = ['name' => $fachbereich['name']];
        }
        $this->lists['index']['headline'] = _('Fachbereich');
        $this->lists['index']['selected'] = $this->chooser_filter['fachbereich'];
    }

    /**
     * resets the selection
     */
    public function reset_action()
    {
        $this->chooser_filter = null;
        URLHelper::removeLinkParam('chooser');
        $this->sessRemove('chooser_filter');
        $this->reset_filter_action();
    }

    private function set_chooser()
    {
        $this->chooser_index();
        $this->chooser_faecher_fachbereich();
        $this->chooser_stgteile_fach();
        $template_factory = $this->get_template_factory();
        $template = $template_factory->open('shared/chooser');
        $template->set_attribute('controller', $this);
        $template->set_attribute('lists', $this->lists);

        // add chooser to sidebar
        $sidebar = Sidebar::get();
        $widget = new SidebarWidget('chooser');
        $widget->setTitle(_('Auswahl'));
        $widget->addElement(new WidgetElement($template->render()));
        $sidebar->addWidget($widget);
    }

    public function index_action($stgteil_id = null)
    {
        $stgteil_id = Request::option('id', $stgteil_id ?: $this->chooser_filter['stgteil']);
        PageLayout::setTitle(_('Versionen des gewählten Studiengangteils'));
        if ($stgteil_id) {
            $this->stgteil = StudiengangTeil::find($stgteil_id);
            if (!$this->stgteil) {
                throw new Trails_Exception(404, _('Unbekannter Studiengangteil'));
            }

            $this->initPageParams();

            // set default semester filter
            if (!$this->filter['start_sem.beginn'] || !$this->filter['end_sem.ende']) {
                $sem_time_switch = Config::get()->getValue('SEMESTER_TIME_SWITCH');
                // switch semester according to time switch
                // (n weeks before next semester)
                $current_sem = Semester::findByTimestamp(
                    time() + $sem_time_switch * 7 * 24 * 3600
                );
                if ($current_sem) {
                    $this->filter['start_sem.beginn'] = $current_sem->beginn;
                    $this->filter['end_sem.ende'] = $current_sem->beginn;
                }
                $this->sessSet('filter', $this->filter);
            }

            $this->versionen = StgteilVersion::findByStgteil(
                $stgteil_id,
                $this->sortby,
                $this->order,
                $this->filter
            );

            $this->chooser_filter['stgteil'] = $stgteil_id;
            $this->sessSet('chooser_filter', $this->chooser_filter);
            $this->setSidebar();
            $this->sidebar_filter();
            $this->set_chooser();
        } else {
            $this->chooser_filter = [];
            $this->sessSet('chooser_filter', $this->chooser_filter);
            $this->setSidebar();
            $this->set_chooser();
        }
    }

    public function details_action($stgteil_id)
    {
        $this->stgteil = StudiengangTeil::find($stgteil_id);
        $this->versionen = StgteilVersion::findByStgteil($stgteil_id);

        if (sizeof($this->versionen)) {
            $this->stgteil_id = $stgteil_id;
            if (!Request::isXhr()) {
                $this->perform_relayed('index');
            }
        } else {
            if (Request::isXhr()) {
                $this->set_status(404, 'Not Found');
                $this->render_nothing();
            } else {
                $this->redirect($this->url_for('/index'));
            }
        }
    }

    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        $widget  = new ActionsWidget();
        $widget->addLink(
            _('Auswahl zurücksetzen'),
            $this->url_for('/reset'),
            Icon::create('refresh', 'clickable')
        );
        if ($this->chooser_filter['stgteil']) {
            $stgteil = StudiengangTeil::find($this->chooser_filter['stgteil']);
            if ($stgteil && MvvPerm::haveFieldPermVersionen($stgteil, MvvPerm::PERM_CREATE)) {
                $widget->addLink(
                    _('Neue Version anlegen'),
                    $this->url_for('/version', $this->chooser_filter['stgteil']),
                    Icon::create('file+add')
                );
            }
        }
        $sidebar->addWidget($widget);

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite können Sie die Versionen der Studiengangteile verwalten.')));
        $helpbar->addWidget($widget);
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();

        // Semesters
        $semesters = new SimpleCollection(Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');

        // Status
        $filter = [
            'start_sem.beginn'              => $this->filter['start_sem.beginn'],
            'end_sem.ende'                  => $this->filter['end_sem.ende'],
            'mvv_stgteilversion.stgteil_id' => $this->chooser_filter['stgteil']
        ];
        $version_ids = StgteilVersion::findByFilter($filter);

        $status_results = [];
        foreach ($GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'] as $status => $values) {
            $count_status = StgteilVersion::countBySql(
                'version_id IN (?) AND stat = ? ', [$version_ids, $status]
            );
            $status_results[$status] = [
                'name'          => $values['name'],
                'count_objects' => $count_status
            ];
        }
        $count_status = StgteilVersion::countBySql(
            'version_id IN (?) AND stat IS NULL', [$version_ids]
        );
        $status_results['__undefined__'] = ['count_objects' => $count_status];

        $filter_template = $template_factory->render('shared/filter', array(
            'semester' => $semesters,
            'selected_semester' => $semesters->findOneBy('beginn', $this->filter['start_sem.beginn'])->id,
            'status' => $status_results,
            'selected_status' => $this->filter['mvv_stgteilversion.stat'],
            'status_array' => $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'],
            'action' => $this->url_for('/set_filter'),
            'action_reset' => $this->url_for('/reset_filter')
        ));

        $sidebar = Sidebar::get();
        $widget = new SidebarWidget();
        $widget->setTitle('Filter');
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget, 'filter');
    }

    /**
     * sets filter parameters and store these in session
     */
    public function set_filter_action()
    {
        $this->filter = [];

        // Semester
        $semester_id = Request::option('semester_filter', 'all');
        if ($semester_id != 'all') {
            $semester = Semester::find($semester_id);
            $this->filter['start_sem.beginn'] = $semester->beginn;
            $this->filter['end_sem.ende'] = $semester->beginn;
        } else {
            $this->filter['start_sem.beginn'] = 2147483647;
            $this->filter['end_sem.ende'] = 1;
        }
        // Status
        $this->filter['mvv_stgteilversion.stat']
                = mb_strlen(Request::get('status_filter'))
                ? Request::option('status_filter') : null;

        // store filter
        $this->sessSet('filter', $this->filter);
        $this->reset_page();
        $this->redirect($this->url_for('/index'));
    }

    public function reset_filter_action()
    {
        $this->filter = [];
        $this->reset_page();
        // all semester
        $this->filter['start_sem.beginn'] = 2147483647;
        $this->filter['end_sem.ende'] = 1;
        $this->sessSet('filter', $this->filter);
        $this->redirect($this->url_for('/index'));
    }

}