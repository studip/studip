<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * SingelDate.class.php - Ein (Ex-)Termin
 *
 * Diese Klasse stellt einen einzelnen Eintrag in der Tabelle termine, bzw. ex_termine dar.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
require_once 'lib/dates.inc.php';

class SingleDate
{
    var $termin_id       = '';
    var $date_typ        = 1;
    var $metadate_id     = '';
    var $date            = 0;
    var $end_time        = 0;
    var $mkdate          = 0;
    var $chdate          = 0;
    var $orig_ex         = false;
    var $ex_termin       = false;
    var $range_id        = '';
    var $author_id       = '';
    var $resource_id     = '';
    var $raum            = '';
    var $request_id      = NULL;
    var $requestData     = NULL;
    var $update          = false;
    var $issues          = NULL;
    var $messages        = NULL;
    var $content         = '';
    var $room_request    = NULL;
    var $related_persons = [];
    var $related_groups  = [];

    /**
     * Return the SingleDate instance of the given id
     *
     * @param string      the id of the instance
     * @return SingleDate the SingleDate instance
     */
    function getInstance($singledate_id)
    {
        static $singledate_object_pool;

        if ($singledate_id) {
            if (is_object($singledate_object_pool[$singledate_id]) && $singledate_object_pool[$singledate_id]->getTerminId() == $singledate_id) {
                return $singledate_object_pool[$singledate_id];
            } else {
                $singledate_object_pool[$singledate_id] = new SingleDate($singledate_id);

                return $singledate_object_pool[$singledate_id];
            }
        } else {
            return new SingleDate();
        }

    }

    function __construct($data = '')
    {
        global $user, $id;
        if ($data instanceOf CourseDate || $data instanceof CourseExDate) {
            $single_date_data = $data->toArray();
            $single_date_data['ex_termin'] = $data instanceOf CourseDate ? 0 : 1;
            $single_date_data['resource_id'] = $data->room_booking->resource_id ?: '';
            if ($data instanceOf CourseDate) {
                $single_date_data['related_persons'] = $data->dozenten->pluck('user_id');
                $single_date_data['related_groups'] = $data->statusgruppen->pluck('statusgruppe_id');
            }
            $this->fillValuesFromArray($single_date_data);
        } else {
            if (is_array($data)) {
                if ($data['termin_id']) $termin_id = $data['termin_id'];
                if ($data['seminar_id']) $id = $data['seminar_id'];
            } else {
                $termin_id = $data;
            }
            if ($termin_id != '') {
                $this->termin_id = $termin_id;
                $this->update = true;
                $this->restore();
            } else {
                $this->termin_id = md5(uniqid('SingleDate', 1));
                $this->author_id = $user->id;
                $this->range_id = $id;
                $this->mkdate = time();
                $this->chdate = time();
                $this->update = false;
            }
        }
    }


    function getStartTime()
    {
        return $this->date;
    }

    function setTime($start, $end)
    {
        if (($start == 0) || ($end == 0)) return false;

        if (($this->date != $start) || ($this->end_time != $end)) {
            // validate the passed variables: they have to be ints and $start
            // has to be smaller than $end
            if ($this->validate($start, $end)) {
                $before = $this->toString();

                // if the time-span has been shortened, keep the room-booking,
                // otherwise remove it.
                if ($this->resource_id) {
                    if ($start >= $this->date && $end <= $this->end_time) {
                        //Shrink the booking.
                        if ($assign_id = SingleDateDB::getAssignID($this->termin_id)) {
                            $assign_object = new ResourceBooking($assign_id);
                            $assign_object->resource_id = $this->resource_id;
                            $assign_object->begin = $start;
                            $assign_object->end = $end;
                            $assign_object->repeat_end = $end;
                            $assign_object->store();
                        }
                    } else {
                        $this->killAssign();
                    }
                }

                $this->date = $start;
                $this->end_time = $end;

                $after = $this->toString();
                // logging
                if ($before) {
                    StudipLog::log("SINGLEDATE_CHANGE_TIME", $this->range_id, $before, $before . ' -> ' . $after);
                } else {
                    StudipLog::log("SEM_ADD_SINGLEDATE", $this->range_id, $after);
                }

                return true;
            }

            return false;
        }

        return false;
    }

