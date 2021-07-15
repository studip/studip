<?php

/**
 * booking.php - contains Resources_BookingController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_BookingController contains functionality for resource bookings.
 */
class Resources_BookingController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!in_array($action, ['add', 'add_from_request'])) {
            $this->booking_id = $args[0];
            $this->booking = ResourceBooking::find($this->booking_id);
            if (!$this->booking) {
                PageLayout::postError(
                    _('Die angegebene Buchung wurde nicht gefunden!')
                );
                return;
            }

            if (!$this->booking->resource) {
                PageLayout::postError(
                    _('Zur angegebenen Buchung ist kein Raum bzw. keine Ressource zugeordnet!')
                );
                return;
            }

            $this->resource = $this->booking->resource->getDerivedClassInstance();
            $this->current_user = User::findCurrent();
            if (($this->booking->booking_user_id != $this->current_user->id)
                && (!$this->resource->bookingPlanVisibleForUser($this->current_user))
                && ($action != 'index')) {
                    throw new AccessDeniedException();
            }
            $this->user_has_autor_perms = $this->resource->userHasPermission(
                $this->current_user,
                'autor'
            );
        }
    }


    public function index_action($booking_id = null)
    {
        PageLayout::setTitle(_('Details zur Buchung'));

        $this->user_has_user_perms = $this->booking->resource->userHasPermission(
            $this->current_user,
            'user'
        ) || $GLOBALS['perm']->have_perm('root');

        if (!$this->user_has_user_perms) {
            $resource = $this->booking->resource->getDerivedClassInstance();
            //The user has no permissions on the resource.
            //But if it is a room resource, we must check if the booking plan
            //is public. In such a case, viewing of the booking can be granted.
            if (!($resource->bookingPlanVisibleForUser($this->current_user))) {
                throw new AccessDeniedException();
            }
        }
        //Only those users who have "tutor" permissions or who have
        //"autor" permissions and created the request may edit the
        //internal comment of the booking.
        $this->make_comment_editable = $this->booking->userIsOwner(
            $this->current_user
        ) || $this->booking->resource->userHasPermission(
                $this->current_user,
                'tutor'
            );
        $this->show_internal_comment = (
            (
                $this->booking->resource->userHasPermission($this->current_user, 'user')
                && $this->booking->internal_comment
            )
            || $this->make_comment_editable
        );

        if ($this->make_comment_editable && Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->booking->internal_comment = Request::get('internal_comment');
            if ($this->booking->isDirty()) {
                $successfully_stored = $this->booking->store();
            } else {
                $successfully_stored = true;
            }

            if ($successfully_stored) {
                PageLayout::postSuccess(
                    _('Der Kommentar zur Buchung wurde gespeichert!')
                );
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern des Kommentars zur Buchung!')
                );
            }
        }
    }


    /**
     * This action handles the creation of an booking by specifying
     * a resource and a resource request.
     */
    public function add_from_request_action($resource_id = null, $request_id = null)
    {
        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die angegebene Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->request = ResourceRequest::find($request_id);
        if (!$this->request) {
            PageLayout::postError(
                _('Die angegebene Ressourcenanfrage wurde nicht gefunden!')
            );
            return;
        }

        $this->resource = $this->resource->getDerivedClassInstance();
        $this->max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;
        $this->preparation_time = intval($this->request->preparation_time / 60);
        $this->notify_lecturers = '1';
        $this->description = '';
        $this->internal_comment = '';

        $this->show_form = true;
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->preparation_time = Request::int('preparation_time');
            $this->notify_lecturers = Request::get('notify_lecturers');
            $this->description = Request::get('description');
            $this->internal_comment = Request::get('internal_comment');

            if ($this->preparation_time > $this->max_preparation_time) {
                PageLayout::postError(
                    sprintf(
                        _('Es sind maximal %d Minuten für die Rüstzeit erlaubt!'),
                        $this->max_preparation_time
                    )
                );
                return;
            }
            try {
                $bookings = $this->resource->createBookingFromRequest(
                    User::findCurrent(),
                    $this->request,
                    $this->preparation_time * 60,
                    $this->description,
                    $this->internal_comment,
                    0,
                    true,
                    $this->notify_lecturers
                );

                if ($bookings && is_array($bookings)) {
                    $this->show_form = false;
                    PageLayout::postSuccess(
                        _('Die Buchung wurde vorgenommen und die Anfrage als bearbeitet markiert!')
                    );
                } else {
                    PageLayout::postError(
                        _('Ein Fehler trat auf beim Erstellen der Buchung!'),
                        [
                            intval(is_array($bookings)),
                            intval($bookings)
                        ]
                    );
                }
            } catch (Exception $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
        }
    }


    protected function sendLockBookingMail(ResourceBooking $booking)
    {
        if ($booking->booking_type != '2') {
            return false;
        }

        //Get all usernames of persons that have at least autor permissions
        //on the resource of the lock booking:


        //chdate is used to determine the temporary permissions
        //that were valid on the last change of the lock booking.
        $users = User::findBySql(
            "user_id IN (
                SELECT user_id FROM resource_permissions
                WHERE resource_id = :resource_id
                AND perms IN ('autor', 'tutor', 'admin')
                UNION
                SELECT user_id FROM resource_temporary_permissions
                WHERE resource_id = :resource_id
                AND (begin <= :chdate) AND (end >= :chdate)
                AND perms IN ('autor', 'tutor', 'admin')
            )
            GROUP BY user_id",
            [
                'resource_id' => $booking->resource_id,
                'chdate' => $booking->chdate
            ]
        );

        if (!$users) {
            //Nothing to do.
            return true;
        }

        $template_factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/locale/'
        );

        $derived_resource = $booking->resource->getDerivedClassInstance();
        $system_lang = $_SESSION['_language'];

        foreach ($users as $user) {
            $user_lang = getUserLanguage($user->id);
            $lang_path = getUserLanguagePath($user->id);

            $mail_template = $template_factory->open(
                $lang_path . '/LC_MAILS/lock_booking_notification.php'
            );
            $mail_template->set_attribute('booking', $booking);
            $mail_template->set_attribute('derived_resource', $derived_resource);
            $mail_text = $mail_template->render();

            setLocaleEnv($user_lang);

            $mail_title = sprintf(
                _('%1$s: Neue Sperrbuchung von %2$s bis %3$s'),
                $derived_resource->getFullName(),
                date('d.m.Y H:i', $booking->begin),
                date('d.m.Y H:i', $booking->end)
            );

            Message::send(
                '____%system%____',
                $user->username,
                $mail_title,
                $mail_text
            );

            setLocaleEnv($system_lang);
        }
    }


    /**
     * An internal helper method for assigning resources and collecting
     * the results.
     *
     * @param ResourceBooking|null $booking Either a ResourceBooking
     *     object or null. If a ResourceBooking object is specified
     *     its content will be updated. Otherwise a new object will be created.
     *
     * @returns array An associative array with the following structure:
     * [
     *     'errors' => Error messages related to booking the resource.
     *     'room_part_errors' => Error messages related to the booking of other resource parts.
     *     'bookings' => The created bookings.
     * ]
     */
    protected function assignResourceAndCollectResults(
        $booking,
        Resource $resource,
        User $user,
        DateTime $begin,
        DateTime $end,
        DateTime $repetition_end,
        $preparation_time = 0,
        $description = '',
        $comment = '',
        $booking_type = '0',
        $assigned_user = null,
        $repetition_interval = null,
        $notification_enabled = false,
        $included_room_parts = [],
        $overwrite_bookings = false
    )
    {
        $result = [
            'bookings' => [],
            'errors' => [],
            'room_part_errors' => []
        ];

        $bookings_to_update = [];

        if ($booking instanceof ResourceBooking) {
            //Update the existing booking:
            $bookings_to_update[] = $booking;
            if ($included_room_parts) {
                //Check if there are identical bookings in the other room parts:
                foreach ($included_room_parts as $room_part) {
                    if (($booking_type == '2') && $overwrite_bookings) {
                        //Create a new booking:
                        $room_part->createBooking(
                            $user,
                            (
                                $assigned_user instanceof User
                                ? $assigned_user->id
                                : ''
                            ),
                            [
                                [
                                    'begin' => $begin,
                                    'end' => $end
                                ]
                            ],
                            $repetition_interval,
                            0,
                            ($repetition_interval ? $repetition_end : null),
                            $preparation_time,
                            $description,
                            $comment,
                            $booking_type,
                            (
                                $booking_type == '2'
                                ? $overwrite_bookings
                                : false
                            )
                        );
                    } else {
                        $matching_bookings = ResourceBooking::findByResourceAndTimeRanges(
                            $room_part,
                            [
                                [
                                    'begin' => $booking->begin,
                                    'end' => $booking->end
                                ]
                            ],
                            [$booking->booking_type],
                            [$booking->id]
                        );
                        if ($matching_bookings) {
                            $bookings_to_update = array_merge(
                                $bookings_to_update,
                                $matching_bookings
                            );
                        } else {
                            //Create a new booking:
                            $room_part->createBooking(
                                $user,
                                (
                                    $assigned_user instanceof User
                                    ? $assigned_user->id
                                    : ''
                                ),
                                [
                                    [
                                        'begin' => $begin,
                                        'end' => $end
                                    ]
                                ],
                                $repetition_interval,
                                0,
                                ($repetition_interval ? $repetition_end : null),
                                $preparation_time,
                                $description,
                                $comment,
                                $booking_type,
                                (
                                    $booking_type == '2'
                                    ? $overwrite_bookings
                                    : false
                                )
                            );
                        }
                    }
                }
            }
            foreach ($bookings_to_update as $a) {
                $a->begin = $begin->getTimestamp();
                $a->end = $end->getTimestamp();
                $a->preparation_time = $preparation_time;
                $a->description = $description;
                $a->internal_comment = $comment;
                $a->booking_type = $booking_type;
                if ($assigned_user instanceof User) {
                    $a->range_id = $assigned_user->id;
                } else {
                    $a->range_id = '';
                }
                if ($repetition_interval instanceof DateInterval) {
                    if ($repetition_end instanceof DateTime) {
                        $a->repeat_end = $repetition_end->getTimestamp();
                    } elseif ($repetition_end) {
                        $a->repeat_end = $repetition_end;
                    }
                    $a->repetition_interval = $repetition_interval->format('P%YY%MM%DD');
                    $a->repeat_end = $repetition_end->getTimestamp();
                }

                try {
                    //Enable overwriting of bookings if it is a lock booking:
                    $a->store(
                        $booking_type == '2'
                        ? $overwrite_bookings
                        : false
                    );
                    $result['bookings'][] = $a;
                } catch (Exception $e) {
                    $result['errors'] = [$e->getMessage()];
                }
            }
        } else {
            //Create a new booking:
            try {
                //Note: Do not multiply the preparation time
                //by 60 since this is already done in
                //the assign action!
                $booking = $resource->createBooking(
                    $user,
                    $assigned_user instanceof User
                        ? $assigned_user->id
                        : ''
                    ,
                    [
                        [
                            'begin' => $begin,
                            'end' => $end
                        ]
                    ],
                    $repetition_interval,
                    0,
                    ($repetition_interval ? $repetition_end : null),
                    $preparation_time,
                    $description,
                    $comment,
                    $booking_type,
                    (
                        $booking_type == '2'
                        ? $overwrite_bookings
                        : false
                    )
                );
                $result['bookings'] = [$booking];
            } catch (Exception $e) {
                $result['errors'] = [$e->getMessage()];
            }

            if ($booking && ($booking_type == '2') && $notification_enabled) {
                $this->sendLockBookingMail($booking);
            }

            //Check if the other room parts are available:
            if ($included_room_parts) {
                $room_part_errors = [];
                foreach ($included_room_parts as $room_part) {
                    if ($room_part instanceof Room) {
                        try {
                            //Note: Do not multiply the preparation time
                            //by 60 since this is already done in
                            //the assign action!
                            $room_part->createBooking(
                                $user,
                                (
                                    $assigned_user instanceof User
                                    ? $assigned_user->id
                                    : ''
                                ),
                                [
                                    [
                                        'begin' => $begin,
                                        'end' => $end
                                    ]
                                ],
                                $repetition_interval,
                                0,
                                ($repetition_interval ? $repetition_end : null),
                                $preparation_time,
                                $description,
                                $comment,
                                $booking_type,
                                (
                                    $booking_type == '2'
                                    ? $overwrite_bookings
                                    : false
                                )
                            );
                        } catch (ResourceBookingOverlapException $e) {
                            $room_part_errors[] = sprintf(
                                _('Raum %1$s: Der Teilraum %2$s ist im Zeitbereich von %3$s bis %4$s nicht verfügbar und kann daher nicht gebucht werden!'),
                                htmlReady($resource->name),
                                htmlReady($room_part->name),
                                $this->begin->format('d.m.Y H:i'),
                                $this->end->format('d.m.Y H:i')
                            );
                        } catch (Exception $e) {
                            $result['room_part_errors'][] = $e->getMessage();
                        }
                    }
                }
                $result['room_part_errors'] = $room_part_errors;
            }
        }

        return $result;
    }


    protected function displayAssignResultMessages(
        $booking_type = 0,
        $booking_count = 0,
        $errors = [],
        $room_part_errors = []
    )
    {
        if ($booking_count > 0) {
            if ($errors) {
                //Bookings could not be saved.
                if ($booking_type == '1') {
                    PageLayout::postWarning(
                        _('Es konnten nicht alle Reservierungen gespeichert werden!'),
                        $errors
                    );
                } elseif ($booking_type == '2') {
                    PageLayout::postWarning(
                        _('Es konnten nicht alle Sperrbuchungen gespeichert werden!'),
                        $errors
                    );
                } else {
                    PageLayout::postWarning(
                        _('Es konnten nicht alle Buchungen gespeichert werden!'),
                        $errors
                    );
                }
            } elseif ($room_part_errors) {
                //Room parts could not be assigned.
                if ($booking_type == '1') {
                    $text = ngettext(
                        'Die Reservierung wurde gespeichert, aber es traten Fehler beim Reservieren der anderen Raumteile auf:',
                        'Die Reservierungen wurden gespeichert, aber es traten Fehler beim Reservieren der anderen Raumteile auf:',
                        $booking_count
                    );
                } elseif ($booking_type == '2') {
                    $text = ngettext(
                        'Die Sperrbuchung wurde gespeichert, aber es traten Fehler beim Sperren der anderen Raumteile auf:',
                        'Die Sperrbuchungen wurden gespeichert, aber es traten Fehler beim Sperren der anderen Raumteile auf:',
                        $booking_count
                    );
                } else {
                    $text = ngettext(
                        'Die Buchung wurde gespeichert, aber es traten Fehler beim Buchen der anderen Raumteile auf:',
                        'Die Buchungen wurden gespeichert, aber es traten Fehler beim Buchen der anderen Raumteile auf:',
                        $booking_count
                    );
                }
                array_walk($room_part_errors, function(&$item){$item = htmlReady($item);});
                PageLayout::postWarning(
                    $text,
                    $room_part_errors
                );
            } else {
                //Everything went fine:
                if ($booking_type == '1') {
                    PageLayout::postSuccess(
                        ngettext(
                            'Die Reservierung wurde gespeichert.',
                            'Die Reservierungen wurden gespeichert.',
                            $booking_count
                        )
                    );
                } elseif ($booking_type == '2') {
                    PageLayout::postSuccess(
                        ngettext(
                            'Die Sperrbuchung wurde gespeichert.',
                            'Die Sperrbuchungen wurden gespeichert.',
                            $booking_count
                        )
                    );
                } else {
                    PageLayout::postSuccess(
                        ngettext(
                            'Die Buchung wurde gespeichert.',
                            'Die Buchungen wurden gespeichert.',
                            $booking_count
                        )
                    );
                }
            }
        } elseif ($errors) {
            array_walk($errors, function (&$item){$item = htmlReady($item);});
             PageLayout::postError(
                 _('Die folgenden Fehler traten beim Speichern der Buchungen auf:'),
                 $errors
             );
        }
    }


    /**
     * This action handles assigning, reserving and locking a resource
     * without having to create a resource request before (direct booking).
     */
    public function addEditHandler($mode = 'edit')
    {
        $this->current_user = User::findCurrent();

        $only_one_room = null;

        if (Request::submitted('semester_id')) {
            URLHelper::addLinkParam('semester_id', Request::get('semester_id'));
        } elseif ($this->booking) {
            $booking_semester = Semester::findByTimestamp($this->booking->begin);
            if ($booking_semester)  {
                URLHelper::addLinkParam('semester_id' , $booking_semester->id);
            }
        }
        if (Request::submitted('display_single_bookings')) {
            URLHelper::addLinkParam('display_single_bookings', Request::get('display_single_bookings'));
        }

        //Get the resource objects and (if needed) the booking object:
        if ($mode == 'add') {
            $only_one_room = count($this->resources) == 1;

            if ($only_one_room) {
                if ($this->booking_type == 3) {
                    PageLayout::setTitle(
                        sprintf(
                            _('%s: Neue geplante Buchung'),
                            $this->resources[0]->getFullName()
                        )
                    );
                } elseif ($this->booking_type == 2) {
                    PageLayout::setTitle(
                        sprintf(
                            '%s: Neue Sperrbuchung',
                            $this->resources[0]->getFullName()
                        )
                    );
                } elseif ($this->booking_type == 1) {
                    PageLayout::setTitle(
                        sprintf(
                            '%s: Neue Reservierung',
                            $this->resources[0]->getFullName()
                        )
                    );
                } else {
                    PageLayout::setTitle(
                        sprintf(
                            '%s: Neue Buchung',
                            $this->resources[0]->getFullName()
                        )
                    );
                }
            } else {
                if ($this->booking_type == 3) {
                    PageLayout::setTitle(
                        _('Neue geplante Buchung')
                    );
                } elseif ($this->booking_type == 2) {
                    PageLayout::setTitle(
                        _('Neue Sperrbuchung')
                    );
                } elseif ($this->booking_type == 1) {
                    PageLayout::setTitle(
                        _('Neue Reservierung')
                    );
                } else {
                    PageLayout::setTitle(
                        _('Neue Buchung')
                    );
                }
            }
        } elseif (($mode == 'edit') || ($mode == 'duplicate')) {
            if (!$this->booking) {
                return;
            }
            $this->booking_type = $this->booking->booking_type;

            $booking_begin = new DateTime();
            $booking_begin->setTimestamp($this->booking->begin);
            $week_day_de = ['Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.', 'So.'];
            $booking_date = $week_day_de[date('N', $booking_begin->getTimestamp())-1];
            $booking_date .= date(' d.m.Y', $booking_begin->getTimestamp());

            $resource = $this->booking->resource;
            if ($resource instanceof Resource) {
                $resource = $resource->getDerivedClassInstance();
                $this->resources = [$resource];
                $this->resource_or_clipboard_id = $resource->id;
            }

            if ($mode == 'edit') {
                if (count($this->resources) == 1) {
                    if ($this->booking_type == 3) {
                        PageLayout::setTitle(
                            sprintf(
                                _('%1$s: Geplante Buchung am %2$s bearbeiten'),
                                $this->resources[0]->getFullName(),
                                $booking_date
                            )
                        );
                    } elseif ($this->booking_type == 2) {
                        PageLayout::setTitle(
                            sprintf(
                                _('%1$s: Sperrbuchung am %2$s bearbeiten'),
                                $this->resources[0]->getFullName(),
                                $booking_date
                            )
                        );
                    } elseif ($this->booking_type == 1) {
                        PageLayout::setTitle(
                            sprintf(
                                _('%1$s: Reservierung am %2$s bearbeiten'),
                                $this->resources[0]->getFullName(),
                                $booking_date
                            )
                        );
                    } else {
                        PageLayout::setTitle(
                            sprintf(
                                _('%1$s: Buchung am %2$s bearbeiten'),
                                $this->resources[0]->getFullName(),
                                $booking_date
                            )
                        );
                    }
                } else {
                    if ($this->booking_type == 3) {
                        PageLayout::setTitle(
                            sprintf(
                                _('Geplante Buchung am %s bearbeiten'),
                                $booking_date
                            )
                        );
                    } elseif ($this->booking_type == 2) {
                        PageLayout::setTitle(
                            sprintf(
                                _('Sperrbuchung am %s bearbeiten'),
                                $booking_date
                            )
                        );
                    } elseif ($this->booking_type == 1) {
                        PageLayout::setTitle(
                            sprintf(
                                _('Reservierung am %s bearbeiten'),
                                $booking_date
                            )
                        );
                    } else {
                        PageLayout::setTitle(
                            sprintf(
                                _('Buchung am %s bearbeiten'),
                                $booking_date
                            )
                        );
                    }
                }
            } else {
                PageLayout::setTitle(_('Buchung duplizieren'));
            }
        } else {
            //Invalid mode.
            return;
        }

        $this->mode = $mode;
        $this->overwrite_reservations = false;
        $this->show_reservation_overwrite_button = false;
        $this->save = Request::isPost() && (
            Request::submitted('save') || Request::submitted('overwrite_and_save')
        );

        //Check permissions:
        if ($mode == 'add') {
            if ($this->booking_type != 2) {
                foreach ($this->resources as $resource) {
                    if (!$resource->userHasBookingRights($this->current_user)) {
                        //The permissions are insufficient for at least one
                        //resource. No need to continue the permission check.
                        if (Request::isDialog()) {
                            PageLayout::postError(
                                _('Ihre Berechtigungsstufe ist mindestens für einen der gewählten Räume zu niedrig!')
                            );
                            return;
                        } else {
                            throw new AccessDeniedException(
                                _('Ihre Berechtigungsstufe ist mindestens für einen der gewählten Räume zu niedrig!')
                            );
                        }
                    }
                }
            } else {
                foreach ($this->resources as $resource) {
                    if (!$resource->userHasPermission($this->current_user, 'admin')) {
                        //The permissions are insufficient for at least one
                        //resource. No need to continue the permission check.
                        if (Request::isDialog()) {
                            PageLayout::postError(
                                _('Ihre Berechtigungsstufe ist mindestens für einen der gewählten Räume zu niedrig!')
                            );
                            return;
                        } else {
                            throw new AccessDeniedException(
                                _('Ihre Berechtigungsstufe ist mindestens für einen der gewählten Räume zu niedrig!')
                            );
                        }
                    }
                }
            }
        } elseif (($mode == 'edit') || ($mode == 'duplicate')) {
            if (!$this->booking->isSimpleBooking()) {
                throw new AccessDeniedException(
                    _('Nur einfache Buchungen dürfen direkt bearbeitet werden!')
                );
            } elseif ($this->booking->booking_user_id != $this->current_user->id) {
                foreach ($this->resources as $resource) {
                    if (!$resource->userHasBookingRights($this->current_user, $this->booking->id, 'booking')) {
                        //The permissions are insufficient for at least one
                        //resource. No need to continue the permission check.
                        throw new AccessDeniedException();
                    }
                }
            }
        }

        $this->begin = null;
        $this->end = null;

        if ($mode == 'add') {
            //Assume an booking will be made for tomorrow.
            //Round the current time to hours and substract
            //two hours from $begin.
            $this->begin = new DateTime();
            $this->begin->add(new DateInterval('P1D'));
            $this->begin->setTime(
                $this->begin->format('H'),
                0,
                0
            );
            $this->end = clone $this->begin;
            $this->repetition_end = clone $this->end;
            $this->begin->sub(new DateInterval('PT2H'));
        } elseif (($mode == 'edit') || ($mode == 'duplicate')) {
            $this->begin = new DateTime();
            $this->begin->setTimestamp($this->booking->begin);
            $this->end = new DateTime();
            $this->end->setTimestamp($this->booking->end);
            $this->repetition_end = new DateTime();
            if ($this->booking->repeat_end) {
                $this->repetition_end->setTimestamp($this->booking->repeat_end);
            } else {
                $this->repetition_end->setTimestamp($this->booking->end);
            }
        }

        $this->max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;

        if ($mode == 'add') {
            //In case a begin and end time are already given
            //use those values instead:
            if (Request::submitted('begin') && Request::submitted('end')) {
                $this->begin->setTimestamp(Request::get('begin'));
                $this->end->setTimestamp(Request::get('end'));
                if (Request::get('semester_id')) {
                    $this->booking_style = 'repeat';
                    $this->repetition_style = 'weekly';
                    $this->repetition_interval = 1;
                    $this->semester_id = Request::get('semester_id');
                    $this->selected_end = 'semester_course_end';
                    $this->repetition_end->setTimestamp(Semester::find($this->semester_id)->vorles_ende);
                }
            } else {
                $this->begin = Request::getDateTime(
                    'begin_date',
                    'd.m.Y',
                    'begin_time',
                    'H:i',
                    $this->begin
                );
                $this->end = Request::getDateTime(
                    'end_date',
                    'd.m.Y',
                    'end_time',
                    'H:i',
                    $this->end
                );
            }
        }

        $separable_room_messages = [];
        $this->separable_rooms_selected = false;
        $this->other_room_parts = [];
        foreach ($this->resources as $resource) {
            //Check if the resource is a room and if the room is part of a
            //separable room.

            if ($resource instanceof Room) {
                $other_room_parts = Room::findOtherRoomParts($resource);

                if ($other_room_parts) {
                    $this->other_room_parts[$resource->id] = $other_room_parts;
                    $this->separable_rooms_selected = true;

                    //We must display a warning.
                    $simple_message = true;

                    if ($only_one_room and $other_room_parts) {
                        //If there is only one room we can be informative.
                        $simple_message = false;
                        $other_room_links = [];
                        foreach ($other_room_parts as $room_part) {
                            $other_room_links[] = sprintf(
                                '<a target="_blank" href="%1$s">%2$s</a>',
                                $room_part->getActionLink('show'),
                                htmlReady($room_part->name)
                            );
                        }
                        $separable_room_messages[] = sprintf(
                            _('Der Raum %1$s ist ein Teilraum eines teilbaren Raumes. Weitere Teilräume sind:'),
                            htmlReady($resource->name)
                        ) . ' ' . implode(
                            ', ',
                            $other_room_links
                        );
                    }
                }
                if ($simple_message) {
                    $separable_room_messages[] = sprintf(
                        _('Der Raum %1$s ist ein Teilraum eines teilbaren Raumes.'),
                        htmlReady($resource->name)
                    );
                }
            }
        }

        if (!$this->save) {
            if ((count($separable_room_messages) == 1)) {
                PageLayout::postInfo($separable_room_messages[0]);
            } else if (!empty($separable_room_messages)) {
                PageLayout::postInfo(
                    _('Die folgenden Räume sind Teilräume:'),
                    $separable_room_messages
                );
            }
        }

        if (!$this->semester_id) {
            $this->selected_end = 'manual';
        }

        $this->semesters = Semester::getAll();
        if ($mode == 'add') {
            $this->selected_semester = Semester::findByTimestamp($this->begin->format('U'));
            if (!$this->selected_semester) {
                $this->selected_semester = Semester::findCurrent();
            }
        } elseif (($mode == 'edit') || ($mode == 'duplicate')) {
            $begin = new DateTime();
            $begin->setTimestamp($this->booking->begin);
            $begin->setTime(0,0,0);
            $end = new DateTime();
            if ($this->booking->repeat_end) {
                $end->setTimestamp($this->booking->repeat_end);
            } else {
                $end->setTimestamp($this->booking->end);
            }
            $end->setTime(23,59,59);

            $begin = $begin->getTimestamp();
            $end = $end->getTimestamp();

            $this->selected_semester = Semester::findByTimestamp($begin);

            if ($this->selected_semester) {
                if ($end == $this->selected_semester->vorles_ende) {
                    $this->selected_end = 'semester_course_end';
                }
            } else {
                //No semester detected. Use the current semester.
                $this->selected_semester = Semester::findCurrent();
            }
            $this->description = $this->booking->description;
        }
        $this->semester_id = $this->selected_semester->id;

        $this->notification_enabled = '0';

        if ($mode == 'add') {
            $this->booking_style = $this->booking_style ?: "single";
            $this->preparation_time = '0';
            $this->block_booking = [];
            $this->repetition_style = 'weekly';
            $this->repetition_interval = '1';
            $this->internal_comment = '';
        } elseif (($mode == 'edit') || ($mode == 'duplicate')) {
            $this->preparation_time = $this->booking->preparation_time / 60;
            $this->block_booking = [];
            $interval = $this->booking->getRepetitionInterval();
            if (!$interval) {
                $this->booking_style = "single";
                $this->repetition_style = 'weekly';
                $this->repetition_interval = '1';
            } else {
                $this->booking_style = "repeat";
                if ($interval->m == 1) {
                    $this->repetition_style = 'monthly';
                    $this->repetition_interval = $interval->m;
                } else if (($interval->d % 7) == 0) {
                    $this->repetition_style = 'weekly';
                    $this->repetition_interval = $interval->d / 7;
                } else {
                    $this->repetition_style = 'daily';
                    $this->repetition_interval = $interval->d;
                }
            }
            $this->internal_comment = $this->booking->internal_comment;
        }

        $user_search = new StandardSearch('user_id');
        $user_search->extendedLayout = false;
        $this->assigned_user_search = QuickSearch::get(
            'assigned_user_id',
            $user_search
        );
        if ((($mode == 'edit') || ($mode == 'duplicate'))
            && ($this->booking->assigned_user instanceof User)
            && !$this->save) {
            //Set assigned user:
            $this->assigned_user_search->defaultValue(
                $this->booking->assigned_user->id,
                $this->booking->assigned_user->getFullName()
            );
        }

        $this->show_form = true;

        if ($this->save) {

            //Get the requested time range and check if the resource
            //is available in that time range when the booking is not
            //a lock booking.
            CSRFProtection::verifyUnsafeRequest();

            $this->overwrite_bookings = Request::get('overwrite_bookings');

            $this->semester_id = Request::get('semester_id');
            $this->selected_end = Request::get('selected_end');

            $this->preparation_time = Request::get('preparation_time');
            $this->notification_enabled = Request::get('notification_enabled');
            $this->booking_style = Request::get('booking_style');
            $this->block_booking = Request::getArray('block_booking');
            $this->book_other_room_parts = Request::get('book_other_room_parts');
            $this->internal_comment = Request::get('internal_comment');

            $this->repetition_style = Request::get('repetition_style');
            $this->repetition_interval = Request::get('repetition_interval');

            $this->internal_comment = Request::get('internal_comment', '');
            $this->description = Request::get('description', '');

            $this->assigned_user_id = Request::get('assigned_user_id');
            $this->assigned_user = null;

            $this->overwrite_reservations = Request::submitted('overwrite_and_save');

            if ($this->assigned_user_id) {
                //Assigning a user is optional. Therefore it is only an error
                //when a person is selected and cannot be found.
                $this->assigned_user = User::find($this->assigned_user_id);
                if (!$this->assigned_user) {
                    PageLayout::postError(
                        _('Die gewählte belegende Person konnte nicht in der Datenbank gefunden werden!')
                    );
                    return;
                }

                $this->assigned_user_search->defaultValue(
                    $this->assigned_user->id,
                    $this->assigned_user->getFullName()
                );
            }

            if (!is_numeric($this->booking_type)) {
                $this->booking_type = Request::int('booking_type');
            }

            //Check if a repetition interval has been selected:
            $this->repetition_date_interval = null;
            if ($this->booking_style == 'repeat') {
                $this->repetition_end = Request::getDateTime(
                    'repetition_end',
                    'd.m.Y'
                );
                $this->repetition_end->setTime(
                    intval($this->end->format('H')),
                    intval($this->end->format('i')),
                    intval($this->end->format('s'))
                );
                if ($this->repetition_style) {
                    if ($this->repetition_style == 'daily' && $this->repetition_interval) {
                        $this->repetition_date_interval = new DateInterval(
                            'P' . intval($this->repetition_interval) . 'D'
                        );
                    }
                    if ($this->repetition_style == 'weekly' && $this->repetition_interval) {
                        $this->repetition_date_interval = new DateInterval(
                            'P' . intval($this->repetition_interval) . 'W'
                        );
                    }
                    if ($this->repetition_style == 'monthly') {
                        $this->repetition_date_interval = new DateInterval(
                            'P1M'
                        );
                    }
                }
            } else {
                $this->repetition_end = clone $this->end;
            }

            //Validation:

            if (($this->booking_type < 0) || ($this->booking_type > 2)) {
                PageLayout::postError(
                    _('Es wurde ein ungültiger Buchungstyp gewählt.')
                );
                $this->show_booking_type_selection = true;
                return;
            }

            if (!$this->begin) {
                PageLayout::postError(
                    _('Es wurde kein Startdatum bzw. Startzeitpunkt angegeben!')
                );
                return;
            }
            if (!$this->end) {
                PageLayout::postError(
                    _('Es wurde kein Enddatum bzw. Endzeitpunkt angegeben!')
                );
                return;
            }

            //Validate the preparation time:
            if ($this->preparation_time > $this->max_preparation_time) {
                PageLayout::postError(
                    sprintf(
                        _('Es sind maximal %d Minuten für die Rüstzeit erlaubt!'),
                        $this->max_preparation_time
                    )
                );
                return;
            }

            $this->semester = null;

            if (preg_match('/^semester/', $this->selected_end)) {
                $this->semester = Semester::find($this->semester_id);

                if (!$this->semester) {
                    PageLayout::postError(
                        _('Das ausgewählte Semester wurde nicht in der Datenbank gefunden!')
                    );
                    return;
                }
            }

            $this->begin = Request::getDateTime(
                'begin_date',
                'd.m.Y',
                'begin_time',
                'H:i'
            );

            if (Request::get('multiple_days')) {
                //The booking spreads over multiple days.
                $this->end = Request::getDateTime(
                    'end_date',
                    'd.m.Y',
                    'end_time',
                    'H:i',
                    $this->end
                );
            } elseif ($this->booking_style == 'block') {
                //The booking spreads over block.
                $this->end = Request::getDateTime(
                    'repetition_end',
                    'd.m.Y',
                    'end_time',
                    'H:i',
                    $this->end
                );
            } else {
                //The booking takes place on one day.
                //Therefore we use the begin date instead of the end date.
                $this->end = Request::getDateTime(
                    'begin_date',
                    'd.m.Y',
                    'end_time',
                    'H:i',
                    $this->begin
                );
            }

            //Check if the start time lies before the end time:
            if ($this->begin >= $this->end) {
                PageLayout::postError(
                    _('Die Startuhrzeit darf nicht nach der Enduhrzeit liegen!')
                );
                return;
            }

            if ($this->preparation_time < 0) {
                PageLayout::postError(
                    _('Die Rüstzeit darf keinen negativen Wert enthalten!')
                );
                return;
            }

            //Check if a block booking is selected:

            $block_booking_days = [];
            if ($this->booking_style == 'block') {
                //If the first two options are selected
                //it doesn't matter if single week days are selected.
                //They are ignored in that case.
                if (in_array('each_day', $this->block_booking)) {
                    $block_booking_days = [0, 1, 2, 3, 4, 5, 6];
                } elseif (in_array('mon_fri', $this->block_booking)) {
                    $block_booking_days = [0, 1, 2, 3, 4];
                } else {
                    //Only single week days are selected.
                    if (in_array('mon', $this->block_booking)) {
                        $block_booking_days[] = 0;
                    }
                    if (in_array('tue', $this->block_booking)) {
                        $block_booking_days[] = 1;
                    }
                    if (in_array('wed', $this->block_booking)) {
                        $block_booking_days[] = 2;
                    }
                    if (in_array('thu', $this->block_booking)) {
                        $block_booking_days[] = 3;
                    }
                    if (in_array('fri', $this->block_booking)) {
                        $block_booking_days[] = 4;
                    }
                    if (in_array('sat', $this->block_booking)) {
                        $block_booking_days[] = 5;
                    }
                    if (in_array('sun', $this->block_booking)) {
                        $block_booking_days[] = 6;
                    }
                }
            }

            $time_intervals = [];

            if ($block_booking_days) {
                $begin_time = [
                    intval($this->begin->format('H')),
                    intval($this->begin->format('i')),
                    intval($this->begin->format('s'))
                ];
                $end_time = [
                    intval($this->end->format('H')),
                    intval($this->end->format('i')),
                    intval($this->end->format('s'))
                ];
                //We must loop the first 7 days. For each weekday that
                //is selected in the block booking area we create
                //an booking.
                $current_day = clone $this->begin;
                $current_day->setTime(
                    $end_time[0],
                    $end_time[1],
                    $end_time[2]
                );
                $this->end_day = clone $this->end;

                while ($current_day <= $this->end_day) {
                    $current_day_index = $current_day->format('N') - 1;

                    if (in_array($current_day_index, $block_booking_days)) {
                        $current_begin = clone $current_day;
                        $current_begin->setTime(
                            $begin_time[0],
                            $begin_time[1],
                            $begin_time[2]
                        );
                        $current_end = clone $current_day;
                        $current_end->setTime(
                            $end_time[0],
                            $end_time[1],
                            $end_time[2]
                        );

                        $time_intervals[] = [
                            'begin' => $current_begin,
                            'end' => $current_end
                        ];

                    }
                    $current_day = $current_day->add(new DateInterval('P1D'));
                }
            } else {
                //It is one single booking.
                $time_intervals[] = [
                    'begin' => $this->begin,
                    'end' => $this->end
                ];
            }

            $reservations_to_overwrite = [];
            //At this point, we have collected all time intervals
            //that are relevant to create/edit new bookings.

            //Check if there are reservations:
            foreach ($this->resources as $resource) {
                $reservations = ResourceBooking::findByResourceAndTimeRanges(
                    $resource,
                    $time_intervals,
                    [1, 3],
                    ($this->booking->id ? [$this->booking->id] : [])
                );
                $reservations_to_overwrite = array_merge(
                    $reservations_to_overwrite,
                    $reservations
                );
            }
            if ($reservations_to_overwrite && !$this->overwrite_reservations) {
                $this->show_reservation_overwrite_button = true;
                $reservation_list = [];

                foreach ($reservations_to_overwrite as $reservation) {
                    $reservation_list[] = implode(', ', $reservation->getTimeIntervalStrings());
                }

                PageLayout::postWarning(
                    ngettext(
                        'Die folgende Reservierung wird beim Buchen überschrieben. Wollen Sie die Reservierung wirklich überschreiben?',
                        'Die folgenden Reservierungen werden beim Buchen überschrieben. Wollen Sie die Reservierungen wirklich überschreiben?',
                        count($reservation_list)
                    ),
                    $reservation_list
                );
                return;
            }

            $errors = [];
            $room_part_errors = [];
            $bookings = [];

            foreach ($time_intervals as $index => $interval) {
                $updated_booking = $this->booking;
                if ($this->booking_style == 'block') {
                    //Use the selected booking only for the first time interval
                    //of the block booking and update it according to the
                    //time interval. Create new bookings for all other
                    //time intervals.
                    if ($index > 0) {
                        $updated_booking = null;
                    }
                }
                foreach ($this->resources as $resource) {
                    $results = $this->assignResourceAndCollectResults(
                        $updated_booking,
                        $resource,
                        $this->current_user,
                        $interval['begin'],
                        $interval['end'],
                        $this->repetition_end,
                        intval($this->preparation_time) * 60,
                        $this->description,
                        $this->internal_comment,
                        $this->booking_type,
                        $this->assigned_user,
                        $this->repetition_date_interval,
                        $this->notification_enabled,
                        (
                            $this->book_other_room_parts
                            ? $this->other_room_parts[$resource->id]
                            : []
                        ),
                        $this->overwrite_bookings
                    );
                    $errors = array_merge($errors, $results['errors']);
                    $room_part_errors = array_merge(
                        $room_part_errors,
                        $results['room_part_errors']
                    );
                    $bookings = array_merge(
                        $bookings,
                        $results['bookings']
                    );
                }
            }

            if ($this->overwrite_reservations) {
                foreach ($reservations_to_overwrite as $reservation) {
                    $reservation->delete();
                }
            }

            if ($bookings) {
                $this->show_form = false;
            }
            $this->displayAssignResultMessages(
                $this->booking_type,
                count($bookings),
                $errors,
                $room_part_errors
            );

            if (!$errors && Request::isXhr()) {
                $this->response->add_header('X-Dialog-Close', '1');
                $this->response->add_header('X-Location', URLHelper::getURL('', ['defaultDate'=> date('Y-m-d', $this->begin->getTimestamp())]));
                $this->render_nothing();
            }

        }

        $this->show_booking_type_selection = false;
        if (!is_numeric($this->booking_type)) {
            $this->show_booking_type_selection = true;
        }
    }


    public function add_action($resource_id = null, $booking_type = null)
    {
        if (!$resource_id) {
            $resource_id = Request::option('ressource_id');
        }
        $resource_ids = Request::getArray('resource_ids');

        $this->resources = [];
        $this->resource_or_clipboard_id = $resource_id;

        if ($resource_ids) {
            //$resource_ids contains the IDs of several resources.
            $resources = Resource::findMany($resource_ids);
            foreach ($resources as $resource) {
                $this->resources[] = $resource->getDerivedClassInstance();
            }
        } elseif (strpos($resource_id, 'clipboard_') !== false) {
            //A clipboard has been selected:
            //Get all resources from it:
            $clipboard_id = substr($resource_id, 10);

            $clipboard = Clipboard::find($clipboard_id);
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
            //$resource_id contains the ID of a resource.
            $resource = Resource::find($resource_id);

            if ($resource) {
                //We need the specialisations here to display the name correctly:
                $this->resources = [$resource->getDerivedClassInstance()];
            }
            if (!$this->resources) {
                PageLayout::postError(
                    _('Die Ressource wurde nicht gefunden!')
                );
                return;
            }
        }

        $this->booking_type = $booking_type;

        $this->addEditHandler('add');
    }


    public function edit_action($booking_id = null)
    {
        $this->addEditHandler('edit');
    }


    public function duplicate_action($booking_id = null)
    {
        $this->addEditHandler('duplicate');
    }


    /**
     * This action can copy a booking to another resource of the same
     * resource class.
     */
    public function copy_action($booking_id = null)
    {
        PageLayout::setTitle(_('Buchung kopieren'));

        $current_user = User::findCurrent();
        if (!$this->booking->resource->userHasPermission($current_user, 'autor')) {
            throw new AccessDeniedException();
        }

        $this->show_form = false;

        $this->available_resources = ResourceManager::getUserResources(
            $current_user,
            'autor',
            null,
            [$this->booking->resource->class_name],
            true
        );

        $this->show_form = true;

        $this->selected_resource_ids = [];

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->selected_resource_ids = Request::getArray('selected_resource_ids');

            if (!$this->selected_resource_ids) {
                PageLayout::postError(
                    _('Es wurde keine Ressource ausgewählt!')
                );
                return;
            }

            $errors = [];
            foreach ($this->selected_resource_ids as $selected_resource_id) {
                $selected_resource = Resource::find($selected_resource_id);
                if (!($selected_resource instanceof Resource)) {
                    continue;
                }
                $selected_resource = $selected_resource->getDerivedClassInstance();

                try {
                    $selected_resource->createBooking(
                        $current_user,
                        $this->booking->range_id,
                        [
                            [
                                'begin' => $this->booking->begin,
                                'end' => $this->booking->end
                            ]
                        ],
                        $this->booking->getRepetitionInterval(),
                        $this->booking->repeat_quantity,
                        $this->booking->repeat_end,
                        $this->booking->preparation_time,
                        $this->booking->description,
                        $this->booking->internal_comment,
                        $this->booking->booking_type
                    );
                } catch (Exception $e) {
                    $errors[] = $selected_resource->getFullName()
                              . ': ' . $e->getMessage();
                }
            }

            if ($errors) {
                PageLayout::postError(
                    _('Es traten Fehler beim Kopieren der Buchung auf!'),
                    $errors
                );
            } else {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Die Buchung wurde kopiert!')
                );

                if (Request::isXhr()) {
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->response->add_header('X-Location', URLHelper::getURL('', ['defaultDate'=> date('Y-m-d', $this->booking->begin)]));
                    $this->render_nothing();
                }
            }
        }
    }

    /**
     * This action can move a booking to another resource of the same
     * resource class.
     */
    public function move_action($booking_id = null)
    {
        PageLayout::setTitle(_('Buchung verschieben'));

        $current_user = User::findCurrent();
        if (!$this->booking->resource->userHasPermission($current_user, 'autor')) {
            throw new AccessDeniedException();
        }

        $this->show_form = false;

        $this->available_resources = ResourceManager::getUserResources(
            $current_user,
            'autor',
            null,
            [$this->booking->resource->class_name],
            true
        );

        $this->show_form = true;

        $this->selected_resource_ids = [];

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->selected_resource_id = Request::option('selected_resource_id');

            if (!$this->selected_resource_id) {
                PageLayout::postError(
                    _('Es wurde keine Ressource ausgewählt!')
                );
                return;
            }

            $errors = [];

            $selected_resource = Resource::find($this->selected_resource_id);
            $selected_resource = $selected_resource->getDerivedClassInstance();

            try {
                $this->booking->resource = $selected_resource;
                $this->booking->store();
            } catch (Exception $e) {
                $errors[] = $selected_resource->getFullName()
                            . ': ' . $e->getMessage();
            }

            if ($errors) {
                PageLayout::postError(
                    _('Es traten Fehler beim Verschieben der Buchung auf!'),
                    $errors
                );
            } else {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Die Buchung wurde verschoben!')
                );

                if (Request::isXhr()) {
                    $this->response->add_header('X-Dialog-Close', '1');
                    $this->response->add_header('X-Location', URLHelper::getURL('', ['defaultDate'=> date('Y-m-d', $this->booking->begin)]));
                    $this->render_nothing();
                }
            }
        }
    }


    /**
     * This action can copy an booking in another semester.
     */
    public function copy_to_semester_action($booking_id = null)
    {
        PageLayout::setTitle(_('Buchung in ein anderes Semester kopieren'));

        $current_user = User::findCurrent();
        if (!$this->booking->resource->userHasPermission($current_user, 'tutor')) {
            throw new AccessDeniedException();
        }

        $this->available_semesters = Semester::findBySql(
            'beginn > :begin ORDER BY beginn ASC, ende ASC',
            [
                'begin' => $this->booking->begin
            ]
        );

        if (!$this->available_semesters) {
            PageLayout::postError(
                _('Es sind keine zukünftigen Semester vorhanden, in die die Buchung kopiert werden kann!')
            );
            return;
        }

        $this->available_semester_weeks = [];

        foreach ($this->available_semesters as $semester) {
            $current_week = new DateTime();
            $current_week->setTimestamp($semester->beginn);
            $semester_end = new DateTime();
            $semester_end->setTimestamp($semester->ende);

            while ($current_week < $semester_end) {
                $current_week_start = clone $current_week;
                $current_week_day = $current_week->format('N') - 1;
                if ($current_week > 0) {
                    $current_week_start = $current_week_start->sub(
                        new DateInterval('P' . ($current_week->format('N') - 1) . 'D')
                    );
                }
                $current_week_end = clone $current_week_start;
                $current_week_end = $current_week_end->add(
                    new DateInterval('P6DT23H59M59S')
                );

                $week_name = sprintf(
                    _('%1$d. KW, vom %2$s bis zum %3$s'),
                    $current_week->format('W'),
                    $current_week_start->format('d.m.Y'),
                    $current_week_end->format('d.m.Y')
                );
                $week_name_index = $current_week->format('Y_W');
                $this->available_semester_weeks[$week_name_index] = $week_name;

                $current_week = $current_week->add(new DateInterval('P1W'));
            }
        }

        $this->show_form = true;
        if (Request::submitted('copy')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->semester_id = Request::get('semester_id');
            $this->semester_week = Request::get('semester_week');

            $selected_semester = Semester::find($this->semester_id);
            if (!$selected_semester) {
                PageLayout::postError(
                    _('Das gewählte Semester wurde nicht gefunden!')
                );
                return;
            }

            //Check if the selected week is in the semester:

            $semester_week_parts = explode('_', $this->semester_week);
            $selected_week = new DateTime();
            $selected_week->setISODate(
                $semester_week_parts[0],
                $semester_week_parts[1]
            );
            $selected_week_timestamp = $selected_week->getTimestamp();
            $out_of_semester = ($selected_week_timestamp > $selected_semester->ende
                || $selected_week_timestamp < $selected_semester->beginn);
            if ($out_of_semester) {
                PageLayout::postError(
                    _('Die gewählte Kalenderwoche liegt außerhalb des gewählten Semesters!')
                );
                return;
            }

            //After that, calculate the new begin and end of the booking:

            $old_begin = new DateTime();
            $old_begin->setTimestamp($this->booking->begin);
            $old_end = new DateTime();
            $old_end->setTimestamp($this->booking->end);

            $begin_week_day = $old_begin->format('N') - 1;
            $duration = $old_begin->diff($old_end);

            $new_begin = clone $selected_week;
            if ($begin_week_day > 0) {
                $new_begin = $new_begin->add(
                    new DateInterval('P' . $begin_week_day . 'D')
                );
            }
            $new_begin->setTime(
                intval($old_begin->format('H')),
                intval($old_begin->format('i')),
                intval($old_begin->format('s'))
            );
            $new_end = clone $new_begin;
            $new_end = $new_end->add($duration);

            $new_booking = new ResourceBooking();
            $new_booking->setData($this->booking->toRawArray());
            $new_booking->id = $new_booking->getNewId();
            $new_booking->begin = $new_begin->getTimestamp();
            $new_booking->end = $new_end->getTimestamp();

            if ($this->booking->repeat_end) {
                $old_repeat_end = new DateTime();
                $old_repeat_end->setTimestamp($this->booking->repeat_end);
                $repeat_duration = $old_end->diff($old_repeat_end);
                $new_repeat_end = clone $new_end;
                $new_repeat_end = $new_repeat_end->add($repeat_duration);
                $new_booking->repeat_end = $new_repeat_end->getTimestamp();
            }

            try {
                $new_booking->store();
            } catch (Exception $e) {
                PageLayout::postError(
                    _('Die Buchung konnte aus folgenden Gründen nicht kopiert werden:'),
                    [htmlReady($e->getMessage())]
                );
                return;
            }

            $this->show_form = false;
            PageLayout::postSuccess(
                sprintf(
                    _('Die Buchung wurde in das Semester %1$s kopiert und findet dort vom %2$s, %3$s Uhr bis zum %4$s, %5$s Uhr statt!'),
                    htmlReady($selected_semester->name),
                    $new_begin->format('d.m.Y'),
                    $new_begin->format('H:i'),
                    $new_end->format('d.m.Y'),
                    $new_end->format('H:i')
                )
            );
        }
    }


    /**
     * This action is responsible for deleting a resource booking.
     */
    public function delete_action($booking_id = null)
    {
        PageLayout::setTitle(_('Buchung löschen'));

        if ($this->booking->isReadOnlyForUser($this->current_user)) {
            //The user must not delete this booking!
            throw new AccessDeniedException();
        }

        $this->show_details = true;
        if (Request::submitted('hide_details')) {
            $this->show_details = false;
        }
        $this->show_question = true;

        if (Request::submitted('confirm')) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->booking->delete()) {
                $this->show_question = false;
                PageLayout::postSuccess(
                    _('Die Buchung wurde gelöscht!')
                );
            } else {
                PageLayout::postError(
                    _('Fehler beim Löschen der Buchung!')
                );
            }
        }
    }
}
