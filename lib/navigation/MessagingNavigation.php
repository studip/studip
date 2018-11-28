<?php
# Lifter010: TODO
/*
 * MessagingNavigation.php - navigation for messaging area
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

class MessagingNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {

        parent::__construct(_('Nachrichten'));
    }

    public function initItem()
    {
        global $user, $neux;

        parent::initItem();
        $my_messaging_settings = UserConfig::get($user->id)->MESSAGING_SETTINGS;
        $lastVisitedTimestamp = isset($my_messaging_settings['last_box_visit'])?(int)$my_messaging_settings['last_box_visit']:0;
        
        $query = "SELECT SUM(mkdate > :time AND readed = 0) AS num_new,
                         SUM(readed = 0) AS num_unread,
                         SUM(readed = 1) AS num_read
                  FROM message_user
                  WHERE snd_rec = 'rec' AND user_id = :user_id AND deleted = 0";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':time', $lastVisitedTimestamp);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->execute();
        list($neux, $neum, $altm) = $statement->fetch(PDO::FETCH_NUM);
        
        $this->setBadgeNumber($neum);

        if ($neux > 0) {
            $tip = sprintf(ngettext('Sie haben %d neue ungelesene Nachricht',
                                    'Sie haben %d neue ungelesene Nachrichten', $neux), $neux);
        } else if ($neum > 1) {
            $tip = sprintf(ngettext('Sie haben %d ungelesene Nachricht',
                                    'Sie haben %d ungelesene Nachrichten', $neum), $neum);
        } else if ($altm > 1) {
            $tip = sprintf(ngettext('Sie haben %d alte empfangene Nachricht',
                                    'Sie haben %d alte empfangene Nachrichten', $altm), $altm);
        } else {
            $tip = _('Sie haben keine alten empfangenen Nachrichten');
        }


        $this->setImage(Icon::create('mail', 'navigation', ["title" => $tip]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();
        
        $messages = new Navigation(_('Nachrichten'), 'dispatch.php/messages/overview');
        $inbox = new Navigation(_('Eingang'), 'dispatch.php/messages/overview');
        $messages->addSubNavigation('inbox', $inbox);
        $messages->addSubNavigation('sent', new Navigation(_('Gesendet'), 'dispatch.php/messages/sent'));
        $this->addSubNavigation('messages', $messages);
        
    }
}
