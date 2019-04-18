<?php
/**
 * studiengaenge.php - Search_StudiengaengeController
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

class Search_StudiengaengeController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);

        // set navigation
        Navigation::activateItem('/search/courses/module');

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Modulsuche'), $this->url_for('search/module'));
        $views->addLink(_('Studienangebot'), $this->url_for('search/angebot'));
        $views->addLink(_('Studiengänge'), $this->url_for('search/studiengaenge'))
                ->setActive();
        $views->addLink(_('Fach-Abschluss-Kombinationen'), $this->url_for('search/stgtable'));

        $sidebar->addWidget($views);

        $this->breadcrumb = new BreadCrumb();
        $this->action = $action;
        $this->verlauf_url = '/verlauf';
        PageLayout::setTitle(_('Modulverzeichnis - Studiengänge'));
    }

    protected static function IsVisible()
    {
        return MVV::isVisibleSearch();
    }

    public function index_action()
    {
        $this->categories = AbschlussKategorie::getAllEnriched();

        $this->abschluss_url = $this->url_for('abschlusskategorie/show/');

        $this->breadcrumb->init();
        $this->breadcrumb->append(_('Studiengänge'), 'index');
        $this->render_template('search/studiengaenge/index', $this->layout);
    }

    public function kategorie_action($kategorie_id)
    {
        $kategorie = AbschlussKategorie::get($kategorie_id);
        $studiengaenge = Studiengang::findByAbschlussKategorie($kategorie->getId());

        // sort by display name
        $studiengaenge_sort = [];
        foreach ($studiengaenge as $key => $studiengang) {
            $studiengaenge_sort[$studiengang->getDisplayName() . $key] = $studiengang;
        }
        ksort($studiengaenge_sort, SORT_LOCALE_STRING);

        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }

        $studiengaenge_abschluss = [];
        $abschluesse = [];
        foreach ($studiengaenge_sort as $studiengang) {
            if (in_array($studiengang->stat, $status_filter)) {
                $abschluss = Abschluss::find($studiengang->abschluss_id);
                $abschluesse[$abschluss->getId()] = $abschluss;
                $studiengaenge_abschluss[$studiengang->abschluss_id][$studiengang->getId()] = $studiengang;
            }
        }
        $this->breadcrumb->append($kategorie, 'kategorie');
        $this->kategorie = $kategorie;
        $this->abschluesse = $abschluesse;
        $this->studiengaenge = $studiengaenge_abschluss;
        $this->render_template('search/studiengaenge/kategorie', $this->layout);
    }

    public function studiengang_action($studiengang_id)
    {
        $this->studiengang = Studiengang::find($studiengang_id);

        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }

        if (!$this->studiengang || !in_array($this->studiengang->stat, $status_filter)) {
            PageLayout::postError( _('Unbekannter Studiengang'));
            $this->relocate($this->url_for('/index'));
            return null;
        }

        $method = $this->studiengang->typ;
        $this->abschluss = Abschluss::get($this->studiengang->abschluss_id);
        $this->breadcrumb->append($this->studiengang, 'studiengang');
        $this->$method($this->studiengang->id);
    }

    private function mehrfach($studiengang_id)
    {
        $faecher = Fach::findByStudiengang($this->studiengang->id);
        $this->studiengangTeilBezeichnungen = $this->studiengang->stgteil_bezeichnungen;

        $this->data = [];
        $this->fachNamen = [];
        foreach ($faecher as $fach) {
            $this->fachNamen[$fach->id] = $fach->getDisplayName();
            $this->data[$fach->id] = [];
            foreach ($this->studiengangTeilBezeichnungen as $studiengangTeilBezeichnung) {

                $schnittpunkte = StudiengangTeil::findByStudiengangStgteilBez(
                        $this->studiengang->id, $studiengangTeilBezeichnung->id);

                foreach ($schnittpunkte as $schnittpunkt) {
                    $versionen = StgteilVersion::findByStgteil($schnittpunkt->id)->filter(
                        function ($version) {
                            return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                        });
                    if ($schnittpunkt->fach_id === $fach->id && count($versionen) > 0) {
                        $this->data[$fach->id][$studiengangTeilBezeichnung->id] = $schnittpunkt->id;
                    }
                }
            }
        }

        if (!$this->verlauf_url) {
            $this->verlauf_url = '/verlauf';
        }

        if(count($this->studiengang->stgteil_assignments) == 1) {
            foreach($this->studiengang->stgteil_assignments as $assignment) {
                $url = $this->verlauf_url . '/' . $assignment->stgteil_id . '/'. $assignment->stgteil_bez_id . '/' . $assignment->studiengang_id;
                $response = $this->relay($url);
                $this->content = $response->body;
                $this->render_template('shared/content', $this->layout);
            }
            return;
        }
        $this->render_template('search/studiengaenge/mehrfach', $this->layout);
    }

    private function einfach($studiengang_id)
    {
        $studiengangTeile = StudiengangTeil::findByStudiengang($studiengang_id);
        if (count($studiengangTeile) == 1) {
            $teil = $studiengangTeile;
            $id = $teil[0]->getId();
            $this->verlauf_action($id);
        } else {
            // Einfach-Studiengang mit Ausprägungen
            // (unterschiedliche Studiengangteile direkt am Studiengang, ohne
            // Studiengangteil-Bezeichnungen)
            $this->data = [];
            foreach ($studiengangTeile as $teil) {
                $filter = function ($version) {
                    return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                };
                if (count($teil->versionen->filter($filter)) > 0) {
                    $this->data[$teil->getId()] = $teil->fach->getDisplayName();
                }
            }
            if (!$this->verlauf_url) {
                $this->verlauf_url = '/verlauf';
            }
            $this->breadcrumb->append(Studiengang::find($studiengang_id), 'einfach');
            $this->render_template('search/studiengaenge/einfach', $this->layout);
        }
    }

    public function verlauf_action($stgteil_id, $stgteil_bez_id = null, $studiengang_id = null)
    {
        $sem = Request::option('semester');
        if ($sem) {
            $this->sessSet('selected_semester', $sem);
        }

        $this->with_courses = Request::option('with_courses', ($_SESSION['MVV_SEARCH_SEQUENCE_WITH_COURSES'] ?: null));
        $_SESSION['MVV_SEARCH_SEQUENCE_WITH_COURSES'] = $this->with_courses;

        $studiengangTeil = StudiengangTeil::find($stgteil_id);
        $versionen = StgteilVersion::findByStgteil($stgteil_id, 'start', 'DESC')->filter(
            function ($version) {
                $public = $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                return ($public ? true : false);
            });
        if (!$studiengangTeil || count($versionen) === 0) {
            PageLayout::postInfo(_('Kein Verlaufsplan im gewählten Bereich verfügbar.'));
        } else {
            $version_id = Request::option('version', $this->sessGet('selected_version'));
            if ($versionen->findOneBy('id', $version_id)) {
                $this->cur_version_id = $version_id;
            } else {
                $this->cur_version_id = $this->findCurrentVersion($versionen);
            }
            $this->sessSet('selected_version', $this->cur_version_id);

            $this->semesters = $this->getSemester($versionen->findOneBy('id', $this->cur_version_id));

            $semester_time_switch = (int) Config::get()->getValue('SEMESTER_TIME_SWITCH');
            $cur_semester = Semester::findByTimestamp(time()
                    + $semester_time_switch * 7 * 24 * 60 * 60);

            $active_semester = $this->sessGet('selected_semester');
            if ($active_semester) {
                $this->active_sem = $this->semesters[$active_semester];
            } else if ($cur_semester) {
                $this->active_sem = $cur_semester;
            } else {
                $this->active_sem = Semester::find($this->sessGet('selected_semester', Semester::findCurrent()->id));
            }
            $this->active_sem = $this->semesters[$this->active_sem->id] ? $this->active_sem : null;
            if (!$this->active_sem && count($this->semesters)) {
                $active_sem = reset($this->semesters);
                $this->active_sem = Semester::find($active_sem['semester_id']);
            }

            $abschnitte = StgteilAbschnitt::findByStgteilVersion($this->cur_version_id);
            $abschnitteData = [];
            $fachsemesterData = [];
            foreach ($abschnitte as $abschnitt) {
                $abschnitteData[$abschnitt->id] = [
                    'name' => $abschnitt->getDisplayName(),
                    'creditPoints' => $abschnitt->kp,
                    'zwischenUeberschrift' => $abschnitt->ueberschrift,
                    'module' => [],
                    'rowspan' => 0,
                    'kommentar' => $abschnitt->kommentar
                ];

                foreach ($abschnitt->modul_zuordnungen as $abschnitt_modul) {

                    // module is not public visible or section has no module
                    // if no modules show only subheading
                    if (!$abschnitt_modul->modul || !$abschnitt_modul->modul->hasPublicStatus()) {
                        continue;
                    }

                    $start_sem = Semester::find($abschnitt_modul->modul->start);
                    $end_sem = Semester::find($abschnitt_modul->modul->end);
                    if ($start_sem->beginn > $this->active_sem->beginn || ($this->active_sem->ende > $end_sem->ende && $end_sem != null)) {
                       continue;
                    }

                    $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id] = [
                        'name' => $abschnitt_modul->getDisplayName(),
                        'modulTeile' => []
                    ];
                    $countcourses = 0;
                    foreach ($abschnitt_modul->modul->modulteile as $teil) {
                        $lvg = Lvgruppe::findByModulteil($teil->id);
                        if ($lvg) {
                            foreach ($lvg as $lv) {
                                $courses = $lv->getAssignedCoursesBySemester($this->active_sem->id, $GLOBALS['user']->id);
                                $countcourses += count($courses);
                            }
                        }

                        // filter modules whether they have courses or not
 	                if ($this->with_courses && $countcourses == 0) continue;

                        $fachSemester = $abschnitt_modul->getAllFachSemester($teil->id);

                        $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id]['modulTeile'][$teil->id] = [
                            'name' => $teil->getDisplayName(),
                            'position' => $teil->position,
                            'fachsemester' => []
                        ];
                        $abschnitteData[$abschnitt->id]['rowspan']++;
                        foreach ($fachSemester as $fachsem) {
                            $fachsemesterData[$fachsem->fachsemester] = $fachsem->fachsemester;
                            $abschnitteData[$abschnitt->getId()]['module'][$abschnitt_modul->modul->getId()]['modulTeile'][$teil->getId()]['fachsemester'][$fachsem->fachsemester] = $fachsem->differenzierung;
                        }
                    }
                    $abschnitteData[$abschnitt->id]['module'][$abschnitt_modul->modul->id]['veranstaltungen'] = $countcourses;
                }
            }

            if ($studiengang_id) {
                if ($stgteil_bez_id) {
                    $this->stgTeilBez = StgteilBezeichnung::get($stgteil_bez_id);
                    $this->breadcrumb->append([$this->stgTeilBez, $studiengangTeil], 'verlauf');
                } else {
                    $this->breadcrumb->append($studiengangTeil, 'verlauf');
                }
                $this->studiengang = Studiengang::get($studiengang_id);
            }

            $this->setVersionSelectWidget($versionen,
                    $this->url_for('/verlauf',
                            $studiengangTeil->id,
                            $stgteil_bez_id,
                            $studiengang_id));

            ksort($fachsemesterData);
            $this->fachsemesterData = $fachsemesterData;
            $this->abschnitteData = $abschnitteData;
            $this->versionen = $versionen;
            // Augsburg
            // Ausgabe des Namens ohne Fach (dieses ist im Zusatz bereits enthalten)
            // $this->studiengangTeilName = $studiengangTeil->getDisplayName(0);
            $this->studiengangTeilName = $studiengangTeil->getDisplayName();

            // add option widget to show only modules with courses in the
            // selected semester
            $widget = new OptionsWidget();
            $widget->addCheckbox(_('Nur Module mit Veranstaltungen anzeigen'),
              $this->with_courses, $this->link_for('/verlauf/' . $stgteil_id,
                ['with_courses' => intval(!$this->with_courses)]));
            Sidebar::get()->addWidget($widget, 'with_courses');
        }
        $this->breadcrumb->append($this->studiengang, 'studiengang');
        $this->render_template('search/studiengaenge/verlauf', $this->layout);
    }

    public function kommentar_action($abschnitt_id)
    {
        $this->abschnitt = StgteilAbschnitt::find($abschnitt_id);
        if (!$this->abschnitt) {
            throw new Trails_Exception(404);
        }
        $this->render_template('search/studiengaenge/kommentar', $this->layout);
    }

    private function getSemester($version)
    {
        if (!$version) {
            return [];
        }
        $start_sem = Semester::find($version->start_sem);
        $end_sem = Semester::find($version->end_sem);
        $start = (int) ($start_sem ? $start_sem->beginn : 0);
        $end = (int) ($end_sem ? $end_sem->beginn : PHP_INT_MAX);
        $semester = [];
        $sql = 'SELECT 1
                FROM mvv_stgteilabschnitt
                INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id)
                INNER JOIN mvv_modul mm USING(modul_id)
                INNER JOIN mvv_modulteil USING(modul_id)
                LEFT JOIN semester_data mm_start_sem ON mm.start = mm_start_sem.semester_id
                LEFT JOIN semester_data mm_end_sem ON mm.end = mm_end_sem.semester_id
                WHERE mvv_stgteilabschnitt.version_id = :version
                AND ((mm_start_sem.beginn IS NULL AND mm_end_sem.ende IS NULL)
                OR (mm_start_sem.beginn <= :beginn
                AND (mm_end_sem.ende >= :ende OR mm_end_sem.ende IS NULL)))
                LIMIT 1';
        $stmt = DBManager::get()->prepare($sql);
        foreach (Semester::getAll() as $one) {
            if ($one->beginn >= $start && $one->beginn <= $end) {
                $stmt->execute([':version' => $version->getId(),
                         ':beginn' => $one->beginn,
                         ':ende' => $one->ende]);
                if ($stmt->fetchColumn()) {
                    $semester[$one->id] = $one;
                }
            }
        }
        return array_reverse($semester);
    }

    private function findCurrentVersion($versions)
    {
        $semester_data = Semester::getAll();
        $current_semester = Semester::findCurrent();
        $cur_version_id = null;
        if (count($versions)) {
            foreach ($versions as $version) {
                if ((!$version->start_sem && !$version->end_sem)
                    || ($semester_data[$version->start_sem]->beginn <= $current_semester->beginn
                            && !$version->end_sem)
                    || ($semester_data[$version->start_sem]->beginn <= $current_semester->beginn
                            && $semester_data[$version->end_sem]->beginn >= $current_semester->beginn)) {
                    return $version->getId();
                }
            }
            // no start or end semester for versions, take the last one
            if (!$cur_version_id) {
                $cur_version_id = $versions->last()->id;
            }
        }
        return $cur_version_id;
    }

    /**
     * Adds a widget to select versions of Studiengang-Teile and semesters
     *
     * @param  $versions SimpleORMapCollection Collection with versions of
     * this Studiengangteil.
     * @param $url string Submit url
     */
    private function setVersionSelectWidget($versions, $url)
    {

        $semester_time_switch = (int) Config::get()->getValue('SEMESTER_TIME_SWITCH');
        $cur_semester = Semester::findByTimestamp(time()
            + $semester_time_switch * 7 * 24 * 60 * 60);

        $sidebar = Sidebar::get();

        if (count($versions) > 1) {
            $widget = new SelectWidget(_('Versionenauswahl'),
                    $url, 'version');
            $options = [];
            foreach ($versions as $version) {
                $options[$version->id] = $version->getDisplayName(0);
                // fallback: show name of Studiengangteil if version or
                // semester is unknown
                $options[$version->id] =
                        trim($options[$version->id])
                        ?: $version->getDisplayName(ModuleManagementModel::DISPLAY_STGTEIL);
            }
            $widget->setOptions($options, $this->cur_version_id);
            $widget->setMaxLength(100);
            $sidebar->addWidget($widget, 'version_filter');
        }

        $widget = new SelectWidget(_('Semesterauswahl'),
                $url, 'semester');
        $options = [];
        foreach ($this->semesters as $sem) {
            $options[$sem['semester_id']] = $sem['name'];
        }
        $widget->setOptions($options, $this->active_sem->id);
        $widget->setMaxLength(100);
        $sidebar->addWidget($widget, 'sem_filter');
    }

}
