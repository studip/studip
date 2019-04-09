<?php
/*
 * This is the controller for the personal calendar
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

/**
* This controller provides all functions for a calendar of an institute.
*/
class Institute_CalendarController extends AuthenticatedController
{

    private $calendar_settings = [];
    private $institute_id;

    # see Trails_Controller#before_filter
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->institute_id = Request::option('cid');
        $this->calendar_settings = Config::get()->getValue('CALENDAR_SETTINGS');
    }

    public function index_action()
    {
        // switch to the view the user has selected in his personal settings
        $this->redirect('institute/calendar/'
                . $this->calendar_settings['view']);
    }

    public function showday_action($timestamp = null)
    {
        $calendar = Calendar::getInstance(Calendar::RANGE_INST,
                $this->institute_id);
        PageLayout::setTitle(Context::getHeaderLine()
                . ' - ' . _("Terminkalender - Tagesansicht"));
        $_SESSION['calendar_sess_control_data']['view_prv'] = 'showday';
        Navigation::activateItem("/course/calendar/day");

        $atime = $timestamp ?: time();

        $at = date('G', $atime);
        if ($at >= $this->calendar_settings['start']
                && $at <= $this->calendar_settings['end'] || !$atime) {
            $st = $this->calendar_settings['start'];
            $et = $this->calendar_settings['end'];
        } elseif ($at < $$this->calendar_settings['start']) {
            $st = 0;
            $et = $this->calendar_settings['start'] + 2;
        } else {
            $st = $this->calendar_settings['end'] - 2;
            $et = 23;
        }

        $this->_calendar = $calendar;
        $this->atime = $atime;
        $this->cmd = 'showday';
        $this->st = $st;
        $this->et = $et;
    }


    /**
    * @todo der include muss weg
    */
    public function showweek_action($timestamp = null)
    {
        $calendar = Calendar::getInstance(Calendar::RANGE_INST,
                $this->institute_id);
        PageLayout::setTitle(Context::getHeaderLine()
                . ' - ' . _("Terminkalender - Wochenansicht"));
        $_SESSION['calendar_sess_control_data']['view_prv'] = 'showweek';
        Navigation::activateItem("/course/calendar/week");

        $atime = $timestamp ?: time();

        $at = date('G', $atime);
        if ($at >= $this->calendar_settings['start']
                && $at <= $this->calendar_settings['end'] || !$atime) {
            $st = $this->calendar_settings['start'];
            $et = $this->calendar_settings['end'];
        } elseif ($at < $this->calendar_settings['start']) {
            $st = 0;
            $et = $this->calendar_settings['start'] + 2;
        } else {
            $st = $this->calendar_settings['end'] - 2;
            $et = 23;
        }

        include_once 'lib/calendar/lib/DbCalendarWeek.class.php';
        $this->_calendar = $calendar;
        $this->atime = $atime;
        $this->cmd = 'showweek';
        $this->st = $st;
        $this->et = $et;
    }

    public function showmonth_action($timestamp = null)
    {

    }

    public function showyear_action($timestamp = null)
    {

    }

    public function event_action($event_id = null)
    {

    }

    public function seminar_events_action()
    {

    }

    public function showexport_action()
    {

    }

    public function export_action()
    {

    }



}
