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
        Navigation::activateItem('/course/admin/room_requests');

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

        $room_requests = RoomRequest::findBySQL('seminar_id = ? ORDER BY seminar_id, metadate_id, termin_id', [$this->course_id]);
        $this->room_requests = $room_requests;
        $this->request_id = Request::option('request_id');

        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Raumanfrage erstellen'), $this->url_for('course/room_requests/new/' . $this->course_id), Icon::create('add', 'clickable'));
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
     * edit one room requests
     */
    public function edit_action()
    {
        Helpbar::get()->addPlainText(_('Information'), _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.'));

        $request_was_closed_before = false;
        $admission_turnout = Seminar::getInstance($this->course_id)->admission_turnout;

        if (Request::option('new_room_request_type')) {
            $request = new RoomRequest();
            $request->seminar_id = $this->course_id;
            $request->user_id = $GLOBALS['user']->id;
            $request->setDefaultSeats($admission_turnout ?: Config::get()->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS);

            list($new_type, $id) = explode('_', Request::option('new_room_request_type'));
            if ($new_type == 'course') {
                if ($existing_request = RoomRequest::existsByCourse($this->course_id)) {
                    $request = RoomRequest::find($existing_request);
                }
            }
            if ($new_type == 'date') {
                $request->termin_id = $id;
                if ($existing_request = RoomRequest::existsByDate($id)) {
                    $request = RoomRequest::find($existing_request);
                }
            } elseif ($new_type == 'cycle') {
                $request->metadate_id = $id;
                if ($existing_request = RoomRequest::existsByCycle($id)) {
                    $request = RoomRequest::find($existing_request);
                }
            }
        } else {
            $request = RoomRequest::find(Request::option('request_id'));

            if($request->user_id != $GLOBALS['user']->id) {
                $request->last_modified_by = $GLOBALS['user']->id;
            }

            $request_was_closed_before = $request->getClosed() > 0;
        }

        $attributes = self::process_form($request);

        $this->params = ['request_id' => $request->getId()];
        $this->params['fromDialog'] = Request::get('fromDialog');
        if (Request::get('origin') !== null) {
            $this->params['origin'] = Request::get('origin');
        }

        if (Request::submitted('save') || Request::submitted('save_close')) {
            if (!($request->getSettedPropertiesCount() || $request->getResourceId())) {
                PageLayout::postMessage(MessageBox::error(_("Die Anfrage konnte nicht gespeichert werden, da Sie mindestens einen Raum oder mindestens eine Eigenschaft (z.B. Anzahl der Sitzplätze) angeben müssen!")));
            } else {
                $request->setClosed(0);
                if ($request_was_closed_before) {
                    //The one who re-activates a request shall be the one who owns it.
                    //(Fix for Biest #2794).
                    $request->user_id = $GLOBALS['user']->id;
                }

                $this->request_stored = $request->store();
                if ($this->request_stored) {
                    PageLayout::postMessage(MessageBox::success(_("Die Raumanfrage und gewünschte Raumeigenschaften wurden gespeichert")));
                }
                if (Request::submitted('save_close')) {
                    if (!Request::isXhr()) {
                        $this->redirect('course/room_requests/index/' . $this->course_id);
                    } else {
                        if (Request::get('fromDialog') == true && !isset($this->params['origin'])) {
                            $this->relocate('course/room_requests/index/' . $this->course_id);
                        } else if (isset($this->params['origin'])) {
                            $this->relocate(str_replace('_', '/', $this->params['origin']) . '?cid=' . $this->course_id);
                        } else {
                            $this->relocate('course/room_requests/index/' . $this->course_id);
                        }
                    }
                }
            }
        }

        if (!$request->isNew() && $request->isDirty()) {
            PageLayout::postMessage(MessageBox::info(_("Die Änderungen an der Raumanfrage wurden noch nicht gespeichert!")));
        }
        $room_categories = array_values(array_filter(getResourcesCategories(), function ($a) { return $a["is_room"] == 1;}));
        if (!$request->getCategoryId() && count($room_categories) == 1) {
            $request->setCategoryId($room_categories[0]['category_id']);
        }
        $this->search_result = $attributes['search_result'];
        $this->search_by_properties = $attributes['search_by_properties'];
        $this->request = $request;
        $this->room_categories = $room_categories;
        $this->new_room_request_type = Request::option('new_room_request_type');
        $this->is_resources_admin = getGlobalPerms($GLOBALS['user']->id);


        $actions = new ActionsWidget();
        $actions->addLink(_('Bearbeitung abbrechen'), $this->url_for('course/room_requests/index/' . $this->course_id), Icon::create('decline', 'clickable'));

        if (!$request->isNew() && (getGlobalPerms($GLOBALS['user']->id) == 'admin' || ($GLOBALS['perm']->have_perm('admin') && count(getMyRoomRequests(null, null, true, $request->getId()))))) {
            $actions->addLink(_('Raumanfrage auflösen'),
                URLHelper::getURL('resources.php', ['view'           => 'edit_request',
                                                          'single_request' => $request->getId()
                ]),
                Icon::create('admin', 'clickable'));
        }

        if (!Request::isXhr()) {
            Sidebar::Get()->addWidget($actions);

            $widget = new SidebarWidget();
            $widget->setTitle(_('Informationen'));
            if ($request->isNew()) {
                $widget->addElement(new WidgetElement(_('Dies ist eine neue Raumanfrage.')));
            } else {
                $info_txt = '';
                if ($request->user) {
                    $info_txt .= '<p>' . sprintf(_('Erstellt von: %s'), htmlReady($request->user->getFullname())) . '</p>';
                }
                $info_txt .= '<p>' . sprintf(_('Erstellt am: %s'), htmlReady(strftime('%x %H:%M', $request->mkdate))) . '</p>';
                $info_txt .= '<p>' . sprintf(_('Letzte Änderung: %s'), htmlReady(strftime('%x %H:%M', $request->chdate))) . '</p>';
                $widget->addElement(new WidgetElement($info_txt));
            }
            Sidebar::Get()->addWidget($widget);
        }
    }

    /**
     * create a new room requests
     */
    public function new_action()
    {
        $options = [];
        $this->url_params = [];
        if (Request::get('origin') !== null) {
            $this->url_params['origin'] = Request::get('origin');
        }
        if (!RoomRequest::existsByCourse($this->course_id)) {
            $options[] = ['value' => 'course',
                               'name'  => _('alle regelmäßigen und unregelmäßigen Termine der Veranstaltung')
            ];
        }
        foreach (SeminarCycleDate::findBySeminar($this->course_id) as $cycle) {
            if (!RoomRequest::existsByCycle($cycle->getId())) {
                $name = _("alle Termine einer regelmäßigen Zeit");
                $name .= ' (' . $cycle->toString('full') . ')';
                $options[] = ['value' => 'cycle_' . $cycle->getId(), 'name' => $name];
            }
        }
        foreach (SeminarDB::getSingleDates($this->course_id) as $date) {
            if (!RoomRequest::existsByDate($date['termin_id'])) {
                $name = _("Einzeltermin der Veranstaltung");
                $termin = new SingleDate($date['termin_id']);
                $name .= ' (' . $termin->toString() . ')';
                $options[] = ['value' => 'date_' . $date['termin_id'], 'name' => $name];
            }
        }
        $this->options = $options;

        Helpbar::get()->addPlainText(_('Information'), _('Hier können Sie festlegen, welche Art von Raumanfrage Sie erstellen möchten.'));
    }

    /**
     * delete one room request
     */
    public function delete_action()
    {
        $request = RoomRequest::find(Request::option('request_id'));
        if (!$request) {
            throw new Trails_Exception(403);
        }
        if (Request::isGet()) {
            $factory = new Flexi_TemplateFactory($this->dispatcher->trails_root . '/views/');
            $template = $factory->open('course/room_requests/_del.php');
            $template->action = $this->link_for('course/room_requests/delete/' . $this->course_id, ['request_id' => $request->getid()]);
            $template->question = sprintf(_('Möchten Sie die Raumanfrage "%s" löschen?'), $request->getTypeExplained());
            $this->flash['message'] = $template->render();
        } else {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('kill')) {
                if ($request->delete()) {
                    $this->flash['message'] = MessageBox::success("Die Raumanfrage wurde gelöscht.");
                }
            }
        }
        $this->redirect('course/room_requests/index/' . $this->course_id);
    }

    /**
     * handle common tasks for the romm request form
     * (set properties, searching etc.)
     */
    public static function process_form($request)
    {
        if (Request::submitted('room_request_form')) {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('send_room')) {
                $request->setResourceId(Request::option('select_room'));
            } else {
                $request->setResourceId(Request::option('selected_room', ''));
            }
            if (Request::submitted('reset_resource_id')) {
                $request->setResourceId('');
            }
            if (Request::submitted('reset_room_type')) {
                $request->setCategoryId('');
            }
            if (Request::get('comment') !== null) {
                $request->setComment(Request::get('comment'));
            }
            if (Request::get('reply_recipients') !== null) {
                $request->reply_recipients = Request::get('reply_recipients');
            }
            if (!Request::submitted('reset_room_type')) {
                $request->setCategoryId(Request::option('select_room_type'));
            }
            //Property Requests
            if ($request->getCategoryId()) {
                $request_property_val = Request::getArray('request_property_val');
                foreach ($request->getAvailableProperties() as $prop) {
                    if ($prop["system"] == 2) { //it's the property for the seat/room-size!
                        if (!Request::submitted('send_room_type')) {
                            $request->setPropertyState($prop['property_id'], abs($request_property_val[$prop['property_id']]));
                        }
                    } else {
                        $request->setPropertyState($prop['property_id'], $request_property_val[$prop['property_id']]);
                    }
                }
            }
            if ((Request::get('search_exp_room') && Request::submitted('search_room'))
                || Request::submitted('search_properties')
            ) {
                $tmp_search_result = $request->searchRoomsToRequest(Request::get('search_exp_room'), Request::submitted('search_properties'));
                $search_by_properties = Request::submitted('search_properties');
                $search_result = [];
                if (count($tmp_search_result)) {
                    $timestamps = $events = [];
                    foreach ($request->getAffectedDates() as $date) {
                        $timestamps[] = $date->date;
                        $timestamps[] = $date->end_time;
                        $assign_id = $date->room_assignment->id ?: $date->id;
                        $event = new AssignEvent($assign_id, $date->date, $date->end_time, $date->room_assignment->resource_id, null);
                        $events[$event->getId()] = $event;
                    }
                    $check_result = [];
                    if (count($events)) {
                        $checker = new CheckMultipleOverlaps();
                        $checker->setTimeRange(min($timestamps), max($timestamps));
                        foreach (array_keys($tmp_search_result) as $room) {
                            $checker->addResource($room);
                        }
                        $checker->checkOverlap($events, $check_result, "assign_id");
                    }
                    foreach ($tmp_search_result as $room_id => $name) {

                        //show only rooms with the requestable property
                        $raum_object = ResourceObject::Factory($room_id);
                        if (Config::get()->RESOURCES_ALLOW_REQUESTABLE_ROOM_REQUESTS && !$raum_object->requestable) {
                            continue;
                        }

                        if (isset($check_result[$room_id]) && count($check_result[$room_id]) > 0) {
                            $details = $check_result[$room_id];
                            if (count($details) >= round(count($events) * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100)) {
                                $icon = Icon::create('decline-circle', 'status-red', [
                                    'title' => sprintf(
                                        _('Es existieren Überschneidungen oder Belegungssperren zu mehr als %s%% aller gewünschten Belegungszeiten.'),
                                        Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE
                                    ),
                                ]);
                            } else {
                                $icon = Icon::create('exclaim-circle', 'status-yellow', [
                                    'title' => _('Es existieren Überschneidungen zur gewünschten Belegungszeit.'),
                                ]);
                            }
                        } else {
                            $icon = Icon::create('check-circle', 'status-green', [
                                'title' => _('Es existieren keine Überschneidungen'),
                            ]);
                        }
                        $search_result[$room_id] = compact('name', 'icon');
                    }
                }
            }
        }
        return compact('search_result', 'search_by_properties');
    }
}
