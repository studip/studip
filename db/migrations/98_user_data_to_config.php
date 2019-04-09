<?php

class UserDataToConfig extends Migration
{

    function __construct()
    {
        parent::__construct();
        $this->new_configs = [
        'calendar_user_control_data' => ['name'=>'CALENDAR_SETTINGS',
                                               'range'=>'user',
                                               'type'=>'array',
                                               'description'=>'persönliche Einstellungen des Kalenders',
                                               'value'=> json_encode(
                                                   [
                                                    "view"             => "showweek",
                                                    "start"            => 9,
                                                    "end"              => 20,
                                                    "step_day"         => 900,
                                                    "step_week"        => 3600,
                                                    "type_week"        => "LONG",
                                                    "holidays"         => TRUE,
                                                    "sem_data"         => TRUE,
                                                    "delete"           => 0
                                                    ])
                                               ],

        'my_messaging_settings' => ['name'=>'MESSAGING_SETTINGS',
                                               'range'=>'user',
                                               'type'=>'array',
                                               'description'=>'persönliche Einstellungen Nachrichtenbereich',
                                               'value'=> json_encode(
                                                   [
                                                    "show_only_buddys"             => FALSE,
                                                    "delete_messages_after_logout" => FALSE,
                                                    "timefilter"                   => '30d',
                                                    "opennew"                      => 1,
                                                    "logout_markreaded"            => FALSE,
                                                    "openall"                      => FALSE,
                                                    "addsignature"                 => FALSE,
                                                    "save_snd"                     => TRUE,
                                                    "sms_sig"                      => '',
                                                    "send_view"                    => FALSE,
                                                    "confirm_reading"              => 3,
                                                    "send_as_email"                => FALSE,
                                                    "folder"                       => ['in' =>  ['dummy'], 'out' =>  ['dummy']]
                                                    ])
                                               ],
        'forum' => ['name'=>'FORUM_SETTINGS',
                                               'range'=>'user',
                                               'type'=>'array',
                                               'description'=>'persönliche Einstellungen Forum',
                                               'value'=> json_encode(
                                                   [
                                                    'neuauf'      => false,
                                                    'rateallopen' => true,
                                                    'showimages'  => true,
                                                    'sortthemes'  => 'last',
                                                    'themeview'   => 'mixed',
                                                    'presetview'  => 'mixed',
                                                    'shrink'      => 7 * 24 * 60 * 60, // = 1 Woche
                                                   ])
                                               ],

        'my_schedule_settings' => ['name'=>'SCHEDULE_SETTINGS',
                                               'range'=>'user',
                                               'type'=>'array',
                                               'description'=>'persönliche Einstellungen Stundenplan',
                                               'value'=> json_encode(
                                                   [
                                                    "glb_start_time"=> 8,
                                                    "glb_end_time"  => 19,
                                                    "glb_days"      =>  [ 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 0 => 0, ],
                                                    "glb_sem"       => null,
                                                    "converted"     => true])
                                               ],

        'homepage_cache_own' => ['name'=>'PROFILE_LAST_VISIT',
                                       'range'=>'user',
                                       'type'=>'integer',
                                       'description'=>'Zeitstempel des letzten Besuchs der Profilseite',
                                       'value'=> 0],
        'CurrentLogin' => ['name'=>'CURRENT_LOGIN_TIMESTAMP',
                                       'range'=>'user',
                                       'type'=>'integer',
                                       'description'=>'Zeitstempel des Logins',
                                       'value'=> 0],
        'LastLogin' => ['name'=>'LAST_LOGIN_TIMESTAMP',
                                       'range'=>'user',
                                       'type'=>'integer',
                                       'description'=>'Zeitstempel des vorherigen Logins',
                                       'value'=> 0],
        'my_studip_settings' => ['name' =>'PERSONAL_STARTPAGE',
                                       'range' => 'user',
                                       'type' => 'integer',
                                       'description' => 'Persönliche Startseite',
                                       'value'=> 0],
        '_my_sem_group_field' => ['name' =>'MY_COURSES_GROUPING',
                                       'range' => 'user',
                                       'type' => 'string',
                                       'description' => 'Gruppierung der Veranstaltungsübersicht',
                                       'value'=> ''],
        '_my_sem_open' => ['name' =>'MY_COURSES_OPEN_GROUPS',
                                       'range' => 'user',
                                       'type' => 'array',
                                       'description' => 'geöffnete Gruppen der Veranstaltungsübersicht',
                                       'value'=> '[]'],
        '_my_admin_inst_id' => ['name' =>'MY_INSTITUTES_DEFAULT',
                                       'range' => 'user',
                                       'type' => 'string',
                                       'description' => 'Standard Einrichtung in der Veranstaltungsübersicht für Admins',
                                       'value'=> ''],
               ];
    }
    function description()
    {
        return 'migrates user forum settings from user_data table to user_config table; adds array type to config table';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE `config` MODIFY `type` enum('boolean','integer','string','array') NOT NULL DEFAULT 'boolean'");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `user_online` (
                                  `user_id` char(32) NOT NULL,
                                  `last_lifesign` int(10) unsigned NOT NULL,
                                  PRIMARY KEY (`user_id`),
                                  KEY `last_lifesign` (`last_lifesign`)
                                ) ENGINE=MyISAM");
        DBManager::get()->exec("INSERT INTO user_online (user_id,last_lifesign) SELECT sid,UNIX_TIMESTAMP(changed) FROM user_data INNER JOIN auth_user_md5 ON sid = user_id");

