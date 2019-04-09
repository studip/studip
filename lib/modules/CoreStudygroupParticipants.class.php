<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreStudygroupParticipants implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        $navigation = new Navigation(_('Teilnehmende'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/course/members/index");
        $navigation->setImage(Icon::create('persons', 'inactive'));
        return $navigation;
    }
    
    function getTabNavigation($course_id) {
        $navigation = new Navigation(_('Teilnehmende'), "dispatch.php/course/studygroup/members/".$course_id);
        $navigation->setImage(Icon::create('persons', 'info_alt'));
        $navigation->setActiveImage(Icon::create('persons', 'info'));
        return ['members' => $navigation];
    }

    /** 
     * @see StudipModule::getMetadata()
     */ 
    function getMetadata()
    {
         return [];
    }
}
