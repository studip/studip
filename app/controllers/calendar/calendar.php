<?php
/*
 * The controller for the personal calendar.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since
 */

class Calendar_CalendarController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setHelpKeyword('Basis.Terminkalender');
        $this->settings = $GLOBALS['user']->cfg->CALENDAR_SETTINGS;
        if (!is_array($this->settings)) {
            $this->settings = Calendar::getDefaultUserSettings();
        }
        URLHelper::bindLinkParam('atime', $this->atime);
        $this->atime = Request::int('atime', time());
        $this->category = Request::int('category');
        $this->last_view = Request::option('last_view',
                $this->settings['view']);
        $this->action = $action;
        $this->restrictions = [
            'STUDIP_CATEGORY'     => $this->category ?: null,
            // hide events with status 3 (CalendarEvent::PARTSTAT_DECLINED)
            'STUDIP_GROUP_STATUS' => $this->settings['show_declined'] ? null : [0,1,2,5]
        ];
        if ($this->category) {
            URLHelper::bindLinkParam('category', $this->category);
        }

        if (Config::get()->COURSE_CALENDAR_ENABLE
            && !Request::get('self')
            && Course::findCurrent()) {
            $current_seminar = new Seminar(Course::findCurrent());
            if ($current_seminar->getSlotModule('calendar') instanceOf CoreCalendar) {
                $this->range_id = $current_seminar->id;
                Navigation::activateItem('/course/calendar');
            }
        }
        if (!$this->range_id) {
            $this->range_id = Request::option('range_id', $GLOBALS['user']->id);
            Navigation::activateItem('/calendar/calendar');
            URLHelper::bindLinkParam('range_id', $this->range_id);
        }

        URLHelper::bindLinkParam('last_view', $this->last_view);
    }

    protected function createSidebar($active = null, $calendar = null)
    {
        $active = $active ?: $this->last_view;
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Kalender'));
        $sidebar->setImage('sidebar/schedule-sidebar.png');
        $views = new ViewsWidget();
        $views->addLink(_('Tag'), $this->url_for($this->base . 'day'))
                ->setActive($active == 'day');
        $views->addLink(_('Woche'), $this->url_for($this->base . 'week'))
                ->setActive($active == 'week');
        $views->addLink(_('Monat'), $this->url_for($this->base . 'month'))
                ->setActive($active == 'month');
        $views->addLink(_('Jahr'), $this->url_for($this->base . 'year'))
                ->setActive($active == 'year');
        $sidebar->addWidget($views);
    }

    protected function createSidebarFilter()
    {
        $tmpl_factory = $this->get_template_factory();

        $filters = new OptionsWidget();
        $filters->setTitle('Auswahl');

        $tmpl = $tmpl_factory->open('calendar/single/_jump_to');
        $tmpl->atime = $this->atime;
        $tmpl->action = $this->action;
        $tmpl->action_url = $this->url_for('calendar/single/jump_to');
        $filters->addElement(new WidgetElement($tmpl->render()));

        $tmpl = $tmpl_factory->open('calendar/single/_select_category');
        $tmpl->action_url = $this->url_for();
        $tmpl->category = $this->category;
        $filters->addElement(new WidgetElement($tmpl->render()));

        if (Config::get()->CALENDAR_GROUP_ENABLE
                || Config::get()->COURSE_CALENDAR_ENABLE) {
            $tmpl = $tmpl_factory->open('calendar/single/_select_calendar');
            $tmpl->range_id = $this->range_id;
            $tmpl->action_url = $this->url_for('calendar/group/switch');
            $tmpl->view = $this->action;
            $filters->addElement(new WidgetElement($tmpl->render()));
            $filters->addCheckbox(_('Abgelehnte Termine anzeigen'),
                    $this->settings['show_declined'],
                    $this->url_for($this->base . 'show_declined',
                            ['show_declined' => 1]),
                    $this->url_for($this->base . 'show_declined',
                            ['show_declined' => 0]));
        }
        Sidebar::get()->addWidget($filters);
    }

    public function index_action()
    {
        // switch to the view the user has selected in his personal settings
        $default_view = $this->settings['view'] ?: 'week';

        // Remove cid
        if (Request::option('self')) {
            Context::close();

            $this->redirect(URLHelper::getURL('dispatch.php/' . $this->base
                . $default_view . '/' . $GLOBALS['user']->id, [], true));
        } else {
            $this->redirect(URLHelper::getURL('dispatch.php/' . $this->base
                . $default_view));
        }
    }

    public function edit_action($range_id = null, $event_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);
        $this->event = $this->calendar->getEvent($event_id);

        if ($this->event->isNew()) {
         //   $this->event = $this->calendar->getNewEvent();
            if (Request::get('isdayevent')) {
                $this->event->setStart(mktime(0, 0, 0, date('n', $this->atime),
                        date('j', $this->atime), date('Y', $this->atime)));
                $this->event->setEnd(mktime(23, 59, 59, date('n', $this->atime),
                        date('j', $this->atime), date('Y', $this->atime)));
            } else {
                $this->event->setStart($this->atime);
                $this->event->setEnd($this->atime + 3600);
            }
            $this->event->setAuthorId($GLOBALS['user']->id);
            $this->event->setEditorId($GLOBALS['user']->id);
            $this->event->setAccessibility('PRIVATE');
            if (!Request::isXhr()) {
                PageLayout::setTitle($this->getTitle($this->calendar, _('Neuer Termin')));
            }
        } else {
            // open read only events and course events not as form
            // show information in dialog instead
            if (!$this->event->havePermission(Event::PERMISSION_WRITABLE)
                    || $this->event instanceof CourseEvent) {
                if (!$this->event instanceof CourseEvent && $this->event->attendees->count() > 1) {
                    if ($this->event->group_status) {
                        $this->redirect($this->url_for('calendar/single/edit_status/' . implode('/',
                            [$this->range_id, $this->event->event_id])));
                    } else {
                        $this->redirect($this->url_for('calendar/single/event/' . implode('/',
                            [$this->range_id, $this->event->event_id])));
                    }
                } else {
                    $this->redirect($this->url_for('calendar/single/event/' . implode('/',
                            [$this->range_id, $this->event->event_id])));
                }
                return null;
            }
            if (!Request::isXhr()) {
                PageLayout::setTitle($this->getTitle($this->calendar, _('Termin bearbeiten')));
            }
        }

        if (Config::get()->CALENDAR_GROUP_ENABLE
                && $this->calendar->getRange() == Calendar::RANGE_USER) {

            if (Config::get()->CALENDAR_GRANT_ALL_INSERT) {
                $search_obj = SQLSearch::get("SELECT DISTINCT auth_user_md5.user_id, "
                    . "{$GLOBALS['_fullname_sql']['full_rev_username']} as fullname, "
                    . "auth_user_md5.perms, auth_user_md5.username "
                    . "FROM auth_user_md5 "
                    . "LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) "
                    . 'WHERE auth_user_md5.user_id <> ' . DBManager::get()->quote($GLOBALS['user']->id)
                    . ' AND (username LIKE :input OR Vorname LIKE :input '
                    . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
                    . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
                    . "OR Nachname LIKE :input OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input "
                    . ") ORDER BY fullname ASC",
                    _('Person suchen'), 'user_id');
            } else {
                $search_obj = SQLSearch::get("SELECT DISTINCT auth_user_md5.user_id, "
                    . "{$GLOBALS['_fullname_sql']['full_rev_username']} as fullname, "
                    . "auth_user_md5.perms, auth_user_md5.username "
                    . "FROM calendar_user "
                    . "LEFT JOIN auth_user_md5 ON calendar_user.owner_id = auth_user_md5.user_id "
                    . "LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) "
                    . 'WHERE calendar_user.user_id = '
                    . DBManager::get()->quote($GLOBALS['user']->id)
                    . ' AND calendar_user.permission > ' . Event::PERMISSION_READABLE
                    . ' AND auth_user_md5.user_id <> ' . DBManager::get()->quote($GLOBALS['user']->id)
                    . ' AND (username LIKE :input OR Vorname LIKE :input '
                    . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
                    . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
                    . "OR Nachname LIKE :input OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input "
                    . ") ORDER BY fullname ASC",
                    _('Person suchen'), 'user_id');
            }

            // SEMBBS
            // Eintrag von Terminen bereits ab PERMISSION_READABLE
            /*
            $search_obj = new SQLSearch('SELECT DISTINCT auth_user_md5.user_id, '
                . $GLOBALS['_fullname_sql']['full_rev'] . ' as fullname, username, perms '
                . 'FROM calendar_user '
                . 'LEFT JOIN auth_user_md5 ON calendar_user.owner_id = auth_user_md5.user_id '
                . 'LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) '
                . 'WHERE calendar_user.user_id = '
                . DBManager::get()->quote($GLOBALS['user']->id)
                . ' AND calendar_user.permission >= ' . Event::PERMISSION_READABLE
                . ' AND (username LIKE :input OR Vorname LIKE :input '
                . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
                . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
                . 'OR Nachname LIKE :input OR '
                . $GLOBALS['_fullname_sql']['full_rev'] . ' LIKE :input '
                . ') ORDER BY fullname ASC',
                _('Nutzer suchen'), 'user_id');
            // SEMBBS
             *
             */


            $this->quick_search = QuickSearch::get('user_id', $search_obj)
                    ->fireJSFunctionOnSelect('STUDIP.Messages.add_adressee')
                    ->withButton();

      //      $default_selected_user = array($this->calendar->getRangeId());
            $this->mps = MultiPersonSearch::get('add_adressees')
                ->setLinkText(_('Mehrere Teilnehmende hinzufügen'))
       //         ->setDefaultSelectedUser($default_selected_user)
                ->setTitle(_('Mehrere Teilnehmende hinzufügen'))
                ->setExecuteURL($this->url_for($this->base . 'edit'))
                ->setJSFunctionOnSubmit('STUDIP.Messages.add_adressees')
                ->setSearchObject($search_obj);
            $owners = SimpleORMapCollection::createFromArray(
                    CalendarUser::findByUser_id($this->calendar->getRangeId()))
                    ->pluck('owner_id');
            foreach (Calendar::getGroups($GLOBALS['user']->id) as $group) {
                $this->mps->addQuickfilter(
                    $group->name,
                    $group->members->filter(
                        function ($member) use ($owners) {
                            if (in_array($member->user_id, $owners)) {
                                return $member;
                            }
                        })->pluck('user_id')
                );
            }
        }

        $stored = false;
        if (Request::submitted('store')) {
            $stored = $this->storeEventData($this->event, $this->calendar);
        }

        if ($stored !== false) {
            if ($stored === 0) {
                if (Request::isXhr()) {
                    header('X-Dialog-Close: 1');
                    exit;
                } else {
                    PageLayout::postMessage(MessageBox::success(_('Der Termin wurde nicht geändert.')));
                    $this->relocate('calendar/single/' . $this->last_view, ['atime' => $this->atime]);
                }
            } else {
                PageLayout::postMessage(MessageBox::success(_('Der Termin wurde gespeichert.')));
                $this->relocate('calendar/single/' . $this->last_view, ['atime' => $this->atime]);
            }
        }

        $this->createSidebar('edit', $this->calendar);
        $this->createSidebarFilter();
    }

    public function edit_status_action($range_id, $event_id)
    {
        global $user;

        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);
        $this->event = $this->calendar->getEvent($event_id);
        $stored = false;
        $old_status = $this->event->group_status;

        if (Request::submitted('store')) {

            if ($this->event->isNew()
                || !Config::get()->CALENDAR_GROUP_ENABLE
                || !$this->calendar->havePermission(Calendar::PERMISSION_OWN)
                || !$this->calendar->getRange() == Calendar::RANGE_USER
                || !$this->event->havePermission(Event::PERMISSION_READABLE)) {
                throw new AccessDeniedException();
            }

            $status = Request::int('status', 1);
            if ($status > 0 && $status < 6) {
                $this->event->group_status = $status;
                $stored = $this->event->store();
            }

            if ($stored !== false) {
                if ($stored === 0) {
                    if (Request::isXhr()) {
                        header('X-Dialog-Close: 1');
                        exit;
                    } else {
                        PageLayout::postMessage(MessageBox::success(_('Der Teilnahmestatus wurde nicht geändert.')));
                        $this->relocate('calendar/single/' . $this->last_view, ['atime' => $this->atime]);
                    }
                } else {
                    // send message to organizer...
                    if ($this->event->author_id != $user->id) {
                        setTempLanguage($this->event->author_id);
                        $message = new messaging();
                        $msg_text = sprintf(_('%s hat den Terminvorschlag für "%s" am %s von %s auf %s geändert.'),
                                get_fullname(), $this->event->getTitle(),
                                strftime('%c', $this->event->getStart()),
                                $this->event->toStringGroupStatus($old_status), $this->event->toStringGroupStatus());
                        if ($status == CalendarEvent::PARTSTAT_DELEGATED) {
                            $msg_text .= "\n"
                                    . sprintf(_('Der Termin wird akzeptiert, aber %s nimmt nicht selbst am Termin teil.'),
                                    get_fullname());
                        }
                        $subject = sprintf(_('Terminvorschlag am %s von %s %s'),
                                strftime('%c', $this->event->getStart()), get_fullname(), $this->event->toStringGroupStatus());
                        $msg_text .= "\n\n**" . _('Beginn:') . '** ';
                        if ($this->event->isDayEvent()) {
                            $msg_text .= strftime('%x ', $this->event->getStart());
                            $msg_text .= _('ganztägig');
                        } else {
                            $msg_text .= strftime('%c', $this->event->getStart());
                        }
                        $msg_text .= "\n**" . _('Ende:') . '** ';
                        if ($this->event->isDayEvent()) {
                            $msg_text .= strftime('%x ', $this->event->getEnd());
                        } else {
                            $msg_text .= strftime('%c', $this->event->getEnd());
                        }
                        $msg_text .= "\n**" . _('Zusammenfassung:') . '** ' . $this->event->getTitle() . "\n";
                        if ($event_data = $this->event->getDescription()) {
                            $msg_text .= '**' . _('Beschreibung:') . "** $event_data\n";
                        }
                        if ($event_data = $this->event->toStringCategories()) {
                            $msg_text .= '**' . _('Kategorie:') . "** $event_data\n";
                        }
                        if ($event_data = $this->event->toStringPriority()) {
                            $msg_text .= '**' . _('Priorität:') . "** $event_data\n";
                        }
                        if ($event_data = $this->event->toStringAccessibility()) {
                            $msg_text .= '**' . _('Zugriff:') . "** $event_data\n";
                        }
                        if ($event_data = $this->event->toStringRecurrence()) {
                            $msg_text .= '**' . _('Wiederholung:') . "** $event_data\n";
                        }
                        $member = [];
                        foreach ($this->event->attendees as $attendee) {
                            if ($attendee->range_id == $this->event->getAuthorId()) {
                                $member[] = $attendee->user->getFullName()
                                    . ' ('. _('Organisator') . ')';
                            } else {
                                $member[] = $attendee->user->getFullName()
                                        . ' (' . $this->event->toStringGroupStatus($attendee->group_status)
                                        . ')';
                            }
                        }
                        $msg_text .= '**' . _('Teilnehmende:') . '** ' . implode(', ', $member);
                        $msg_text .= "\n\n" . _('Hier kommen Sie direkt zum Termin in Ihrem Kalender:') . "\n";
                        $msg_text .= URLHelper::getURL('dispatch.php/calendar/single/edit/'
                                . $this->event->getAuthorId() . '/' . $this->event->event_id);
                        $message->insert_message(
                                addslashes($msg_text),
                                [get_username($this->event->getAuthorId())],
                                $this->event->range_id,
                                '', '', '', '', addslashes($subject));
                        restoreLanguage();
                    }
                    PageLayout::postMessage(MessageBox::success(_('Der Teilnahmestatus wurde gespeichert.')));
                    $this->relocate('calendar/single/' . $this->last_view, ['atime' => $this->atime]);
                }
            }
        }

        $this->createSidebar('edit', $this->calendar);
        $this->createSidebarFilter();
    }

    public function switch_action()
    {
        $default_view = $this->settings['view'] ?: 'week';
        $view = Request::option('last_view', $default_view);
        $this->range_id = Request::option('range_id', $GLOBALS['user']->id);
        $object_type = get_object_type($this->range_id);
        switch ($object_type) {
            case 'user':
                URLHelper::addLinkParam('cid', '');
                $this->redirect($this->url_for('calendar/single/'
                        . $view . '/' . $this->range_id));
                break;
            case 'sem':
            case 'inst':
            case 'fak':
                URLHelper::addLinkParam('cid', $this->range_id);
                $this->redirect($this->url_for('calendar/single/'
                        . $view . '/' . $this->range_id));
                break;
            case 'group':
                URLHelper::addLinkParam('cid', '');
                $this->redirect($this->url_for('calendar/group/'
                        . $view . '/' . $this->range_id));
                break;
        }
    }

    public function jump_to_action()
    {
        $date = Request::get('jmp_date');
        if ($date) {
            $atime = strtotime($date . strftime(' %T', $this->atime));
        } else {
            $atime = 'now';
        }
        $action = Request::option('action', 'week');
        $this->range_id = $this->range_id ?: $GLOBALS['user']->id;
        $this->redirect($this->url_for($this->base . $action,
                ['atime' => $atime, 'range_id' => $this->range_id]));
    }

    public function show_declined_action ()
    {
        $config = UserConfig::get($GLOBALS['user']->id);
        $this->settings['show_declined'] = Request::int('show_declined') ? '1' : '0';
     //   var_dump($this->settings); exit;
        $config->store('CALENDAR_SETTINGS', $this->settings);
        $action = Request::option('action', 'week');
        $this->range_id = $this->range_id ?: $GLOBALS['user']->id;
        $this->redirect($this->url_for($this->base . $action,
                ['range_id' => $this->range_id]));
    }

    protected function storeEventData(CalendarEvent $event, SingleCalendar $calendar)
    {
        if (Request::int('isdayevent')) {
            $dt_string = Request::get('start_date') . ' 00:00:00';
        } else {
            $dt_string = sprintf(
                '%s %u:%02u',
                Request::get('start_date'),
                Request::int('start_hour'),
                Request::int('start_minute')
            );
        }
        $event->setStart($this->parseDateTime($dt_string));
        if (Request::int('isdayevent')) {
            $dt_string = Request::get('end_date') . ' 23:59:59';
        } else {
            $dt_string = sprintf(
                '%s %u:%02u',
                Request::get('end_date'),
                Request::int('end_hour'),
                Request::int('end_minute')
            );
        }
        $event->setEnd($this->parseDateTime($dt_string));
        if ($event->getStart() > $event->getEnd()) {
            $messages[] = _('Die Startzeit muss vor der Endzeit liegen.');
        }

        if (Request::isXhr()) {
            $event->setTitle(Request::get('summary', ''));
            $event->event->description = Request::get('description', '');
            $event->setUserDefinedCategories(Request::get('categories', ''));
            $event->event->location = Request::get('location', '');
        } else {
            $event->setTitle(Request::get('summary'));
            $event->event->description = Request::get('description', '');
            $event->setUserDefinedCategories(Request::get('categories', ''));
            $event->event->location = Request::get('location', '');
        }
        $event->event->category_intern = Request::int('category_intern', 1);
        $event->setAccessibility(Request::option('accessibility', 'PRIVATE'));
        $event->setPriority(Request::int('priority', 0));

        if (!$event->getTitle()) {
            $messages[] = _('Es muss eine Zusammenfassung angegeben werden.');
        }

        $rec_type = Request::option('recurrence', 'single');
        $expire = Request::option('exp_c', 'never');
        $rrule = [
            'linterval' => null,
            'sinterval' => null,
            'wdays' => null,
            'month' => null,
            'day' => null,
            'rtype' => 'SINGLE',
            'count' => null,
            'expire' => null
        ];
        if ($expire == 'count') {
            $rrule['count'] = Request::int('exp_count', 10);
        } else if ($expire == 'date') {
            if (Request::isXhr()) {
                $exp_date = Request::get('exp_date');
            } else {
                $exp_date = Request::get('exp_date');
            }
            $exp_date = $exp_date ?: strftime('%x', time());
            $rrule['expire'] = $this->parseDateTime($exp_date . ' 12:00');
        }
        switch ($rec_type) {
            case 'daily':
                if (Request::option('type_daily', 'day') == 'day') {
                    $rrule['linterval'] = Request::int('linterval_d', 1);
                    $rrule['rtype'] = 'DAILY';
                } else {
                    $rrule['linterval'] = 1;
                    $rrule['wdays'] = '12345';
                    $rrule['rtype'] = 'WEEKLY';
                }
                break;
            case 'weekly':
                $rrule['linterval'] = Request::int('linterval_w', 1);
                $rrule['wdays'] = implode('', Request::intArray('wdays',
                        [strftime('%u', $event->getStart())]));
                $rrule['rtype'] = 'WEEKLY';
                break;
            case 'monthly':
                if (Request::option('type_m', 'day') == 'day') {
                    $rrule['linterval'] = Request::int('linterval_m1', 1);
                    $rrule['day'] = Request::int('day_m',
                            strftime('%e', $event->getStart()));
                    $rrule['rtype'] = 'MONTHLY';
                } else {
                    $rrule['linterval'] = Request::int('linterval_m2', 1);
                    $rrule['sinterval'] = Request::int('sinterval_m', 1);
                    $rrule['wdays'] = Request::int('wday_m',
                            strftime('%u', $event->getStart()));
                    $rrule['rtype'] = 'MONTHLY';
                }
                break;
            case 'yearly':
                if (Request::option('type_y', 'day') == 'day') {
                    $rrule['linterval'] = 1;
                    $rrule['day'] = Request::int('day_y',
                            strftime('%e', $event->getStart()));
                    $rrule['month'] = Request::int('month_y1',
                            date('n', $event->getStart()));
                    $rrule['rtype'] = 'YEARLY';
                } else {
                    $rrule['linterval'] = 1;
                    $rrule['sinterval'] = Request::int('sinterval_y', 1);
                    $rrule['wdays'] = Request::int('wday_y',
                            strftime('%u', $event->getStart()));
                    $rrule['month'] = Request::int('month_y2',
                            date('n', $event->getStart()));
                    $rrule['rtype'] = 'YEARLY';
                }
                break;
        }
        if (sizeof($messages)) {
            PageLayout::postMessage(MessageBox::error(_('Bitte Eingaben korrigieren'), $messages));
            return false;
        } else {
            $event->setRecurrence($rrule);
            $exceptions = array_diff(Request::getArray('exc_dates'),
                    Request::getArray('del_exc_dates'));
            $event->setExceptions($this->parseExceptions($exceptions));
            // if this is a group event, store event in the calendars of each attendee
            if (Config::get()->CALENDAR_GROUP_ENABLE) {
                $attendee_ids = Request::optionArray('attendees');
                return $calendar->storeEvent($event, $attendee_ids);
            } else {
                return $calendar->storeEvent($event);
            }
        }
    }

    /**
     * Parses a string with exception dates from input form and returns an array
     * with all dates as unix timestamp identified by an internally used pattern.
     *
     * @param string $exc_dates
     * @return array An array of unix timestamps.
     */
    protected function parseExceptions($exc_dates) {
        $matches = [];
        $dates = [];
        preg_match_all('%(\d{1,2})\h*([/.])\h*(\d{1,2})\h*([/.])\h*(\d{4})\s*%',
                implode(' ', $exc_dates), $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ($match[2] == '/') {
                $dates[] = strtotime($match[1].'/'.$match[3].'/'.$match[5]);
            } else {
                $dates[] = strtotime($match[1].$match[2].$match[3].$match[4].$match[5]);
            }
        }
        return $dates;
    }

    /**
     * Parses a string as date time in the format "j.n.Y H:i:s" and returns the
     * corresponding unix time stamp.
     *
     * @param string $dt_string The date time string.
     * @return int A unix time stamp
     */
    protected function parseDateTime($dt_string)
    {
        $dt_array = date_parse_from_format('j.n.Y H:i:s', $dt_string);
        return mktime($dt_array['hour'], $dt_array['minute'], $dt_array['second'],
                $dt_array['month'], $dt_array['day'], $dt_array['year']);
    }

}
