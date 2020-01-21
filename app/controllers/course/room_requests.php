<?php
# Lifter010: TODO
/**
 * room_requests.php - administration of room requests
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */
class Course_RoomRequestsController extends AuthenticatedController
{
    /**
     * Common tasks for all actions
     *
     * @param String $action Called action
     * @param Array  $args   Possible arguments
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        $this->current_action = $action;

        parent::before_filter($action, $args);

        $course_id = $args[0];

        $this->course_id = Request::option('cid', $course_id);
        $pagetitle = '';
        //Navigation in der Veranstaltung:
        if (Navigation::hasItem('/course/admin/room_requests')) {
            Navigation::activateItem('/course/admin/room_requests');
        }

        if (!get_object_type($this->course_id, ['sem']) ||
            SeminarCategories::GetBySeminarId($this->course_id)->studygroup_mode ||
            !$perm->have_studip_perm("tutor", $this->course_id)
        ) {
            throw new Trails_Exception(400);
        }

        PageLayout::setHelpKeyword('Basis.VeranstaltungenVerwaltenAendernVonZeitenUndTerminen');
        $pagetitle .= Course::find($this->course_id)->getFullname() . ' - ';
        $pagetitle .= _('Verwalten von Raumanfrage');
        PageLayout::setTitle($pagetitle);
    }

    /**
     * Display the list of room requests
     */
    public function index_action()
    {
        $this->url_params = [];
        if (Request::get('origin') !== null) {
            $this->url_params['origin'] = Request::get('origin');
        }

        $this->room_requests = RoomRequest::findBySQL(
            'course_id = :course_id
            ORDER BY course_id, metadate_id, termin_id',
            [
                'course_id' => $this->course_id
            ]
        );
        $this->request_id = Request::option('request_id');

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Raumanfrage erstellen'),
            $this->url_for('course/room_requests/new'),
            Icon::create('add', 'clickable')
        );
        Sidebar::get()->addWidget($actions);

