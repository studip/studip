<?php
class Tic5661AddConfig extends Migration
{
    public function description()
    {
        return 'Adds the config entry "NEW_INDICATOR_THRESHOLD" that indicates '
             . 'after how many days an item is consired "old" and will no '
             . 'longer be marked as new';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->execute("
                INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ", [
                    'name'        => 'NEW_INDICATOR_THRESHOLD',
                    'value'       => '180',
                    'type'        => 'integer',
                    'range'       => 'global',
                    'section'     => 'global',
                    'description' => 'Gibt an, nach wieviel Tagen ein Eintrag als alt '
                        . 'angesehen und nicht mehr rot markiert werden '
                        . 'soll (0 angeben, um nur das tatsächliche Alter) '
                        . 'zu betrachten.',
            ]
        );
    }

    public function down()
    {
        DBManager::get()->exec("DELETE FROM config WHERE `field` = 'NEW_INDICATOR_THRESHOLD'");
    }
}
