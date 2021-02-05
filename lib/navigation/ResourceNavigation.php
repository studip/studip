<?php

/**
 * ResourceNavigation.php - Room management area navigation class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017
 * @category    Stud.IP
 * @since       TODO
 */


/**
 * This navigagion class provides all standard navigation items for the
 * room management.
 */
class ResourceNavigation extends Navigation
{
    public function __construct()
    {
        parent::__construct(_('Raumverwaltung'));
    }


    public function initItem()
    {
        $user = User::findCurrent();
        if (ResourceManager::userHasGlobalPermission($user, 'user')) {
            $this->setURL('dispatch.php/room_management/overview/index');
        } else {
            $this->setURL('dispatch.php/room_management/overview/rooms');
        }
        $this->setImage(
            Icon::create('resources', 'navigation', ['title' => _('Raumverwaltung')])
        );
    }


    public function isActive()
    {
        return parent::isActive();
    }


    public function initSubNavigation()
    {
        $user = User::findCurrent();
        $show_global_admin_actions = ResourceManager::userHasGlobalPermission(
            $user,
            'admin'
        );
        //Check if the user has permissions one at lease one room:
        $user_has_rooms = RoomManager::userHasRooms($user);

        $user_is_global_resource_user = ResourceManager::userHasGlobalPermission($user, 'user');
        if (!$show_global_admin_actions && !$user_has_rooms && !$user_is_global_resource_user) {
            return;
        }
        $user_is_global_resource_autor = ResourceManager::userHasGlobalPermission($user, 'autor');

        parent::initSubNavigation();

        //Overview tab:

        if ($show_global_admin_actions) {
            $overview_navigation = new Navigation(
                _('Übersicht'),
                'dispatch.php/room_management/overview/index'
            );
            $this->addSubNavigation('overview', $overview_navigation);
        } else {
            //Non-admin users get the "my rooms" page as default
            //instead of the overview page.
            $overview_navigation = new Navigation(
                _('Meine Räume'),
                'dispatch.php/room_management/overview/rooms'
            );
            $this->addSubNavigation('overview', $overview_navigation);
            $this->setURL('dispatch.php/room_management/overview/rooms');
        }

        if ($show_global_admin_actions) {
            $sub_navigation = new Navigation(
                _('Übersicht'),
                'dispatch.php/room_management/overview/index'
            );
            $overview_navigation->addSubNavigation('index', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Standorte'),
                'dispatch.php/room_management/overview/locations'
            );
            $overview_navigation->addSubNavigation('locations', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Gebäude'),
                'dispatch.php/room_management/overview/buildings'
            );
            $overview_navigation->addSubNavigation('buildings', $sub_navigation);
            $sub_navigation = new Navigation(
                _('Räume'),
                'dispatch.php/room_management/overview/rooms'
            );
            $overview_navigation->addSubNavigation('rooms', $sub_navigation);
        } else if ($user_has_rooms || $user_is_global_resource_user) {
            $sub_navigation = new Navigation(
                _('Meine Räume'),
                'dispatch.php/room_management/overview/rooms'
            );
            $overview_navigation->addSubNavigation('rooms', $sub_navigation);
        }

        if (Config::get()->RESOURCES_SHOW_PUBLIC_ROOM_PLANS) {
            $sub_navigation = new Navigation(
                _('Öffentlich zugängliche Raumpläne'),
                'dispatch.php/room_management/overview/public_booking_plans'
            );
            $overview_navigation->addSubNavigation('public_booking_plans', $sub_navigation);
        }

        //Room planning tab:
        if (RoomManager::userHasRooms($user, 'user', true) || $user_is_global_resource_user) {
            $sub_navigation = null;
            if (RoomManager::userHasRooms($user, 'autor', true) || $user_is_global_resource_autor) {
                if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
                    $sub_navigation = new Navigation(
                        _('Raumplanung'),
                        'dispatch.php/resources/room_request/overview'
                    );
                    $this->addSubNavigation('planning', $sub_navigation);

                    $sub_sub_nav = new Navigation(
                        _('Anfragenliste'),
                        'dispatch.php/resources/room_request/overview'
                    );
                    $sub_navigation->addSubNavigation('requests_overview', $sub_sub_nav);

                    $sub_sub_nav = new Navigation(
                        _('Anfragenplan'),
                        'dispatch.php/resources/room_request/planning'
                    );
                    $sub_navigation->addSubNavigation('requests_planning', $sub_sub_nav);
                } else {
                    $sub_navigation = new Navigation(
                        _('Raumplanung'),
                        'dispatch.php/resources/room_planning/booking_plan'
                    );
                    $this->addSubNavigation('planning', $sub_navigation);
                }
            } else {
                $sub_navigation = new Navigation(
                    _('Raumplanung'),
                    'dispatch.php/room_management/planning/booking_comments'
                );
                $this->addSubNavigation('planning', $sub_navigation);

                $sub_sub_nav = new Navigation(
                    _('Buchungen mit Kommentaren'),
                    'dispatch.php/room_management/planning/booking_comments'
                );
                $sub_navigation->addSubNavigation('booking_comments', $sub_sub_nav);
            }

            $sub_sub_nav = new Navigation(
                _('Belegungsplan'),
                'dispatch.php/resources/room_planning/booking_plan'
            );
            $sub_navigation->addSubNavigation('booking_plan', $sub_sub_nav);

            $sub_sub_nav = new Navigation(
                _('Semester-Belegungsplan'),
                'dispatch.php/resources/room_planning/semester_plan'
            );
            $sub_navigation->addSubNavigation('semester_plan', $sub_sub_nav);

            $sub_sub_nav = new Navigation(
                _('Raumgruppen-Belegungsplan'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/index',
                    [],
                    true
                )
            );
            $sub_navigation->addSubNavigation('index', $sub_sub_nav);

            $sub_sub_nav = new Navigation(
                _('Raumgruppen-Semester-Belegungsplan'),
                URLHelper::getURL(
                    'dispatch.php/room_management/planning/semester_plan',
                    [],
                    true
                )
            );
            $sub_navigation->addSubNavigation('semestergroup_plan', $sub_sub_nav);

            if (RoomManager::userHasRooms($user, 'autor', true)) {
                $sub_sub_nav = new Navigation(
                    _('Buchungen mit Kommentaren'),
                    'dispatch.php/room_management/planning/booking_comments'
                );
                $sub_navigation->addSubNavigation('booking_comments', $sub_sub_nav);
            }
        }