    function getEndTime()
    {
        return $this->end_time;
    }

    function setComment($comment)
    {
        $this->content = $comment;
    }

    function getComment()
    {
        if (!$this->isExTermin()) return '';

        return $this->content;
    }

    function getMetaDateID()
    {
        return $this->metadate_id;
    }

    function setMetaDateID($id)
    {
        if ($id != '') {
            $this->metadate_id = $id;

            return true;
        } else {
            $this->metadate_id = 0;

            return false;
        }
    }

    function getRangeID()
    {
        return $this->range_id;
    }

    function setDateType($typ)
    {
        $this->date_typ = $typ;

        return true;
    }

    function getDateType()
    {
        return $this->date_typ;
    }

    function getTypeName()
    {
        global $TERMIN_TYP;

        return $TERMIN_TYP[$this->date_typ]['name'];
    }

    function getAuthorID()
    {
        return $this->author_id;
    }

    function getChDate()
    {
        return $this->chdate;
    }

    function getMkDate()
    {
        return $this->mkdate;
    }

    function setSeminarID($seminar_id)
    {
        $this->range_id = $seminar_id;
    }

    function getSeminarID()
    {
        return $this->range_id;
    }

    function getSingleDateID()
    {
        return $this->termin_id;
    }

    function getResourceID()
    {
        return $this->resource_id;
    }

    function getTerminID()
    {
        return $this->termin_id;
    }

    function getFreeRoomText()
    {
        return $this->raum;
    }

    function setFreeRoomText($freeRoomText)
    {
        $this->raum = $freeRoomText;
    }

    function getCycleID()
    {
        return $this->metadate_id;
    }

    function killIssue()
    {
        // We delete the issue, cause there is no chance anybody can get to it without the expert view
        if (!Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) {
            if ($issue_ids = $this->getIssueIDs()) {
                foreach ($issue_ids as $issue_id) {
                    // delete this issue
                    unset($this->issues[$issue_id]);
                    $issue = new Issue(['issue_id' => $issue_id]);
                    $issue->delete();
                }
            }
        }
    }

    function delete($keepIssues = false)
    {
        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/' . $this->range_id);

        $this->chdate = time();
        $this->killAssign();
        if (!$keepIssues) {
            $this->killIssue();
        }

        return SingleDateDB::deleteSingleDate($this->termin_id, $this->ex_termin);
    }

    function store()
    {

        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/' . $this->range_id);

        $this->chdate = time();
        if ($this->ex_termin) {
            $this->killAssign();
        }

        // date_typ = 0 defaults to TERMIN_TYP[1] because there never exists one with zero
        if (!$this->date_typ) $this->date_typ = 1;

        if ($this->orig_ex != $this->ex_termin) {
            SingleDateDB::deleteSingleDate($this->termin_id, $this->orig_ex);
        }

        return SingleDateDB::storeSingleDate($this);
    }

    function restore()
    {
        if (!($data = SingleDateDB::restoreSingleDate($this->termin_id))) {
            return false;
        }
        $this->fillValuesFromArray($data);

        return true;
    }

    function setExTermin($ex)
    {
        if ($ex != $this->ex_termin) {
            $this->update = false;
            $this->ex_termin = $ex;

            return true;
        }

        return false;
    }

    function isExTermin()
    {
        return $this->ex_termin;
    }

    function isPresence()
    {
        return $GLOBALS['TERMIN_TYP'][$this->date_typ]['sitzung'] ? true : false;
    }

    function isUpdate()
    {
        return $this->update;
    }

    function isHoliday()
    {
        foreach (SemesterHoliday::getAll() as $val) {
            if (($val['beginn'] <= $this->date) && ($val['ende'] >= $this->end_time)) {
                $name = $val['name'];
            }
        }

        if (!$name) {
            $holy_type = holiday($this->date);
            $name = $holy_type['name'];
        }

        if ($name) {
            return $name;
        } else {
            return false;
        }
    }

