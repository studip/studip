<?php

class PersonalNotificationsUser extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'personal_notifications_user';
        $config['belongs_to']['notification'] = [
            'class_name'  => 'PersonalNotifications',
            'foreign_key' => 'personal_notification_id'
        ];
        parent::configure($config);
    }
}
