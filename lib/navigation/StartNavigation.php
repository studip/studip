<?php
# Lifter010: TODO
/*
 * StartNavigation.php - navigation for start page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StartNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        $url = (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody')
             ? 'dispatch.php/start'
             : 'index.php';
        parent::__construct(_('Start'), $url);
    }

    public function initItem()
    {
        parent::initItem();

        if (mb_stripos($_SERVER['REQUEST_URI'], "web_migrate.php") === false && is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') {
            if (WidgetHelper::hasWidget($GLOBALS['user']->id, 'News')) {
                $news = StudipNews::CountUnread();
            }
            if (Config::get()->VOTE_ENABLE && WidgetHelper::hasWidget($GLOBALS['user']->id, 'Evaluations')) {
                $threshold = object_get_visit_threshold();
                $statement = DBManager::get()->prepare("
                    SELECT COUNT(*)
                    FROM questionnaire_assignments
                        INNER JOIN questionnaires ON (questionnaires.questionnaire_id = questionnaire_assignments.questionnaire_id)
                    LEFT JOIN object_user_visits b ON (b.object_id = questionnaires.questionnaire_id AND b.user_id = :user_id AND b.plugin_id = :plugin_id)

                    WHERE questionnaire_assignments.range_id = 'start'
                        AND questionnaires.chdate > IFNULL(b.visitdate, :threshold)
                        AND questionnaires.user_id <> :user_id
                        AND questionnaires.startdate IS NOT NULL AND questionnaires.startdate < UNIX_TIMESTAMP()
                AND (
                    questionnaires.stopdate IS NULL OR questionnaires.stopdate > UNIX_TIMESTAMP() )
                ");
                $statement->execute(['threshold' => $threshold,
                    ':user_id' => $GLOBALS['user']->id, ':plugin_id' => -1]);
                $vote = (int) $statement->fetchColumn();
                $query = "SELECT COUNT(IF(chdate > IFNULL(b.visitdate, :threshold) AND d.author_id != :user_id, a.eval_id, NULL))
                          FROM eval_range a
                          INNER JOIN eval d ON (a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP() AND
                                            (d.stopdate > UNIX_TIMESTAMP() OR d.startdate + d.timespan > UNIX_TIMESTAMP() OR (d.stopdate IS NULL AND d.timespan IS NULL)))
                          LEFT JOIN object_user_visits b ON (b.object_id = d.eval_id AND b.user_id = :user_id AND b.plugin_id = :plugin_id)
                          WHERE a.range_id = 'studip'
                          GROUP BY a.range_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':user_id', $GLOBALS['user']->id);
                $statement->bindValue(':threshold', $threshold);
                $statement->bindValue(':plugin_id', -2);
                $statement->execute();
                $vote += (int)$statement->fetchColumn();
            }
        }

        $homeinfo = _('Zur Startseite');
        if ($news) {
            $homeinfo .= ' - ';
            $homeinfo .= sprintf(ngettext('%u neue Ankündigung', '%u neue Ankündigungen', $news), $news);
        }
        if ($vote) {
            $homeinfo .= ' - ';
            $homeinfo .= sprintf(ngettext('%u neuer Fragebogen', '%u neue Fragebögen', $vote), $vote);
        }
        $this->setBadgeNumber($vote + $news);

        $this->setImage(Icon::create('home', 'navigation', ["title" => $homeinfo]));
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm, $auth;
        $username = $auth->auth['uname'];

        parent::initSubNavigation();

        if (!is_object($perm) || !$perm->have_perm('user')) {
            return;
        }

        $sem_create_perm = in_array(Config::get()->SEM_CREATE_PERM, ['root','admin','dozent'])
                         ? Config::get()->SEM_CREATE_PERM
                         : 'dozent';

        // my courses
        if ($perm->have_perm('root')) {
            $navigation = new Navigation(_('Veranstaltungsübersicht'), 'dispatch.php/admin/courses');
        } else if ($perm->have_perm('admin')) {
            $navigation = new Navigation(_('Veranstaltungen an meinen Einrichtungen'), 'dispatch.php/my_courses');
        } else {
            $navigation = new Navigation(_('Meine Veranstaltungen'), 'dispatch.php/my_courses');

            if (!$perm->have_perm('dozent')) {
                $navigation->addSubNavigation('browse', new Navigation(_('Veranstaltung hinzufügen'), 'dispatch.php/search/courses'));

                if ($perm->have_perm('autor') && Config::get()->STUDYGROUPS_ENABLE) {
                    $navigation->addSubNavigation('new_studygroup', new Navigation(_('Studiengruppe anlegen'), 'dispatch.php/course/wizard?studygroup=1'));
                }
            } else {
                if ($perm->have_perm($sem_create_perm)) {
                    $navigation->addSubNavigation('new_course', new Navigation(_('Neue Veranstaltung anlegen'), 'dispatch.php/course/wizard'));
                }
                if (Config::get()->STUDYGROUPS_ENABLE) {
                    $navigation->addSubNavigation('new_studygroup', new Navigation(_('Studiengruppe anlegen'), 'dispatch.php/course/wizard?studygroup=1'));
                }

            }
        }
        // coursesets
        if ($perm->have_perm('admin') || (Config::get()->ALLOW_DOZENT_COURSESET_ADMIN && $perm->have_perm('dozent'))) {
            $navigation->addSubNavigation('coursesets', new Navigation(_('Anmeldesets'), 'dispatch.php/admission/courseset'));
        }
        // export
        if (Config::get()->EXPORT_ENABLE && $perm->have_perm('tutor')) {
            $navigation->addSubNavigation('export', new Navigation(_('Export'), 'export.php'));
        }

        $this->addSubNavigation('my_courses', $navigation);

        // course administration
        if ($perm->have_perm('admin')) {
           $navigation = new Navigation(_('Verwaltung von Veranstaltungen'), 'dispatch.php/my_courses');

           if ($perm->have_perm($sem_create_perm)) {
               $navigation->addSubNavigation('new_course', new Navigation(_('Neue Veranstaltung anlegen'), 'dispatch.php/course/wizard'));
           }

           if (Config::get()->STUDYGROUPS_ENABLE) {
               $navigation->addSubNavigation('new_studygroup', new Navigation(_('Studiengruppe anlegen'), 'dispatch.php/course/wizard?studygroup=1'));
           }

           $this->addSubNavigation('admin_course', $navigation);
       }

        // insitute administration
        if ($perm->have_perm('admin')) {
            $navigation = new Navigation(_('Verwaltung von Einrichtungen'), 'dispatch.php/institute/basicdata/index?list=TRUE');
            $this->addSubNavigation('admin_inst', $navigation);
        }

        // user administration
        if ($perm->have_perm('root') || $perm->have_perm('admin') && !Config::get()->RESTRICTED_USER_MANAGEMENT) {
            $navigation = new Navigation(_('Globale Benutzerverwaltung'), 'dispatch.php/admin/user');
            $this->addSubNavigation('admin_user', $navigation);
        }

        // plugin and role administration
        if ($perm->have_perm('root')) {
            $navigation = new Navigation(_('Verwaltung von Plugins'), 'dispatch.php/admin/plugin');
            $navigation->addSubNavigation('admin_roles', new Navigation(_('Verwaltung von Rollen'), 'dispatch.php/admin/role'));
            $this->addSubNavigation('admin_plugins', $navigation);
        }

        // administration of resources
        if (Config::get()->RESOURCES_ENABLE) {
            $current_user = User::findCurrent();
            $global_resource_permissions = ResourceManager::getGlobalResourcePermission(
                $current_user
            );
            $show_resources_navigation = false;
            if ($global_resource_permissions == 'admin') {
                $show_resources_navigation = true;
            } else {
                $show_resources_navigation = RoomManager::userHasRooms(
                    $current_user,
                    'user'
                );
            }
            if ($show_resources_navigation) {
                if ($global_resource_permissions == 'admin') {
                    $navigation = new Navigation(_('Raumverwaltung'));
                    $navigation->addSubNavigation(
                        'resources_overview',
                        new Navigation(
                            _('Übersicht'),
                            'dispatch.php/room_management/overview/index'
                        )
                    );
                    $navigation->addSubNavigation(
                        'room_planning',
                        new Navigation(
                            _('Raumplanung'),
                            'dispatch.php/room_management/planning/index'
                        )
                    );
                    $navigation->addSubNavigation(
                        'categories',
                        new Navigation(
                            _('Ressourcenkategorien anpassen'),
                            'dispatch.php/resources/admin/categories'
                        )
                    );
                    $this->addSubNavigation('resources', $navigation);
                } else {
                    //Users who are not resource admins see another page that
                    //displays only those resources where they have permissions for.
                    $navigation = new Navigation(
                        _('Meine Räume'),
                        'dispatch.php/room_management/overview/rooms'
                    );
                    $this->addSubNavigation('resources', $navigation);
                }
            }
        }

        // contents
        $navigation = new Navigation(_('Mein Arbeitsplatz'), 'dispatch.php/contents/overview');
        $navigation->addSubNavigation('courseware', new Navigation(_('Courseware'), 'dispatch.php/contents/courseware'));
        $navigation->addSubNavigation('files', new Navigation(_('Dateien'), 'dispatch.php/files/overview'));

        if (Config::get()->VOTE_ENABLE) {
            $navigation->addSubNavigation('questionnaire', new Navigation(_('Ankündigungen'), 'dispatch.php/news/admin_news'));
            $navigation->addSubNavigation('evaluation', new Navigation(_('Evaluationen'), 'admin_evaluation.php', ['rangeID' => $auth->auth['uname']]));
        }

        // elearning
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            $navigation->addSubNavigation('my_elearning', new Navigation(_('Lernmodule'), 'dispatch.php/elearning/my_accounts'));
        }

        if (!$GLOBALS['perm']->have_perm('root') && $GLOBALS['user']->getAuthenticatedUser()->hasRole('Hilfe-Administrator(in)')) {
            $navigation->addSubNavigation('help_content', new Navigation(_('Hilfe-Texte und Touren'), 'dispatch.php/help_content/admin_overview'));
        }

        $this->addSubNavigation('contents', $navigation);

        // oer
        $this->addSubNavigation('oer', new Navigation(_('OER Campus'), 'dispatch.php/oer/market'));

        // messaging
        $navigation = new Navigation(_('Nachrichten'));
        $navigation->addSubNavigation('in', new Navigation(_('Posteingang'), 'dispatch.php/messages/overview'));
        $navigation->addSubNavigation('out', new Navigation(_('Gesendet'), 'dispatch.php/messages/sent'));
        $this->addSubNavigation('messaging', $navigation);

        // community
        $navigation = new Navigation(_('Community'));
        $navigation->addSubNavigation('online', new Navigation(_('Wer ist online?'), 'dispatch.php/online'));
        $navigation->addSubNavigation('contacts', new Navigation(_('Meine Kontakte'), 'dispatch.php/contact'));
        // study groups
        if (Config::get()->STUDYGROUPS_ENABLE) {
            $navigation->addSubNavigation('browse',new Navigation(_('Studiengruppen'), 'dispatch.php/studygroup/browse'));
        }
        // ranking
        if (Config::get()->SCORE_ENABLE) {
            $navigation->addSubNavigation('score', new Navigation(_('Rangliste'), 'dispatch.php/score'));
            $this->addSubNavigation('community', $navigation);
        }

        // calendar / home page
        if (!$perm->have_perm('admin')) {
            $navigation = new Navigation(_('Mein Profil'), 'dispatch.php/profile');

            if ($perm->have_perm('autor')) {
                $navigation->addSubNavigation('settings', new Navigation(_('Einstellungen'), 'dispatch.php/settings/general'));
            }

            $this->addSubNavigation('profile', $navigation);

            $navigation = new Navigation(_('Mein Planer'));

            if (Config::get()->CALENDAR_ENABLE) {
                $navigation->addSubNavigation('calendar', new Navigation(_('Terminkalender'), 'dispatch.php/calendar/single'));
            }

            if (Config::get()->SCHEDULE_ENABLE) {
                $navigation->addSubNavigation('schedule', new Navigation(_('Stundenplan'), 'dispatch.php/calendar/schedule'));
            }

            $this->addSubNavigation('planner', $navigation);
        }

        // global search
        $navigation = new Navigation(_('Suchen'), 'dispatch.php/search/globalsearch');
        $navigation->addSubNavigation('course', new Navigation(_('Veranstaltungssuche'), 'dispatch.php/search/courses'));
        if (Config::get()->RESOURCES_ENABLE) {
            $navigation->addSubNavigation('rooms', new Navigation(_('Räume suchen'), 'dispatch.php/resources/search/rooms'));
        }
        $this->addSubNavigation('search', $navigation);

        //mvv pages
        if (MVV::isVisible()) {
            $this->addSubNavigation('mvv', new MVVNavigation());
        }

        // external help
        $navigation = new Navigation(_('Hilfe'), format_help_url('Basis.Allgemeines'));
        $navigation->addSubNavigation('intro', new Navigation(_('Schnelleinstieg'), format_help_url('Basis.SchnellEinstiegKomplett')));
        $this->addSubNavigation('help', $navigation);
    }
}
