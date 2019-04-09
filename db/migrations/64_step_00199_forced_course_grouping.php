<?php
class Step00199ForcedCourseGrouping extends Migration
{

    static $config_entries = [
        [
            'name'        => 'MY_COURSES_FORCE_GROUPING',
            'type'        => 'string',
            'value'       => 'sem_number',
            'description' => 'Legt fest, ob die persönliche Veranstaltungsübersicht systemweit zwangsgruppiert werden soll, wenn keine eigene Gruppierung eingestellt ist. Werte: not_grouped, sem_number, sem_tree_id, sem_status, gruppe, dozent_id.'
        ]
    ];

    function description()
    {
        return 'add configuration entry for forced grouping of My courses for all users who haven\'t grouped for themselves';
    }

    function up()
    {
        $db = DBManager::get();
        $query = $db->prepare("INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES (MD5(?), '', ?, ?, '1', ?, 'global', '', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ?, '', '')");

        // insert new configuration entries
        foreach (self::$config_entries as $entry) {
            $query->execute([$entry['name'], $entry['name'], $entry['value'], $entry['type'], $entry['description']]);
        }
    }

    function down()
    {
        $db = DBManager::get();
        $query = $db->prepare("DELETE FROM `config` WHERE `config_id` = MD5(?)");

        foreach (self::$config_entries as $entry) {
            $query->execute([md5($entry['name'])]);
        }
    }
}
?>