    function fillValuesFromArray($daten)
    {
        $this->metadate_id = $daten['metadate_id'];
        $this->termin_id = $daten['termin_id'];
        if ($daten['date_typ'] != 0) {
            $this->date_typ = $daten['date_typ'];
        } else {
            // if no date_typ is specified it defaults to 1
            $this->date_typ = 1;
        }
        $this->date = $daten['date'];
        $this->end_time = $daten['end_time'];
        $this->mkdate = $daten['mkdate'];
        $this->chdate = $daten ['chdate'];
        $this->ex_termin = $daten['ex_termin'];
        $this->orig_ex = $daten['ex_termin'];
        $this->range_id = $daten['range_id'];
        $this->author_id = $daten['autor_id'];
        $this->resource_id = $daten['resource_id'];
        $this->raum = $daten['raum'];
        $this->content = $daten['content'];
        $this->update = true;
        $this->related_persons = is_array($daten['related_persons']) ? $daten['related_persons'] : [];
        $this->related_groups = is_array($daten['related_groups']) ? $daten['related_groups'] : [];

        return true;
    }

    function toString()
    {
        $end_hours = strtotime(strftime('%H:%M', $this->end_time));
        $start_hours = strtotime(strftime('%H:%M', $this->date));
        if (!$this->date) {
            return null;
        } elseif ((($end_hours - $start_hours) / 60 / 60) > 23) {
            return sprintf('%s , %s (%s)',strftime('%a', $this->date),
                strftime('%d.%m.%Y', $this->date),
                _('ganztägig'));
        } else {
            return sprintf('%s., %s - %s',strftime('%a', $this->date),
                strftime('%d.%m.%Y %H:%M', $this->date),
                strftime('%H:%M', $this->end_time));
        }
    }

    function bookRoom($room_id, $preparation_time = 0)
    {
        if ($this->ex_termin || !$room_id) {
            return false;
        }

        // create a resource-object of the passed room
        $room = Room::find($room_id);

        // there is no room with the passed id
        if (!$room) {
            return false;
        }

        // check permissions (is current user allowed to book the passed room?)
        if (!$room->userHasBookingRights(User::findCurrent())) {
            return false;
        }

        // clear the freetext-field, if we book a room
        $this->setFreeRoomText('');
        $this->store();

        //If there is already a room assigned, "change" the booking.
        //Otherwise create a new one.
        if ($this->resource_id != '') {
            $this->changeAssign($room, $preparation_time);
        } else {
            $this->insertAssign($room, $preparation_time);
        }

        return $room;
    }


    /**
     * This method converts overlap data about an overlapping booking
     * to a string that can be used to output overlap information to the user.
     * Only one overlap is converted by this method. For multiple overlaps
     * this method must be called multiple times.
     *
     * @param ResourceBooking $booking The overlapping booking.
     *
     * @return string A string representation of the overlap.
     */
    protected function getOverlapMessage(ResourceBooking $booking)
    {
        $message = '';

        if ($booking->booking_type == '2') {
            $message .= sprintf(
                _('Vom %1$s, %2$s Uhr bis zum %3$s, %4$s Uhr (Sperrzeit)') . "\n",
                date("d.m.Y", $booking->begin),
                date("H:i", $booking->begin),
                date("d.m.Y", $booking->end),
                date("H:i", $booking->end)
            );
        } else {
            $course = Course::find($booking->course_id);

            if ($course) {
                $user_has_permissions = $GLOBALS['perm']->have_studip_perm(
                    'dozent',
                    $course->id,
                    $GLOBALS['user']->id
                );
                if ($user_has_permissions) {
                    $course_link = URLHelper::getLink(
                        'dispatch.php/course/timesrooms/index',
                        [
                            'cid' => $course->id
                        ]
                    );
                    $message .= sprintf(
                        _('Am %1$s von %2$s bis %3$s Uhr durch Veranstaltung %4$s') . "\n",
                        date('d.m.Y', $booking->begin),
                        date('H:i', $booking->begin),
                        date('H:i', $booking->end),
                        sprintf(
                            '<a href="%1$s">%2$s</a>',
                            $course_link,
                            $course->name
                        )
                    );
                } else {
                    $course_link = URLHelper::getLink(
                        'dispatch.php/course/details',
                        [
                            'sem_id' => $course->id
                        ]
                    );
                    $message .= sprintf(
                        _('Am %1$s von %2$s bis %3$s Uhr durch Veranstaltung %4$s') . "\n",
                        date('d.m.Y', $booking->begin),
                        date('H:i', $booking->begin),
                        date('H:i', $booking->end),
                        sprintf(
                            '<a href="%1$s">%2$s</a>',
                            $course_link,
                            $course->name
                        )
                    );
                }
            } else {
                $message .= sprintf(
                    _('Am %1$s von %2$s bis %3$s Uhr belegt von "%4$s"') . "\n",
                    date("d.m.Y", $booking->begin),
                    date("H:i", $booking->begin),
                    date("H:i", $booking->end),
                    htmlready($booking->description)
                );
            }
        }

        return $message;
    }


