<?php
# Lifter010: TODO
/*
 * ToolsNavigation.php - navigation for tools page
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
 * @since       2.0
 */

/**
 * This navigation includes all tools for a user depending on the
 * activated modules.
 */
class ToolsNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Tools'));

        $this->setImage(Icon::create('tools', 'navigation', ["title" => _('Tools')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $auth, $perm;

        parent::initSubNavigation();

        $username = $auth->auth['uname'];

        // news
        $navigation = new Navigation(_('Ankündigungen'), 'dispatch.php/news/admin_news');
        $this->addSubNavigation('news', $navigation);

        // votes and tests, evaluations
        if (Config::get()->VOTE_ENABLE) {
            $navigation = new Navigation(_('Fragebögen'), 'dispatch.php/questionnaire/overview');
            $this->addSubNavigation('questionnaire', $navigation);

            $sub_nav = new Navigation(
                _('Übersicht'),
                'dispatch.php/questionnaire/overview'
            );
            $navigation->addSubNavigation('overview', $sub_nav);

            if ($GLOBALS['perm']->have_perm('admin')) {
                $sub_nav = new Navigation(
                    _('Fragebögen zuordnen'),
                    'dispatch.php/questionnaire/assign'
                );
                $navigation->addSubNavigation('assign', $sub_nav);
            }

            $navigation = new Navigation(_('Evaluationen'), 'admin_evaluation.php', ['rangeID' => $username]);
            $this->addSubNavigation('evaluation', $navigation);
        }

        // elearning
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            $navigation = new Navigation(_('Lernmodule'), 'dispatch.php/elearning/my_accounts');
            $this->addSubNavigation('my_elearning', $navigation);
        }

        // export
        if (Config::get()->EXPORT_ENABLE && $perm->have_perm('tutor')) {
            $navigation = new Navigation(_('Export'), 'export.php');
            $this->addSubNavigation('export', $navigation);
        }

        if ($perm->have_perm('admin') || ($perm->have_perm('dozent') && Config::get()->ALLOW_DOZENT_COURSESET_ADMIN)) {
            $navigation = new Navigation(_('Anmeldesets'), 'dispatch.php/admission/courseset/index');
            $this->addSubNavigation('coursesets', $navigation);
            $navigation->addSubNavigation('sets', new Navigation(_('Anmeldesets verwalten'), 'dispatch.php/admission/courseset/index'));
            $navigation->addSubNavigation('userlists', new Navigation(_('Personenlisten'), 'dispatch.php/admission/userlist/index'));
            $navigation->addSubNavigation('restricted_courses', new Navigation(_('teilnahmebeschränkte Veranstaltungen'), 'dispatch.php/admission/restricted_courses'));
        }

        if (!$GLOBALS['perm']->have_perm('root') && $GLOBALS['user']->getAuthenticatedUser()->hasRole('Hilfe-Administrator(in)')) {
            $navigation = new Navigation(_('Hilfe'), 'dispatch.php/help_content/admin_overview');
            $this->addSubNavigation('help_admin', $navigation);
            if (Config::get()->TOURS_ENABLE) {
                $navigation->addSubNavigation('tour', new Navigation(_('Touren'), 'dispatch.php/tour/admin_overview'));
            }
            $navigation->addSubNavigation('help_content', new Navigation(_('Hilfe-Texte'), 'dispatch.php/help_content/admin_overview'));
        }

    }
}
