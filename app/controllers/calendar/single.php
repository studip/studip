<?php
/*
 * This is the controller for the single calendar view
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/controllers/calendar/calendar.php';
require_once 'app/models/calendar/Calendar.php';
require_once 'app/models/calendar/SingleCalendar.php';
require_once 'app/models/ical_export.php';

class Calendar_SingleController extends Calendar_CalendarController
{
    public function before_filter(&$action, &$args) {
        $this->base = 'calendar/single/';
        parent::before_filter($action, $args);
    }

    protected function createSidebar($active = null, $calendar = null)
    {
        parent::createSidebar($active, $calendar);
        $sidebar = Sidebar::Get();
        if ($calendar->havePermission(Calendar::PERMISSION_WRITABLE)) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Termin anlegen'),
                        $this->url_for('calendar/single/edit'),
                        Icon::create('add', 'clickable'),
                        ['data-dialog' => 'size=auto']);
            if ($calendar->havePermission(Calendar::PERMISSION_OWN)) {
                if (get_config('CALENDAR_GROUP_ENABLE')) {
                    $actions->addLink(_('Kalender freigeben'),
                            $this->url_for('calendar/single/manage_access'),
                            Icon::create('community', 'clickable'),
                            ['id' => 'calendar-open-manageaccess',
                                'data-dialog' => '',
                                'data-dialogname' => 'manageaccess']);
                }
                $actions->addLink(_('Veranstaltungstermine'),
                        $this->url_for('calendar/single/seminar_events'),
                        Icon::create('seminar', 'clickable'),
                        ['data-dialog' => 'size=auto']);
            }
            $sidebar->addWidget($actions);
        }
        if ($calendar->havePermission(Calendar::PERMISSION_OWN)) {
            $export = new ExportWidget();
            $export->addLink(_('Termine exportieren'),
                        $this->url_for('calendar/single/export_calendar'),
                        Icon::create('download', 'clickable'),
                        ['data-dialog' => 'size=auto'])
                    ->setActive($active == 'export_calendar');
            $export->addLink(_('Termine importieren'),
                        $this->url_for('calendar/single/import'),
                        Icon::create('upload', 'clickable'),
                        ['data-dialog' => 'size=auto'])
                    ->setActive($active == 'import');
            $export->addLink(_('Kalender teilen'),
                        $this->url_for('calendar/single/share'),
                        Icon::create('group2', 'clickable'),
                        ['data-dialog' => 'size=auto'])
                    ->setActive($active == 'share');
            $sidebar->addWidget($export);
        }
    }

    public function day_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = SingleCalendar::getDayCalendar($this->range_id,
                $this->atime, null, $this->restrictions);

        PageLayout::setTitle($this->getTitle($this->calendar, _('Tagesansicht')));

        $this->last_view = 'day';

        $this->createSidebar('day', $this->calendar);
        $this->createSidebarFilter();
    }

    public function week_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $timestamp = mktime(12, 0, 0, date('n', $this->atime),
                date('j', $this->atime), date('Y', $this->atime));
        $monday = $timestamp - 86400 * (strftime('%u', $timestamp) - 1);
        $day_count = $this->settings['type_week'] == 'SHORT' ? 5 : 7;
        for ($i = 0; $i < $day_count; $i++) {
            $this->calendars[$i] =
                    SingleCalendar::getDayCalendar($this->range_id,
                            $monday + $i * 86400, null, $this->restrictions);
        }

        PageLayout::setTitle($this->getTitle($this->calendars[0],  _('Wochenansicht')));

        $this->last_view = 'week';

        $this->createSidebar('week', $this->calendars[0]);
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
        for ($start_day = $this->first_day; $start_day <= $this->last_day; $start_day += 86400) {
            $this->calendars[] = SingleCalendar::getDayCalendar($this->range_id,
                    $start_day, null, $this->restrictions);
        }

        PageLayout::setTitle($this->getTitle($this->calendars[0], _('Monatsansicht')));

        $this->last_view = 'month';
        $this->createSidebar('month', $this->calendars[0]);
        $this->createSidebarFilter();
    }

    public function year_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $start = mktime(0, 0, 0, 1, 1, date('Y', $this->atime));
        $end = mktime(23, 59, 59, 12, 31, date('Y', $this->atime));
        $this->calendar = new SingleCalendar($this->range_id, $start, $end);
        $this->count_list = $this->calendar->getListCountEvents(null, null,
                $this->restrictions);

        PageLayout::setTitle($this->getTitle($this->calendar, _('Jahresansicht')));

        $this->last_view = 'year';
        $this->createSidebar('year', $this->calendar);
        $this->createSidebarFilter();
    }

    public function event_action($range_id = null, $event_id = null)
    {
        PageLayout::setTitle(_('Termindaten'));

        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);
        $this->event = $this->calendar->getEvent($event_id);

        $this->createSidebar('edit', $this->calendar);
        $this->createSidebarFilter();
    }

    public function delete_action($range_id, $event_id)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);
        if ($this->calendar->deleteEvent($event_id, true)) {
            PageLayout::postMessage(MessageBox::success(_('Der Termin wurde gelöscht.')));
        }
        $this->redirect($this->url_for('calendar/single/' . $this->last_view));
    }

    public function delete_recurrence_action($range_id, $event_id, $atime)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $calendar = new SingleCalendar($this->range_id);
        $event = $calendar->getEvent($event_id);
        if ($event->getRecurrence('rtype') != 'SINGLE') {
            $exceptions = $event->getExceptions();
            $exceptions[] = $atime;
            $event->setExceptions($exceptions);
            if ($event->store() !== false) {
                PageLayout::postMessage(MessageBox::success(
                    strftime(_('Termin am %x aus Serie gelöscht.'), $atime)));
            }
        }
        $this->redirect($this->url_for('calendar/single/' . $this->last_view));
    }

    public function export_event_action($event_id, $range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $calendar = new SingleCalendar($this->range_id);
        $event = $calendar->getEvent($event_id);
        if (!$event->isNew()) {
            $export = new CalendarExportFile(new CalendarWriterICalendar());
            $export->exportFromObjects($event);
            $export->sendFile();
        }
        $this->render_nothing();
    }

    public function export_calendar_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);

        if (Request::submitted('export')) {
            $export = new CalendarExportFile(new CalendarWriterICalendar());
            if (Request::get('event_type') == 'user') {
                $types = ['CalendarEvent'];
            } else if (Request::get('event_type') == 'course') {
                $types = ['CourseEvent', 'CourseCancelledEvent'];
            } else {
                $types = ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent'];
            }
            if (Request::get('export_time') == 'date') {
                $exstart = $this->parseDateTime(Request::get('export_start'));
                $exend = $this->parseDateTime(Request::get('export_end'));
            } else {
                $exstart = 0;
                $exend = Calendar::CALENDAR_END;
            }
            $export->exportFromDatabase($this->calendar->getRangeId(), $exstart,
                    $exend, $types);
            $export->sendFile();
            $this->render_nothing();
            exit;
        }

        PageLayout::setTitle($this->getTitle($this->calendar, _('Termine exportieren')));

        $this->createSidebar('export_calendar', $this->calendar);
        $this->createSidebarFilter();
    }

    public function import_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);

        if ($this->calendar->havePermission(Calendar::PERMISSION_OWN)) {
            if (Request::submitted('import')) {
                CSRFProtection::verifySecurityToken();
                $import = new CalendarImportFile(new CalendarParserICalendar(),
                        $_FILES['importfile']);
                if (Request::get('import_as_private_imp')) {
                    $import->changePublicToPrivate();
                }
                $import->importIntoDatabase($range_id);
                $import_count = $import->getCount();
                PageLayout::postMessage(MessageBox::success(
                        sprintf('Es wurden %s Termine importiert.', $import_count)));
                $this->redirect($this->url_for('calendar/single/' . $this->last_view));
            }
        }
        PageLayout::setTitle($this->getTitle($this->calendar, _('Termine importieren')));
        $this->createSidebar('import', $this->calendar);
        $this->createSidebarFilter();
    }

    public function share_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);

        $this->short_id = null;
        if ($this->calendar->havePermission(Calendar::PERMISSION_OWN)) {
            if (Request::submitted('delete_id')) {
                CSRFProtection::verifySecurityToken();
                IcalExport::deleteKey($GLOBALS['user']->id);
                PageLayout::postMessage(MessageBox::success(
                        _('Die Adresse, unter der Ihre Termine abrufbar sind, wurde gelöscht')));
            }

            if (Request::submitted('new_id')) {
                CSRFProtection::verifySecurityToken();
                $this->short_id = IcalExport::setKey($GLOBALS['user']->id);
                PageLayout::postMessage(MessageBox::success(
                        _('Eine Adresse, unter der Ihre Termine abrufbar sind, wurde erstellt.')));
            } else {
                $this->short_id = IcalExport::getKeyByUser($GLOBALS['user']->id);
            }

            if (Request::submitted('submit_email')) {
                $email_reg_exp = '/^([-.0-9=?A-Z_a-z{|}~])+@([-.0-9=?A-Z_a-z{|}~])+\.[a-zA-Z]{2,6}$/i';
                if (preg_match($email_reg_exp, Request::get('email')) !== 0) {
                    $subject = '[' .Config::get()->UNI_NAME_CLEAN . ']' . _('Exportadresse für Ihre Termine');
                    $text .= _('Diese Email wurde vom Stud.IP-System verschickt. Sie können auf diese Nachricht nicht antworten.') . "\n\n";
                    $text .= _('Über diese Adresse erreichen Sie den Export für Ihre Termine:') . "\n\n";
                    $text .= $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/ical/index/'
                            . IcalExport::getKeyByUser($GLOBALS['user']->id);
                    StudipMail::sendMessage(Request::get('email'), $subject, $text);
                    PageLayout::postMessage(MessageBox::success(_('Die Adresse wurde verschickt!')));
                } else {
                    PageLayout::postMessage(MessageBox::error(_('Bitte geben Sie eine gültige Email-Adresse an.')));
                }
                $this->short_id = IcalExport::getKeyByUser($GLOBALS['user']->id);
            }
        }
        PageLayout::setTitle($this->getTitle($this->calendar,
                _('Kalender teilen oder einbetten')));

        $this->createSidebar('share', $this->calendar);
        $this->createSidebarFilter();
    }

    public function manage_access_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);

        $all_calendar_users =
                CalendarUser::getUsers($this->calendar->getRangeId());

        $this->filter_groups = Statusgruppen::findByRange_id(
                $this->calendar->getRangeId());

        $this->users = [];
        $this->group_filter_selected = Request::option('group_filter', 'list');
        if ($this->group_filter_selected != 'list') {
            $contact_group = Statusgruppen::find($this->group_filter_selected);
            $calendar_users = [];
            foreach ($contact_group->members as $member) {
                $calendar_users[] = new CalendarUser(
                        [$this->calendar->getRangeId(), $member->user_id]);
            }
            $this->calendar_users =
                        SimpleORMapCollection::createFromArray($calendar_users);
        } else {
            $this->group_filter_selected = 'list';
            $this->calendar_users = $all_calendar_users;
        }

        $this->own_perms = [];
        foreach ($this->calendar_users as $calendar_user) {
            $other_user = CalendarUser::find(
                    [$calendar_user->user_id, $this->calendar->getRangeId()]);
            if ($other_user) {
                $this->own_perms[$calendar_user->user_id] = $other_user->permission;
            } else {
                $this->own_perms[$calendar_user->user_id] = Calendar::PERMISSION_FORBIDDEN;
            }
            $this->users[mb_strtoupper(SimpleCollection::translitLatin1(
                    $calendar_user->nachname[0]))][] = $calendar_user;
        }

        ksort($this->users);
        $this->users = array_map(function ($g) {
            return SimpleCollection::createFromArray($g)->orderBy('nachname, vorname');
        }, $this->users);

        $this->mps = MultiPersonSearch::get('calendar-manage_access')
                ->setTitle(_('Personhinzufügen'))
                ->setLinkText(_('Person hinzufügen'))
                ->setDefaultSelectedUser($all_calendar_users->pluck('user_id'))
                ->setJSFunctionOnSubmit('STUDIP.CalendarDialog.closeMps')
                ->setExecuteURL($this->url_for('calendar/single/add_users/'
                        . $this->calendar->getRangeId()))
                ->setSearchObject(new StandardSearch('user_id'));

        PageLayout::setTitle($this->getTitle($this->calendar, _('Kalender freigeben')));

        $this->createSidebar('manage_access', $this->calendar);
        $this->createSidebarFilter();
    }

    public function add_users_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);
        if (Request::isXhr()) {
            $added_users = Request::optionArray('added_users');
        } else {
            $mps = MultiPersonSearch::load('calendar-manage_access');
            $added_users = $mps->getAddedUsers();
            $mps->clearSession();
        }

        $added = 0;
        foreach ($added_users as $user_id) {
            $user_to_add = User::find($user_id);
            if ($user_to_add) {
                $calendar_user = new CalendarUser(
                        [$this->calendar->getRangeId(), $user_to_add->id]);
                if ($calendar_user->isNew()) {
                    $calendar_user->permission = Calendar::PERMISSION_READABLE;
                    $added += $calendar_user->store();
                }
            }
        }
        if ($added) {
            PageLayout::postMessage(MessageBox::success(sprintf(
                    ngettext('Eine Person wurde mit der Berechtigung zum Lesen des Kalenders hinzugefügt.',
                            '%s Personen wurden mit der Berechtigung zum Lesen des Kalenders hinzugefügt.',
                            $added), $added)));
        }

        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->response->set_status(200);
            $this->render_nothing();
        } else {
            $this->redirect($this->url_for('calendar/single/manage_access/'
                    . $this->calendar->getRangeId()));
        }
    }

    public function remove_user_action($range_id = null, $user_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $user_id = $user_id ?: Request::option('user_id');
        $this->calendar = new SingleCalendar($this->range_id);
        $calendar_user = new CalendarUser(
                    [$this->calendar->getRangeId(), $user_id]);
        if (!$calendar_user->isNew()) {
            $name = $calendar_user->user->getFullname();
            $calendar_user->delete();
        }
        if (Request::isXhr()) {
            $this->response->set_status(200);
            $this->render_nothing();
        } else {
            PageLayout::postMessage(MessageBox::success(
                     sprintf(_('Person %s wurde entfernt.', $name))));
            $this->redirect($this->url_for('calendar/single/manage_access/'
                    . $this->calendar->getRangeId()));
        }
    }

    public function store_permissions_action($range_id = null)
    {
        $this->range_id = $range_id ?: $this->range_id;
        $this->calendar = new SingleCalendar($this->range_id);

        $deleted = 0;
        $read = 0;
        $write = 0;
        $submitted_permissions = Request::intArray('perm');
        foreach ($submitted_permissions as $user_id => $new_perm) {
            $calendar_user = new CalendarUser([$this->calendar->getRangeId(), $user_id]);
            if (!$calendar_user->isNew() && $new_perm == 1) {
                $deleted += $calendar_user->delete();
                $new_perm = 0;
            }
            if ($new_perm >= Calendar::PERMISSION_READABLE
                    && $calendar_user->permission != $new_perm) {
                $calendar_user->permission = $new_perm;
                if ($calendar_user->store()) {
                    if ($new_perm == Calendar::PERMISSION_READABLE) {
                        $read++;
                    } else {
                        $write++;
                    }
                }
            }
        }
        $sum = $deleted + $read + $write;
        if ($sum) {
            if ($deleted) {
                $details[] = sprintf(ngettext('Einer Person wurde die Berechtigungen entzogen.',
                        '%s Personen wurden die Berechtigungen entzogen.', $deleted), $deleted);
            }
            if ($read) {
                $details[] = sprintf(ngettext('Eine Person wurde auf leseberechtigt gesetzt.',
                        '%s Personen wurden auf leseberechtigt gesetzt.', $read), $read);
            }
            if ($write) {
                $details[] = sprintf(ngettext('Eine Person wurde auf schreibberechtigt gesetzt.',
                        '%s Personen wurden auf schreibberechtigt gesetzt.', $write), $write);
            }
            PageLayout::postMessage(MessageBox::success(sprintf(
                    ngettext('Die Berechtigungen von einer Person wurde geändert.',
                            'Die Berechtigungen von %s Personen wurden geändert.',
                    $sum), $sum), $details));
        // no message if the group was changed
        } else if (!Request::submitted('calendar_group_submit')) {
            PageLayout::postMessage(MessageBox::success(_('Es wurden keine Berechtigungen geändert.')));
        }
        $this->redirect($this->url_for('calendar/single/manage_access/'
                . $this->calendar->getRangeId(),
                ['group_filter' => Request::option('group_filter', 'list')]));
    }

    public function seminar_events_action($order_by = null, $order = 'asc')
    {
        $config_sem = $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE;
        if (!Config::get()->MY_COURSES_ENABLE_ALL_SEMESTERS && $config_sem == 'all') {
            $config_sem = 'future';
        }
        $this->sem_data = SemesterData::GetSemesterArray();

        $sem = ($config_sem && $config_sem != '0' ? $config_sem : Config::get()->MY_COURSES_DEFAULT_CYCLE);
        if (Request::option('sem_select')) {
            $sem = Request::get('sem_select', $sem);
        }
        if (!in_array($sem, words('future all last current')) && isset($sem)) {
            Request::set('sem_select', $sem);
        }
        $this->group_field = 'sem_number';
        // Needed parameters for selecting courses
        $params = ['group_field'         => $this->group_field,
                        'order_by'            => $order_by,
                        'order'               => $order,
                        'studygroups_enabled' => false,
                        'deputies_enabled'    => false];

        $this->sem_courses  = MyRealmModel::getPreparedCourses($sem, $params);

        $semesters       = new SimpleCollection(Semester::getAll());
        $this->sem       = $sem;
        $this->semesters = $semesters->orderBy('beginn desc');

        $this->bind_calendar = SimpleCollection::createFromArray(
                CourseMember::findBySQL('user_id = ? AND bind_calendar = 1',
                    [$GLOBALS['user']->id]))->pluck('seminar_id');

    }

    public function store_selected_sem_action()
    {
        CSRFProtection::verifySecurityToken();
        if (Request::submitted('store')) {
            $selected_sems = Request::intArray('selected_sem');
            $courses = SimpleORMapCollection::createFromArray(
                    CourseMember::findBySQL('user_id = ? AND Seminar_id IN (?)',
                    [$GLOBALS['user']->id, array_keys($selected_sems)]));
            $courses->each(function ($a) use ($selected_sems) {
                $a->bind_calendar = $selected_sems[$a->seminar_id];
                $a->store();
            });
            PageLayout::postMessage(MessageBox::success(
                    _('Die Auswahl der Veranstaltungen wurde gespeichert.')));
        }
        $this->redirect($this->url_for('calendar/single/' . $this->last_view));
    }

    /**
     * Retrieve the title of the calendar depending on calendar owner (range).
     *
     * @param SingleCalendar $calendar The calendar
     * @param string $title_end Additional text
     * @return string The complete title for the headline
     */
    protected function getTitle(SingleCalendar $calendar, $title_end)
    {
        $title = '';
        $status = '';
        if ($calendar->getRangeId() == $GLOBALS['user']->id) {
            $title = _('Mein persönlicher Terminkalender');
        } else {
            if ($calendar->getRange() == Calendar::RANGE_USER) {
            $title = sprintf(_('Terminkalender von %s'),
                    $calendar->range_object->getFullname());
            } else {
                $title = Context::getHeaderLine();
            }
            if ($calendar->havePermission(Calendar::PERMISSION_WRITABLE)) {
                $status = ' (' . _('schreibberechtigt') . ')';
            } else {
                $status = ' (' . _('leseberechtigt') . ')';
            }
        }
        return $title . ' - ' . $title_end . $status ;
    }

}
