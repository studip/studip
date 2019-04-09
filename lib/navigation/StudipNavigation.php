<?php
# Lifter010: TODO
/**
 * StudipNavigation.php - Stud.IP root navigation class
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

/**
 * This is the class for the top navigation (toolbar) in the page header.
 */
class StudipNavigation extends Navigation
{
    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm, $user;

        parent::initSubNavigation();

        // top navigation (toolbar)
        $this->addSubNavigation('start', new StartNavigation());

        // if the user is not logged in, he will see the free courses, otherwise
        // the my courses page will be shown.
        if (is_object($user) && $user->id != 'nobody' || Config::get()->ENABLE_FREE_ACCESS) {
            $this->addSubNavigation('browse', new BrowseNavigation());
        }

        // if a course is selected, the navigation for it will be loaded, but
        // it will not be shown in the main toolbar
        if (Context::getId()) {
            $this->addSubNavigation('course', new CourseNavigation());
        }

        if (is_object($user) && $user->id != 'nobody') {
            // internal message system
            $this->addSubNavigation('messaging', new MessagingNavigation());

            // community page
            $this->addSubNavigation('community', new CommunityNavigation());

            // user profile page
            $this->addSubNavigation('profile', new ProfileNavigation());

            // calendar and schedule page
            $this->addSubNavigation('calendar', new CalendarNavigation());

            // search page
            $this->addSubNavigation('search', new SearchNavigation());

            // avatar menu
            $this->addSubNavigation('avatar', new AvatarNavigation());
        } else if ($user->id == 'nobody'
                && Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY) {
            // search page
            $this->addSubNavigation('search', new SearchNavigation());
        }

        // tools page
        if (is_object($user) && $perm->have_perm('autor')) {
            $this->addSubNavigation('tools', new ToolsNavigation());
        }

        // admin page
        if (is_object($user) && $perm->have_perm('admin')) {
            $this->addSubNavigation('admin', new AdminNavigation());
        }

        //mvv pages
        if (MVV::isVisible()) {
            $this->addSubNavigation('mvv', new MVVNavigation());
        }

        // resource managment, if it is enabled
        if (get_config('RESOURCES_ENABLE')) {
            require_once 'lib/resources/resourcesFunc.inc.php';
            //TODO: suboptimal, es sollte eine ResourcesNavigation geben
            $navigation = new Navigation(_('Ressourcen'), 'resources.php', ['view' => 'resources']);

            if (is_object($user) && (getGlobalPerms($user->id)=='admin' || checkAvailableResources($user->id))) {
                $navigation->setImage(Icon::create('resources', 'navigation', ["title" => _('Zur Ressourcenverwaltung')]));
            }

            $this->addSubNavigation('resources', $navigation);
        }

        if (is_object($user) && $user->id != 'nobody') {
            $this->addSubNavigation('files_dashboard', new FilesDashboardNavigation());
        }

        // quick links
        $links = new Navigation('Links');

        // login / logout
        if (!is_object($user) && $user->id === 'nobody') {
            if (in_array('CAS', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
                $links->addSubNavigation('login_cas', new Navigation(_('Login CAS'), Request::url(), ['again' => 'yes', 'sso' => 'cas']));
            }

            if (in_array('Shib', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
                $links->addSubNavigation('login_shib', new Navigation(_('Login Shibboleth'), Request::url(), ['again' => 'yes', 'sso' => 'shib']));
            }

            $links->addSubNavigation('login', new Navigation(_('Login'), Request::url(), ['again' => 'yes']));
        }

        $this->addSubNavigation('links', $links);

        // footer links
        $this->addSubNavigation('footer', new FooterNavigation(_('Footer')));

        // login page
        $this->addSubNavigation('login', new LoginNavigation(_('Login')));
    }
}
