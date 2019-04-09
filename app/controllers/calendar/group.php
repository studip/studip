<?php

require_once 'app/controllers/calendar/calendar.php';
require_once 'app/controllers/authenticated_controller.php';
require_once 'app/models/calendar/Calendar.php';
require_once 'app/models/calendar/SingleCalendar.php';

class Calendar_GroupController extends Calendar_CalendarController
{
    public function before_filter(&$action, &$args)
    {
        $this->base = 'calendar/group/';
        parent::before_filter($action, $args);
    }

    protected function createSidebar($active = 'week', $calendar = null)
    {
        parent::createSidebar($active, $calendar);
        $sidebar = Sidebar::Get();
        $actions = new ActionsWidget();
        $actions->addLink(_('Termin anlegen'),
                          $this->url_for('calendar/group/edit'),
                          Icon::create('add', 'clickable'),
            ['data-dialog' => 'size=auto']);
        $actions->addLink(_('Kalender freigeben'),
                $this->url_for('calendar/single/manage_access/' . $GLOBALS['user']->id,
                               ['group_filter' => $this->range_id]),
                          Icon::create('community', 'clickable'),
                          ['id' => 'calendar-open-manageaccess',
                                'data-dialog' => '', 'data-dialogname' => 'manageaccess']);
        $sidebar->addWidget($actions);
    }

    protected function getTitle($group)
    {
        $title = sprintf(_('Terminkalender der Gruppe "%s"'), $group->name);
        return $title;
    }

    public function index_action()
    {
        // switch to the view the user has selected in his personal settings
        $default_view = $this->settings['view'] ?: 'week';
        $this->redirect($this->url_for('calendar/group/' . $default_view));
    }

