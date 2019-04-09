<?php
/**
 * grouping.php - grouping of courses
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionne für den Export
require_once 'lib/export/export_linking_func.inc.php';

/**
 * @addtogroup notifications
 *
 * Adding or removing a course to a group triggers a CourseDidAddToGroup or
 * CourseDidRemoveFromGroup notification. The parent and the child course IDs
 * are transmitted as subjects of the notification.
 */
class Course_GroupingController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        checkObject();
        $this->course = Course::findCurrent();

        // Allow only tutor and upwards
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course->id)) {
            throw new AccessDeniedException(_('Sie haben leider nicht die notwendige Berechtigung für diese Aktion.'));
        }
    }

    /**
     * This course belongs to a parent or can be assigned to one.
     */
    public function parent_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Zuordnung zu Hauptveranstaltung'));
        Navigation::activateItem('/course/admin/parent');

        $this->parent = $this->course->parent;

        // Prepare context for MyCoursesSearch...
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = [
                'semtypes'  => SemType::getNonGroupingSemTypes(),
                'exclude'   => [$this->course->parent_course ?: ''],
                'semesters' => [$this->course->start_semester->id],
            ];
        } elseif ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = [
                'semtypes'   => SemType::getNonGroupingSemTypes(),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude'    => [$this->course->parent_course ?: ''],
                'semesters'  => [$this->start_semester->id],
            ];
        } else {
            $parameters = [
                'userid'    => $GLOBALS['user']->id,
                'semtypes'  => SemType::getNonGroupingSemTypes(),
                'exclude'   => [$this->course->parent_course ?: ''],
                'semesters' => [$this->course->start_semester->id],
            ];
        }

        // Provide search object for finding groupable courses.
        $find = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);

        $this->search = QuickSearch::get('parent', $find)->setInputClass('target-seminar');
    }

    /**
     * This course can be a parent with one or more children.
     */
    public function children_action()
    {
        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Unterveranstaltungen'));
        Navigation::activateItem('/course/admin/children');

        $this->children = $this->course->children;

        // Prepare context for MyCoursesSearch...
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = [
                'semtypes'  => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'exclude'   => count($this->children) > 0 ? $this->children->pluck('seminar_id') : [],
                'semesters' => [$this->course->start_semester->id],
            ];
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = [
                'semtypes'   => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude'    => count($this->children) > 0 ? $this->children->pluck('seminar_id') : [],
                'semesters'  => [$this->course->start_semester->id],
            ];

        } else {
            $parameters = [
                'userid'    => $GLOBALS['user']->id,
                'semtypes'  => array_merge(studygroup_sem_types(), SemType::getGroupingSemTypes()),
                'exclude'   => count($this->children) > 0 ? $this->children->pluck('seminar_id') : [],
                'semesters' => [$this->course->start_semester->id]
            ];
        }

        // Provide search object for finding groupable courses.
        $find = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);

        $this->search = QuickSearch::get('child', $find)->setInputClass('target-seminar');

        if ($GLOBALS['perm']->have_perm(Config::get()->SEM_CREATE_PERM)) {
            $sidebar = Sidebar::get();
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Unterveranstaltungen anlegen'),
                $this->url_for('course/grouping/create_children'),
                Icon::create('seminar+add', 'clickable')
            )->asDialog('size=auto');
            $sidebar->addWidget($actions);
        }
    }

    /**
     * Show a list of all members, grouped by child course.
     */
    public function members_action()
    {
        PageLayout::setTitle(sprintf(
            '%s - %s',
            Course::findCurrent()->getFullname(),
            _('Teilnehmende in Unterveranstaltungen')
        ));
        Navigation::activateItem('course/members/children');
        $this->courses = SimpleCollection::createFromArray(
            Course::findByParent_Course(
                $this->course->id,
                'ORDER BY ' . (Config::get()->IMPORTANT_SEMNUMBER ? 'veranstaltungsnummer, name' : 'name')
            )
        );

        if (count($this->course->children) > 0) {
            $query = "SELECT DISTINCT s.`user_id`
                      FROM `seminar_user` s
                      WHERE s.`Seminar_id` = :parent AND NOT EXISTS (
                          SELECT `user_id`
                          FROM `seminar_user`
                          WHERE `user_id` = s.`user_id`
                            AND `Seminar_id` IN (:children)
                      )
                      LIMIT 1";
            $this->parentOnly = DBManager::get()->fetchFirst($query, [
                'parent'   => $this->course->id,
                'children' => $this->course->children->pluck('seminar_id'),
            ]);
        } else {
            $this->parentOnly = true;
        }

        // Write message to all participants.
        $sidebar = Sidebar::get();
        $actions = new ActionsWidget();
        $actions->addLink(
            _('Nachricht an alle Teilnehmenden schreiben'),
            $this->url_for('messages/write', [
                'filter'          => 'all',
                'course_id'       => $this->course->id,
                'default_subject' => '[' . $this->course->getFullname() . ']',
            ]),
            Icon::create('mail', 'clickable')
        )->asDialog('size=auto');
        $sidebar->addWidget($actions);

        // Export all participants.
        if (Config::get()->EXPORT_ENABLE) {
            $widget = new ExportWidget();

            // create csv-export link
            $csvExport = export_link(
                $this->course->id,
                'person',
                htmlReady(sprintf(
                    '%s %s',
                    get_title_for_status('autor', 2),
                    $this->course->getFullname()
                )),
                'csv',
                'csv-teiln',
                '',
                _('Teilnehmendenliste als CSV-Dokument exportieren'),
                'passthrough'
            );
            $widget->addLinkFromhtmL(
                $csvExport,
                Icon::create('file-office', 'clickable')
            );

            // create csv-export link
            $rtfExport = export_link(
                $this->course->id,
                'person',
                htmlReady(sprintf(
                    '%s %s',
                    get_title_for_status('autor', 2), $this->course->getFullname()
                )),
                'rtf',
                'rtf-teiln',
                '',
                _('Teilnehmendenliste als rtf-Dokument exportieren'),
                'passthrough'
            );
            $widget->addLinkFromHTML(
                $rtfExport,
                Icon::create('file-text', 'clickable')
            );

            $sidebar->addWidget($widget);
        }
    }

    /**
     * Shows members of given child course.
     * @param $course_id
     */
    public function child_course_members_action($course_id)
    {
        $this->child = Course::find($course_id);
    }

    /**
     * Collect users which are only in parent course and not in any child.
     */
    public function parent_only_members_action()
    {
        if (count($this->course->children) > 0) {
            $childrens_users = DBManager::get()->fetchFirst(
                "SELECT DISTINCT `user_id` FROM `seminar_user` WHERE `Seminar_id` IN (:children)",
                ['children' => $this->course->children->pluck('seminar_id')]
            );

            $this->parentOnly = $this->course->members->findBy('user_id', $childrens_users, '!=');
        } else {
            $this->parentOnly = $this->course->members;
        }
    }

    /**
     * Batch actions, like message sending, moving or removing for several members at once.
     */
    public function action_action()
    {
        if (Request::submitted('single_action')) {
            list($course_id, $permission) = explode('-', Request::get('single_action'));

            $selected = Request::getArray('members');

            $users = SimpleORMapCollection::createFromArray(
                User::findMany($selected[$course_id][$permission])
            );

            switch (Request::option('selected_single_action_' . $course_id . '_' . $permission)) {
                case 'message':
                    $this->redirect($this->url_for('messages/write', [
                        'rec_uname'       => $users->pluck('username'),
                        'default_subject' => '[' . Course::find($course_id)->getFullname() . ']',
                    ]));
                    break;
                case 'move':
                    $this->redirect($this->url_for('course/grouping/move_members_target', $course_id, [
                        'users' => $selected[$course_id][$permission],
                    ]));
                    break;
                case 'remove':
                    $this->flash['users'] = $selected[$course_id][$permission];
                    $this->relocate('course/grouping/remove_members', $course_id);
                    break;
                default:
                    $this->relocate('course/grouping/members');
            }
        } elseif (Request::submitted('courses_action')) {
            $this->flash['action']  = Request::option('action');
            $this->flash['courses'] = Request::getArray('courses');

            $this->redirect($this->url_for('course/grouping/find_members_to_add'));
        } else {
            $this->relocate('course/grouping/members');
        }
    }

    /**
     * Select a course to move selected persons to.
     * @param string $source_id id of source course
     * @param string $user_id optional id of single user to move
     */
    public function move_members_target_action($source_id, $user_id = '')
    {
        PageLayout::setTitle(_('Personen verschieben'));

        $this->source_id = $source_id;
        $this->users   = $user_id ? [$user_id] : Request::getArray('users');
        $this->targets = count($this->course->children) > 0
                       ? $this->course->children->findBy('id', $source_id, '!=')
                       : new SimpleORMapCollection();
    }

    /**
     * Move members to another cours
     * @param string $source_id The course to move members from.
     */
    public function move_members_action($source_id)
    {
        $source = Seminar::getInstance($source_id);
        $target = Seminar::getInstance(Request::option('target'));

        $success = 0;
        $fail    = 0;
        foreach (Request::getArray('users') as $user) {
            $m = CourseMember::find([$source_id, $user]);
            $status = $m->status;

            if ($source->deleteMember($user)) {
                $target->addMember($user, $status);
                $success += 1;
            } else {
                $fail += 1;
            }
        }

        if ($success > 0) {
            PageLayout::postSuccess(sprintf(_('%u Personen wurden verschoben.'), $success));
        }

        if ($fail > 0) {
            PageLayout::postError(sprintf(_('%u Personen konnten nicht verschoben werden.'), $fail));
        }

        $this->relocate('course/grouping/members');
    }

    /**
     * Removes selected members from given course.
     * @param string $course_id the course to remove members from
     */
    public function remove_members_action($course_id, $user_id = null)
    {
        $s = Seminar::getInstance($course_id);

        $success = 0;
        $fail    = 0;

        $users = $user_id ? [$user_id] : $this->flash['users'];

        foreach ($users as $user) {
            if ($s->deleteMember($user)) {
                $success += 1;
            } else {
                $fail += 1;
            }
        }

        if ($success > 0) {
            PageLayout::postSuccess(sprintf(_('%u Personen wurden entfernt.'), $success));
        }

        if ($fail > 0) {
            PageLayout::postError(sprintf(_('%u Personen konnten nicht entfernt werden.'), $fail));
        }

        $this->relocate('course/grouping/members');
    }

    /**
     * Select people to add to the given courses.
     */
    public function find_members_to_add_action()
    {
        switch ($this->flash['action']) {
            case 'add_dozent':
                $this->permission = 'dozent';
                $title = get_title_for_status('dozent', 2, $this->course->status);
                $perms = ['dozent'];
                break;
            case 'add_deputy':
                $this->permission = 'deputy';
                $title = _('Vertretung/en');
                $perms = ['dozent'];
                break;
            case 'add_tutor':
                $this->permission = 'tutor';
                $title = get_title_for_status('tutor', 2, $this->course->status);
                $perms = ['tutor', 'dozent'];
                break;
            case 'add_autor':
            default:
                $this->permission = 'autor';
                $title = get_title_for_status('autor', 2, $this->course->status);
                $perms = ['autor', 'tutor', 'dozent'];
                break;
        }

        PageLayout::setTitle(sprintf(_('%s hinzufügen'), $title));

        $this->courses = $this->flash['courses'];
        $searchtype = new PermissionSearch(
            'user',
            sprintf(_('%s suchen'), $title),
            'user_id',
            ['permission' => $perms, 'exclude_user' => []]
        );

        $this->search =  QuickSearch::get('user_id', $searchtype)
            ->withoutButton()
            ->setInputStyle('width: 75%')
            ->fireJSFunctionOnSelect('STUDIP.Members.addPersonToSelection');
    }

    /**
     * Assign a (new) parent to the current course.
     */
    public function assign_parent_action()
    {
        if ($parent = Request::option('parent')) {
            $this->course->parent_course = $parent;
            NotificationCenter::postNotification('CourseWillAddToGroup', $this->course->id, $parent);
            if ($this->course->store()) {
                $this->sync_users($parent, $this->course->id);
                NotificationCenter::postNotification('CourseDidAddToGroup', $this->course->id, $parent);
                StudipLog::log('SEM_ADD_TO_GROUP', $this->course->id, $parent, null, null, $GLOBALS['user']->id);
                PageLayout::postSuccess(_('Die Hauptveranstaltung wurde zugeordnet.'));
            } else {
                PageLayout::postError(_('Die Hauptveranstaltung konnte nicht zugeordnet werden.'));
            }
        } else {
            PageLayout::postError(_('Bitte geben Sie eine Veranstaltung an, zu der zugeordnet werden soll.'));
        }
        $this->relocate('course/grouping/parent');
    }

    /**
     * Remove this courses' current parent.
     */
    public function unassign_parent_action()
    {
        $parent = $this->course->parent_course;
        $this->course->parent_course = null;
        NotificationCenter::postNotification('CourseWillRemoveFromGroup', $this->course->id, $parent);
        if ($this->course->store()) {
            NotificationCenter::postNotification('CourseDidRemoveFromGroup', $this->course->id, $parent);
            StudipLog::log('SEM_DEL_FROM_GROUP', $this->course->id, $parent, null, null, $GLOBALS['user']->id);
            PageLayout::postSuccess(_('Die Zuordnung zur Hauptveranstaltung wurde entfernt.'));
        } else {
            PageLayout::postError(_('Die Zuordnung zur Hauptveranstaltung konnte nicht entfernt werden.'));
        }
        $this->relocate('course/grouping/parent');
    }

    /**
     * Assign a (new) child to the current course.
     */
    public function assign_child_action()
    {
        if ($child = Request::option('child')) {

            $child_course = Course::find($child);
            $child_course->parent_course = $this->course->id;
            NotificationCenter::postNotification('CourseWillAddToGroup', $child, $this->course->id);
            if ($child_course->store()) {
                $this->sync_users($this->course->id, $child);
                NotificationCenter::postNotification('CourseDidAddToGroup', $child, $this->course->id);
                StudipLog::log('SEM_ADD_TO_GROUP', $child, $this->course->id, null, null, $GLOBALS['user']->id);
                PageLayout::postSuccess(_('Die Unterveranstaltung wurde hinzugefügt.'));
            } else {
                PageLayout::postError(_('Die Unterveranstaltung konnte nicht hinzugefügt werden.'));
            }
        } else {
            PageLayout::postError(_('Bitte geben Sie eine Veranstaltung an, die als Unterveranstaltung hinzugefügt werden soll.'));
        }
        $this->relocate('course/grouping/children');
    }

    /**
     * Remove the given child.
     * @param String $id The course ID to remove as child.
     */
    public function unassign_child_action($id)
    {
        $child = Course::find($id);
        $child->parent_course = null;
        NotificationCenter::postNotification('CourseWillRemoveFromGroup', $child, $this->course->id);
        if ($child->store()) {
            NotificationCenter::postNotification('CourseDidRemoveFromGroup', $child, $this->course->id);
            StudipLog::log('SEM_DEL_FROM_GROUP', $id, $this->course->id, null, null, $GLOBALS['user']->id);
            PageLayout::postSuccess(_('Die Unterveranstaltung wurde entfernt.'));
        } else {
            PageLayout::postError(_('Die Unterveranstaltung konnte nicht entfernt werden.'));
        }
        $this->relocate('course/grouping/children');
    }

    /**
     * Batch creation of several subcourses at once.
     */
    public function create_children_action()
    {
    }

    /**
     * Add selected members to given courses
     * with the given permission level.
     */
    public function add_members_action()
    {
        CSRFProtection::verifySecurityToken();

        $fail = [];
        // Iterate over selected courses...
        foreach (Request::optionArray('courses') as $course) {
            $sem = Seminar::getInstance($course);

            // ... and selected users.
            foreach (Request::optionArray('users') as $user) {
                // Try to add deputies.
                if (Request::option('permission') == 'deputy') {
                    // If not already deputy, create new entry.
                    if (!Deputy::exists([$course, $user])) {
                        $d = new Deputy();
                        $d->range_id = $course;
                        $d->user_id = $user;
                        // Error on storing.
                        if (!$d->store()) {
                            $fail[$sem->getFullname()][] = $user;
                        // Check if new deputy was regular member before, remove entry.
                        } else {
                            $m = CourseMember::find([$course, $user]);
                            // Could not delete old course membership, remove deputy entry.
                            if ($m && !$m->delete()) {
                                $d->delete();
                                $fail[$sem->getFullname()][] = $user;
                            }
                        }
                    }
                // Add member with given permission.
                } elseif (!$sem->addMember($user, Request::option('permission'))) {
                    $fail[$sem->getFullname()][] = $user;
                }
            }
        }

        if (count($fail) > 0) {
            PageLayout::postError(
                _('In folgenden Veranstaltungen sind Probleme beim Eintragen der gewünschten Personen aufgetreten:'),
                array_keys($fail)
            );
        } else {
            PageLayout::postSuccess(ngettext(
                'Die gewählte Person wurde eingetragen.',
                'Die gewählten Personen wurden eingetragen.',
                count(Request::optionArray('users'))
            ));
        }

        $this->relocate('course/grouping/members');
    }

    /**
     * Sychronizes members between parent and child course.
     * @param string $parent_id parent course ID
     * @param string $child_id child course ID
     */
    private function sync_users($parent_id, $child_id)
    {
        $sem = Seminar::getInstance($parent_id);
        $csem = Seminar::getInstance($child_id);
        /*
         * Find users that are in current course but not in parent.
         */
         $query = "SELECT u.`user_id`
                   FROM `seminar_user` u
                   WHERE u.`Seminar_id` = :course
                     AND u.`status` = :status
                     AND NOT EXISTS (
                         SELECT `user_id` FROM `seminar_user`
                         WHERE `Seminar_id` = :parent
                           AND `status` = :status
                           AND `user_id` = u.`user_id`
                     )";
        $diff = DBManager::get()->prepare($query);

        /*
         * Before synchronizing the lecturers, we add all institutes
         * from child course.
         */
        $sem->setInstitutes(array_merge($sem->getInstitutes(), $csem->getInstitutes()));

        /*
         * Synchronize all members (including lecturers, tutors
         * and deputies with parent course.
         */
        foreach (words('user autor tutor dozent') as $permission) {
            $diff->execute([
                'course' => $child_id,
                'status' => $permission,
                'parent' => $parent_id
            ]);
            foreach ($diff->fetchFirst() as $user) {
                $sem->addMember($user, $permission);

                // Add default deputies of current user if applicable.
                if ($permission === 'dozent' && Config::get()->DEPUTIES_ENABLE
                    && Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE)
                {
                    foreach (Deputy::findByRange_id($user) as $deputy) {
                        if (!Deputy::exists([$parent_id, $deputy->user_id]) &&
                                !CourseMember::exists([$parent_id, $deputy->user_id]))
                        {
                            $d = new Deputy();
                            $d->range_id = $parent_id;
                            $d->user_id = $user;
                            $d->store();
                        }
                    }
                }
            }
        }

        // Deputies.
        if (Config::get()->DEPUTIES_ENABLE) {
            foreach (Deputy::findByRange_id($child_id) as $deputy) {
                if (!Deputy::exists([$parent_id, $deputy->user_id])
                    && !CourseMember::exists([$parent_id, $deputy->user_id]))
                {
                    $d = new Deputy();
                    $d->range_id = $parent_id;
                    $d->user_id = $deputy->user_id;
                    $d->store();
                }
            }
        }

    }

}