        if ($GLOBALS['perm']->have_perm('admin')) {
            $list = new SelectWidget(_('Veranstaltungen'), '?#admin_top_links', 'cid');

            foreach (AdminCourseFilter::get()->getCoursesForAdminWidget() as $seminar) {
                $list->addElement(new SelectElement(
                    $seminar['Seminar_id'],
                    $seminar['Name'],
                    $seminar['Seminar_id'] === Context::getId(),
                    $seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name']
                ));
            }
            $list->size = 8;
            Sidebar::get()->addWidget($list);
        }
    }


    /**
     * Show information about a request
     *
     * @param String $request_id Id of the request
     */
    public function info_action($request_id)
    {
        $request = RoomRequest::find($request_id);
        $this->request = $request;
        $this->render_template('course/room_requests/_request.php', null);
    }


    /**
     * create a new room request
     */
    public function new_action()
    {
        $options = array();
        $this->url_params = array();
        if (Request::get('origin') !== null) {
            $this->url_params['origin'] = Request::get('origin');
        }
        if (!RoomRequest::existsByCourse($this->course_id)) {
            $options[] = array('value' => 'course',
                               'name'  => _('alle regelmäßigen und unregelmäßigen Termine der Veranstaltung')
            );
        }
        foreach (SeminarCycleDate::findBySeminar($this->course_id) as $cycle) {
            if (!RoomRequest::existsByMetadate($cycle->getId())) {
                $name = _("alle Termine einer regelmäßigen Zeit");
                $name .= ' (' . $cycle->toString('full') . ')';
                $options[] = array('value' => 'cycle_' . $cycle->getId(), 'name' => $name);
            }
        }
        foreach (CourseDate::findBySeminar_id($this->course_id) as $date) {
            if (!RoomRequest::existsByDate($date['termin_id'])) {
                $name = _("Einzeltermin der Veranstaltung");
                $name .= ' (' . $date->getFullname() . ')';
                $options[] = array('value' => 'date_' . $date['termin_id'], 'name' => $name);
            }
        }
        $this->options = $options;

        Helpbar::get()->addPlainText(_('Information'), _('Hier können Sie festlegen, welche Art von Raumanfrage Sie erstellen möchten.'));
    }


    public function edit_action($request_id = null)
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        //Make sure the session data are cleared:
        $_SESSION['course_room_request_edit'] = [];

        $this->current_user = User::findCurrent();
        $this->user_is_global_resource_admin = ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'admin'
        );
        $config = Config::get();
        $this->request_id = $request_id;
        $this->request = new RoomRequest($request_id);

        //Step 1: Search by name or select room category
        //Step 2: Select properties (in case a room category is selected)
        //Step 3: Select room
        //Step 4: Set seats and preparation time
        //Step 5: Store request

        $this->step = 1;

        $this->direct_room_requests_only = false;
        $this->search_by_name = false;
        $this->saving_allowed = false;
        if ($config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) {
            $this->search_by_name = true;
            $this->direct_room_requests_only = true;
        }

        $this->range = Request::get('range');
        $this->range_ids = Request::getArray('range_ids');
        $this->range_id = Request::get('range_id');

        if (Request::submitted('create_room_request')) {
            //Get the type and the range-ID of request:
            $range_str = explode('_', Request::get('range_str'), 2);
            $this->range = $range_str[0];
            if ($this->range == 'course') {
                $this->range_id = $range_str[1];
                if (!$this->range_id) {
                    $this->range_id = Context::getId();
                }
                $this->range_ids = [];
            } else {
                $this->range_id = $range_str[1];
                $this->range_ids = explode($range_str[1], '_');
            }
        }
        $this->course_id = Context::getId();
        if ($this->request->isNew()) {
            if ($this->range == 'date-multiple') {
                $this->request->setRangeFields($this->range, $this->range_ids);
            } else {
                $this->request->setRangeFields($this->range, [$this->range_id]);
            }
            $this->request->course_id = $this->course_id;
        } else {
            if ($this->request->termin_id) {
                $this->range_id = $this->request->termin_id;
                $this->range = 'date';
            } elseif ($this->request->metadate_id) {
                $this->range_id = $this->request->metadate_id;
                $this->range = 'cycle';
            } elseif ($this->request->appointments) {
                $this->range = 'date-multiple';
                $this->range_ids = [];
                foreach ($this->request->appointments as $appointment) {
                    $this->range_ids[] = $appointment->appointment_id;
                }
            } elseif ($this->request->course_id) {
                $this->range = 'course';
                $this->range_id = $this->request->course_id;
                $this->range_ids = [];
            }
        }

        if ($this->request->isNew()) {
            //Set the requester:
            $this->request->user_id = $this->current_user->id;
        }

        //Step 1 variables:
        $this->available_room_categories = ResourceCategory::findByClass_name(
            'Room'
        );
        //Step 2 variables:
        $this->room_category = null;
        $this->room_category_id = '';
        $this->available_properties = [];
        //Step 3 variables:
        $this->search_rooms = false;
        $this->room_name = '';
        $this->selected_properties = [];
        $this->available_rooms = [];
        //Step 4 variables:
        $this->selected_room_id = '';
        $this->selected_room = null;
        //Step 5 variables:
        $this->comment = '';
        $this->seats = '';
        $this->preparation_time = 0;
        $this->max_preparation_time = $config->RESOURCES_MAX_PREPARATION_TIME;
        $this->reply_lecturers = false;
        $this->confirmed_selected_room_id = '';

        if (!$this->request->isNew()) {
            $this->room_category_id = $this->request->category_id;
            foreach ($this->request->properties as $property) {
                $this->selected_properties[$property->name] = $property->state;
            }
            $this->selected_room_id = $this->request->resource_id;
            $this->selected_room = $this->request->room;
            $this->comment = $this->request->comment;
            $this->seats = $this->request->seats;
            $this->preparation_time = intval($this->request->preparation_time / 60);
            $this->reply_lecturers = $this->request->reply_recipients == 'lecturer';
            $this->step = 4;
        }
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->room_name = Request::get('room_name');

            if (Request::submitted('reset_search')) {
                $this->step = 1;
            }
            if (Request::submitted('select_category') || Request::submitted('step1')) {
                $this->step = 2;
            } elseif (Request::submitted('search_rooms')) {
                $this->search_rooms = true;
                $this->step = 3;
            } elseif (Request::submitted('select_room') || Request::submitted('step3')
                      || Request::submitted('reset_selected_room')) {
                $this->step = 4;
            } elseif (Request::submitted('save') || Request::submitted('save_and_close_form')
                      || Request::submitted('step4')) {
                $this->step = 5;
            }
        }

        if ($this->step >= 2) {
            if (Request::submitted('room_category_id')) {
                $this->room_category_id = Request::get('room_category_id');
                //Reset the room, otherwise the form will be unusable:
                $this->selected_room = null;
            }
            if (Request::submitted('step1') || Request::submitted('reset_category')) {
                if (Request::submitted('reset_category')) {
                    $this->room_category_id = null;
                    $this->request->category_id = '';
                }
                $this->step = 1;
            }
            if ($this->room_category_id) {
                $this->room_category = ResourceCategory::find($this->room_category_id);
                if (!$this->room_category) {
                    PageLayout::postError(
                        _('Die gewählte Raumkategorie wurde nicht gefunden!')
                    );
                    $this->step = 1;
                    return;
                }
                if (!is_a($this->room_category->class_name, 'Room', true)) {
                    PageLayout::postError(
                        _('Die gewählte Kategorie ist keine Raumkategorie!')
                    );
                    $this->step = 1;
                    return;
                }
                $this->request->category_id = $this->room_category->id;

                $this->available_properties = $this->room_category->getRequestableProperties();
                $this->saving_allowed = true;
            }

            if (!$this->selected_properties['seats']) {
                //Special treatment of the seats property:
                $admission_turnout = $this->course->admission_turnout;
                $this->selected_properties['seats'] =
                    $admission_turnout ? $admission_turnout : $config->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS;
            }
        }
        if ($this->step >= 3) {
            if (Request::submitted('search_rooms') || Request::submitted('select_room')
                || ($this->step == 5)) {
                $this->selected_properties = Request::getArray('selected_properties');
            }

            //Filter the selected properties so that only those properties
            //that are available for the room category can be used.
            $filtered_selected_properties = [];
            foreach ($this->available_properties as $property) {
                if (in_array($property->name, array_keys($this->selected_properties))) {
                    //Filter out all empty properties:
                    if ($this->selected_properties[$property->name]) {
                        $filtered_selected_properties[$property->name] =
                            $this->selected_properties[$property->name];
                    }
                }
            }
            $this->selected_properties = $filtered_selected_properties;

            $search_properties = $this->selected_properties;
            if ($this->room_category_id) {
                $search_properties['room_category_id'] = $this->room_category_id;
            }
            if ($search_properties['seats']) {
                //The seats property value is a minimum.
                $search_properties['seats'] = [
                    $search_properties['seats'],
                    null
                ];
            }

            //Search rooms by the selected properties:
            $this->available_rooms = RoomManager::findRooms(
                $this->room_name,
                null,
                null,
                $search_properties,
                $this->request->getTimeIntervals(),
                'name ASC, mkdate ASC'
            );
        }
        if ($this->step >= 4) {
            $this->saving_allowed = true;
            if (Request::submitted('reset_selected_room')) {
                $this->step = 2;
                $this->selected_room = null;
                $this->selected_room_id = null;
                $this->reset_selected_room = true;
                return;
            }
            if (Request::submitted('selected_room_id')) {
                $this->selected_room_id = Request::get('selected_room_id');
            }
            if ($this->selected_room_id) {
                $this->selected_room = Room::find($this->selected_room_id);
                if (!$this->selected_room) {
                    PageLayout::postError(
                        _('Der gewählte Raum wurde nicht gefunden!')
                    );
                    $this->step = 3;
                    return;
                }
                if (!is_a($this->selected_room->class_name, 'Room', true)) {
                    PageLayout::postError(
                        _('Die gewählte Ressource ist kein Raum!')
                    );
                    $this->step = 3;
                    return;
                }
                $this->request->resource_id = $this->selected_room->id;
            }
            if (!Request::isPost() && !$this->selected_properties['seats']) {
                //An existing request has been opened for modification.
                $this->selected_properties['seats'] = $this->seats;
            }
            if (!$this->selected_properties['seats']) {
                $admission_turnout = Seminar::getInstance(
                    $this->course_id
                )->admission_turnout;
                $this->selected_properties['seats'] = (
                    $admission_turnout
                    ? $admission_turnout
                    : $config->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS
                );
            }
        }

        if ($this->step >= 5) {
            //This is the last chance to enter the amount of seats:
            if (Request::isPost()) {
                $this->seats = Request::get('seats');
            }
            if (!count($this->selected_properties)) {
                if (!$this->request->resource_id) {
                    PageLayout::postError(
                        'Es wurde weder eine Raumkategorie noch ein konkreter Raum ausgewählt!'
                    );
                    $this->step = 1;
                    return;
                } else {
                    //A room has been selected. Use its category-ID.
                    //If the category-ID isn't set, we cannot set the seats property.
                    $this->request->category_id = $this->request->resource->category_id;
                }
            }

            $this->comment = Request::get('comment');
            $this->reply_lecturers = Request::get('reply_lecturers');
            $this->confirmed_selected_room_id = Request::get('confirmed_selected_room_id');
            $this->preparation_time = Request::get('preparation_time');

            if ($this->seats <= 0) {
                PageLayout::postError(
                    _('Es wurde keine Anzahl an Sitzplätzen angegeben!')
                );
                $this->step = 4;
                return;
            }
            if ($this->preparation_time > $this->max_preparation_time) {
                PageLayout::postError(
                    sprintf(
                        _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                        $this->max_preparation_time
                    )
                );
                $this->step = 4;
                return;
            }

            $this->request->comment = $this->comment;
            if ($this->reply_lecturers) {
                $this->request->reply_recipients = 'lecturer';
            } else {
                $this->request->reply_recipients = 'requester';
            }
            $this->request->preparation_time = $this->preparation_time * 60;

            $this->request->course_id = $this->course_id;
            $this->request->last_modified_by = $this->current_user->id;

            if ($this->confirmed_selected_room_id) {
                $this->request->resource_id = $this->confirmed_selected_room_id;
            } else {
                $this->request->resource_id = '';
            }

            $storing_successful = false;
            if ($this->request->isDirty()) {
                $storing_successful = $this->request->store();
            } else {
                $storing_successful = true;
            }

            if ($storing_successful) {
                //Store the properties:
                foreach ($this->selected_properties as $name => $state) {
                    $result = $this->request->setProperty($name, $state);
                }
                $result = $this->request->setProperty('seats', $this->seats);
                PageLayout::postSuccess(_('Die Anfrage wurde gespeichert!'));

                if (Request::submitted('save_and_close_form')) {
                    $this->relocate('course/room_requests/index');
                }
                $this->request_id = $this->request->id;
            } else {
                PageLayout::postError(
                    _('Die Anfrage konnte nicht gespeichert werden!')
                );
            }
        }
    }


    /**
     * Returns a reference to the session data array for the
     * specified request-ID.
     */
    protected function &getRequestSessionData($request_id = null)
    {
        if (!$request_id) {
            return null;
        }
        if (!$_SESSION['course_room_request']) {
            $_SESSION['course_room_request'] = [];
        }
        if (!$_SESSION['course_room_request'][$request_id]) {
            $_SESSION['course_room_request'][$request_id] = [];
        }
        return $_SESSION['course_room_request'][$request_id];
    }


    /**
     * Returns a request instance that gets all its data set from the session.
     * This is useful if a new request is being created.
     */
    protected function getRequestInstanceFromSession($request_id)
    {
        $session_data = &$this->getRequestSessionData($request_id);
        $request = new RoomRequest($request_id);
        if ($session_data['range'] == 'date-multiple') {
            $request->setRangeFields($session_data['range'], $session_data['range_ids']);
        } else {
            $request->setRangeFields($session_data['range'], [$session_data['range_id']]);
        }
        $request->course_id = $this->course_id;

        return $request;
    }


    /**
     * This method loads data for the request editing actions.
     * Depending on the editing step, more or less data have to
     * be loaded.
     *
     * @param array $session_data Request data stored in the session.
     *
     * @param int $step The editing step:
     *     1 = request_start
     *     2 = request_select_properties
     *     3 = request_select_room
     *     4 = request_overview
     */
    protected function loadData($session_data, $step = 1)
    {
        $this->course_id = Context::getId();
        $this->current_user = User::findCurrent();
        $this->user_is_global_resource_admin = ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'admin'
        );
        $this->config = Config::get();
        $this->direct_room_requests_only = $this->config->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY;
        if ($step >= 1) {
            //Load all available room categories:
            $this->available_room_categories = ResourceCategory::findByClass_name(
                'Room'
            );
        }
        if ($step >= 2) {
            if ($session_data['category_id']) {
                $this->category = ResourceCategory::find($session_data['category_id']);
                if ($this->category instanceof ResourceCategory) {
                    //Get all available properties for the category:
                    $this->available_properties = $this->category->getRequestableProperties();
                }
            }
            $this->room_name = $session_data['room_name'];
            $this->category_id = $session_data['category_id'];
        }
        if ($step >= 3) {
            if ($this->category instanceof ResourceCategory) {
                $this->selected_properties = $session_data['selected_properties'];
            }
        }
        if ($step >= 4) {
            $this->seats = $session_data['selected_properties']['seats'];
            $this->comment = $session_data['comment'];
            $this->reply_lecturers = $session_data['reply_lecturers'];
            $this->preparation_time = $session_data['preparation_time'];
        }
    }


    /**
     * This action is the entry point for adding properties to a room request.
     */
    public function request_start_action($request_id = '')
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->request_id = $request_id;
        if (Request::submitted('request_id')) {
            $this->request_id = Request::get('request_id');
        }
        if (!$this->request_id) {
            $this->request_id = md5(uniqid('RoomRequest'));
        }

        $session_data = &$this->getRequestSessionData($this->request_id);

        $this->loadData($session_data, 1);

        $this->request = null;

        //Check if a request exists and set its ID in the session,
        //it is needed later.
        $this->request = RoomRequest::find(Request::get('request_id'));
        if ($this->request instanceof RoomRequest) {
            $session_data['request_id'] = $this->request->id;
            $this->request_id = $this->request->id;
        } elseif ($session_data['request_id']) {
            //It is a new request that isn't stored yet. Load its basic data
            //from the session and create a request object:
            $this->request_id = $session_data['request_id'];
            $this->request = $this->getRequestInstanceFromSession($this->request_id);
        } else {
            //A new request shall be created.
            //Get the range from URL parameters.
            $range = null;
            $range_id = null;
            $range_ids = [];
            if (Request::submitted('range_str')) {
                $range_str = explode('_', Request::get('range_str'));
                $range = $range_str[0];
                if ($range == 'course') {
                    $range_id = $range_str[1];
                    if (!$range_id) {
                        $range_id = Context::getId();
                    }
                } else {
                    if (count($range_str) > 2) {
                        //More than one ID has been specified.
                        $range_ids = array_slice($range_str, 1);
                    } else {
                        $range_id = $range_str[1];
                    }
                }
            } else {
                $range = Request::get('range');
                $range_id = Request::get('range_id');
                $range_ids = Request::getArray('range_ids');
            }
            $session_data['range'] = $range;
            $session_data['range_id'] = $range_id;
            $session_data['range_ids'] = $range_ids;
            $session_data['request_id'] = $this->request_id;

            //Create a request object:
            $this->request = new RoomRequest($session_data['request_id']);
            if ($range == 'date-multiple') {
                $this->request->setRangeFields($range, $range_ids);
            } else {
                $this->request->setRangeFields($range, [$range_id]);
            }
            $this->request->course_id = $this->course_id;
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $this->room_name = Request::get('room_name');
            $this->category_id = Request::get('category_id');
            if (Request::submitted('search_by_name')) {
                if (!$this->room_name) {
                    PageLayout::postError(
                        _('Es wurde kein Raumname angegeben!')
                    );
                    return;
                }
                $session_data['room_name'] = $this->room_name;
                $session_data['request_id'] = $this->request_id;
                //Redirect to the room selection action.
                $this->redirect(
                    'course/room_requests/request_select_room/' . $this->request_id
                );
            } elseif (Request::submitted('select_properties')) {
                if (!$this->category_id) {
                    PageLayout::postError(
                        _('Es wurde keine Raumkategorie ausgewählt!')
                    );
                    return;
                }
                foreach ($this->available_room_categories as $category) {
                    if ($category->id == $this->category_id) {
                        //The selected category is in the array of
                        //available categories.
                        $session_data['request_id'] = $this->request_id;
                        $session_data['category_id'] = $this->category_id;
                        $session_data['room_name'] = $this->room_name;
                        //Redirect to the property selection page:
                        $this->redirect(
                            'course/room_requests/request_select_properties/' . $this->request_id
                           );
                        return;
                    }
                }

                //If this point is reached, then the selected room category ID
                //is not in the array of available room categories.
                PageLayout::postError(
                    _('Die gewählte Raumkategorie wurde nicht gefunden!')
                );
            }
        }
    }


    /**
     * This action is called from request_start in the case that a
     * resource category has been selected.
     */
    public function request_select_properties_action($request_id = '')
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->request_id = $request_id;
        $session_data = &$this->getRequestSessionData($this->request_id);
        $this->loadData($session_data, 2);

        if ($this->direct_room_requests_only) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen anhand von Eigenschaften ist nicht erlaubt!')
            );
        }

        $this->selected_properties = [];
        $this->request = RoomRequest::find($this->request_id);
        if ($this->request instanceof RoomRequest) {
            //It is an existing request. If no properties have been selected
            //via HTTP POST, set them from the request itself:
            foreach ($this->request->properties as $property) {
                $this->selected_properties[$property->name] = $property->state;
            }
            $this->category = $this->request->category;
            $this->category_id = $this->request->category_id;
        } else {
            //It is a new request. Create the request object and do nothing else.
            $this->request = $this->getRequestInstanceFromSession($this->request_id);
        }

        if (!($this->category instanceof ResourceCategory)) {
            PageLayout::postError(
                _('Die gewählte Raumkategorie wurde nicht gefunden!')
            );
            return;
        }

        $this->available_properties = $this->category->getRequestableProperties();

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_properties = Request::getArray('selected_properties');
            //Filter the selected properties so that only those properties
            //that are available for the room category can be used.
            $filtered_selected_properties = [];
            foreach ($this->available_properties as $property) {
                if (in_array($property->name, array_keys($this->selected_properties))) {
                    //Filter out all empty properties:
                    if ($this->selected_properties[$property->name]) {
                        $filtered_selected_properties[$property->name] =
                            $this->selected_properties[$property->name];
                    }
                }
            }
            $this->selected_properties = $filtered_selected_properties;

            if (Request::submitted('search_by_name')) {
                //Delete all selected properties from the session since the
                //search by name (name only) has been used.
                $session_data['selected_properties'] = [];
                $session_data['room_name'] = Request::get('room_name');
                $this->redirect(
                    'course/room_requests/request_select_room/' . $this->request_id
                );
            } elseif (Request::submitted('select_room')) {
                //Store the selected properties in the session and redirect.
                $session_data['selected_properties'] = $this->selected_properties;
                $this->redirect(
                    'course/room_requests/request_select_room/' . $this->request_id
                );
            } elseif (Request::submitted('save') || Request::submitted('save_and_close')) {
                //Save the request and stay on the page (save) or return
                //to the overview page (save_and_close).

                if ($this->selected_properties['seats'] < 1) {
                    PageLayout::postError(
                        _('Es wurde keine Anzahl an gewünschten Sitzplätzen angegeben!')
                    );
                    return;
                }

                $this->request->category_id = $session_data['category_id'];
                if ($this->request->isNew()) {
                    //Set the requester:
                    $this->request->user_id = $this->current_user->id;
                } else {
                    //Do another thing: Delete all previously set properties:
                    $this->request->properties->delete();
                }

                $storing_successful = false;
                if ($this->request->isDirty()) {
                    $storing_successful = $this->request->store();
                } else {
                    $storing_successful = true;
                }

                if ($storing_successful) {
                    //Store the properties:
                    foreach ($this->selected_properties as $name => $state) {
                        $result = $this->request->setProperty($name, $state);
                    }
                    PageLayout::postSuccess(_('Die Anfrage wurde gespeichert!'));

                    if (Request::submitted('save_and_close')) {
                        $this->relocate('course/room_requests/index');
                    }
                } else {
                    PageLayout::postError(
                        _('Die Anfrage konnte nicht gespeichert werden!')
                    );
                }
            }
        }
    }


    /**
     * This action is called either directly from request_start
     * (in case a room name is set but no resource category) or after
     * selecting properties in request_select_properties.
     * It searches for rooms depending on the selected properties
     * or the selected name.
     */
    public function request_select_room_action($request_id)
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->request_id = $request_id;
        $session_data = &$this->getRequestSessionData($this->request_id);

        $this->loadData($session_data, 3);

        $this->request = RoomRequest::find($this->request_id);
        if (!($this->request instanceof RoomRequest)) {
            //It is a new request. Create the request object and do nothing else.
            $this->request = $this->getRequestInstanceFromSession($this->request_id);
        }

        $search_properties = $this->selected_properties;
        if ($session_data['category_id']) {
            $search_properties['room_category_id'] = $session_data['category_id'];
        }
        if ($search_properties['seats']) {
            //The seats property value is a minimum.
            $search_properties['seats'] = [
                $search_properties['seats'],
                null
            ];
        }

        //Search rooms by the selected properties:
        $this->available_rooms = RoomManager::findRooms(
            $this->room_name,
            null,
            null,
            $search_properties,
            $this->request->getTimeIntervals(),
            'name ASC, mkdate ASC'
        );

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->selected_room_id = Request::get('selected_room_id');
            if ($this->selected_room_id) {
                $room_found = false;
                foreach ($this->available_rooms as $room) {
                    if ($this->selected_room_id == $room->id) {
                        $room_found = true;
                        break;
                    }
                }
                if (!$room_found) {
                    //The room could not be found in the list of available rooms.
                    PageLayout::postError(_('Der gewählte Raum wurde nicht gefunden!'));
                    return;
                }
            }
            if (Request::submitted('select_room')) {
                $session_data['selected_room_id'] = $this->selected_room_id;
                $this->redirect('course/room_requests/request_summary/' . $this->request_id);
            } elseif (Request::submitted('save') || Request::submitted('save_and_close')) {
                if ($session_data['selected_properties']['seats'] < 1) {
                    PageLayout::postError(
                        _('Es wurde keine Anzahl an gewünschten Sitzplätzen angegeben!')
                    );
                    return;
                }
                $session_data['selected_room_id'] = $this->selected_room_id;
                //Store all request data from the session in the request:
                $this->request->category_id = $session_data['category_id'];
                $this->request->updateProperties($session_data['selected_properties']);
                $this->request->resource_id = (
                    $this->selected_room_id
                    ? $this->selected_room_id
                    : ''
                );

                if ($this->request->isNew()) {
                    //Set the requester:
                    $this->request->user_id = $this->current_user->id;
                }

                $storing_successful = false;
                if ($this->request->isDirty()) {
                    $storing_successful = $this->request->store();
                } else {
                    $storing_successful = true;
                }

                if ($storing_successful) {
                    PageLayout::postSuccess(_('Die Anfrage wurde gespeichert!'));
                    if (Request::submitted('save_and_close')) {
                        $this->relocate('course/room_requests/index');
                    }
                } else {
                    PageLayout::postError(
                        _('Die Anfrage konnte nicht gespeichert werden!')
                    );
                }
            }
        }
    }


    /**
     * This action is either called from request_select_room after a room
     * has been selected, from request_select_properties after properties
     * have been selected or when an existing request shall be edited.
     */
    public function request_summary_action($request_id = null)
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->request_id = $request_id;
        $session_data = &$this->getRequestSessionData($this->request_id);

        $this->loadData($session_data, 4);

        $this->max_preparation_time = $this->config->RESOURCES_MAX_PREPARATION_TIME;

        $this->request = RoomRequest::find($this->request_id);
        $selected_room = null;
        $this->seats = null;
        if (($this->request instanceof RoomRequest) && !$session_data['request_id']) {
            //It is an existing request that hasn't been modified yet.
            //Load its data directly.
            if ($this->request->resource_id) {
                $selected_room = Resource::find($this->request->resource_id);
            }
            $this->seats = $this->request->seats;
            $this->preparation_time = intval($this->request->preparation_time / 60);
        } else {
            //It is a new request or an existing request that is being modified.
            //Create the request object from the session and do nothing else.
            $this->request = $this->getRequestInstanceFromSession($this->request_id);
            if ($session_data['selected_room_id']) {
                $selected_room = Resource::find($session_data['selected_room_id']);
            }
            $this->seats = $session_data['selected_properties']['seats'];
        }
        if ($selected_room instanceof Resource) {
            $this->selected_room = $selected_room->getDerivedClassInstance();
            if (!($this->selected_room instanceof Room)) {
                PageLayout::postWarning(
                    _('Die ausgewählte Ressource ist kein Raum!')
                );
            }
        }

        if (!$this->seats) {
            $admission_turnout = $this->course->admission_turnout;
            $this->seats = $admission_turnout
                         ? $admission_turnout
                         : $config->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS;
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->seats = Request::get('seats');
            $this->comment = Request::get('comment');
            $this->reply_lecturers = Request::get('reply_lecturers');
            $this->confirmed_selected_room_id = Request::get('confirmed_selected_room_id');
            $this->preparation_time = Request::get('preparation_time');

            if (Request::submitted('select_other_room') || Request::submitted('select_properties')) {
                //The checks for the values of the seats property, the amount of
                //preparation time and the other fields are skipped here since
                //the request isn't stored. Even if it gets stored in one of
                //the other steps that allow storing the request, the data
                //from this step won't be stored with the request.
                $session_data['selected_properties']['seats'] = $this->seats;
                $session_data['comment'] = $this->comment;
                $session_data['reply_lecturers'] = $this->reply_lecturers;
                $session_data['preparation_time'] = $this->preparation_time;
                if (Request::submitted('select_other_room')) {
                    $this->redirect('course/room_requests/request_select_room/' . $this->request_id);
                } else {
                    $this->redirect('course/room_requests/request_select_properties/' . $this->request_id);
                }
                return;
            } elseif (Request::submitted('save') || Request::submitted('save_and_close')) {
                if ($this->seats < 1) {
                    PageLayout::postError(
                        _('Es wurde keine Anzahl an Sitzplätzen angegeben!')
                    );
                    return;
                }
                if ($this->preparation_time > $this->max_preparation_time) {
                    PageLayout::postError(
                        sprintf(
                            _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                            $this->max_preparation_time
                        )
                    );
                    return;
                }

                if ($this->request->isNew()) {
                    //Set the requester:
                    $this->request->user_id = $this->current_user->id;
                }

                $this->request->comment = $this->comment;
                if ($this->reply_lecturers) {
                    $this->request->reply_recipients = 'lecturer';
                } else {
                    $this->request->reply_recipients = 'requester';
                }
                $this->request->preparation_time = $this->preparation_time * 60;

                $this->request->course_id = $this->course_id;
                $this->request->last_modified_by = $this->current_user->id;

                if ($this->selected_room) {
                    $this->request->resource_id = $this->selected_room->id;
                } else {
                    $this->request->resource_id = '';
                }

                $storing_successful = false;
                if ($this->request->isDirty()) {
                    $storing_successful = $this->request->store();
                } else {
                    $storing_successful = true;
                }

                if ($storing_successful) {
                    //Store the properties:
                    $selected_properties = $session_data['selected_properties'];
                    if ($selected_properties) {
                        $selected_properties['seats'] = $this->seats;
                        foreach ($this->selected_properties as $name => $state) {
                            $result = $this->request->setProperty($name, $state);
                        }
                    } else {
                        $result = $this->request->setProperty('seats', $this->seats);
                    }
                    PageLayout::postSuccess(_('Die Anfrage wurde gespeichert!'));

                    if (Request::submitted('save_and_close')) {
                        $this->relocate('course/room_requests/index');
                    }
                } else {
                    PageLayout::postError(
                        _('Die Anfrage konnte nicht gespeichert werden!')
                    );
                }
            }
        }
    }


    /**
     * Stores the request. This is called from request_summary,
     * request_select_room and request_select_properties after the user
     * clicked on "save" or "save and close".
     */
    public function request_store_action()
    {
        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->current_user = User::findCurrent();
        $this->user_is_global_resource_admin = ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'admin'
        );
        $config = Config::get();
    }


    /**
     * delete one room request
     */
    public function delete_action($request_id)
    {
        $request = RoomRequest::find($request_id);
        if (!$request) {
            throw new Trails_Exception(403);
        }
        if (Request::isGet()) {
            PageLayout::postQuestion(sprintf(
                _('Möchten Sie die Raumanfrage "%s" löschen?'),
                htmlReady($request->getTypeString())), $this->url_for('course/room_requests/delete/' . $request_id));
        } else {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('yes')) {
                if ($request->delete()) {
                    PageLayout::postSuccess("Die Raumanfrage wurde gelöscht.");
                }
            }
        }
        $this->redirect('course/room_requests/index');
    }
}