        if ($user_has_rooms || $user_is_global_resource_user) {
            //Resource tree tab:
            $sub_navigation = new Navigation(
                _('Struktur'),
                'dispatch.php/room_management/overview/structure'
            );
            $this->addSubNavigation('structure', $sub_navigation);
        }
        if ($show_global_admin_actions) {
            //Export tab:
            $export_navigation = new Navigation(
                _('Export'),
                'dispatch.php/resources/export/select_booking_sources'
            );
            $this->addSubNavigation('export', $export_navigation);

            $sub_navigation = new Navigation(
                _('Buchungen'),
                URLHelper::getURL(
                    'dispatch.php/resources/export/select_booking_sources',
                    [],
                    true
                )
            );
            $export_navigation->addSubNavigation(
                'select_booking_sources',
                $sub_navigation
            );
            $sub_nav = new Navigation(
                _('Belegungsplan-Seriendruck'),
                URLHelper::getURL(
                    'dispatch.php/resources/print/clipboard_rooms',
                    [],
                    true
                )
            );
            $export_navigation->addSubNavigation('print_clipboard_rooms', $sub_nav);


            //Mail tab:

            $messages_navigation = new Navigation(
                _('Rundmails'),
                'dispatch.php/resources/messages/index'
            );
            $this->addSubNavigation('messages', $messages_navigation);

            $sub_navigation = new Navigation(
                _('Rundmails'),
                'dispatch.php/resources/messages/index'
            );
            $messages_navigation->addSubNavigation('index', $sub_navigation);

            //Administration tab:
            $admin_navigation = new Navigation(
                _('Administration'),
                'dispatch.php/resources/admin/permissions/global'
            );
            $this->addSubNavigation('admin', $admin_navigation);

            $sub_navigation = new Navigation(
                _('Globale Berechtigungen verwalten'),
                'dispatch.php/resources/admin/permissions/global'
            );
            $admin_navigation->addSubNavigation('permissions', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Globale Sperren verwalten'),
                'dispatch.php/resources/admin/global_locks'
            );
            $admin_navigation->addSubNavigation('global_locks', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Berechtigungs-Übersicht'),
                'dispatch.php/resources/admin/user_permissions'
            );
            $admin_navigation->addSubNavigation('user_permissions', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Kategorien verwalten'),
                'dispatch.php/resources/admin/categories'
            );
            $admin_navigation->addSubNavigation('categories', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Eigenschaften verwalten'),
                'dispatch.php/resources/admin/properties'
            );
            $admin_navigation->addSubNavigation('properties', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Eigenschaftsgruppen verwalten'),
                'dispatch.php/resources/admin/property_groups'
            );
            $admin_navigation->addSubNavigation('property_groups', $sub_navigation);

            $sub_navigation = new Navigation(
                _('Teilbare Räume verwalten'),
                'dispatch.php/resources/admin/separable_rooms'
            );
            $admin_navigation->addSubNavigation('separable_rooms', $sub_navigation);

            if ($GLOBALS['perm']->have_perm('root')) {
                $sub_navigation = new Navigation(
                    _('Konfigurationsoptionen'),
                    'dispatch.php/resources/admin/configuration'
                );
                $admin_navigation->addSubNavigation('configuration', $sub_navigation);
            }
        }
    }
}
