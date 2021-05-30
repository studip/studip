<?php
/**
 * Settings_NotificataionController - Administration of all user notification
 * related settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'settings.php';

class Settings_NotificationController extends Settings_SettingsController
{
    /**
     * Set up this controller
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     *
     * @throws AccessDeniedException if notifications are not globally enabled
     *                               or if the user has no access to these
     *                               notifications (admin or root accounts).
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Config::get()->MAIL_NOTIFICATION_ENABLE) {
            $message = _('Die Benachrichtigungsfunktion wurde in den Systemeinstellungen nicht freigeschaltet.');
            throw new AccessDeniedException($message);
        }

        if (!$GLOBALS['auth']->is_authenticated() || $GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        PageLayout::setHelpKeyword('Basis.MyStudIPBenachrichtigung');
        PageLayout::setTitle(_('Benachrichtigung über neue Inhalte anpassen'));
        Navigation::activateItem('/profile/settings/notification');
        SkipLinks::addIndex(_('Benachrichtigung über neue Inhalte anpassen'), 'layout_content', 100);
    }

    /**
     * Display the notification settings of a user.
     */
    public function index_action()
    {
        $group_field = 'sem_number';

        $add_fields = $add_query = '';
        if ($group_field == 'sem_tree_id') {
            $add_fields = ',sem_tree_id';
            $add_query  = "LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminare.Seminar_id)";
        } else if ($group_field == 'dozent_id') {
            $add_fields = ', su1.user_id as dozent_id';
            $add_query  = "LEFT JOIN seminar_user as su1 ON (su1.seminar_id=seminare.Seminar_id AND su1.status='dozent')";
        }

        $dbv = DbView::getView('sem_tree');

        $query = "SELECT seminare.VeranstaltungsNummer AS sem_nr, seminare.Name, seminare.Seminar_id,
                         seminare.status AS sem_status, seminar_user.gruppe, seminare.visible,
                         {$dbv->sem_number_sql} AS sem_number, {$dbv->sem_number_end_sql} AS sem_number_end
                         {$add_fields}
                  FROM seminar_user
                  LEFT JOIN seminare  USING (Seminar_id)
                  {$add_query}
                  WHERE seminar_user.user_id = ?";
        if (Config::get()->DEPUTIES_ENABLE) {
            $query .= " UNION " . Deputy::getMySeminarsQuery(
                'notification', $dbv->sem_number_sql, $dbv->sem_number_end_sql, $add_fields, $add_query
                );
        }
        $query .= " ORDER BY sem_nr ASC";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->user->user_id]);
        $seminars = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!count($seminars)) {
            $message = sprintf(_('Sie haben zur Zeit keine Veranstaltungen belegt. Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzufügen</b>%s um sch für Veranstaltungen anzumdelden.'),
                '<a href="' . URLHelper::getLink('dispatch.php/search/courses') . '">', '</a>');
            PageLayout::postInfo($message);
            $this->render_nothing();
            return;
        }
        $modules_notification = new ModulesNotification();
        $enabled_modules = $modules_notification->registered_notification_modules;

        $groups = [];
        $my_sem = [];
        foreach ($seminars as $seminar) {
            $my_sem[$seminar['Seminar_id']] = [
                'obj_type'       => "sem",
                'sem_nr'         => $seminar['sem_nr'],
                'name'           => $seminar['Name'],
                'visible'        => $seminar['visible'],
                'gruppe'         => $seminar['gruppe'],
                'sem_status'     => $seminar['sem_status'],
                'sem_number'     => $seminar['sem_number'],
                'sem_number_end' => $seminar['sem_number_end'],
            ];
            if ($group_field) {
                fill_groups($groups, $seminar[$group_field], [
                    'seminar_id' => $seminar['Seminar_id'],
                    'sem_nr'     => $seminar['sem_nr'],
                    'name'       => $seminar['Name'],
                    'gruppe'     => $seminar['gruppe'],
                ]);
            }
        }

        if ($group_field == 'sem_number') {
            correct_group_sem_number($groups, $my_sem);
        } else {
            add_sem_name($my_sem);
        }


        sort_groups($group_field, $groups);
        $group_names   = get_group_names($group_field, $groups);
        $notifications = $this->user->course_notifications;
        $open          = UserConfig::get($this->user->user_id)->MY_COURSES_OPEN_GROUPS;
        $checked       = [];
        foreach ($groups as $group_id => $group_members) {
            if ($group_id !== 'not_grouped' && !isset($open[$group_id])) {
                continue;
            }
            foreach ($group_members as $member) {
                $checked[$member['seminar_id']] = [];
                foreach ($enabled_modules as $index => $module) {
                    $notify = $notifications->findOneBy('seminar_id', $member['seminar_id']);
                    $checked[$member['seminar_id']][$index] = $notify && in_array($index, $notify->notification_data->getArrayCopy());
                }
                $checked[$member['seminar_id']]['all'] = count($enabled_modules) === count(array_filter($checked[$member['seminar_id']]));
            }
        }

        $this->modules       = $enabled_modules;
        $this->groups        = $groups;
        $this->group_names   = $group_names;
        $this->group_field   = $group_field;
        $this->open          = $open;
        $this->seminars      = $my_sem;
        $this->notifications = $notifications;
        $this->checked       = $checked;
    }

    /**
     * Stores the notification settings of a user.
     */
    public function store_action()
    {
        $this->check_ticket();
        foreach (Request::getArray('m_checked') as $course_id => $checked) {
            unset($checked['empty']);
            if (!count($checked)) {
                CourseMemberNotification::deleteBySQL('user_id=? AND seminar_id=?', [$this->user->user_id, $course_id]);
            } else {
                $notify = new CourseMemberNotification([$this->user->user_id, $course_id]);
                $notify->notification_data = array_keys($checked);
                $notify->store();
            }
        }
        PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        $this->redirect('settings/notification');
    }

    /**
     * Opens a specific area.
     *
     * @param String $id Id of the area to be opened
     */
    public function open_action($id)
    {
        $open      = $this->config->MY_COURSES_OPEN_GROUPS;
        $open[$id] = true;
        $this->config->store('MY_COURSES_OPEN_GROUPS', $open);

        $this->redirect('settings/notification');
    }

    /**
     * Closes a specific area.
     *
     * @param String $id Id of the area to be closed
     */
    public function close_action($id)
    {
        $open = $this->config->MY_COURSES_OPEN_GROUPS;
        unset($open[$id]);
        $this->config->store('MY_COURSES_OPEN_GROUPS', $open);

        $this->redirect('settings/notification');
    }

    public function module_icon($area)
    {
        $mapping = [
            'documents'           => 'files',
            'elearning_interface' => 'learnmodule',
            'scm'                 => 'infopage',
            'votes'               => 'vote',
            'basic_data'          => 'seminar',
            'participants'        => 'persons',
            'plugins'             => 'plugin',
        ];

        return $mapping[$area] ?: $area;
    }
}
