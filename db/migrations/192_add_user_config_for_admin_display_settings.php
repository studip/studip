<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class AddUserConfigForAdminDisplaySettings extends Migration
{
    public function description()
    {
        return 'Adds user config entry for "PLUGINADMIN_DISPLAY_SETTINGS"';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (
                    `config_id`, `parent_id`, `field`, `value`, `is_default`,
                    `type`, `range`, `section`, `mkdate`, `chdate`, `description`
                  ) VALUES (
                    MD5(:field), '', :field, :value, 1, 'array', 'user', '',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'PLUGINADMIN_DISPLAY_SETTINGS');
        $statement->bindValue(':value', json_encode(['plugin_filter' => null, 'core_filter' => 'yes']));
        $statement->bindValue(':description', 'Speichert die Darstellungseinstellungen der Pluginadministration');
        $statement->execute();
    }

    public function down()
    {
        $query = "DELETE FROM `config` WHERE `field` = 'PLUGINADMIN_DISPLAY_SETTINGS'";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `user_config` WHERE `field` = 'PLUGINADMIN_DISPLAY_SETTINGS'";
        DBManager::get()->exec($query);
    }
}
