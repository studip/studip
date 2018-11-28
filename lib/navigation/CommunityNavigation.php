<?php
# Lifter010: TODO
/*
 * CommunityNavigation.php - navigation for community page
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

/**
 * Navigation for the community page used for user interaction.
 * It includes the contacts, study groups and ranking.
 */
class CommunityNavigation extends Navigation
{
    public function __construct()
    {
        parent::__construct(_('Community'));
    }

    public function initItem()
    {
        parent::initItem();
        $onlinetip = _('Nur Sie sind online');
        $user_count = get_users_online_count();

        if ($user_count) {

            if ($user_count == 1) {
                $onlinetip = _('Außer Ihnen ist eine Person online');
            } else {
                $onlinetip = sprintf(_('Es sind außer Ihnen %d Personen online'), $user_count);
            }
        }

        $this->setImage(Icon::create('community', 'navigation', ["title" => $onlinetip]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm;

        parent::initSubNavigation();

        // online list
        $navigation = new Navigation(_('Wer ist online?'), 'dispatch.php/online/index');
        $this->addSubNavigation('online', $navigation);

        // contacts
        $navigation = new Navigation(_('Kontakte'), 'dispatch.php/contact');
        $this->addSubNavigation('contacts', $navigation);

        // study groups
        if (Config::get()->STUDYGROUPS_ENABLE) {
            $navigation = new Navigation(_('Studiengruppen'));
            $navigation->addSubNavigation('browse', new Navigation(_('Studiengruppensuche'), 'dispatch.php/studygroup/browse'));
            if (Config::get()->MY_COURSES_ENABLE_STUDYGROUPS) {
                $navigation->addSubNavigation('index', new Navigation(_('Meine Studiengruppen'), 'dispatch.php/my_studygroups'));
            }
            $this->addSubNavigation('studygroups', $navigation);
        }

        // ranking
        if (Config::get()->SCORE_ENABLE) {
            $navigation = new Navigation(_('Rangliste'), 'dispatch.php/score');
            $this->addSubNavigation('score', $navigation);
        }
    }
}
