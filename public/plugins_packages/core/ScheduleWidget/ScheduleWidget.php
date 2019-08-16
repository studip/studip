<?php
/*
 * This class displays a seminar-schedule for
 * users on a seminar-based view and for admins on an institute based view
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

require_once 'app/models/calendar/schedule.php';
require_once __DIR__ . '/CalendarWidgetView.php';

class ScheduleWidget extends StudIPPlugin implements PortalPlugin
{
    /**
     * Returns the name of the plugin/widget.
     *
     * @return String containing the name
     */
    public function getPluginName()
    {
        return _('Mein Stundenplan');
    }

    /**
     * Return the template for the widget.
     *
     * @return Flexi_PhpTemplate The template containing the widget contents
     */
    public function getPortalTemplate()
    {
        $view = CalendarScheduleModel::getUserCalendarView(
            $GLOBALS['user']->id,
            false,
            false,
            $days = array(0,1,2,3,4)
        );

        $template = $GLOBALS['template_factory']->open('shared/string');
        $template->content = CalendarWidgetView::createFromWeekView($view)->render();

        return $template;
    }
}
