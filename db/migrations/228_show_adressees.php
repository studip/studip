<?php

class ShowAdressees extends Migration
{
    private $options = [
        [
            'name'        => 'SHOW_ADRESSEES_LIMIT',
            'description' => 'Ab wievielen Adressaten dürfen diese aus datenschutzgründen nicht mehr angezeigt werden in einer empfangenen Nachricht?',
            'section'     => 'global',
            'range'       => 'global',
            'type'        => 'integer',
            'value'       => '20'
        ]
    ];

    public function description()
    {
        return 'Lets Stud.IP display the adressees of a Stud.IP-message.';
    }

    public function up()
    {
        foreach ($this->options as $option) {
            DBManager::get()->execute("INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)",
                $option);
        }

        DBManager::get()->exec("
            ALTER TABLE message
            ADD COLUMN `show_adressees` tinyint(4) NOT NULL DEFAULT '0' AFTER `message`
        ");
    }

    public function down()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("DELETE FROM config WHERE field = :name");

        foreach ($this->options as $option) {
            $stmt->execute(['name' => $option['name']]);
        }
        DBManager::get()->exec("
            ALTER TABLE message
            DROP COLUMN `show_adressees`
        ");
    }
}
