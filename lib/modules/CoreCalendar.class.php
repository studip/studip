<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreCalendar implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('CALENDAR_GROUP_ENABLE')) {
            $navigation = new Navigation(_('Kalender'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/calendar/single/");
            $navigation->setImage(Icon::create('wiki', 'inactive'));
            return $navigation;
        }
    }
    
    function getTabNavigation($course_id) {
        if (get_config('CALENDAR_GROUP_ENABLE')) {
            $navigation = new Navigation(_('Kalender'), 'dispatch.php/calendar/single/');
            $navigation->setImage(Icon::create('schedule', 'info_alt'));
            $navigation->setActiveImage(Icon::create('schedule', 'info'));
            return ['calendar' => $navigation];
        } else {
            return null;
        }
    }

    /** 
     * @see StudipModule::getMetadata()
     */ 
    function getMetadata()
    {
        return [];
    }
}