    public function edit_action($range_id = null, $event_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        // get group and the calendars of the members
        // the first calendar is the calendar of the actual user
        $this->calendar = new SingleCalendar($GLOBALS['user']->id);
        $group = $this->getGroup($this->calendar);
        if ($group) {
            $calendar_owners = CalendarUser::getOwners($GLOBALS['user']->id,
                        Calendar::PERMISSION_WRITABLE)->pluck('owner_id');
            $members = $group->members->pluck('user_id');
            $user_id = Request::option('user_id');
            $this->attendee_ids = array_intersect($calendar_owners, $members);
            $this->attendee_ids[] = $GLOBALS['user']->id;
            if ($user_id && in_array($user_id, $this->attendee_ids)) {
                $this->attendee_ids = [$user_id];
            }
        }

        $this->event = $this->calendar->getEvent($event_id);

        if ($this->event->isNew()) {
            $this->event = $this->calendar->getNewEvent();
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
            if ($this->attendee_ids) {
                foreach ($this->attendee_ids as $attendee_id) {
                    $attendee_event = clone $this->event;
                    $attendee_event->range_id = $attendee_id;
                    $this->attendees[] = $attendee_event;
                }
            }
            if (!Request::isXhr()) {
                PageLayout::setTitle($this->getTitle($this->calendar, _('Neuer Termin')));
            }
        } else {
            // open read only events and course events not as form
            // show information in dialog instead
            if (!$this->event->havePermission(Event::PERMISSION_WRITABLE)
                    || $this->event instanceof CourseEvent) {
                $this->redirect($this->url_for('calendar/single/event/' . implode('/',
                        [$this->range_id, $this->event->event_id])));
                return null;
            }
            $this->attendees = $this->event->attendees;
            if (!Request::isXhr()) {
                PageLayout::setTitle($this->getTitle($this->calendar, _('Termin bearbeiten')));
            }
        }

        if (get_config('CALENDAR_GROUP_ENABLE')
                && $this->calendar->getRange() == Calendar::RANGE_USER) {
            $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, {$GLOBALS['_fullname_sql']['full_rev']} as fullname, username, perms "
                . "FROM calendar_user "
                . "LEFT JOIN auth_user_md5 ON calendar_user.owner_id = auth_user_md5.user_id "
                . "LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) "
                . 'WHERE calendar_user.user_id = '
                . DBManager::get()->quote($GLOBALS['user']->id)
                . ' AND calendar_user.permission > ' . Event::PERMISSION_READABLE
                . ' AND (username LIKE :input OR Vorname LIKE :input '
                . "OR CONCAT(Vorname,' ',Nachname) LIKE :input "
                . "OR CONCAT(Nachname,' ',Vorname) LIKE :input "
                . "OR Nachname LIKE :input OR {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input "
                . ") ORDER BY fullname ASC",
                _('Nutzer suchen'), 'user_id');
            $this->quick_search = QuickSearch::get('user_id', $search_obj)
                    ->fireJSFunctionOnSelect('STUDIP.Messages.add_adressee');

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
            // switch back to group context
            $this->range_id = $group->getId();
            if ($stored === 0) {
                if (Request::isXhr()) {
                    header('X-Dialog-Close: 1');
                    exit;
                } else {
                    PageLayout::postMessage(MessageBox::success(_('Der Termin wurde nicht geändert.')));
                    $this->relocate('calendar/group/' . $this->last_view, ['atime' => $this->atime]);
                }
            } else {
                PageLayout::postMessage(MessageBox::success(_('Der Termin wurde gespeichert.')));
                $this->relocate('calendar/group/' . $this->last_view, ['atime' => $this->atime]);
            }
        } else {
            $this->createSidebar('edit', $this->calendar);
            $this->createSidebarFilter();
            $this->render_template('calendar/single/edit', $this->layout);
        }
    }

    public function day_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        // get group and the calendars of the members
        // the first calendar is the calendar of the actual user
        $this->calendars[0] = SingleCalendar::getDayCalendar(
                $GLOBALS['user']->id, $this->atime);
        $group = $this->getGroup($this->calendars[0]);
        foreach ($group->members as $member) {
            $calendar = new SingleCalendar($member->user_id);
            if ($calendar->havePermission(Calendar::PERMISSION_READABLE)) {
                $this->calendars[] = SingleCalendar::getDayCalendar($calendar,
                        $this->atime, null, $this->restrictions);
            }
        }

        PageLayout::setTitle($this->getTitle($group)
                . ' - ' . _('Tagesansicht'));
        Navigation::activateItem('/calendar/calendar');

        $this->last_view = 'day';

        $this->createSidebar('day');
        $this->createSidebarFilter();
    }

    /**
     * Returns the Statusgruppe for the given calendar.
     *
     * @param SingleCalendar The calendar of the group owner.
     * @return Statusgruppen The found group.
     * @throws AccessDeniedException If the group does not exists or the owner
     * of the calendar is not the owner of the group.
     */
    private function getGroup($calendar)
    {
        $group = Statusgruppen::find($this->range_id);
        if (!$group) {
            throw new AccessDeniedException();
        }
        // is the user the owner of this group
        if ($group->range_id != $calendar->getRangeId()) {
            // not the owner...
            throw new AccessDeniedException();
        }
        return $group;
    }

    public function week_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $timestamp = mktime(12, 0, 0, date('n', $this->atime),
                date('j', $this->atime), date('Y', $this->atime));
        $monday = $timestamp - 86400 * (strftime('%u', $timestamp) - 1);
        $day_count = $this->settings['type_week'] == 'SHORT' ? 5 : 7;
        // one calendar for each day for the actual user
        for ($i = 0; $i < $day_count; $i++) {
            // one calendar holds the events of one day
            $this->calendars[0][$i] =
                    SingleCalendar::getDayCalendar($GLOBALS['user']->id,
                            $monday + $i * 86400, null, $this->restrictions);
        }
        // check and get the group
        $group = $this->getGroup($this->calendars[0][0]);
        $n = 1;
        foreach ($group->members as $member) {
            $calendar = new SingleCalendar($member->user_id);
            if ($calendar->havePermission(Calendar::PERMISSION_READABLE)) {
                for ($i = 0; $i < $day_count; $i++) {
                    $this->calendars[$n][$i] =
                            SingleCalendar::getDayCalendar($member->user_id,
                                $monday + $i * 86400, null, $this->restrictions);
                }
                $n++;
            }
        }

        PageLayout::setTitle($this->getTitle($group)
                . ' - ' . _('Wochenansicht'));
        Navigation::activateItem('/calendar/calendar');

        $this->last_view = 'week';

        $this->createSidebar('week');
        $this->createSidebarFilter();
    }

    public function month_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $month_start = mktime(12, 0, 0, date('n', $this->atime), 1, date('Y', $this->atime));
        $month_end = mktime(12, 0, 0, date('n', $this->atime), date('t', $this->atime), date('Y', $this->atime));
        $adow = strftime('%u', $month_start) - 1;
        $cor = date('n', $this->atime) == 3 ? 1 : 0;
        $this->first_day = $month_start - $adow * 86400;
        $this->last_day = ((42 - ($adow + date('t', $this->atime))) % 7 + $cor) * 86400 + $month_end;
        // one calendar each day for the actual user
        for ($start_day = $this->first_day; $start_day <= $this->last_day; $start_day += 86400) {
            $this->calendars[0][] = SingleCalendar::getDayCalendar(
                    $GLOBALS['user']->id, $start_day, null, $this->restrictions);
        }
        // check and get the group
        $group = $this->getGroup($this->calendars[0][0]);
        $n = 1;
        // get the calendars of the group members
        foreach ($group->members as $member) {
            $calendar = new SingleCalendar($member->user_id);
            if ($calendar->havePermission(Calendar::PERMISSION_READABLE)) {
                for ($start_day = $this->first_day; $start_day <= $this->last_day; $start_day += 86400) {
                    $this->calendars[$n][] =
                            SingleCalendar::getDayCalendar($member->user_id,
                                    $start_day, null, $this->restrictions);
                }
                $n++;
            }
        }
        PageLayout::setTitle($this->getTitle($group)
                . ' - ' . _('Monatssicht'));
        Navigation::activateItem('/calendar/calendar');

        $this->last_view = 'month';

        $this->createSidebar('month');
        $this->createSidebarFilter();
    }

    public function year_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $start = mktime(0, 0, 0, 1, 1, date('Y', $this->atime));
        $end = mktime(23, 59, 59, 12, 31, date('Y', $this->atime));
        $this->calendars[0] = new SingleCalendar(
                $GLOBALS['user']->id, $start, $end);
        $this->count_lists[0] = $this->calendars[0]->getListCountEvents();

        // check and get the group
        $group = $this->getGroup($this->calendars[0]);
        $n = 1;
        // get the calendars of the group members
        foreach ($group->members as $member) {
            $calendar = new SingleCalendar($member->user_id);
            if ($calendar->havePermission(Calendar::PERMISSION_READABLE)) {
                $this->calendars[$n] = $calendar->setStart($start)->setEnd($end);
                $this->count_lists[$n] = $this->calendars[$n]->getListCountEvents();
                $n++;
            }
        }

        PageLayout::setTitle($this->getTitle($group)
                . ' - ' . _('Jahresansicht'));
        Navigation::activateItem("/calendar/calendar");

        $this->last_view = 'year';
        $this->createSidebar('year');
        $this->createSidebarFilter();
    }
}
