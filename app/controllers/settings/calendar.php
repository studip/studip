<?php
/**
 * Settings_CalendarController - Administration of all user calendar related
 * settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'settings.php';

class Settings_CalendarController extends Settings_SettingsController
{
    /**
     * Set up this controller
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     * @throws AccessDeniedException if the calendar is not globally enabled
     */
    public function before_filter(&$action, &$args)
    {
        if (!Config::get()->CALENDAR_ENABLE) {
            throw new AccessDeniedException(_('Der Kalender ist nicht aktiviert.'));
        }

        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.MyStudIPTerminkalender');
        PageLayout::setTitle(_('Einstellungen des Terminkalenders anpassen'));
        Navigation::activateItem('/profile/settings/calendar_new');
        SkipLinks::addIndex(_('Einstellungen des Terminkalenders anpassen'), 'main_content', 100);
    }

    /**
     * Display a user's calendar settings
     */
    public function index_action()
    {
        $calendar_user_control_data = (array) UserConfig::get($GLOBALS['user']->id)->getValue('CALENDAR_SETTINGS');
        foreach ($calendar_user_control_data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Stores a user's calendar settings
     */
    public function store_action()
    {
        $this->check_ticket();

        $this->config->store('CALENDAR_SETTINGS', [
            'view'            => Request::option('cal_view'),
            'start'           => Request::option('cal_start'),
            'end'             => Request::option('cal_end'),
            'step_day'        => Request::option('cal_step_day'),
            'step_week'       => Request::option('cal_step_week'),
            'type_week'       => Request::option('cal_type_week'),
            'holidays'        => Request::option('cal_holidays'),
            'sem_data'        => Request::option('cal_sem_data'),
            'delete'          => Request::option('cal_delete'),
            'step_week_group' => Request::option('cal_step_week_group'),
            'step_day_group'  => Request::option('cal_step_day_group')
        ]);

        PageLayout::postSuccess(_('Ihre Einstellungen wurden gespeichert'));
        $this->redirect('settings/calendar');
    }
}
