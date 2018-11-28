<?php

class PersonalNotificationsUser extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'personal_notifications_user';
        $config['belongs_to']['notification'] = array(
            'class_name'  => 'PersonalNotifications',
            'foreign_key' => 'personal_notification_id'
        );
        parent::configure($config);
    }
}
