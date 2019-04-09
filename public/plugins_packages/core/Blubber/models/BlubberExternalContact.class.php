<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once __DIR__ . '/BlubberUser.class.php';
require_once __DIR__ . '/BlubberContactAvatar.class.php';

/**
 * Entity for external contacts that write something in blubber. In most cases
 * this is for anonymous writers.
 */
class BlubberExternalContact extends SimpleORMap implements BlubberContact
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_external_contact';

        parent::configure($config);
    }

    /**
     * Finds a user by it's ID, but returns a class BlubberExternalContact or
     * an object of the class contact_type. If such a class exists, this means
     * that another plugin wants to handle the contact.
     * @param string $user_id
     * @return BlubberContact-object
     */
    static public function find($user_id) {
        $user = parent::find($user_id);
        if (class_exists($user['contact_type'])) {
            $new_user = new $user['contact_type']();
            $new_user->setData($user->toArray());
            $new_user->setNew(false);
            return $new_user;
        } else {
            return $user;
        }
    }

    /**
     * finds user by email and returns an instance of BlubberExternalContact
     * @param string $email
     * @return BlubberExternalContact
     */
    static public function findByEmail($email) {
        $email = mb_strtolower($email);
        $user = self::findBySQL("mail_identifier = ".DBManager::get()->quote($email));
        if (!count($user)) {
            $user = new BlubberExternalContact();
            $user['mail_identifier'] = $email;
            return $user;
        }
        return $user[0];
    }

    /**
     * Returns the name that should be displayed.
     * @return string name
     */
    public function getName() {
        return $this->content['name'];
    }

    /**
     * Returns an URL to the user, which is a mailto-link.
     * @return type
     */
    public function getURL() {
        return $this['mail_identifier'] ? "mailto:".$this['mail_identifier'] : null;
    }

    /**
     * Returns an BlubberContactAvatar-object.
     * @return BlubberContactAvatar
     */
    public function getAvatar() {
        return BlubberContactAvatar::getAvatar($this->getId());
    }
    
    /**
     * This sends an email to the user to recognize him/her that he/she was 
     * mentioned in a blubber.
     * @param type $posting 
     */
    public function mention($posting) {
        $url = $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/"
            . $posting['root_id']
            . ($posting['context_type'] === "course" ? '?cid='.$posting['Seminar_id'] : "");
        $message = sprintf(
            _("%s hat Sie in einem Blubber erwähnt. Zum Beantworten klicken auf Sie auf folgenen Link:\n\n%s\n"),
            get_fullname(), 
            $url
        );
        StudipMail::sendMessage($this['mail_identifier'], _("Sie wurden erwähnt."), $message);
    }

    /**
     * Returns if the given user is following the BlubberExternalContact
     * @param string|null $user_id
     * @return boolean 
     */
    public function isFollowed($user_id = null) {
        $user_id or $user_id = $GLOBALS['user']->id;
        $statement = DBManager::get()->prepare(
            "SELECT 1 " .
            "FROM blubber_follower " .
            "WHERE studip_user_id = :user_id " .
                "AND external_contact_id = :contact_id " .
                "AND left_follows_right = '1' " .
        "");
        $statement->execute([
            'user_id' => $user_id,
            'contact_id' => $this->getId()
        ]);
        return (bool) $statement->fetch(PDO::FETCH_COLUMN, 0);
    }
}