    private function insertAssign(Room $room, $preparation_time = 0)
    {
        $begin = new DateTime();
        $begin->setTimestamp($this->date);
        $end = new DateTime();
        $end->setTimestamp($this->end_time);

        //If the following code is executed a new room booking can be created:
        try {
            $booking = $room->createBooking(
                User::findCurrent(),
                $this->termin_id,
                [
                    [
                        'begin' => $begin,
                        'end' => $end
                    ]
                ],
                null,
                0,
                null,
                $preparation_time * 60
            );
            if ($booking instanceof ResourceBooking) {
                $booking->deleteOverlappingReservations();
                $room_link_string = sprintf(
                    '<a href="%1$s" data-dialog="1">%2$s</a>',
                    $room->getActionLink(),
                    htmlReady($room->name)
                );

                $this->assign_id = $booking->id;
                SingleDateDB::storeSingleDate($this);
                $msg = sprintf(
                    _('Für den Termin %1$s wurde der Raum %2$s gebucht.'),
                    $this->toString(),
                    $room_link_string
                );
                $this->messages['success'][] = $msg;
            }
        } catch (ResourceBookingRangeException $e) {
            $this->messages['error'][] = _('Fehler beim Verknüpfen der Raumbelegung mit dem Einzeltermin!');
            return false;
        } catch (ResourceBookingOverlapException $e) {
            $error_message = sprintf(
                _('Für den Termin %1$s konnte der Raum %2$s nicht gebucht werden, da es Überschneidungen mit folgenden Terminen gibt:'),
                $this->toString(),
                $room->name
                ) . '<br>';
            $overlapping_bookings = array_merge(
                $room->getResourceBookings($begin, $end),
                $room->getResourceLocks($begin, $end)
            );
            foreach ($overlapping_bookings as $overlapping_booking) {
                $course_link = null;
                if ($overlapping_booking->course) {
                    $user_is_lecturer = $GLOBALS['perm']->have_studip_perm(
                        'dozent',
                        $overlapping_booking->course->id,
                        $GLOBALS['user']->id
                    );
                    if ($user_is_lecturer) {
                        $course_link = URLHelper::getLink(
                            'dispatch.php/course/timesrooms/index',
                            [
                                'cid' => $overlapping_booking->course->id
                            ]
                        );
                    }
                }

                $error_message .= $this->getOverlapMessage(
                    $overlapping_booking
                );
            }
            $this->messages['error'][] = $error_message;
            return false;
        } catch (ResourcePermissionException $e) {
            $this->messages['error'][] = $e->getMessage();
            return false;
        } catch (ResourceBookingException $e) {
            $this->messages['error'][] = $e->getMessage();
            return false;
        }

        return true;
    }


