<?php
# Lifter010: TODO
/*
 * AdminNavigation.php - navigation for admin area
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class AdminNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Admin'));

        $this->setImage(Icon::create('admin', 'navigation', ["title" => _('Zu Ihrer Administrationsseite')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $SessionSeminar, $archive_kill, $perm;

        parent::initSubNavigation();

        if (Context::isInstitute()) {
            if (isset($_SESSION['links_admin_data']['referred_from']) && $_SESSION['links_admin_data']['referred_from'] == 'inst') {
                $back_jump = _('zurück zur ausgewählten Einrichtung');
            } else {
                $back_jump = _('zur ausgewählten Einrichtung');
            }
        } else if (Context::isCourse()) {
            if (isset($_SESSION['links_admin_data']['referred_from']) && $_SESSION['links_admin_data']['referred_from'] == 'sem' && !$archive_kill && !(isset($_SESSION['links_admin_data']['assi']) && $_SESSION['links_admin_data']['assi'])) {
                $back_jump = _('zurück zur ausgewählten Veranstaltung');
            } else if (isset($_SESSION['links_admin_data']['referred_from']) && $_SESSION['links_admin_data']['referred_from'] == 'assi' && !$archive_kill) {
                $back_jump = _('zur neu angelegten Veranstaltung');
            } else if (!(isset($_SESSION['links_admin_data']['assi']) && $_SESSION['links_admin_data']['assi'])) {
                $back_jump = _('zur ausgewählten Veranstaltung');
            }
        }

        $sem_create_perm = in_array(Config::get()->SEM_CREATE_PERM, ['root', 'admin', 'dozent']) ? Config::get()->SEM_CREATE_PERM : 'dozent';

        // global config / user administration
        if (!Config::get()->RESTRICTED_USER_MANAGEMENT || $perm->have_perm('root')) {
            $navigation = new Navigation(_('Benutzer'));
            $navigation->setURL('dispatch.php/admin/user/');
            $navigation->addSubNavigation('index', new Navigation(_('Benutzer'), 'dispatch.php/admin/user'));

            if ($perm->have_perm('root')) {
                $navigation->addSubNavigation('user_domains', new Navigation(_('Nutzerdomänen'), 'dispatch.php/admin/domain'));
                $navigation->addSubNavigation('auto_insert', new Navigation(_('Automatisiertes Eintragen'), 'dispatch.php/admin/autoinsert'));
            }
            $this->addSubNavigation('user', $navigation);
        }

        // institute administration
        $navigation = new Navigation(_('Einrichtungen'));

        $navigation->setURL('dispatch.php/institute/basicdata/index?cid=');
        $navigation->addSubNavigation('details', new Navigation(_('Grunddaten'), 'dispatch.php/institute/basicdata/index'));
        $navigation->addSubNavigation('faculty', new Navigation(_('Mitarbeiter'), 'dispatch.php/institute/members?admin_view=1'));
        $navigation->addSubNavigation('groups', new Navigation(_('Funktionen / Gruppen'), 'dispatch.php/admin/statusgroups?type=inst'));

        if (Config::get()->VOTE_ENABLE) {
            $navigation->addSubNavigation('evaluation', new Navigation(_('Evaluationen'), 'admin_evaluation.php?view=eval_inst'));
        }

        $navigation->addSubNavigation('modules', new Navigation(_('Inhaltselemente'), 'dispatch.php/course/plus/index'));

        if (Config::get()->EXTERN_ENABLE) {
            $navigation->addSubNavigation('external', new Navigation(_('Externe Seiten'), 'admin_extern.php?view=extern_inst'));
        }

        if ($perm->have_perm("root") || ($perm->is_fak_admin() && Config::get()->INST_FAK_ADMIN_PERMS != 'none')) {
            $navigation->addSubNavigation('create', new Navigation(_('Neue Einrichtung anlegen'), 'dispatch.php/institute/basicdata/index?cid=&i_view=new'));
        }

        $this->addSubNavigation('institute', $navigation);
        $navigation = new Navigation(_('Standort'));

        if ($perm->have_perm(Config::get()->RANGE_TREE_ADMIN_PERM ? Config::get()->RANGE_TREE_ADMIN_PERM : 'admin')) {
            $navigation->addSubNavigation('range_tree', new Navigation(_('Einrichtungshierarchie'), 'admin_range_tree.php'));
        }

        if ($perm->have_perm(Config::get()->SEM_TREE_ADMIN_PERM ? Config::get()->SEM_TREE_ADMIN_PERM : 'admin') && $perm->is_fak_admin()) {
            $navigation->addSubNavigation('sem_tree', new Navigation(_('Veranstaltungshierarchie'), 'admin_sem_tree.php'));
        }

        if ($perm->have_perm(Config::get()->LOCK_RULE_ADMIN_PERM ? Config::get()->LOCK_RULE_ADMIN_PERM : 'admin')) {
            $navigation->addSubNavigation('lock_rules', new Navigation(_('Sperrebenen'), 'dispatch.php/admin/lockrules'));
        }


        if ($perm->have_perm('root')) {
            if (Config::get()->SEMESTER_ADMINISTRATION_ENABLE) {
                $navigation->addSubNavigation('semester', new Navigation(_('Semester'), 'dispatch.php/admin/semester'));
                $navigation->addSubNavigation('holidays', new Navigation(_('Ferien'), 'dispatch.php/admin/holidays'));
            }

            if (Config::get()->EXTERN_ENABLE) {
                $navigation->addSubNavigation('external', new Navigation(_('Externe Seiten'), 'admin_extern.php?list=TRUE&view=extern_global'));
            }

            $navigation->addSubNavigation('sem_classes', new Navigation(_('Veranstaltungskategorien'), 'dispatch.php/admin/sem_classes/overview'));
            $navigation->addSubNavigation('loginstyle', new Navigation(_('Startbildschirm'), 'dispatch.php/admin/loginstyle'));
            $navigation->addSubNavigation(
                'content_terms_of_use',
                new Navigation(
                    _('Inhalts-Nutzungsbedingungen'),
                    'dispatch.php/admin/content_terms_of_use/index'
                )
            );

            if (Config::get()->BANNER_ADS_ENABLE) {
                $navigation->addSubNavigation('banner', new Navigation(_('Werbebanner'), 'dispatch.php/admin/banner'));
            }
        }
        $this->addSubNavigation('locations', $navigation);

        // global config / user administration
        $navigation = new Navigation(_('System'));


        if ($perm->have_perm('root')) {
            $navigation->addSubNavigation('plugins', new Navigation(_('Plugins'), 'dispatch.php/admin/plugin'));
            $navigation->addSubNavigation('roles', new Navigation(_('Rollen'), 'dispatch.php/admin/role'));
            $navigation->addSubNavigation('datafields', new Navigation(_('Datenfelder'), 'dispatch.php/admin/datafields'));
            $navigation->addSubNavigation('configuration', new Navigation(_('Konfiguration'), 'dispatch.php/admin/configuration/configuration'));

            $navigation->addSubNavigation('coursewizardsteps',
                new Navigation(_('Anlegeassistent'), 'dispatch.php/admin/coursewizardsteps'));
            $navigation->addSubNavigation('studygroup', new Navigation(_('Studiengruppen'), 'dispatch.php/course/studygroup/globalmodules'));

            if (Config::get()->SMILEYADMIN_ENABLE) {
                $navigation->addSubNavigation('smileys', new Navigation(_('Smileys'), 'dispatch.php/admin/smileys'));
            }

            if (Config::get()->TOURS_ENABLE) {
                $navigation->addSubNavigation('tour', new Navigation(_('Touren'), 'dispatch.php/tour/admin_overview'));
            }
            $navigation->addSubNavigation('help_content', new Navigation(_('Hilfe-Texte'), 'dispatch.php/help_content/admin_overview'));

            if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
                $navigation->addSubNavigation('elearning', new Navigation(_('Lernmodule'), 'admin_elearning_interface.php'));
            }

            if (Config::get()->WEBSERVICES_ENABLE) {
                $navigation->addSubNavigation('webservice_access', new Navigation(_('Webservices'), 'dispatch.php/admin/webservice_access'));
            }

            if (Config::get()->CRONJOBS_ENABLE) {
                $navigation->addSubNavigation('cronjobs', new Navigation(_('Cronjobs'), 'dispatch.php/admin/cronjobs/schedules'));
            }

            if (Config::get()->PERSONALDOCUMENT_ENABLE) {
                $navigation->addSubNavigation('document_area', new Navigation(_('Pers. Dateibereich'), 'dispatch.php/document/administration'));
            }


            $navigation->addSubNavigation('admissionrules', new Navigation(_('Anmelderegeln'), 'dispatch.php/admission/ruleadministration'));

            if (Config::get()->API_ENABLED) {
                $navigation->addSubNavigation('api', new Navigation(_('API'), 'dispatch.php/admin/api'));
            }

            $navigation->addSubNavigation('globalsearch', new Navigation(_('Globale Suche'), 'dispatch.php/globalsearch/settings'));
        }
        if ($perm->have_perm(Config::get()->AUX_RULE_ADMIN_PERM ? Config::get()->AUX_RULE_ADMIN_PERM : 'admin')) {
            $navigation->addSubNavigation('specification', new Navigation(_('Zusatzangaben'), 'dispatch.php/admin/specification'));
        }

        $this->addSubNavigation('config', $navigation);

        // log view
        if ($perm->have_perm('root') && Config::get()->LOG_ENABLE) {
            $navigation = new Navigation(_('Log'));
            $navigation->addSubNavigation('show', new Navigation(_('Log'), 'dispatch.php/event_log/show'));
            $navigation->addSubNavigation('admin', new Navigation(_('Einstellungen'), 'dispatch.php/event_log/admin'));
            $this->addSubNavigation('log', $navigation);
        }

        // link to course
        if (Context::isInstitute()) {
            $navigation = new Navigation($back_jump, 'dispatch.php/institute/overview?auswahl=' . Context::getId());
            $this->addSubNavigation('back_jump', $navigation);
        } else if (Context::isCourse() && !$archive_kill && !(isset($_SESSION['links_admin_data']['assi']) && $_SESSION['links_admin_data']['assi'])) {
            $navigation = new Navigation($back_jump, 'seminar_main.php?auswahl=' . Context::getId());
            $this->addSubNavigation('back_jump', $navigation);
        }

        // admin plugins
        $navigation = new Navigation(_('Admin-Plugins'));
        $this->addSubNavigation('plugins', $navigation);
    }
}
