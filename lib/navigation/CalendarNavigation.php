<?php
# Lifter010: TODO
/*
 * CalendarNavigation.php - navigation for calendar
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class CalendarNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        global $perm;
        
        parent::__construct(_('Planer'));

        if (!$perm->have_perm('admin') && get_config('SCHEDULE_ENABLE')) {
            $planerinfo = _('Stundenplan');
        } else {
            $planerinfo = _('Termine');
        }

        $this->setImage(Icon::create('schedule', 'navigation', ["title" => $planerinfo]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm, $atime;

        parent::initSubNavigation();

        // schedule
        if (!$perm->have_perm('admin') && get_config('SCHEDULE_ENABLE')) {
            $navigation = new Navigation(_('Stundenplan'), 'dispatch.php/calendar/schedule');
            $this->addSubNavigation('schedule', $navigation);
        }

        // calendar
        $atime = $atime ? intval($atime) : Request::int($atime);
        if (get_config('CALENDAR_ENABLE')) {
            $navigation = new Navigation(_('Terminkalender'), 'dispatch.php/calendar/single', ['self' => 1]);
            $this->addSubNavigation('calendar', $navigation);
        }
    }
}
