<?php
/**
 * admin/overlapping.php - controller to check for overlapping
 * courses in Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2018 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

class Admin_OverlappingController extends AuthenticatedController
{
    /**
     * Common before filter for all actions.
     *
     * @param String $action Called actions
     * @param Array  $args   Passed arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/browse/my_courses/overlapping');
        
        if (Request::option('sem_select')) {
            $GLOBALS['user']->cfg->store('MY_COURSE_SELECTED_CYCLE', Request::option(sem_select));
        }
        $this->selected_semester = Semester::find($GLOBALS['user']->cfg->MY_COURSE_SELECTED_CYCLE);
        if (!$this->selected_semester) {
            $this->selected_semester = Semester::findCurrent();
        }
        PageLayout::setTitle(_('Überschneidung von Veranstaltungen'));
    }
    
    /**
     * Main view: Shows selection form and result.
     */
    public function index_action()
    {
        $this->setSidebar();
        $selections = SimpleORMapCollection::createFromArray(
            MvvOverlappingSelection::findBySQL('`selection_id` = ? AND `user_id` = ?', [
                Request::option('selection'),
                $GLOBALS['user']->id
            ])
        );
        $this->selection_id = null;
        if (count($selections)) {
            $this->base_version = StgteilVersion::find($selections->first()->base_version_id);
            $this->fachsems = (array) explode(',', $selections->first()->fachsems);
            $this->semtypes = (array) explode(',', $selections->first()->semtypes);
            $this->comp_versions = StgteilVersion::findMany($selections->pluck('comp_version_id'));
            $this->selection_id = $selections->first()->selection_id;
            if (Request::int('show_hidden') !== null) {
                $_SESSION['MVV_OVL_HIDDEN'] = Request::int('show_hidden');
            }
        } else {
            $this->base_version = StgteilVersion::find(Request::option('base_version'));
            $this->comp_versions = StgteilVersion::findMany(Request::optionArray('comp_versions'));
            $this->fachsems = Request::intArray('fachsems');
            $this->semtypes = Request::intArray('semtypes');
        }
        $this->conflicts = MvvOverlappingSelection::getConflictsBySelection(
            $this->selection_id,
            !$_SESSION['MVV_OVL_HIDDEN']
        );
    }
    
    /**
     * Resets form and shows index view.
     */
    public function reset_action()
    {
        $this->setSidebar();
        $_SESSION['MVV_OVL_HIDDEN'] = 0;
        $this->conflicts = [];
        $this->render_action('index');
    }
    
    /**
     * Calculates the conflicts and redirects to index view.
     */
    public function check_action()
    {
        $this->setSidebar();
        $this->base_version = StgteilVersion::find(Request::option('base_version'));
        if ($this->base_version) {
            $this->comp_versions = [];
            foreach (Request::optionArray('comp_versions') as $comp_version_id) {
                $this->comp_versions[] = StgteilVersion::find($comp_version_id);
            }
            // if no comparison version, check base version for internal conflicts
            if (count($this->comp_versions) == 0) {
                $this->comp_versions[$this->base_version->id] = $this->base_version;
            }
            $this->fachsems = Request::intArray('fachsems');
            $this->semtypes = Request::intArray('semtypes');

            if (Request::submitted('compare')) {
                $selection_id = MvvOverlappingSelection::createSelectionId(
                    $this->base_version,
                    $this->comp_versions,
                    $this->fachsems,
                    $this->semtypes
                );
                
                // refresh conflicts
                MvvOverlappingConflict::deleteBySelection($selection_id);
                
                foreach ($this->comp_versions as $comp_version) {
                    $selection[$comp_version->id] = MvvOverlappingSelection::findOneBySQL(
                    '`selection_id` = ? AND `comp_version_id` = ?', [
                        $selection_id,
                        $comp_version->id
                    ]);
                    if (!$selection[$comp_version->id]) {
                        $selection[$comp_version->id] = new MvvOverlappingSelection();
                        $selection[$comp_version->id]->semester_id = $this->selected_semester->id;
                        $selection[$comp_version->id]->selection_id = $selection_id;
                        $selection[$comp_version->id]->base_version_id = $this->base_version->id;
                        $selection[$comp_version->id]->comp_version_id = $comp_version->id;
                        $selection[$comp_version->id]->setFachsemester($this->fachsems);
                        $selection[$comp_version->id]->setCoursetypes($this->semtypes);
                        $selection[$comp_version->id]->user_id = $GLOBALS['user']->id;
                        $selection[$comp_version->id]->store();
                    }
                    $selection[$comp_version->id]->storeConflicts();
                }
                $conflicts = MvvOverlappingSelection::getConflictsBySelection($selection_id);
                $visible_conflicts = MvvOverlappingSelection::getConflictsBySelection($selection_id, true);
                if (count($conflicts)) {
                    if (count($conflicts) != count($visible_conflicts)) {
                        PageLayout::postSuccess(
                            sprintf(
                                ngettext('1 Konflikt gefunden (1 ausgeblendet)',
                                    '%s Konflikte gefunden (%s ausgeblendet).', count($conflicts)),
                                count($conflicts),
                                count($conflicts) - count($visible_conflicts)
                            )
                        );
                    } else {
                        PageLayout::postSuccess(
                            sprintf(
                                ngettext('1 Konflikt gefunden.',
                                    '%s Konflikte gefunden.', count($conflicts)),
                                count($conflicts)
                            )
                        );
                    }
                } else {
                    PageLayout::postSuccess(_('Keine Konflikte gefunden.'));
                }
            }
        } else {
            PageLayout::postError('Die Basis-Version muss angegeben werden!');
        }
        $_SESSION['MVV_OVL_HIDDEN'] = Request::int('show_hidden');
        $this->redirect($this->url_for('/index', ['selection' => $selection_id]));
    }
    
    /**
     * Shows the responsible admin of the course.
     * 
     * @param type $course_id The id of the course.
     */
    public function admin_info_action($course_id)
    {
        $this->course = Course::find($course_id);
        if ($this->course) {
            $this->admins = InstituteMember::findByInstituteAndStatus($this->course->institut_id, 'admin');
        } else {
            PageLayout::postMessage(MessageBox::error(_('Unbekannte Veranstaltung.')));
        }
    }
    
    /**
     * Shows the course details.
     * 
     * @param type $course_id The id of the course.
     */
    public function course_info_action($course_id)
    {
        $course = Course::find($course_id);
        if ($course) {
            Request::set('sem_id', $course->id);
            $this->redirect('course/details' . '?sem_id=' . $course->id);
        } else {
            PageLayout::postMessage(MessageBox::error(_('Unbekannte Veranstaltung.')));
        }
    }
    
    /**
     * Sets a course as hidden.
     */
    public function set_exclude_action()
    {
        $selection = MvvOverlappingSelection::find(Request::int('selection_id'));
        if ($selection->user_id == $GLOBALS['user']->id) {
            $exclude = new MvvOverlappingExclude([$selection->selection_id, Request::option('course_id')]);
            if (Request::int('excluded')) {
                $success = $exclude->delete();
            } else {
                $success = $exclude->store();
            }
            $this->set_status($success ? 204 : 400);
        } else {
            $this->set_status(403);
        }
        $this->render_nothing();
    }
    
    /**
     * Shows detailed information about the studiengangteil version.
     * 
     * @param type $version_id The id of the studiengangteil version.
     */
    public function version_info_action($version_id)
    {
        $version = StgteilVersion::find($version_id);
        if ($version) {
            Request::set('version', $version->id);
            $this->redirect($this->url_for('search/studiengaenge/verlauf/' . $version->stgteil_id,
                    ['semester' => $this->selected_semester,
                     'version'  => $version->id]));
            return;
        } else {
            PageLayout::postMessage(MessageBox::error(_('Unbekannte Studiengangteil-Version.')));
        }
    }
    
    /**
     * Init the sidebar.
     */
    private function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));
        
        $widget = new SelectWidget(
            _('Semesterauswahl'),
            $this->url_for('admin/overlapping/index'),
            'sem_select'
        );
        foreach (array_reverse(Semester::getAll()) as $semester) {
            $widget->addElement(new SelectElement(
                    $semester->id,
                    $semester->name,
                    $semester->id === $this->selected_semester->id
                ), 'sem_select-' . $semester->id
            );
        }
        $sidebar->addWidget($widget);
    }
    
    /**
     * Search for base version by given search term.
     */
    public function base_version_action()
    {
        $sword = Request::get('term');
        $this->render_text(json_encode($this->getResult($sword)));
    }
    
    /**
     * Search für comparison version by given search term.
     */
    public function comp_versions_action()
    {
        $sword = Request::get('term');
        $version_id = Config::get()->MVV_OVERLAPPING_SHOW_VERSIONS_INSIDE_MULTIPLE_STUDY_COURSES
            ? Request::option('version_id')
            : null;
        $version_ids = $this->getRelatedVersions($version_id);
        $this->render_text(json_encode($this->getResult($sword, $version_ids)));
    }
    
    /**
     * Returns versions related to the base version.
     * 
     * @param type $version_id
     * @return type
     */
    private function getRelatedVersions($version_id)
    {
        $version_ids = [];
        $version = StgteilVersion::find($version_id);
        if ($version) {
            $studiengaenge = Studiengang::findByStgTeil($version->stgteil_id);
        } else {
            return null;
        }
        foreach ($studiengaenge as $studiengang) {
            if ($studiengang->typ == 'mehrfach') {
                foreach ($studiengang->studiengangteile as $studiengangteil) {
                    $version_ids = array_merge(
                        $version_ids,
                        $studiengangteil->versionen->pluck('version_id')
                    );
                }
            }
        }
        return count($version_ids) ? array_diff($version_ids, [$version_id]) : null;
    }
    
    /**
     * Search for studiengangteil versionen by given keyword. The result can be
     * filtered by version ids.
     * 
     * @param string $keyword The keyword to search for.
     * @param array $version_ids An array of version ids.
     * @return array An array of studiengangteil versionen.
     */
    private function getResult($keyword, $version_ids = null) {
        $version_query = '';
        
        if (!is_null($version_ids)) {
            $version_query = ' AND `mvv_stgteilversion`.`version_id` IN (:version_ids) ';
        }

        $query = "SELECT `version_id`, `fach`.`name`, `mvv_stgteil`.`kp`
             FROM `fach`
                INNER JOIN `mvv_stgteil` USING(`fach_id`)
                INNER JOIN `mvv_stgteilversion` USING(`stgteil_id`)
                INNER JOIN `semester_data` AS `start_sem`
                    ON (`mvv_stgteilversion`.`start_sem` = `start_sem`.`semester_id`)
                LEFT JOIN `semester_data` AS `end_sem`
                    ON (`mvv_stgteilversion`.`end_sem` = `end_sem`.`semester_id`)
             WHERE (`fach`.`name` LIKE :keyword
                    OR `mvv_stgteil`.`zusatz` LIKE :keyword
                    OR `mvv_stgteilversion`.`code` LIKE :keyword)
                
                AND (`start_sem`.`beginn` <= :sem_end
                    OR ISNULL(`start_sem`.`beginn`))
                AND (`end_sem`.`ende` >= :sem_start
                    OR ISNULL(`end_sem`.`ende`))
                " . $version_query . "
            ORDER BY `name` ASC, `kp` ASC";
        
        $stat = array_keys(array_filter(
            $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'],
            function ($v) {
                return $v['public'];
            }
        ));
        
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([
            ':keyword'     => '%' . $keyword . '%',
            ':stat'        => $stat,
            ':sem_start'   => $this->selected_semester->beginn,
            ':sem_end'     => $this->selected_semester->ende,
            ':version_ids' => $version_ids
        ]);
        $res = ['results' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $version_id) {
            $version = StgteilVersion::find($version_id);
            $res['results'][] = [
                'id'   => $version->id,
                'text' => $version->getDisplayName()
            ];
        }
        return $res;
    }
    
}