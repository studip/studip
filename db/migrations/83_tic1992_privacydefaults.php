<?php

class tic1992PrivacyDefaults extends Migration
{

    static $config_entries = array(
        // Private chat room visible per default?
        array(
            'name'        => 'CHAT_VISIBILITY_DEFAULT',
            'type'        => 'boolean',
            'value'       => 1,
            'description' => 'Ist der private Chatraum sichtbar, falls der Nutzer nichts anderes eingestellt hat?'
        ),
        // E-Mail address visible per default?
        array(
            'name'        => 'EMAIL_VISIBILITY_DEFAULT',
            'type'        => 'boolean',
            'value'       => 1,
            'description' => 'Ist die eigene Emailadresse sichtbar, falls der Nutzer nichts anderes eingestellt hat?'
        ),
        // Private chat room visible per default?
        array(
            'name'        => 'ONLINE_VISIBILITY_DEFAULT',
            'type'        => 'boolean',
            'value'       => 1,
            'description' => 'Sind Nutzer sichtbar in der Wer ist online-Liste, falls sie nichts anderes eingestellt haben?'
        ),
        // Private chat room visible per default?
        array(
            'name'        => 'SEARCH_VISIBILITY_DEFAULT',
            'type'        => 'boolean',
            'value'       => 1,
            'description' => 'Sind Nutzer auffindbar in der Personensuche, falls sie nichts anderes eingestellt haben?'
        )
    );

    function description()
    {
        return 'add default privacy settings';
    }

    function up()
    {
        $db = DBManager::get();
        $query = $db->prepare("INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES (MD5(?), '', ?, ?, '1', ?, 'global', 'privacy', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ?, '', '')");

        // insert new configuration entries
        foreach (self::$config_entries as $entry) {
            $query->execute(array($entry['name'], $entry['name'], $entry['value'], $entry['type'], $entry['description']));
        }

        // remove old default entry with user id 'studip'
        $db->exec("DELETE FROM `user_visibility` WHERE `user_id`='studip'");

    }

    function down()
    {
        $db = DBManager::get();
        $query = $db->prepare("DELETE FROM `config` WHERE `field` = ?");

        foreach (self::$config_entries as $entry) {
            $query->execute(array($entry['name']));
        }

        // insert default values
        $db->exec("INSERT INTO `user_visibility` VALUES ('studip', 1, 1, 1, 1, '', 0, ".time().")");

    }
}
?>
