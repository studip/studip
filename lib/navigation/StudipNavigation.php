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
        try {
            if (Config::get()->RESOURCES_ENABLE
                && Config::get()->RESOURCES_SHOW_PUBLIC_ROOM_PLANS
                && $user->id == 'nobody'
                && Room::publicBookingPlansExists()) {
                //Show a navigation entry for the public booking plans overview.
                $nav = new Navigation(
                    _('Belegungspläne'),
                    URLHelper::getURL('dispatch.php/room_management/overview/public_booking_plans')
                );
                $nav->setImage(Icon::create('timetable', 'navigation'));
                $this->addSubNavigation('public_booking_plans', $nav);
            }
        } catch (PDOException $e) {
            //The resource migration probably hasn't run yet.
            //Do nothing here.
        }

        // if a course is selected, the navigation for it will be loaded, but
        // it will not be shown in the main toolbar
        if (Context::getId()) {
            $this->addSubNavigation('course', new CourseNavigation());
        }

        // contents pages
        if (is_object($user) && $user->id != 'nobody') {
            $this->addSubNavigation('contents', new ContentsNavigation());
        }

        // contents pages
        if (Config::get()->OERCAMPUS_ENABLED && $perm && $perm->have_perm(Config::get()->OER_PUBLIC_STATUS)) {
            $this->addSubNavigation('oer', new OERNavigation());
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

        //Add the resource management icon, if that area is enabled
        //and the current user either has global admin permissions in the
        //room and resource management or if the the current user has
        //autor permissions on at least one resource.
        $current_user = User::findCurrent();
        //The resources navigation entry shall only be visible
        //for users who are logged in.
        if ($current_user) {
            try {
                $show_resources_navigation = (
                    (
                        RoomManager::userHasRooms($current_user)
                        ||
                        ResourceManager::userHasGlobalPermission(
                            $current_user,
                            'user'
                        )
                    )
                    &&
                    Config::get()->RESOURCES_ENABLE
                );
                if ($show_resources_navigation) {
                    $this->addSubNavigation('resources', new ResourceNavigation());
                }
            } catch (PDOException $e) {
                //The resource migration probably hasn't run yet.
                //Do nothing here.
            }
        }

        // admin page
        if (is_object($user) && $perm->have_perm('admin')) {
            $this->addSubNavigation('admin', new AdminNavigation());
        }

        //mvv pages
        if (MVV::isVisible()) {
            $this->addSubNavigation('mvv', new MVVNavigation());
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
