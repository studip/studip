<?php
class Biest8034ConfigSwitchForChildInsts extends Migration
{
    public function description()
    {
        return 'add config switch to show child institutes for admins in course overview';
    }

    public function up()
    {
        $config_data = array('name' =>'MY_INSTITUTES_INCLUDE_CHILDREN',
           'range' => 'user',
           'type' => 'boolean',
           'description' => 'Sollen untergeordnete Institute mit angezeigt werden in der Veranstaltungsübersicht für Admins?',
           'value'=> 0
        );

        $stmt = DBManager::get()->prepare("
            REPLACE INTO config
            (field, value, `type`, `range`, mkdate, chdate, description)
            VALUES
            (:name, :value, :type, :range, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
        ");

        $stmt->execute($config_data);
    }

    public function down()
    {
        DBManager::get()->exec("DELETE FROM config WHERE field = 'MY_INSTITUTES_INCLUDE_CHILDREN'");
        DBManager::get()->exec("DELETE FROM config_values WHERE field = 'MY_INSTITUTES_INCLUDE_CHILDREN'");
    }
}
