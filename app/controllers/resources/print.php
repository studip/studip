<?php

/**
 * print.php - contains Resources_PrintController
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
 * Resources_PrintController contains actions for printing resource data.
 */
class Resources_PrintController extends AuthenticatedController
{
    public function individual_booking_plan_action($resource_id = null)
    {
        if (Navigation::hasItem('/resources/planning')) {
            Navigation::activateItem('/resources/planning');
        }
        if (Navigation::hasItem('/resources/planning/individual_booking_plan')) {
            Navigation::activateItem('/resources/planning/individual_booking_plan');
        }

        $this->resource = Resource::find($resource_id);
        if (!$this->resource) {
            PageLayout::postError(
                _('Die Ressource wurde nicht gefunden!')
            );
            return;
        }
        $this->resource = $this->resource->getDerivedClassInstance();

        $current_user = User::findCurrent();

        PageLayout::setTitle(
            sprintf(
                _('Individueller Belegungsdruck: %s'),
                $this->resource->getFullName()
            )
        );

        if (!$this->resource->userHasPermission($current_user, 'user')) {
            throw new AccessDeniedException();
        }

        $this->timestamp = Request::get('timestamp', time());

        $this->date = new DateTime();
        $this->date->setTimestamp($this->timestamp);

        $sidebar = Sidebar::get();

        $views = new ViewsWidget();
        if ($GLOBALS['user']->id && ($GLOBALS['user']->id != 'nobody')) {
            if ($this->resource->userHasPermission($current_user, 'user')) {
                $views->addLink(
                    _('Standard Zeitfenster'),
                    URLHelper::getURL(
                        'dispatch.php/resources/print/individual_booking_plan/' . $this->resource->id,
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
                        'dispatch.php/resources/print/individual_booking_plan/' . $this->resource->id,
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

        $template = $GLOBALS['template_factory']->open(
            'sidebar/resources_individual_booking_plan_sidebar.php'
        );

        $html_widget = new TemplateWidget(
            _('Farbwähler'),
            $template
        );
        $sidebar->addWidget($html_widget);
    }


    /**
     * This action is responsible for printing all schedules of all rooms
     * which are part of a clipboard.
     */
    public function clipboard_rooms_action()
    {
        if (Navigation::hasItem('/resources/export')) {
            Navigation::activateItem('/resources/export');
        }
        if (Navigation::hasItem('/resources/export/print_clipboard_rooms')) {
            Navigation::activateItem('/resources/export/print_clipboard_rooms');
        }
        PageLayout::setTitle(_('Belegungsplan-Seriendruck'));

        $this->clipboard_selected = false;
        $this->print_schedules = false;
        if (Request::submitted('select_clipboard')) {
            $this->clipboard_selected = true;
        } elseif (Request::submitted('print')) {
            $this->print_schedules = true;
        }

        //The parameters are collected here since they are used in all cases
        //and In both cases we must collect the selected
        //clipboard, the selected date and the selected schedule type.
        //Furthermore a date and the type of schedule has been selected.
        $this->selected_clipboard_id = Request::get('clipboard_id');
        $this->schedule_type = Request::get('schedule_type');
        $this->selected_date_string = Request::get('date');

        if (!$this->clipboard_selected && !$this->print_schedules) {
            //We have to load all selectable clipboards of the current user:
            $this->available_clipboards = Clipboard::getClipboardsForUser(
                $GLOBALS['user']->id
            );
        } else {
            //Either a clipboard has been selected or the print view
            //is requested.

            //Now we check for CSRF:
            CSRFProtection::verifyUnsafeRequest();

            $this->selected_clipboard = Clipboard::find(
                $this->selected_clipboard_id
            );
            if (!$this->selected_clipboard) {
                PageLayout::postError(
                    _('Die gewählte Raumgruppe wurde nicht gefunden!')
                );
                return;
            }

            if ($this->clipboard_selected) {
                //We must load a list of available rooms.
                //The rooms are loaded one by one to keep the
                //sorting of the room clipboard.
                $this->available_rooms = [];
                $room_ids = $this->selected_clipboard->getAllRangeIds(
                    'Room'
                );
                foreach ($room_ids as $room_id) {
                    $room = Room::find($room_id);
                    if ($room instanceof Room) {
                        $this->available_rooms[] = $room;
                    }
                }
            } elseif ($this->print_schedules) {
                //Load the list of selected rooms, but make sure
                //the rooms are placed inside the selected clipboard and that
                //they are loaded in the order defined in the room clipboard.
                $this->selected_room_ids = Request::getArray('selected_room_ids');
                $this->selected_rooms = [];
                $room_ids = $this->selected_clipboard->getAllRangeIds(
                    'Room'
                );
                foreach ($room_ids as $room_id) {
                    if (!in_array($room_id, $this->selected_room_ids)) {
                        continue;
                    }
                    $room = Room::find($room_id);
                    if ($room instanceof Room) {
                        $this->selected_rooms[] = $room;
                    }
                }

                $date_parts = explode('.', $this->selected_date_string);
                $date = new DateTime();
                $date->setDate(
                    intval($date_parts[2]),
                    intval($date_parts[1]),
                    intval($date_parts[0])
                );
                $date->setTime(12, 0, 0);

                $this->print_date = $date->format('Y-m-d');

                $this->schedules = [];
                $this->rooms = [];

                //Add a sidebar action to init the printing process:
                $sidebar = Sidebar::get();
                $actions = new ActionsWidget();
                $actions->addLink(
                    _('Drucken'),
                    'javascript:void(window.print());',
                    Icon::create('print')
                );
                $sidebar->addWidget($actions);
            }
        }
    }
}
