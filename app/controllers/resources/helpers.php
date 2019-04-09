<?php
/**
 * helpers.php - ajax helpers for room/resources
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
class Resources_HelpersController extends AuthenticatedController
{
    public function bookable_rooms_action()
    {
        if (!getGlobalPerms($GLOBALS['user']->id) == 'admin') {
            $resList = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, false);
            if (!$resList->roomsExist()) {
                throw new AccessDeniedException();
            }
        }
        $select_options = Request::optionArray('rooms');
        $rooms = array_filter($select_options, function($v) {return mb_strlen($v) === 32;});
        $events = [];
        $dates = [];
        $timestamps = [];
        if (count(Request::getArray('new_date'))) {
            $new_date = [];
            foreach (Request::getArray('new_date') as $one) {
                if ($one['name'] == 'startDate') {
                    $dmy = explode('.', $one['value']);
                    $new_date['day'] = (int)$dmy[0];
                    $new_date['month'] = (int)$dmy[1];
                    $new_date['year'] = (int)$dmy[2];
                }
                $new_date[$one['name']] = (int)$one['value'];
            }

            if (check_singledate($new_date['day'], $new_date['month'], $new_date['year'], $new_date['start_stunde'],
            $new_date['start_minute'], $new_date['end_stunde'], $new_date['end_minute'])) {
                $start = mktime($new_date['start_stunde'], $new_date['start_minute'], 0, $new_date['month'], $new_date['day'], $new_date['year']);
                $ende = mktime($new_date['end_stunde'], $new_date['end_minute'], 0, $new_date['month'], $new_date['day'], $new_date['year']);
                $timestamps[] = $start;
                $timestamps[] = $ende;
                $event = new AssignEvent('new_date', $start, $ende, null, null, '');
                $events[$event->getId()] = $event;
            }
        }
        foreach(Request::optionArray('selected_dates') as $one) {
            $date = new SingleDate($one);
            if ($date->getStartTime()) {
                $timestamps[] = $date->getStartTime();
                $timestamps[] = $date->getEndTime();
                $event = new AssignEvent($date->getTerminID(), $date->getStartTime(), $date->getEndTime(), null, null, '');
                $events[$event->getId()] = $event;
                $dates[$date->getTerminID()] = $date;
            }
        }
        if (count($events)) {
            $result = [];
            $checker = new CheckMultipleOverlaps();
            $checker->setTimeRange(min($timestamps), max($timestamps));
            foreach($rooms as $room) $checker->addResource($room);
            $checker->checkOverlap($events, $result, "assign_id");
            foreach((array)$result as $room_id => $details) {
                foreach($details as $termin_id => $conflicts) {
                    if ($termin_id == 'new_date' && Request::option('singleDateID')) {
                        $assign_id = SingleDateDB::getAssignID(Request::option('singleDateID'));
                    } else {
                        $assign_id = SingleDateDB::getAssignID($termin_id);
                    }
                    $filter = function($a) use ($assign_id)
                        {
                            if ($a['assign_id'] && $a['assign_id'] == $assign_id) {
                                return false;
                            }
                            return true;
                        };
                    if (!count(array_filter($conflicts, $filter))) {
                        unset($result[$room_id][$termin_id]);
                    }
                }
            }
            $result = array_filter($result);
            $this->render_json(array_keys($result));
            return;
        }

        $this->render_nothing();
    }

    function resource_message_action($resource_id)
    {
        $r_perms = new ResourceObjectPerms($resource_id, $GLOBALS['user']->id);
        if (!$r_perms->havePerm('admin')) {
            throw new AccessDeniedException();
        }
            $this->resource = new ResourceObject($resource_id);
            $title = sprintf(_("Nutzer von %s benachrichtigen"),htmlReady($this->resource->getName()));
            $form_fields['start_day'] = ['type' => 'text', 'size' => '10', 'required' => true, 'caption' => _("Belegungen berücksichtigen von")];
            $form_fields['start_day']['attributes'] = ['onMouseOver' => 'jQuery(this).datepicker();this.blur();', 'onChange' => '$(this).closest("form").submit();'];
            $form_fields['start_day']['default_value'] = strftime('%x');
            $form_fields['end_day'] = ['type' => 'text', 'size' => '10', 'required' => true, 'caption' => _("Belegungen berücksichtigen bis")];
            $form_fields['end_day']['attributes'] = ['onMouseOver' => 'jQuery(this).datepicker();this.blur();', 'onChange' => '$(this).closest("form").submit();'];
            $form_fields['end_day']['default_value'] = strftime('%x', strtotime('+6 months'));
            $form_fields['subject'] = ['type' => 'text', 'size' => '200','attributes' => ['style' => 'width:100%'], 'required' => true, 'caption' => _("Betreff")];
            $form_fields['subject']['default_value'] = $this->resource->getName();
            $form_fields['message'] = ['caption' => _("Nachricht"), 'type' => 'textarea', 'required' => true, 'attributes' => ['rows' => 4, 'style' => 'width:100%']];

            $form_buttons['save_close'] = ['caption' => _('OK'), 'info' => _("Benachrichtigung verschicken und Dialog schließen")];

            $form = new StudipForm($form_fields, $form_buttons, 'resource_message', false);

            $start_time = strtotime($form->getFormFieldValue('start_day'));
            $end_time = strtotime($form->getFormFieldValue('end_day'));

            $assign_events = new AssignEventList($start_time, $end_time, $resource_id, '', '', TRUE, 'all');
            $rec = [];
            while ($event = $assign_events->nextEvent()) {
                if ($owner_type = $event->getOwnerType()) {
                    if ($owner_type == 'date') {
                        $seminar = new Seminar(Seminar::GetSemIdByDateId($event->getAssignUserId()));
                        foreach($seminar->getMembers('dozent') as $dozent) {
                            $rec[$dozent['username']][] = strftime('%x %R', $event->begin) . ' - ' . strftime('%R', $event->end) . ' ' . $seminar->getName();
                        }
                    } else {
                        $rec[get_username($event->getAssignUserId())][] = strftime('%x %R', $event->begin) . ' - ' . strftime('%R', $event->end);
                    }
                }
            }

            if ($form->isSended() && count($rec) && $form->getFormFieldValue('message')) {
                $messaging = new Messaging();
                $ok = $messaging->insert_message($form->getFormFieldValue('message'),
                                           array_keys($rec),
                                           $GLOBALS['user']->id,
                                           null,
                                           null,
                                           null,
                                           '',
                                           $form->getFormFieldValue('subject'),
                                           true);
                PageLayout::postMessage(MessageBox::success(sprintf(_("Die Nachricht wurde an %s Nutzer verschickt"), $ok)));
                return $this->redirect(URLHelper::getUrl('resources.php?view=resources'));
            }

            if (!count($rec)) {
                PageLayout::postMessage(MessageBox::error(sprintf(_("Im Zeitraum %s - %s wurden keine Belegungen gefunden!"), strftime('%x', $start_time), strftime('%x', $end_time))));
                $this->no_receiver = true;
            } else {
                $submessage = [];
                foreach ($rec as $username => $slots) {
                    $submessage[] = get_fullname_from_uname($username, 'full_rev_username', true) . ' '. sprintf(_('(%s Belegungen)'), count($slots));
                }
                PageLayout::postMessage(MessageBox::info(sprintf(_("Benachrichtigung an %s Nutzer verschicken"), count($rec)), $submessage, true));
            }
            $this->form = $form;
            $this->response->add_header('X-Title', $title);
    }

    public function export_requestlist_action()
    {
        $data[] = [_('V.-Nummer'), _('Titel'), _('Dozenten'), _('Anfrager'), _('Startsemester'), _('Datum der Erstellung'),
            _('Datum der letzten Änderung'), _('angeforderte Belegungszeiten'), _('gewünschte Raumeigenschaften'), _('angeforderter Raum'),
            _('Teilnehmendenanzahl'), _('Kommentar des Anfragenden')];

        $resources_data = unserialize($_SESSION['resources_data']);

        foreach ($resources_data['requests_working_on'] as $key => $val) {
            if ($resources_data['requests_open'][$val['request_id']] || !$resources_data['skip_closed_requests']) {
                $reqObj = new RoomRequest($val['request_id']);
                $semObj = new Seminar($reqObj->getSeminarId());

                //number and name of course
                if ($semObj->getName() != "") {
                    $no = $semObj->seminar_number;
                    $title = $semObj->getName();
                }
                //lecturer
                $lec = [];
                foreach ($semObj->getMembers('dozent') as $doz) {
                    $lec[] = $doz['fullname'];
                }
                $lec = join(', ', $lec);

                //request created by:
                $rp = get_fullname($reqObj->user_id);

                // start of semester
                $start = SemesterData::getSemesterDataByDate($semObj->semester_start_time);
                $start = $start['name'];

                // issued-date, last modified-date
                $date = strftime('%Y-%m-%d %H:%M:%S', $reqObj->mkdate);
                $dateChanged = strftime('%Y-%m-%d %H:%M:%S', $reqObj->chdate);

                // requested time slots:
                $timeslot = [];
                $dates = $semObj->getGroupedDates($reqObj->getTerminId(), $reqObj->getMetadateId());
                if ($dates['first_event']) {
                    if (is_array($dates['info']) && sizeof($dates['info']) > 0) {
                        foreach ($dates['info'] as $info) {
                            $timeslot[] = $info['export'];
                        }
                    }
                    if ($reqObj->getType() != 'date') {
                        $timeslot[] = _("regelmäßige Buchung ab: ") . strftime('%x', $dates['first_event']);
                    }
                }
                $time = join(', ', $timeslot);

                // room requirements
                $properties = $reqObj->getProperties();
                $size = sizeof($properties);
                $roomReq = '';

                if ($size) {
                    $i = 1;
                    foreach ($properties as $key => $val) {
                        switch ($val["type"]) {
                            case "bool":
                                $roomReq .= $val["name"];
                                break;
                            case "num":
                                $roomReq .= $val["name"] . ": " . $val["state"];
                                break;
                            case "select":
                                $options = explode(";", $val["options"]);
                                foreach ($options as $a) {
                                    if ($val["state"] == $a)
                                        $roomReq .= $a;
                                }
                                break;
                        }
                        if ($i < $size) {
                            $roomReq = $roomReq . ", ";
                        }
                        $i++;
                    }
                }

                // room request
                if ($request_resource_id = $reqObj->getResourceId()) {
                    $resObj = ResourceObject::Factory($request_resource_id);
                    $room = $resObj->getName();
                }

                // participants at the moment
                $people = $semObj->getNumberOfParticipants('total');

                //comment
                $comment = $reqObj->getComment();

                //csv export
                $data[] = [$no, $title, $lec, $rp, $start, $date, $dateChanged, $time, $roomReq, $room, $people, $comment];
            }
        }

        $this->response->add_header('Content-Type', 'text/csv');
        $this->response->add_header('Content-Disposition', 'attachment; ' . encode_header_parameter('filename', _('Anfragenliste') . '.csv'));
        $this->render_text(array_to_csv($data));
    }

    function after_filter($action, $args)
    {
        if (Request::isXhr()) {
            foreach ($this->response->headers as $k => $v) {
                if ($k === 'Location') {
                    $this->response->headers['X-Location'] = $v;
                    unset($this->response->headers['Location']);
                    $this->response->set_status(200);
                    $this->response->body = '';
                }
            }
        }
        parent::after_filter($action, $args);
    }
}