        $stmt = DBManager::get()->prepare("
            REPLACE INTO config
            (config_id, field, value, is_default, `type`, `range`, mkdate, chdate, description, comment)
            VALUES
            (MD5(:name), :name, :value, 1, :type, :range, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description, '')
            ");

        foreach ($this->new_configs as $values) {
            $stmt->execute($values);
        }
        $check = DBManager::get()->prepare("DELETE FROM user_config WHERE field=? AND user_id=?");
        $stmt = DBManager::get()->prepare("
            REPLACE INTO user_config (userconfig_id, user_id, field, value, mkdate, chdate, comment)
            VALUES (?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),'')");

        //for all users:
        $db = DBManager::get()->query("SELECT sid,val FROM user_data INNER JOIN auth_user_md5 ON sid = user_id");
        while ($rs = $db->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $rs['sid'];
            $vars = @unserialize($rs['val']);
            if (is_array($vars)) {
                foreach(['my_messaging_settings','forum','my_schedule_settings','calendar_user_control_data'] as $key) {
                    $option = $this->new_configs[$key];
                    $defaults = json_decode($option['value'], true);
                    if (is_array($vars[$key])) {
                        $old_values = array_intersect_key((array)$vars[$key], $defaults);
                        $new_values = array_merge($defaults, $old_values);
                        $check->execute([$option['name'], $user_id]);
                        $stmt->execute([md5($option['name'].$user_id), $user_id, $option['name'], json_encode($new_values)]);
                    }
                }
                foreach(['homepage_cache_own','CurrentLogin','LastLogin','_my_sem_group_field','_my_admin_inst_id'] as $key) {
                    $option = $this->new_configs[$key];
                    if (isset($vars[$key])) {
                        $check->execute([$option['name'], $user_id]);
                        $stmt->execute([md5($option['name'].$user_id), $user_id, $option['name'], (string)$vars[$key]]);
                    }
                }
                if (isset($vars['my_studip_settings']['startpage_redirect'])) {
                    $option = $this->new_configs['my_studip_settings'];
                    $check->execute([$option['name'], $user_id]);
                    $stmt->execute([md5($option['name'].$user_id), $user_id, $option['name'], (int)$vars['my_studip_settings']['startpage_redirect']]);
                }
                if (isset($vars['_my_sem_open'])) {
                    $option = $this->new_configs['_my_sem_open'];
                    $check->execute([$option['name'], $user_id]);
                    $stmt->execute([md5($option['name'].$user_id), $user_id, $option['name'], json_encode($vars['_my_sem_open'])]);
                }
            }
        }
        DBManager::get()->exec("DROP TABLE `user_data`");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `config` MODIFY `type` enum('boolean','integer','string') NOT NULL DEFAULT 'boolean'");
        $db->exec("DROP TABLE IF EXISTS `user_online`");
        $db->exec("CREATE TABLE IF NOT EXISTS `user_data` (
                  `sid` varchar(32) NOT NULL DEFAULT '',
                  `val` mediumtext NOT NULL,
                  `changed` timestamp NOT NULL,
                  PRIMARY KEY (`sid`),
                  KEY `changed` (`changed`)
                  ) ENGINE=MyISAM");
        foreach ($this->new_configs as $config) {
            $db->exec("DELETE FROM config WHERE field = " . $db->quote($config['name']));
            $db->exec("DELETE FROM user_config WHERE field = " . $db->quote($config['name']));
        }
    }
}