    private function changeAssign(Room $room, $preparation_time = 0)
    {
        $max_preparation_time = (Config::get()->RESOURCES_MAX_PREPARATION_TIME);
        if ($preparation_time > $max_preparation_time) {
            $this->messages['error'][] = sprintf(
                _('Die eingegebene Rüstzeit überschreitet das erlaubte Maximum von %d Minuten!'),
                $max_preparation_time
            );
            return false;
        }
        if ($assign_id = SingleDateDB::getAssignID($this->termin_id)) {
            $changeAssign = new ResourceBooking($assign_id);
            $changeAssign->resource_id = $room->id;
            $changeAssign->range_id = $this->termin_id;
            $changeAssign->begin = $this->date;
            $changeAssign->end = $this->end_time;
            $changeAssign->repeat_end = $this->end_time;
            $changeAssign->repeat_quantity = 0;
            $changeAssign->repetition_interval = '';
            if ($preparation_time > 0) {
                $changeAssign->preparation_time = $preparation_time * 60;
            }

            $room_link_string = sprintf(
                '<a href="%1$s" data-dialog="1">%2$s</a>',
                $room->getActionLink(),
                htmlReady($room->name)
            );

            $overlaps = $changeAssign->getOverlappingBookings();
            if (is_array($overlaps) && (sizeof($overlaps) > 0)) {
                $msg = sprintf(
                    _('Für den Termin %1$s konnte der Raum %2$s nicht gebucht werden, da es Überschneidungen mit folgenden Terminen gibt:'),
                    $this->toString(),
                    $room_link_string
                    ) . '<br>';
                foreach ($overlaps as $overlap) {
                    $msg .= $this->getOverlapMessage($overlap);
                }
                $this->messages['error'][] = $msg;

                return false;
            }

            $this->resource_id = $room->id;
            try {
                $changeAssign->store();
            } catch (ResourceBookingOverlapException $e) {
                $room = $changeAssign->resource->getDerivedClassInstance();
                if($room instanceof Room) {
                    $room_text = $link = sprintf(
                        '<a href="%1$s" data-dialog="1">%2$s</a>',
                        $room->getActionLink(),
                        htmlReady($room->name)
                    );
                } else {
                    $room_text = $changeAssign->resource->name;
                }
                $this->messages['error'][] = sprintf(
                    _('%1$s: Die Buchung vom %2$s bis %3$s konnte wegen Überlappungen nicht gespeichert werden: %4$s'),
                    $room_text,
                    date('d.m.Y H:i', $changeAssign->begin),
                    date('H:i', $changeAssign->end),
                    $e->getMessage()
                );
            }

            $msg = sprintf(
                _('Für den Termin %1$s wurde der Raum %2$s gebucht.'),
                $this->toString(),
                $room_link_string
            );
            $this->messages['success'][] = $msg;

            return true;
        }

        return false;
    }

    function killAssign()
    {
        $this->resource_id = '';
        if ($assign_id = SingleDateDB::getAssignID($this->termin_id)) {
            $assign_object = new ResourceBooking($assign_id);
            $assign_object->delete();
        }
    }


    /**
     * Returns the room name for this SingleDate object.
     *
     * @returns string The room name.
     */
    public function getRoom()
    {
        if (!$this->resource_id) {
            return null;
        } else {
            $room = Room::find($this->resource_id);

            return $room->name;
        }
    }


    function readIssueIDs()
    {
        if (!$this->issues) {
            if ($data = SingleDateDB::getIssueIDs($this->termin_id)) {
                foreach ($data as $val) {
                    $this->issues[$val['issue_id']] = $val['issue_id'];
                }
            }
        }

        return true;
    }

    function getIssueIDs()
    {
        $this->readIssueIDs();

        return $this->issues;
    }

    function addIssueID($issue_id)
    {
        $this->readIssueIDs();
        $this->issues[$issue_id] = $issue_id;

        return true;
    }

    function deleteIssueID($issue_id)
    {
        $this->readIssueIDs();
        unset($this->issues[$issue_id]);
        SingleDateDB::deleteIssueID($issue_id, $this->termin_id);

        return true;
    }

    function getMessages()
    {
        $temp = $this->messages;
        $this->messages = NULL;

        return $temp;
    }

    // checks, if the single-date has plausible values
    function validate($start = 0, $end = 0)
    {
        if ($start == 0) {
            $start = $this->date;
        }
        if ($end == 0) {
            $end = $this->end_time;
        }

        if ($start < 100000) return false;
        if ($end < 100000) return false;
        if ($start > $end) {
            $this->messages['error'][] = _("Die Endzeitpunkt darf nicht vor dem Anfangszeitpunkt liegen!");

            return false;
        }

        return true;
    }


