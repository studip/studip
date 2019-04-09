<?php
/*
 * details.php - realises a redirector for administrative pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @copyright   2014
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.1
 */

require_once 'lib/dates.inc.php'; //Funktionen zum Anzeigen der Terminstruktur

class Course_DetailsController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $course_id = Request::option('sem_id', $args[0]);
        if (empty($course_id)) {
            checkObject(); //wirft Exception, wenn Context::get() leer ist
            $course_id = $GLOBALS['SessionSeminar'];
        }

        $this->course = Course::find($course_id);
        if (!$this->course) {
            throw new Trails_Exception(
                404,
                _('Es konnte keine Veranstaltung gefunden werden')
            );
        }
        $this->send_from_search_page = Request::get('send_from_search_page');

        if ($GLOBALS['SessionSeminar'] != $this->course->id
            && !(int)$this->course->visible
            && !($GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)
                || $GLOBALS['perm']->have_studip_perm('user', $this->course->id))) {
            throw new AccessDeniedException(_('Diese Veranstaltung ist versteckt. Hier gibt es nichts zu sehen.'));
        }

        if (!preg_match('/^(' . preg_quote($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'], '/') . ')?([a-zA-Z0-9_-]+\.php)([a-zA-Z0-9_?&=-]*)$/', $this->send_from_search_page)) {
            $this->send_from_search_page = '';
        }

        if ($this->course->getSemClass()->offsetGet('studygroup_mode')) {
            if ($GLOBALS['perm']->have_studip_perm('autor', $this->course->id)) { // participants may see seminar_main
                $link = URLHelper::getUrl('seminar_main.php', ['auswahl' => $this->course->id]);
            } else {
                $link = URLHelper::getUrl('dispatch.php/course/studygroup/details/' . $this->course->id, [
                    'send_from_search_page' => $this->send_from_search_page,
                    'cid' => null
                ]);
            }
            $this->redirect($link);
            return;
        }

    }

    public function index_action()
    {

        $this->prelim_discussion = vorbesprechung($this->course->id);
        $this->title             = $this->course->getFullname();
        $this->course_domains    = UserDomain::getUserDomainsForSeminar($this->course->id);
        $this->sem = new Seminar($this->course);

        //public folders
        $folders = Folder::findBySQL("range_type='course' AND range_id = ? AND folder_type = 'CoursePublicFolder'", [$this->course->id]);
        $public_files = [];
        $public_folders =[];
        foreach ($folders as $folder) {
            $one_public_folder = $folder->getTypedFolder();
            $all_files = FileManager::getFolderFilesRecursive($one_public_folder, $GLOBALS['user']->id);
            $public_files = array_merge($public_files, $all_files['files']);
            $public_folders = array_merge($public_folders, $all_files['folders']);
        }
        if (count($public_files)) {
            $this->public_files = $public_files;
            $this->public_folders = $public_folders;
        }

        // Retrive display of sem_tree
        if (Config::get()->COURSE_SEM_TREE_DISPLAY) {
            $this->studyAreaTree = StudipStudyArea::backwards($this->course->study_areas);
        } else {
            $this->study_areas = $this->course->study_areas->filter(function ($m) {
                return !$m->isModule();
            });
        }

        // Ausgabe der Modulzuordnung MVV
        if ($this->course->getSemClass()->offsetGet('module')) {
            $course_start = $this->course->start_time;
            $course_end = ($this->course->end_time < 0 || is_null($this->course->end_time))
                    ? PHP_INT_MAX
                    : $this->course->end_time;
            // set filter to show only pathes with valid semester data
            ModuleManagementModelTreeItem::setObjectFilter('Modul',
                function ($modul) use ($course_start, $course_end) {
                    // check for public status
                    if (!$GLOBALS['MVV_MODUL']['STATUS']['values'][$modul->stat]['public']) {
                        return false;
                    }
                    $modul_start = Semester::find($modul->start)->beginn ?: 0;
                    $modul_end = Semester::find($modul->end)->ende ?: PHP_INT_MAX;
                    return ($modul_start <= $course_end && $modul_end >= $course_start);
                });

            ModuleManagementModelTreeItem::setObjectFilter('StgteilVersion',
                function ($version) {
                    return (bool) $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                });

            $trail_classes = ['Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion'];
            $mvv_object_pathes = MvvCourse::get($this->course->getId())->getTrails($trail_classes);
            if ($mvv_object_pathes) {
                if (Config::get()->COURSE_SEM_TREE_DISPLAY) {
                    $this->mvv_tree = [];
                    foreach ($mvv_object_pathes as $mvv_object_path) {
                        // show only complete pathes
                        if (count($mvv_object_path) == 4) {
                            // flatten the pathes to a linked list
                            $stg = reset($mvv_object_path);
                            $parent_id = 'root';
                            foreach ($mvv_object_path as $mvv_object) {
                                $mvv_object_id = $mvv_object instanceof StgteilabschnittModul
                                        ? $mvv_object->modul_id
                                        : $mvv_object->id;
                                $this->mvv_tree[$parent_id][$mvv_object_id] =
                                        ['id'    => $mvv_object_id,
                                         'name'  => $mvv_object->getDisplayName(),
                                         'class' => get_class($mvv_object)];
                                $parent_id = $mvv_object_id;
                            }
                        }
                    }
                    if (count($this->mvv_tree)) {
                        // add the root node
                        $this->mvv_tree['start'][] = [
                            'id'    => 'root',
                            'name'  => Config::get()->UNI_NAME_CLEAN,
                            'class' => ''
                        ];
                    }
                } else {
                    foreach ($mvv_object_pathes as $mvv_object_path) {
                        // show only complete pathes
                        if (count($mvv_object_path) == 4) {
                            $mvv_object_names = [];
                            $modul_id = '';
                            foreach ($mvv_object_path as $mvv_object) {
                                if ($mvv_object instanceof StgteilabschnittModul) {
                                    $modul_id = $mvv_object->modul_id;
                                }
                                $mvv_object_names[] = $mvv_object->getDisplayName();
                            }
                            $this->mvv_pathes[] = [$modul_id => $mvv_object_names];
                        }
                    }
                }
                // to prevent collisions of object ids in the tree
                // in the case of same objects listed in more than one part
                // of the tree
                $this->id_sfx = new stdClass();
                $this->id_sfx->c = 1;
            }
        }

        $order = Config::get()->IMPORTANT_SEMNUMBER ? 'veranstaltungsnummer, name' : 'name';

        // Find child courses or parent course if applicable.
        if ($this->course->getSemClass()->isGroup()) {
            $this->children = SimpleCollection::createFromArray(
                Course::findByParent_Course($this->course->id, "ORDER BY $order")
            )->filter(function ($c) {
                return $c->isVisibleForUser();
            });
        // Find other courses belonging to the same parent.
        } else if ($this->course->parent_course) {
            $this->siblings = SimpleCollection::createFromArray(
                Course::findbyParent_Course($this->course->parent_course, "ORDER BY $order")
            )->findBy('id', $this->course->id, '!=')
             ->filter(function ($c) {
                 return $c->isVisibleForUser();
             });
        }

        if (Request::isXhr()) {
            PageLayout::setTitle($this->title);
        } else {
            PageLayout::setHelpKeyword('Basis.InVeranstaltungDetails');
            PageLayout::setTitle($this->title . ' - ' . _('Details'));
            PageLayout::addScript('studip-admission.js');

            $sidebar = Sidebar::Get();

            if ($GLOBALS['SessionSeminar'] === $this->course->id) {
                Navigation::activateItem('/course/main/details');
                SkipLinks::addIndex(Navigation::getItem('/course/main/details')->getTitle(), 'main_content', 100);
            } else {
                $sidebarlink = true;
                $enrolment_info = $this->sem->getEnrolmentInfo($GLOBALS['user']->id);
            }

            $sidebar->setTitle(_('Details'));
            $links = new ActionsWidget();
            $links->addLink(
                _('Drucken'),
                $this->url_for("course/details/index/{$this->course->id}"),
                Icon::create('print'),
                ['class' => 'print_action', 'target' => '_blank']
            );
            if ($enrolment_info['enrolment_allowed'] && $sidebarlink) {
                if (in_array($enrolment_info['cause'], ['member', 'root', 'courseadmin'])) {
                    $abo_msg = _('direkt zur Veranstaltung');
                } else {
                    $abo_msg = _('Zugang zur Veranstaltung');
                    if ($this->sem->admission_binding) {
                        PageLayout::postInfo(_('Die Anmeldung ist verbindlich, Teilnehmende können sich nicht selbst austragen.'));
                    }
                }
                $links->addLink(
                    $abo_msg,
                    $this->url_for("course/enrolment/apply/{$this->course->id}"),
                    Icon::create('door-enter'),
                    ['data-dialog' => 'size=big']
                );

            }

            if (Config::get()->SCHEDULE_ENABLE
                && !$GLOBALS['perm']->have_studip_perm('user', $this->course->id)
                && !$GLOBALS['perm']->have_perm('admin')
                && $this->sem->getMetaDateCount()
            ) {
                $query = "SELECT 1
                          FROM `schedule_seminare`
                          WHERE `seminar_id` = ? AND `user_id` = ?";
                $penciled = DBManager::Get()->fetchColumn($query, [
                    $this->course->id,
                    $GLOBALS['user']->id,
                ]);
                if (!$penciled) {
                    $links->addLink(
                        _('Nur im Stundenplan vormerken'),
                        $this->url_for("calendar/schedule/addvirtual/{$this->course->id}"),
                        Icon::create('info')
                    );
                }
            }

            if ($this->send_from_search_page) {
                $links->addLink(
                    _('Zurück zur letzten Auswahl'),
                    URLHelper::getURL($this->send_from_search_page),
                    Icon::create('link-intern')
                );
            }

            if (!$this->course->admission_binding
                && in_array($GLOBALS['perm']->get_studip_perm($this->course->id), ['user','autor'])
                && !$this->course->getSemClass()->isGroup())
            {
                $links->addLink(
                    _('Austragen aus der Veranstaltung'),
                    $this->url_for("my_courses/decline/{$this->course->id}", ['cmd' => 'suppose_to_kill']),
                    Icon::create('door-leave')
                );
            }

            if ($links->hasElements()) {
                $sidebar->addWidget($links);
            }

            $share = new ShareWidget();
            $share->addCopyableLink(
                _('Link zu dieser Veranstaltung kopieren'),
                $this->link_for('course/details', [
                    'sem_id' => $this->course->id,
                    'cid'    => null,
                ]),
                Icon::create('group')
            );
            $sidebar->addWidget($share);

            if ($enrolment_info['description']) {
                PageLayout::postInfo($enrolment_info['description']);
            }
        }
    }
}
