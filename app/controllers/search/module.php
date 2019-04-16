<?php
/**
 * module.php - Search_ModuleController
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



class Search_ModuleController extends MVVController
{
    private $drill_down_filter = [];

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);

        $this->drill_down_type = Request::option('type');
        $this->drill_down_id = Request::option('id');
        $this->sterm = Request::get('sterm');
        if ($this->sterm) {
            URLHelper::bindLinkParam('sterm', $this->sterm);
        }
        if ($this->drill_down_type) {
            URLHelper::bindLinkParam('type', $this->drill_down_type);
        }
        if ($this->drill_down_id) {
            if($this->drill_down_id == '0') {
                $this->reset_drilldown();
            } else {
                URLHelper::bindLinkParam('id', $this->drill_down_id);
            }
        }

        // set navigation
        Navigation::activateItem('/search/courses/module');

        //set title
        PageLayout::setTitle(_('Modulverzeichnis - Modulsuche'));

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Modulsuche'), $this->url_for('search/module'))
                ->setActive();
        $views->addLink(_('Studienangebot'), $this->url_for('search/angebot'));
        $views->addLink(_('Studiengänge'), $this->url_for('search/studiengaenge'));
        $views->addLink(_('Fach-Abschlusskombinationen'), $this->url_for('search/stgtable'));

        $sidebar->addWidget($views);
    }

    protected static function IsVisible()
    {
        return MVV::isVisibleSearch();
    }

    public function after_filter($action, $args) {
        parent::after_filter($action, $args);
    }

    public function index_action()
    {
        $template = $this->get_template_factory()
                ->open('search/module/_infobox_info');

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement($template->render().'</br>'));
        $widget->addElement(new WidgetElement(_('Auch unvollständige Namen (mindestens 3 Zeichen) werden akzeptiert.')));
        $helpbar->addWidget($widget);

        $this->setSemester();

        $do_search = Request::int('do_search');
        if (mb_strlen(trim(str_replace('%', '', $this->sterm))) < 3) {
            if ($do_search) {
                PageLayout::postInfo(_('Der Suchbegriff muss mindestens 3 Zeichen lang sein.'));
            }
        } else {
            // reset old search
            if ($do_search) {
                $this->page = 1;
                $this->reset_drilldown();
            }

            // search
            // search in module content
            $this->search_result['Modul'] = Modul::search($this->sterm);

            // search for responsible institutes
            $search_responsible_institutes
                    = $this->search_responsible_institutes();
            // search for faecher
            $search_faecher = $this->search_faecher();
            // search for studiengaenge
            $search_studiengaenge = $this->search_studiengaenge();
            $this->search_result['Modul'] = array_unique(
                    array_merge($this->search_result['Modul'],
                            $search_responsible_institutes,
                            $search_faecher,
                            $search_studiengaenge));

            foreach ($this->search_result['Modul'] as $i => $mod_id) {
                $modul = Modul::find($mod_id);
                $start_sem = Semester::find($modul->start);
                $end_sem = Semester::find($modul->end);
                if ($start_sem->beginn > $this->selected_semester->beginn
                        || ($this->selected_semester->ende > $end_sem->ende && $end_sem != null)) {
                    unset($this->search_result['Modul'][$i]);
                }
            }

            if ($do_search) {
                PageLayout::postInfo(sprintf(ngettext(
                            '%s Modul gefunden für die Suche nach <em>%s</em>',
                            '%s Module gefunden für die Suche nach <em>%s</em>',
                            count($this->search_result['Modul'])),
                            count($this->search_result['Modul']),
                            htmlReady($this->sterm)));
            }
        }

        $sidebar = Sidebar::get();

        $widget = new SelectWidget(_('Semesterauswahl'),
            $this->url_for('',['sterm' => $this->sterm]), 'sem_select');
        $options = [];
        $semester = SemesterData::GetSemesterArray();
        unset($semester[0]);
        $semester = array_reverse($semester, true);
        foreach ($semester as $sem) {
            $options[$sem['semester_id']] = $sem['name'];
        }
        $widget->setOptions($options, $this->selected_semester->semester_id);
        $widget->setMaxLength(100);
        $sidebar->addWidget($widget, 'sem_filter');

        $this->input_search = $this->sterm;
        $this->result_count = is_array($this->search_result['Modul']) ? count($this->search_result['Modul']) : 0;

        $drill_down['studiengaenge']['objects'] =
                $this->drilldown_studiengaenge($this->search_result['Modul']);
        $drill_down['faecher']['objects'] =
                $this->drilldown_faecher($this->search_result['Modul']);
        $drill_down['institutes']['objects'] =
                $this->drilldown_institutes($this->search_result['Modul']);
        if (count($drill_down['institutes']['objects'])
                || count($drill_down['studiengaenge']['objects'])
                || count($drill_down['faecher']['objects'])) {

            $widget = new SelectWidget(_('Studiengänge'),
                $this->url_for('',['sterm' => $this->sterm, 'type' => 'Studiengang']), 'id');
            $options = [0 => 'Alle'];
            if(!empty($drill_down['studiengaenge']['objects'])){
                foreach ($drill_down['studiengaenge']['objects'] as $studiengang) {
                    $options[$studiengang->studiengang_id] = $studiengang->name;
                }
            }
            $widget->setOptions($options, null);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'studiengaenge_filter');


            $widget = new SelectWidget(_('Fächer'),
                $this->url_for('',['sterm' => $this->sterm, 'type' => 'Fach']), 'id');
            $options = [0 => 'Alle'];
            if(!empty($drill_down['faecher']['objects'])){
                foreach ($drill_down['faecher']['objects'] as $fach) {
                    $options[$fach->fach_id] = $fach->name;
                }
            }
            $widget->setOptions($options, null);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'faecher_filter');


            $widget = new SelectWidget(_('Verantwortliche Einrichtungen'),
                $this->url_for('',['sterm' => $this->sterm, 'type' => 'Fachbereich']), 'id');
            $widget->class = 'institute-list';
            $options = [0 => 'Alle'];
            $widget->addElement(new SelectElement(0, _('Alle')), 'select-all');
            if(!empty($drill_down['institutes']['objects'])){
                foreach ($drill_down['institutes']['objects'] as $institut) {
                    $widget->addElement(
                        new SelectElement(
                            $institut->institut_id,
                            ($institut->institut_id != $institut->fakultaets_id ? ' ' : '') . $institut->name
                            , $institut->institut_id === $this->drill_down_id
                            ),
                        'select-' . $institut->name
                        );
                }
            }
            $sidebar->addWidget($widget, 'institutes_filter');
        }

        $this->module = [];
        if (is_array($this->search_result['Modul'])
                && count($this->search_result['Modul'])) {
            if (!empty($this->drill_down_type) && !empty($this->drill_down_id)) {
                $this->search_result['Modul'] = $this->filter_modules(
                        $this->search_result['Modul'], $this->drill_down_type, $this->drill_down_id);
            }
            $this->count = count($this->search_result['Modul']);
            $this->module = Modul::getAllEnriched('code, bezeichnung', 'ASC',
                    self::$items_per_page,
                    self::$items_per_page * (($this->page ?: 1) - 1),
                    ['mvv_modul.modul_id' => $this->search_result['Modul']]);
        }
    }


    private function filter_modules($module_ids, $filter_by, $filter_id) {
        $ret = [];
        foreach ($module_ids as $modul_id) {
            switch ($filter_by) {
                case 'Studiengang':
                    foreach (Studiengang::findByModule($modul_id) as $stg) {
                        if ($stg->id == $filter_id) {
                            $ret[] = $modul_id;
                            break;
                        }
                    }
                    break;
                case 'Fach':
                    foreach (Fach::findPublicByModule($modul_id) as $fa) {
                        if ($fa->id == $filter_id) {
                            $ret[] = $modul_id;
                            break;
                        }
                    }
                    break;
                case 'Fachbereich':
                    foreach (ModulInst::findByModul_id($modul_id) as $fab){
                        if ($fab->institut_id == $filter_id) {
                            $ret[] = $modul_id;
                            break;
                        }
                    }
                    break;
            }
        }

        return $ret;
    }

    public function details_action($modul_id)
    {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        $this->setSemester();

        $this->modul = Modul::find($modul_id);
        $courses = $this->getSemesterCourses($this->modul);
        $this->semester_select = [];
        // only valid (semesters between start and end of module)
        // semesters for selector
        // $sem_valid = false;
        // Augsburg Module ohne Angabe der Gültigkeit
        $sem_valid = !((boolean) $this->modul->start);
        $sem_number = 1;
        foreach (Semester::getAll() as $semester) {
            if ($sem_valid || $this->modul->start == $semester->getId()) {
                // show only semesters with assigned courses
                if (in_array($sem_number, $courses)) {
                    $this->semester_select[] = $semester;
                }
                $sem_valid = true;
            }
            if ($this->modul->end == $semester->getId()) {
                break;
            }
            $sem_number++;
        }
        
        $this->semester_select = array_reverse($this->semester_select);
        $response = $this->relay('shared/modul/overview', $this->modul->getId(), $this->selected_semester->semester_id);

        if (Request::isXhr()) {
            $this->modul_content = $response->body;
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    public function drilldown_action()
    {
        $this->initPageParams();
        $this->drilldown();
        $this->page = 1;
        $this->redirect($this->url_for('/index'));
    }

    private function drilldown()
    {
        $object_type = $this->drill_down_type;
        if (in_array($object_type, ['Studiengang', 'Fach', 'Fachbereich'])) {
            $selected_object = $object_type::find($this->drill_down_id);
            if ($selected_object) {
                $this->drill_down_filter = $selected_object->getRelatedModules(true,
                        $this->search_result['Modul']);
            }
        }
    }

    public function reset_drilldown_action()
    {
        $this->initPageParams();
        $this->page = 1;
        $this->reset_drilldown();
        $this->redirect($this->url_for('/index'));
    }

    private function reset_drilldown()
    {
        unset($this->drill_down_filter);
        URLHelper::removeLinkParam('filter');
        unset($this->drill_down_type);
        URLHelper::removeLinkParam('type');
        unset($this->drill_down_id);
        URLHelper::removeLinkParam('id');
    }

    private function search_responsible_persons()
    {
        $term = '%' . $this->sterm . '%';
        $stmt = DBManager::get()->prepare('SELECT modul_id, user_id FROM '
                . 'mvv_modul_user LEFT JOIN auth_user_md5 USING(user_id) '
                . 'WHERE Vorname LIKE ? OR Nachname LIKE ? '
                . ' OR username LIKE ?');
        $stmt->execute([$term, $term, $term]);
        $ret = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $module) {
            $ret[$module['modul_id']][] = $module['user_id'];
        }
        return $ret;
    }

    private function search_responsible_institutes()
    {
        $term = '%' . $this->sterm . '%';
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
                ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
                ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)
                && count($studiengang_public_status)) {
            $query = 'SELECT DISTINCT(mmi.modul_id) '
                    . 'FROM Institute i '
                    . 'INNER JOIN mvv_modul_inst mmi '
                    . 'ON i.Institut_id = mmi.institut_id '
                    . 'INNER JOIN mvv_modul mm ON mmi.modul_id = mm.modul_id '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul msm '
                    . 'ON mm.modul_id = msm.modul_id '
                    . 'INNER JOIN mvv_stgteilabschnitt msa '
                    . 'ON msm.abschnitt_id = msa.abschnitt_id '
                    . 'INNER JOIN mvv_stgteilversion msv '
                    . 'ON msa.version_id =  msv.version_id '
                    . 'INNER JOIN mvv_stg_stgteil mss '
                    . 'ON msv.stgteil_id = mss.stgteil_id '
                    . 'INNER JOIN mvv_studiengang ms '
                    . 'ON mss.studiengang_id = ms.studiengang_id '
                    . 'WHERE i.Name LIKE ? '
                    . 'AND mm.stat IN (?) AND msv.stat IN (?) '
                    . 'AND ms.stat IN (?)';
            $params = [$term, $modul_public_status,
                $version_public_status, $studiengang_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);;
    }

    private function search_studiengaenge()
    {
        $term = '%' . $this->sterm . '%';
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
                ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
                ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)
                && count($studiengang_public_status)) {
            $query = 'SELECT DISTINCT(mm.modul_id) '
                    . 'FROM mvv_studiengang ms '
                    . 'INNER JOIN mvv_stg_stgteil USING(studiengang_id) '
                    . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE (ms.name LIKE ? OR ms.name_kurz LIKE ?) '
                    . 'AND ms.stat IN (?) '
                    . 'AND msv.stat IN (?) AND mm.stat IN (?)';
            $params = [$term, $term,
                $studiengang_public_status, $version_public_status,
                $modul_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function search_faecher()
    {
        $term = '%' . $this->sterm . '%';
        $ret = [];
        $modul_public_status = ModuleManagementModel::getPublicStatus('Modul');
        $version_public_status =
                ModuleManagementModel::getPublicStatus('StgteilVersion');
        $studiengang_public_status =
                ModuleManagementModel::getPublicStatus('Studiengang');
        if (count($modul_public_status) && count($version_public_status)) {
            $query = 'SELECT DISTINCT(mm.modul_id) '
                    . 'FROM fach mf INNER JOIN mvv_stgteil USING(fach_id) '
                    . 'INNER JOIN mvv_stg_stgteil mss USING(stgteil_id) '
                    . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                    . 'INNER JOIN mvv_stgteilversion msv '
                    . 'ON mss.stgteil_id = msv.stgteil_id '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id) '
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE (mf.name LIKE ? OR mf.name_kurz LIKE ?) '
                    . 'AND ms.stat IN (?) AND mm.stat IN (?) '
                    . 'AND msv.stat IN (?)';
            $params = [$term, $term,
                $studiengang_public_status, $modul_public_status,
                $version_public_status];
            $stmt = DBManager::get()->prepare($query);
            $stmt->execute($params);
            $ret = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $ret;
    }

    private function drilldown_persons($modul_ids)
    {
        if (count($modul_ids)) {
            $stmt = DBManager::get()->prepare('SELECT mmu.modul_id, aum.user_id, '
                    . $GLOBALS['_fullname_sql']['full'] . ' AS fullname, '
                    . 'COUNT(mmu.modul_id) AS count_module '
                    . 'FROM mvv_modul_user mmu LEFT JOIN auth_user_md5 aum USING(user_id) '
                    . 'LEFT JOIN user_info ui USING(user_id) '
                    . 'WHERE mmu.modul_id IN (?) '
                    . 'GROUP BY modul_id '
                    . 'ORDER BY count_module DESC');
            $stmt->execute([$modul_ids]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    private function drilldown_institutes($modul_ids)
    {
        if (is_array($modul_ids) && count($modul_ids)) {
            $fabs = [];
            foreach ($modul_ids as $modul_id) {
                $modul = Modul::find($modul_id);
                foreach ($modul->getResponsibleInstitutes() as $fab) {
                    $fabs[$fab->id] = Fachbereich::find($fab->id);
                }
            }
            return SimpleORMapCollection::createFromArray($fabs);
        }
        return [];
    }

    private function drilldown_faecher($modul_ids)
    {
        if (is_array($modul_ids) && count($modul_ids)) {
            return Fach::findPublicByModule($modul_ids);
        }
        return [];
    }

    private function drilldown_studiengaenge($modul_ids)
    {
        if (is_array($modul_ids) && count($modul_ids)) {
            return Studiengang::findByModule($modul_ids);
        }
        return [];
    }

    public function reset_action()
    {
        $this->reset_search();
        $this->redirect('search/module/index');
    }

    protected function reset_search($action = '')
    {
        //parent::reset_search();
        unset($this->sterm);
        URLHelper::removeLinkParam('sterm');
        $this->reset_drilldown();
    }

    public function overview_action($modul_id) {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        $this->setSemester();

        $this->modul = Modul::get($modul_id);
        $courses = $this->getSemesterCourses($this->modul);
        $this->semester_select = [];
        // only valid (semesters between start and end of module)
        // semesters for selector
        // $sem_valid = false;
        // Augsburg Module ohne Angabe der Gültigkeit
        $sem_valid = !((boolean) $this->modul->start);
        $sem_number = 1;
        foreach (Semester::getAll() as $semester) {
            if ($sem_valid || $this->modul->start == $semester->getId()) {
                // show only semesters with assigned courses
                if (in_array($sem_number, $courses)) {
                    $this->semester_select[] = $semester;
                }
                $sem_valid = true;
            }
            if ($this->modul->end == $semester->getId()) {
                break;
            }
            $sem_number++;
        }

        $this->semester_select = array_reverse($this->semester_select);
        $response = $this->relay('shared/modul/overview', $this->modul->getId(), $this->selected_semester->semester_id);

        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    public function description_action($modul_id)
    {
        if ($this->drill_down_filter) {
            $this->drilldown();
        }

        $response = $this->relay('shared/modul/description', $modul_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            $this->details_id = $modul_id;
            $this->modul_content = $response->body;
            $this->perform_relayed('index');
        }
    }

    private function getSemesterCourses($modul)
    {
        $semester = Semester::getAll();
        $courses = [];
        foreach ($modul->modulteile as $modulteil) {
            foreach ($modulteil->lvgruppen as $lvgruppe) {
                $courses = array_merge($courses,
                        array_keys($lvgruppe->getAllAssignedCourses(true)));
            }
        }
        return $courses;
    }
    
    /**
     * Sets the default semester if no semester was selected by semester filter.
     */
    private function setSemester()
    {
        if (Request::option('sem_select')) {
            $this->sessSet('selected_semester', Request::option('sem_select'));
        }
        if (!$this->sessGet('selected_semester')) {
            $semester_switch = intval(get_config('SEMESTER_TIME_SWITCH'));
            $current_semester = SemesterData::getSemesterDataByDate(time() + $semester_switch * 7 * 24 * 60 * 60);
            $this->sessSet('selected_semester', $current_semester['semester_id']);
        }
        $this->selected_semester = Semester::find($this->sessGet('selected_semester'));
    }
}
