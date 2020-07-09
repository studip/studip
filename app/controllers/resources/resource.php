<?php

/**
 * resource.php - contains Resources_ResourceController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_ResourceController contains general resource actions.
 */
class Resources_ResourceController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->edit_global_permissions = false;

        $this->resource_id_parameter = $args[0];

        $this->resources = [];
        $this->resource_ids = [];
        $this->clipboard_id = null;

        $this->no_reload = false;
        if (Request::submitted('no_reload')) {
            $this->no_reload = true;
        }

        if ($action == 'add') {
            //Noting to be done below this point.
            return;
        }

        if (strpos($args[0], 'clipboard_') !== false) {
            //A clipboard has been selected:
            //Get all resources from it:
            $this->clipboard_id = substr($args[0], 10);

            $clipboard = Clipboard::find($this->clipboard_id);
            if ($clipboard) {
                $this->resource_ids = $clipboard->getAllRangeIds(
                    ['Resource', 'Room', 'Building', 'Location']
                );

                $resources = Resource::findMany($this->resource_ids);
                if ($resources) {
                    foreach ($resources as $resource) {
                        $this->resources[] = $resource->getDerivedClassInstance();
                    }
                }
            }

            if (!$this->resources) {
                PageLayout::postError(
                    _('Es konnten keine Ressourcen gefunden werden!')
                );
                return;
            }
        } else {
            //$args[0] contains the ID of a resource.
            $resource = Resource::find($args[0]);

            if ($resource) {
                //We need the specialisations here to display the name correctly:
                try {
                    $this->resources = [$resource->getDerivedClassInstance()];
                } catch (Exception $e) {
                    //Use the base class as fallback.
                    $this->resources = [$resource];
                }
                $this->resource_ids = [$resource->id];
            } elseif ($args[0] == 'global') {
                $this->edit_global_permissions = true;
                $this->resource_ids = [$args[0]];
            }
            if (!$this->resources && ($args[0] != 'global')) {
                PageLayout::postError(
                    _('Die Ressource wurde nicht gefunden!')
                );
                return;
            }
        }
        if (!Request::isDialog()) {
            if (Navigation::hasItem('/resources/structure')) {
                Navigation::activateItem('/resources/structure');
            }
        }
    }


    protected function getUserAndCheckPermissions($permission_level = 'admin')
    {
        $this->current_user = User::findCurrent();

        $insufficient_permissions = true;
        if ($this->edit_global_permissions) {
            if (ResourceManager::userHasGlobalPermission($this->current_user, $permission_level)) {
                $insufficient_permissions = false;
            }
        } else {
            $for_loop_break = false;
            foreach ($this->resources as $resource) {
                if (!$resource->userHasPermission($this->current_user, $permission_level)) {
                    //The permissions are insufficient for at least one
                    //resource. No need to continue the permission check.
                    $for_loop_break = true;
                    break;
                }
            }
            if (!$for_loop_break) {
                //The for loop checked all resources and the user has
                //sufficient permissiosn for all resources:
                $insufficient_permissions = false;
            }
        }

        if ($insufficient_permissions) {
            //The permissions are insufficient for at least one resource.
            if (count($this->resources) == 1) {
                throw new AccessDeniedException(
                    sprintf(
                        _('%s: Unzureichende Berechtigungen für die gewählte Aktion!'),
                        $this->resources[0]->getFullName()
                    )
                );
            } else {
                throw new AccessDeniedException(
                    sprintf(
                        _('Unzureichende Berechtigungen für die gewählte Aktion!')
                    )
                );
            }
        }
    }

    protected function getPermissionUserSearch()
    {
        return QuickSearch::get(
            'searched_user_id', new StandardSearch('user_id'));
    }


    protected function getCourseSearch()
    {
        $all_semesters = Semester::getAll();
        $all_semester_ids = [];
        foreach ($all_semesters as $semester) {
            $all_semester_ids[] = $semester->id;
        }

        return QuickSearch::get(
            'course_id',
            new StandardSearch(
                'Seminar_id'
            )
        );
    }


    public function index_action($resource_id = null)
    {
        if (count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen oder Räume verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];

        PageLayout::setTitle(
            $this->resource->getFullName()
        );

        if ($GLOBALS['user']->id != 'nobody') {
            $current_user = User::findCurrent();

            if (!Request::isDialog()) {
                $sidebar = Sidebar::get();

                if (!($this->resource instanceof ResourceLabel)) {
                    $actions = new ActionsWidget();
                    $actions->addLink(
                        _('Belegungsplan'),
                        URLHelper::getURL(
                            'dispatch.php/resources/resource/booking_plan/' . $this->resource->id
                        ),
                        Icon::create('timetable')
                    );
                    $sidebar->addWidget($actions);

                }

                $tree_widget = new ResourceTreeWidget(
                    Location::findAll(),
                    null,
                    null
                );
                $tree_widget->setCurrentResource($this->resource);
                $sidebar->addWidget($tree_widget);
            }
        }
    }

    protected function addEditDeleteHandler($mode = 'add')
    {
        $this->resource = $this->resources[0];
        $user = User::findCurrent();

        $this->show_form = false;
        $this->mode = $mode;

        if (($mode == 'edit') || ($mode == 'delete')) {
            if (!$this->resource) {
                PageLayout::postError(
                    _('Die angegebene Ressource wurde nicht gefunden!')
                );
                return;
            }

            if ((($mode == 'edit') && !$this->resource->userHasPermission($user, 'autor'))
                || (($mode == 'delete') && !$this->resource->userHasPermission($user, 'admin'))) {
                throw new AccessDeniedException();
            }
        }

        if (($mode == 'add') && !ResourceManager::userHasGlobalPermission($user, 'admin')) {
            throw new AccessDeniedException();
        }

        //Get a quick search for the parent resource selection:
        $resource_search = new ResourceSearch();

        $this->parent_search = new QuickSearch(
            'parent_id',
            $resource_search
        );
        if (($mode == 'edit') && $this->resource->parent) {
            $this->parent_search->defaultValue(
                $this->resource->parent_id,
                $this->resource->parent->name
            );
        }

        //Get all properties of the resource:
        $this->property_data = [];
        $this->grouped_defined_properties = [];
        if ($mode == 'edit') {
            $this->grouped_defined_properties =
                $this->resource->category->getGroupedPropertyDefinitions();
            foreach ($this->grouped_defined_properties as $group => $properties) {
                foreach ($properties as $property) {
                    $this->property_data[$property->id] =
                        $this->resource->getProperty($property->name);
                }
            }
        } elseif ($mode == 'add') {
            $this->grouped_defined_properties =
                $this->category->getGroupedPropertyDefinitions();
            foreach ($this->grouped_defined_properties as $group => $properties) {
                foreach ($properties as $property) {
                    //Set the property state to an empty string
                    //for all properties as a default value:
                    $this->property_data[$property->id] = '';
                }
            }
        }

        $this->show_form = true;
        if (Request::submitted('confirmed')) {
            CSRFProtection::verifyUnsafeRequest();
            if (($mode == 'add') || ($mode == 'edit')) {
                //Process submitted form:
                $this->parent_id = $this->resource->parent_id;
                if ($mode == 'add') {
                    $this->category_id = Request::get('category_id');
                    $this->parent_id = Request::get('parent_id', '');
                }
                $this->name = Request::get('name');
                $this->description = Request::get('description');
                $this->property_data = Request::getArray('properties');
                $this->sort_position = Request::get('sort_position');

                if ($mode == 'add') {
                    $category_class_name = ResourceCategory::getClassNameById(
                        $this->category_id
                    );
                    if ((!$category_class_name)
                        || !is_a($category_class_name, 'Resource', true)) {
                        PageLayout::postError(
                            _('Die gewählte Kategorie ist für Ressourcen nicht geeignet!')
                        );
                        return;
                    }
                }

                if (!$this->name) {
                    PageLayout::postError(
                        _('Der Name des Ressource ist leer!')
                    );
                    return;
                }

                if ($mode == 'add') {
                    $this->resource = new Resource();
                }

                //store the resource object:
                $this->resource->parent_id = $this->parent_id;
                if ($mode == 'add') {
                    $this->resource->category_id = $this->category_id;
                }
                $this->resource->name = $this->name;
                $this->resource->description = $this->description;

                if ($GLOBALS['perm']->have_perm('root')) {
                    $this->resource->sort_position = $this->sort_position;
                }
                if ($this->resource->isDirty()) {
                    $successfully_stored = $this->resource->store();
                } else {
                    $successfully_stored = true;
                }

                //Now we can store the resource's properties, if the permissions
                //are high enough:
                $unchanged_properties = $this->resource->setPropertiesById(
                    $this->property_data,
                    $user
                );
                array_walk($unchanged_properties, function(&$item){$item = htmlReady($item);});

                if ($successfully_stored && !$unchanged_properties) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Die Ressource wurde gespeichert!')
                    );
                } elseif ($successfully_stored) {
                    $this->show_form = false;
                    if (count($unchanged_properties) == 1) {
                        PageLayout::postWarning(
                            sprintf(
                                _('Die Ressource wurde gespeichert, aber die Eigenschaft %s konnte wegen fehlender Berechtigungen nicht gespeichert werden!'),
                                htmlReady($unchanged_properties[0])
                            )
                        );
                    } else {
                        PageLayout::postWarning(
                            _('Die Ressource wurde gespeichert, aber die folgenden Eigenschaften konnten wegen fehlender Berechtigungen nicht gespeichert werden:'),
                            $unchanged_properties
                        );
                    }
                } else {
                    PageLayout::postError(
                        _('Fehler beim Speichern der Ressource!')
                    );
                }
            } elseif ($mode == 'delete') {
                if ($this->resource->delete()) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Die Ressource wurde gelöscht!')
                    );
                } else {
                    PageLayout::postError(
                        _('Fehler beim Löschen der Ressource!')
                    );
                }
            }
        } else {
            //For add mode $this->category_id is set directly in add_action!
            if (($mode == 'edit') || ($mode == 'delete')) {
                //Show form with current data:
                $this->parent_id = $this->resource->parent_id;
                $this->category_id = $this->resource->category_id;
                $this->name = $this->resource->name;
                $this->description = $this->resource->description;
                $this->sort_position = $this->resource->sort_position;
            }
        }
    }


    public function add_action()
    {
        $this->category_selected = false;

        $category_id = Request::get('category_id');
        $this->category = ResourceCategory::find($category_id);
        if ($this->category instanceof ResourceCategory) {
            $this->category_selected = true;
            $this->addEditDeleteHandler('add');
        } else {
            PageLayout::postError(
                _('Die gewählte Ressourcenkategorie wurde nicht gefunden!')
            );
        }

        PageLayout::setTitle(
            sprintf(
                _('Kategorie %s: Ressource hinzufügen'),
                $this->category->name
            )
        );
    }


    public function edit_action($resource_id = null)
    {
        $this->addEditDeleteHandler('edit');

        PageLayout::setTitle(
            sprintf(
                _('%s: bearbeiten'),
                $this->resource->getFullname()
            )
        );
    }


    public function delete_action($resource_id = null)
    {
        $this->addEditDeleteHandler('delete');

        PageLayout::setTitle(
            sprintf(
                _('%s: löschen'),
                $this->resource->getFullname()
            )
        );
    }


    public function booking_plan_action($resource_id = null)
    {
        if (count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen oder Räume verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];

        $current_user = User::findCurrent();

        //The booking plan is visible when the user-ID is not 'nobody'
        //and the user has at least 'user' permissions on the resource.
        //The booking plan is also visible when the resource is a room
        //and its booking plan is publicly available.
        $booking_plan_is_visible = false;
        if ($GLOBALS['user']->id != 'nobody') {
            $booking_plan_is_visible = $this->resource->userHasPermission(
                $current_user,
                'user'
            );
        }
        if ($booking_plan_is_visible === false) {
            $booking_plan_is_visible = (
                ($this->resource instanceof Room)
                and $this->resource->booking_plan_is_public
            );
        }
        if (!$booking_plan_is_visible) {
            throw new AccessDeniedException(
                _('Der Belegungsplan ist für Sie nicht zugänglich!')
            );
        }

        PageLayout::setTitle(
            sprintf(
                _('Belegungsplan: %s'),
                $this->resource->getFullName()
            )
        );

        $week_timestamp = Request::get('timestamp');
        $this->date = new DateTime();
        if ($week_timestamp) {
            $this->date->setTimestamp($week_timestamp);
        }

        //Build sidebar:
        $sidebar = Sidebar::get();

        $views = new ViewsWidget();
        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            if ($this->resource->userHasPermission($current_user, 'user')) {
                $views->addLink(
                    _('Standard Zeitfenster'),
                    URLHelper::getURL(
                        'dispatch.php/resources/resource/booking_plan/' . $this->resource->id,
                        [
                            'defaultDate' => Request::get('defaultDate', date('Y-m-d'))
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-std_view']
                )->setActive(!Request::get('allday'));

                $views->addLink(
                    _('Ganztägiges Zeitfenster'),
                    URLHelper::getURL(
                        'dispatch.php/resources/resource/booking_plan/' . $this->resource->id,
                        [
                            'allday' => true,
                            'defaultDate' => Request::get('defaultDate', date('Y-m-d'))
                        ]
                    ),
                    null,
                    ['class' => 'booking-plan-allday_view']
                )->setActive(Request::get('allday'));
            }
        }
        $sidebar->addWidget($views);

        $actions = new ActionsWidget();
        if ($GLOBALS['user']->id and ($GLOBALS['user']->id != 'nobody')) {
            if ($this->resource->userHasPermission($current_user, 'user')) {
                $actions->addLink(
                    _('Druckansicht'),
                    'javascript:void(window.print());',
                    Icon::create('print')
                );
                $actions->addLink(
                    _('Individuelle Druckansicht'),
                    URLHelper::getURL(
                        'dispatch.php/resources/print/individual_booking_plan/' . $this->resource->id,
                        [
                            'timestamp' => $week_timestamp
                        ]
                    ),
                    Icon::create('print'),
                    [
                        'target' => '_blank'
                    ]
                );
                $actions->addLink(
                    _('Als Tabelle exportieren'),
                    URLHelper::getURL(
                        'dispatch.php/resources/export/booking_plan/' . $this->resource->id,
                        [
                            'timestamp' => $week_timestamp,
                            'export_type' => 'csv'
                        ]
                    ),
                    Icon::create('file-excel'),
                    [
                        'target' => '_blank'
                    ]
                );
            }
        }
        if (($this->resource instanceof Room) and $this->resource->booking_plan_is_public) {
            $actions->addLink(
                _('QR-Code anzeigen'),
                $this->resource->getActionURL('booking_plan'),
                Icon::create('download'),
                [
                    'data-qr-code' => '',
                    'data-qr-code-print' => '1'
                ]
            );
        }

        $sidebar->addWidget($actions);

        $booking_colour = ColourValue::find('Resources.BookingPlan.Booking.Bg');
        $lock_colour = ColourValue::find('Resources.BookingPlan.Lock.Bg');
        $preparation_colour = ColourValue::find('Resources.BookingPlan.PreparationTime.Bg');
        $reservation_colour = ColourValue::find('Resources.BookingPlan.Reservation.Bg');
        $this->table_keys = [
            [
                'colour' => $booking_colour->__toString(),
                'text' => _('Buchungen')
            ],
            [
                'colour' => $lock_colour->__toString(),
                'text' => _('Sperrbuchungen')
            ],
            [
                'colour' => $preparation_colour->__toString(),
                'text' => _('Rüstzeiten')
            ],
            [
                'colour' => $reservation_colour->__toString(),
                'text' => _('Reservierungen')
            ]
        ];
    }


    public function permissions_action($resource_id = null)
    {
        PageLayout::setTitle(
            _('Berechtigungen verwalten')
        );

        if (is_array($this->resources) && count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];

        $this->getUserAndCheckPermissions('admin');

        if ($this->resource_id == 'global') {
            PageLayout::setTitle(
                _('Globale Berechtigungen verwalten')
            );
        } else {
            $this->resource_id = $this->resource->id;
            PageLayout::setTitle(
                sprintf(
                    _('%s: Berechtigungen verwalten'),
                    (
                        $this->resource instanceof Resource
                        ? $this->resource->getFullName()
                        : _('unbekannt')
                    )
                )
            );
        }

        //The user_id parameter is only needed in
        //the single user permission mode.
        $this->single_user_mode = false;
        $this->user = User::find(Request::get('user_id'));
        if ($this->user) {
            $this->single_user_mode = true;
        }

        if (!$this->single_user_mode) {
            //Setup the user search:
            $this->user_search = $this->getPermissionUserSearch();
            $this->user_search->fireJSFunctionOnSelect(
                "function (user_id) {
                     var table = jQuery('#PermissionList')[0];
                     STUDIP.Resources.addUserToPermissionList(user_id, table);
                }"
            );

            if (!$this->edit_global_permissions &&
                (ResourceManager::userHasGlobalPermission($this->current_user, 'admin') ||
                 $GLOBALS['perm']->have_perm('root'))) {
                //Setup the course search:
                $this->course_search = $this->getCourseSearch();
                $this->course_search->fireJSFunctionOnSelect(
                    "function (course_id) {
                         var table = jQuery('#PermissionList')[0];
                         STUDIP.Resources.addCourseUsersToPermissionList(course_id, table);
                    }"
                );
            }
        }
        $this->table_id = 'PermissionList';

        if (Request::submitted('bulk_delete')) {
            CSRFProtection::verifyUnsafeRequest();

            $resource_permissions = Request::getArray('selected_permissions');
            $deleted_c = ResourcePermission::deleteBySql(
                'resource_id = :resource_id AND user_id IN ( :user_ids )',
                [
                    'resource_id' => $this->resource_id,
                    'user_ids' => $resource_permissions
                ]
            );

            PageLayout::postSuccess(
                sprintf(
                    ngettext(
                        '%d Berechtigung wurde gelöscht!',
                        '%d Berechtigungen wurden gelöscht!',
                        $deleted_c
                    ),
                    $deleted_c
                )
            );
        } else if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            //Get the list of permissions for the user-IDs in the list:
            $user_permissions = Request::getArray('permissions');

            $processed_permissions = 0;
            $deleted_permissions = 0;
            $errors = [];
            $user_ids = [];
            //We must check for each permission if the user exists and if
            //a permission object already exists. If a permission object
            //exists we must update it.
            foreach (array_keys($user_permissions['user_id']) as $key) {
                $user_id = $user_permissions['user_id'][$key];
                $permission_level = $user_permissions['level'][$key];

                $user = User::find($user_id);
                if (!$user) {
                    //We don't have to do anything if the user doesn't exist.
                    continue;
                }
                if (!in_array($permission_level, ['user', 'autor', 'tutor', 'admin'])) {
                    //If the permission level is invalid we don't have to do
                    //anything either.
                    continue;
                }

                $user_ids[] = $user_id;

                $permission_object = ResourcePermission::findOneBySql(
                    'resource_id = :resource_id
                    AND
                    user_id = :user_id',
                    [
                        'resource_id' => $this->resource_id,
                        'user_id' => $user_id
                    ]
                );

                if (!$permission_object) {
                    //Create a new permission object:
                    $permission_object = new ResourcePermission();
                    $permission_object->resource_id = $this->resource_id;
                    $permission_object->user_id = $user_id;
                }

                $permission_object->perms = $permission_level;

                if ($permission_object->isDirty()) {
                    $processed_permissions++;
                    if (!$permission_object->store()) {
                        if ($this->edit_global_permissions) {
                            $errors[] = sprintf(
                                _(
                                    'Die globalen Berechtigungen von %s konnten nicht gespeichert werden!'
                                ),
                                htmlReady($user->getFullName())
                            );
                        } else {
                            $errors[] = sprintf(
                                _(
                                    'Die Berechtigungen von %1$s an der Ressource %2$s konnten nicht gespeichert werden!'
                                ),
                                htmlReady($user->getFullName()),
                                htmlReady($this->resource->name)
                            );
                        }
                    }
                }
            }

            if ($this->single_user_mode) {
                if (!in_array($this->user->id, $user_ids) || empty($user_ids)) {
                    //The user is not in the array of processed user permissions
                    //or no user permissions have been processed which means that
                    //no user permissions were submitted at all.Both cases mean
                    //that the user's permissions have been revoked.
                    ResourcePermission::deleteBySql(
                        'resource_id = :resource_id AND user_id = :user_id',
                        [
                            'resource_id' => $this->resource_id,
                            'user_id' => $this->user->id
                        ]
                    );
                }
            } else {
                //We must remove all permissions where the resource_id is given
                //and where the user_id is not in the $user_ids array which has been
                //filled above.
                if ($user_ids) {
                    $deleted_permissions = ResourcePermission::deleteBySql(
                        'resource_id = :resource_id
                        AND
                        user_id NOT IN ( :user_ids )',
                        [
                            'resource_id' => $this->resource_id,
                            'user_ids' => $user_ids
                        ]
                    );
                } else {
                    //In case no user_ids are collected above all permissions
                    //for the resource have to be deleted:
                    $deleted_permissions = ResourcePermission::deleteBySQL(
                        'resource_id = :resource_id',
                        [
                            'resource_id' => $this->resource_id,
                        ]
                    );
                }
            }

            if (count($errors)) {
                PageLayout::postError(
                    _('Die folgenden Fehler traten auf beim Speichern der Berechtigungen:'),
                    $errors
                );
            } elseif (($processed_permissions > 0) || ($deleted_permissions > 0)) {
                PageLayout::postSuccess(
                    _('Die Berechtigungen wurden gespeichert!')
                );
            }
        }

        if ($this->single_user_mode) {
            //Load permissions of the specified user:
            $this->permissions = ResourcePermission::findBySql(
                "resource_id = :resource_id
                AND user_id = :user_id",
                [
                    'resource_id' => $this->resource_id,
                    'user_id' => $this->user->id
                ]
            );
        } else {
            //Load existing permissions:
            //Sort the permissions by the user's name:
            $this->permissions = ResourcePermission::findBySql(
                "INNER JOIN auth_user_md5 USING (user_id)
                WHERE
                resource_id = :resource_id
                ORDER BY
                nachname ASC, vorname ASC",
                [
                    'resource_id' => $this->resource_id
                ]
            );
        }
    }


    public function temporary_permissions_action($resource_id = null)
    {
        PageLayout::setTitle(
            _('Temporäre Berechtigungen verwalten')
        );

        if (count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];
        $this->resource_id = $this->resource->id;

        $this->getUserAndCheckPermissions('admin');

        PageLayout::setTitle(
            sprintf(
                _('%s: Temporäre Berechtigungen verwalten'),
                $this->resource->getFullName()
            )
        );

        //The user_id parameter is only needed in
        //the single user permission mode.
        $this->single_user_mode = false;
        $this->user = User::find(Request::get('user_id'));
        if ($this->user) {
            $this->single_user_mode = true;
        }

        if (!$this->single_user_mode) {
            //Setup the user search:
            $this->user_search = $this->getPermissionUserSearch();
            $this->user_search->fireJSFunctionOnSelect(
                "function (user_id) {
                     var table = jQuery('#TemporaryPermissionList')[0];
                     STUDIP.Resources.addUserToPermissionList(user_id, table);
                }"
            );

            if (ResourceManager::userHasGlobalPermission($this->current_user, 'admin') ||
                $GLOBALS['perm']->have_perm('root')) {
                //Setup the course search:
                $this->course_search = $this->getCourseSearch();
                $this->course_search->fireJSFunctionOnSelect(
                    "function (course_id) {
                         var table = jQuery('#TemporaryPermissionList')[0];
                         STUDIP.Resources.addCourseUsersToPermissionList(course_id, table);
                    }"
                );
            }
        }

        $this->bulk_begin = new DateTime();
        $this->bulk_begin->setTime(
            intval($this->bulk_begin->format('H')),
            0,
            0
        );
        $this->bulk_end = new DateTime();
        $this->bulk_end = $this->bulk_end->add(
            new DateInterval('P1M')
        );
        $this->bulk_end->setTime(
            intval($this->bulk_end->format('H')),
            0,
            0
        );

        if (Request::submitted('bulk_save')) {
            CSRFProtection::verifyUnsafeRequest();

            $permission_ids = Request::getArray('selected_permission_ids');
            $this->bulk_begin = Request::getDateTime(
                'bulk_begin_date',
                'd.m.Y',
                'bulk_begin_time',
                'H:i'
            );
            $this->bulk_end = Request::getDateTime(
                'bulk_end_date',
                'd.m.Y',
                'bulk_end_time',
                'H:i'
            );

            $valid = true;

            if ($this->bulk_begin === false) {
                PageLayout::postError(
                    _('Der Startzeitpunkt ist in einem ungültigen Format!')
                );
                $valid = false;
            }

            if ($this->bulk_end === false) {
                PageLayout::postError(
                    _('Der Endzeitpunkt ist in einem ungültigen Format!')
                );
                $valid = false;
            }

            if ($this->bulk_begin >= $this->bulk_end) {
                PageLayout::postError(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
                $valid = false;
            }

            if ($valid) {
                $permissions = ResourceTemporaryPermission::findBySql(
                    'id IN ( :permission_ids ) AND resource_id = :resource_id',
                    [
                        'permission_ids' => $permission_ids,
                        'resource_id' => $this->resource_id
                    ]
                );

                $errors = 0;
                $processed_permissions = 0;
                $amount = count($permissions);
                foreach ($permissions as $permission) {
                    $permission->begin = $this->bulk_begin->getTimestamp();
                    $permission->end = $this->bulk_end->getTimestamp();
                    if ($permission->isDirty()) {
                        $processed_permissions++;
                        $result = $permission->store();
                    }
                    if (!$result) {
                        $errors++;
                    }
                }

                if (($errors == $amount) && ($amount > 0)) {
                    PageLayout::postError(
                        _('Fehler beim Speichern der Berechtigungen!')
                    );
                } elseif ($errors) {
                    PageLayout::postWarning(
                        _('Es konnten nicht alle Berechtigungen gespeichert werden!')
                    );
                } elseif ($processed_permissions > 0) {
                    PageLayout::postSuccess(
                        _('Die Änderung der Zeitbereiche der Berechtigungen wurden gespeichert!')
                    );
                }
            }
        } else if (Request::submitted('bulk_delete')) {
            CSRFProtection::verifyUnsafeRequest();

            $permission_ids = Request::getArray('selected_permission_ids');
            $deleted_c = ResourceTemporaryPermission::deleteBySql(
                'resource_id = :resource_id AND id IN ( :ids )',
                [
                    'resource_id' => $this->resource_id,
                    'ids' => $permission_ids
                ]
            );

            PageLayout::postSuccess(
                sprintf(
                    ngettext(
                        '%d Berechtigung wurde gelöscht!',
                        '%d Berechtigungen wurden gelöscht!',
                        $deleted_c
                    ),
                    $deleted_c
                )
            );
        } else if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            //Get the list of temporary permissions for the user-IDs in the list:
            $user_permissions = Request::getArray('permissions');
            //var_dump($user_permissions);die();

            $processed_permissions = 0;
            $errors = [];
            $user_ids = [];
            //We must check for each permission if the user exists and if
            //a permission object already exists. If a permission object
            //exists we must update it.
            foreach (array_keys($user_permissions['user_id']) as $key) {
                $permission_id = $user_permissions['permission_id'][$key];
                $user_id = $user_permissions['user_id'][$key];
                $permission_level = $user_permissions['level'][$key];
                $permission_begin_date = $user_permissions['begin_date'][$key];
                $permission_begin_time = $user_permissions['begin_time'][$key];
                $permission_end_date = $user_permissions['end_date'][$key];
                $permission_end_time = $user_permissions['end_time'][$key];

                $user = User::find($user_id);
                if (!$user) {
                    //We don't have to do anything if the user doesn't exist.
                    continue;
                }
                if ($user_permissions['user_id'][$key] == 'USERID') {
                    //It is just the entry from the JavaScript template.
                    continue;
                }
                if (!in_array($permission_level, ['user', 'autor', 'tutor', 'admin'])) {
                    //If the permission level is invalid we don't have to do
                    //anything either.
                    continue;
                }

                $user_ids[] = $user_id;

                //Generate DateTime objects from the date and time strings:
                $begin = new DateTime();
                $time_zone = $begin->getTimezone();
                $begin = DateTime::createFromFormat(
                    'd.m.Y H:i',
                    $permission_begin_date . ' ' . $permission_begin_time,
                    $time_zone
                );
                if ($begin === false) {
                    $begin = DateTime::createFromFormat(
                        'Y-m-d H:i',
                        $permission_begin_date . ' ' . $permission_begin_time,
                        $time_zone
                    );
                }
                $end = DateTime::createFromFormat(
                    'd.m.Y H:i',
                    $permission_end_date . ' ' . $permission_end_time,
                    $time_zone
                );
                if ($end === false) {
                    $end = DateTime::createFromFormat(
                        'Y-m-d H:i',
                        $permission_end_date . ' ' . $permission_end_time,
                        $time_zone
                    );
                }

                $permission_object = null;
                if ($permission_id) {
                    //It is an existing permission.
                    //Existing permission objects can only be determined
                    //by their permission-ID or if the resource-ID,
                    //user-ID and the begin and end timestamps match.
                    //In all other cases we must create a new permission object.
                    $permission_object = ResourceTemporaryPermission::findOneBySql(
                        'id = :permission_id
                        OR
                        (
                            resource_id = :resource_id
                            AND
                            user_id = :user_id
                            AND
                            begin = :begin
                            AND
                            end = :end
                        )',
                        [
                            'permission_id' => $permission_id,
                            'resource_id' => $this->resource_id,
                            'user_id' => $user_id,
                            'begin' => $begin->getTimestamp(),
                            'end' => $end->getTimestamp()
                        ]
                    );
                }

                if (!$permission_object) {
                    //Create a new permission object:
                    $permission_object = new ResourceTemporaryPermission();
                    $permission_object->resource_id = $this->resource_id;
                    $permission_object->user_id = $user_id;
                }

                $permission_object->perms = $permission_level;
                $permission_object->begin = $begin->getTimestamp();
                $permission_object->end = $end->getTimestamp();

                if ($permission_object->isDirty()) {
                    $processed_permissions++;
                    if (!$permission_object->store()) {
                        if ($this->edit_global_permissions) {
                            $errors[] = sprintf(
                                _(
                                    'Die globalen Berechtigungen von %s konnten nicht gespeichert werden!'
                                ),
                                htmlReady($user->getFullName())
                            );
                        } else {
                            $errors[] = sprintf(
                                _(
                                    'Die Berechtigungen von %1$s an der Ressource %2$s konnten nicht gespeichert werden!'
                                ),
                                htmlReady($user->getFullName()),
                                htmlReady($this->resource->name)
                            );
                        }
                    }
                }
            }

            //Now we must remove all permissions where the resource_id is given
            //and where the user_id is not in the $user_ids array which has been
            //filled above.
            if ($user_ids) {
                $deleted_permissions = ResourceTemporaryPermission::deleteBySql(
                    'resource_id = :resource_id
                    AND
                    user_id NOT IN ( :user_ids )',
                    [
                        'resource_id' => $this->resource_id,
                        'user_ids' => $user_ids
                    ]
                );
            } else {
                //In case no user_ids are collected above all permissions
                //for the resource have to be deleted:
                $deleted_permissions = ResourceTemporaryPermission::deleteBySQL(
                    'resource_id = :resource_id',
                    [
                        'resource_id' => $this->resource_id,
                    ]
                );
            }

            if (count($errors)) {
                PageLayout::postError(
                    _('Die folgenden Fehler traten auf beim Speichern der Berechtigungen:'),
                    $errors
                );
            } elseif (($processed_permissions > 0) || ($deleted_permissions > 0)) {
                PageLayout::postSuccess(
                    _('Die Berechtigungen wurden gespeichert!')
                );
            }
        }

        if ($this->single_user_mode) {
            //Load permissions of the specified user:
            $this->temp_permissions = ResourceTemporaryPermission::findBySql(
                "resource_id = :resource_id
                AND user_id = :user_id",
                [
                    'resource_id' => $this->resource_id,
                    'user_id' => $this->user->id
                ]
            );
        } else {
            //Load existing permissions:
            //Sort the permissions by the user's name:
            $this->temp_permissions = ResourceTemporaryPermission::findBySql(
                "INNER JOIN auth_user_md5 USING (user_id)
                WHERE
                resource_id = :resource_id
                ORDER BY
                nachname ASC, vorname ASC",
                [
                    'resource_id' => $this->resource_id
                ]
            );
        }
    }


    public function special_assign_action($resource_id = null)
    {
        if (count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];

        $this->getUserAndCheckPermissions('admin');

        $this->show_form = false;

        $this->booking_type = Request::int('type');
        if (!in_array($this->booking_type, [1, 2])) {
            PageLayout::postError(
                _('Es wurde kein passender Buchungstyp angegeben!')
            );
            return;
        }

        if ($this->booking_type == 1) {
            PageLayout::setTitle(
                sprintf(
                    _('%s: Reservierung erstellen'),
                    $this->resource->getFullName()
                )
            );
        } elseif ($this->booking_type == 2) {
            PageLayout::setTitle(
                sprintf(
                    _('%s: Sperrbuchung erstellen'),
                    $this->resource->getFullName()
                )
            );
        }

        //Set default form data:
        $this->begin = new DateTime();
        $this->begin->setTime(date('H') + 1, 0, 0);
        $this->end = clone $this->begin;
        $this->end->add(new DateInterval('P1D'));
        $this->show_form = true;

        if (Request::submitted('save')) {
            $begin_date = Request::get('begin_date');
            $begin_time = Request::get('begin_time');
            $end_date = Request::get('end_date');
            $end_time = Request::get('end_time');

            //$begin and $end are in the format Y-m-d H:i
            $begin_date = explode('-', $begin_date);
            $begin_time = explode(':', $begin_time);
            $this->begin = new DateTime();
            $this->begin->setDate(
                intval($begin_date[0]),
                intval($begin_date[1]),
                intval($begin_date[2])
            );
            $this->begin->setTime(
                intval($begin_time[0]),
                intval($begin_time[1]),
                0
            );

            $end_date = explode('-', $end_date);
            $end_time = explode(':', $end_time);
            $this->end = new DateTime();
            $this->end->setDate(
                intval($end_date[0]),
                intval($end_date[1]),
                intval($end_date[2])
            );
            $this->end->setTime(
                intval($end_time[0]),
                intval($end_time[1]),
                0
            );

            try {
                $this->resource->createSimpleBooking(
                    $this->current_user,
                    $this->begin,
                    $this->end,
                    $this->booking_type
                );
            } catch (ResourceUnlockableException $e) {
                PageLayout::postError(
                    $e->getMessage()
                );
                $this->show_form = true;
                return;
            } catch (ResourceUnavailableException $e) {
                PageLayout::postError(
                    $e->getMessage()
                );
                $this->show_form = true;
                return;
            }
            $this->show_form = false;
            PageLayout::postSuccess(
                sprintf(
                    _('%1$s: Die Sperrbuchung für den Zeitraum von %2$s bis %3$s wurde gespeichert!'),
                    htmlReady($this->resource->getFullName()),
                    $this->begin->format('d.m.Y H:i'),
                    $this->end->format('d.m.Y H:i')
                )
            );
        }
    }


    public function delete_bookings_action($resource_id = null)
    {
        //Set the next week as default time range:
        $this->begin = new DateTime();
        $this->begin->setTimestamp(strtotime('next week'));
        $this->end = clone $this->begin;
        $this->end = $this->end->add(new DateInterval('P7DT23H59M59S'));

        $this->show_form = true;

        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->begin = Request::getDateTime(
                'begin_date',
                'd.m.Y',
                'begin_time',
                'H:i'
            );
            $this->end = Request::getDateTime(
                'end_date',
                'd.m.Y',
                'end_time',
                'H:i'
            );

            if ($this->begin >= $this->end) {
                PageLayout::postError(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
                return;
            }

            //Collect all resource-IDs:
            $resource_ids = [];
            foreach ($this->resources as $resource) {
                $resource_ids[] = $resource->id;
            }

            //Delete all bookings in that time range
            //which are not assigned to a course:
            $deleted = ResourceBookingInterval::deleteBySql(
                'INNER JOIN resource_bookings
                ON resource_bookings.id = resource_booking_intervals.booking_id
                INNER JOIN auth_user_md5
                ON resource_bookings.range_id = auth_user_md5.user_id
                WHERE
                resource_booking_intervals.resource_id IN ( :resource_ids )
                AND
                resource_booking_intervals.begin >= :begin
                AND
                resource_booking_intervals.end <= :end',
                [
                    'resource_ids' => $resource_ids,
                    'begin' => $this->begin->getTimestamp(),
                    'end' => $this->end->getTimestamp()
                ]
            );

            if ($deleted !== false) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    sprintf(
                        ngettext(
                            'Eine Buchung wurde im Zeitbereich von %1$s, %2$s Uhr bis zum %3$s, %4$s Uhr gelöscht!',
                            '%5$d Buchungen wurde im Zeitbereich von %1$s, %2$s Uhr bis zum %3$s, %4$s Uhr gelöscht!',
                            $deleted
                        ),
                        $this->begin->format('d.m.Y'),
                        $this->begin->format('H:i'),
                        $this->end->format('d.m.Y'),
                        $this->end->format('H:i'),
                        $deleted
                    )
                );
            } else {
                PageLayout::postError(
                    sprintf(
                        _('Die Buchungen im Zeitbereich von %1$s, %2$s Uhr bis %3$s, %4$s Uhr konnten nicht gelöscht werden!'),
                        $this->begin->format('d.m.Y'),
                        $this->begin->format('H:i'),
                        $this->end->format('d.m.Y'),
                        $this->end->format('H:i')
                    )
                );
            }
        }
    }


    public function files_action($resource_id = null, $folder_id = null)
    {
        if (count($this->resources) > 1) {
            PageLayout::postError(
                _('Diese Aktion kann nur für einzelne Ressourcen verwendet werden!')
            );
            return;
        }
        $this->resource = $this->resources[0];

        $this->getUserAndCheckPermissions('user');

        //Set the page title:

        PageLayout::setTitle(
            $this->resource->getFullName() . ': ' . _('Dateien')
        );

        //Get the resource's folder:

        if ($folder_id) {
            $folder = Folder::find($folder_id);
            if ($folder) {
                $this->folder = $folder->getTypedFolder();
            }
        } else {
            $this->folder = $this->resource->getFolder();
        }

        if (!$this->folder) {
            PageLayout::postError(
                sprintf(
                    _('Der Dateiordner zur Ressource %1$s konnte nicht gefunden werden!'),
                    htmlReady($this->resource->name)
                )
            );
            return;
        }

        if (!$this->folder->isReadable($this->current_user->id)) {
            throw new AccessDeniedException(
                _('Sie sind nicht berechtigt, den Inhalt dieses Ordners zu sehen!')
            );
        }

        //Build the sidebar:

        $sidebar = Sidebar::get();
        $actions = new ActionsWidget();
        $sidebar->addWidget($actions);

        if ($this->folder->isWritable($this->current_user->id)) {
            $actions->addLink(
                _('Dokument hinzufügen'),
                '#',
                Icon::create('file+add'),
                [
                    'onclick' => 'STUDIP.Files.openAddFilesWindow(); return false;'
                ]
            );
            //Add the file upload widget:
            $upload_area = new LinksWidget();
            $upload_area->setTitle(_('Dateien hinzufügen'));
            $upload_area->addElement(
                new WidgetElement(
                    $this->render_template_as_string('files/upload-drag-and-drop')
                )
            );
            $sidebar->addWidget($upload_area);
        }

        //Get all files of the folder:

        $this->folder_files = $this->folder->getFiles();

        //Set the last visit date to the current timestamp:
        $this->last_visitdate = time();
    }
}