    /**
     * returns a html representation of the date
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the html-representation of the date
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    function getDatesHTML($params = [])
    {
        $template = $GLOBALS['template_factory']->open('dates/date_html.php');
        $template->set_attributes($params);

        return $this->getDatesTemplate($template);
    }

    /**
     * returns a representation without html of the date
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the representation of the date without html
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    function getDatesExport($params = [])
    {
        $template = $GLOBALS['template_factory']->open('dates/date_export.php');
        $params['link'] = false;
        $template->set_attributes($params);

        return $this->getDatesTemplate($template);
    }

    /**
     * returns a xml-representation of the date
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the xml-representation of the date
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    function getDatesXML($params = [])
    {
        $template = $GLOBALS['template_factory']->open('dates/date_xml.php');
        $template->set_attributes($params);

        return $this->getDatesTemplate($template);
    }

    /**
     * returns a representation of the date with a specifiable template
     *
     * @param  mixed  this can be a template-object or a string pointing to a template in path_to_studip/templates
     * @return  string  the template output of the date
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    function getDatesTemplate($template)
    {
        if (!$template instanceof Flexi_Template && is_string($template)) {
            $template = $GLOBALS['template_factory']->open($template);
        }

        $template->set_attribute('date', $this);

        return $template->render();
    }

    /**
     * adds a given user_id as a related person to the date
     * @param string $user_id user_id from auth_user_md5 of the person to be added
     */
    public function addRelatedPerson($user_id)
    {
        $this->related_persons[] = $user_id;
        $this->related_persons = array_unique($this->related_persons);
    }

    /**
     * unsets a given user_id from the array of related persons
     * @param string $user_id user_id from auth_user_md5 of the person to be added
     */
    public function deleteRelatedPerson($user_id)
    {
        if (!$this->related_persons) {
            $sem = Seminar::getInstance($this->getSeminarID());
            $this->related_persons = array_keys($sem->getMembers('dozent'));
        }
        foreach ($this->related_persons as $key => $related_person) {
            if ($related_person === $user_id) {
                unset($this->related_persons[$key]);
            }
        }
    }

    /**
     * gets all user_ids of related persons of this date
     * @return array of user_ids
     */
    public function getRelatedPersons()
    {
        if (count($this->related_persons)) {
            return $this->related_persons;
        } else {
            $sem = Seminar::getInstance($this->getSeminarID());

            return array_keys($sem->getMembers('dozent'));
        }
    }

    /**
     * clears all related persons (in the interface this means that all dozents are
     * marked as related to the date)
     */
    public function clearRelatedPersons()
    {
        $this->related_persons = [];
    }

    /**
     * adds a given statusgruppe_id as a related group to the date
     * @param string $statusgruppe_id statusgruppe_id from statusgruppen of the group to be added
     */
    public function addRelatedGroup($statusgruppe_id)
    {
        $this->related_groups[] = $statusgruppe_id;
        $this->related_groups = array_unique($this->related_groups);
    }

    /**
     * unsets a given statusgruppe_id from the array of related statusgruppen
     * @param string $statusgruppe_id statusgruppe_id from statusgruppen of the group to be removed
     */
    public function deleteRelatedGroup($statusgruppe_id)
    {
        if (!$this->related_groups) {
            $groups = Statusgruppen::findBySeminar_id($this->getSeminarID());
            $this->related_groups = array_map(function ($g) {
                return $g->getId();
            }, $groups);
        }
        foreach ($this->related_groups as $key => $related_group) {
            if ($related_group === $statusgruppe_id) {
                unset($this->related_groups[$key]);
            }
        }
    }

    /**
     * gets all statusgruppe_ids of related groups of this date
     * @return array of statusgruppe_ids
     */
    public function getRelatedGroups()
    {
        if (count($this->related_groups)) {
            return $this->related_groups;
        } else {
            $groups = Statusgruppen::findBySeminar_id($this->getSeminarID());

            return array_map(function ($g) {
                return $g->getId();
            }, $groups);
        }
    }

    /**
     * clears all related groups
     */
    public function clearRelatedGroups()
    {
        $this->related_groups = [];
        $this->messages['success'][] = _('Die beteiligten Gruppen wurden zurückgesetzt!');
    }
}
