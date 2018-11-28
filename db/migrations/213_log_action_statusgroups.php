<?php
class LogActionStatusgroups extends Migration
{
    public function up()
    {
        DBManager::get()->exec("
            INSERT IGNORE INTO log_actions
            SET action_id = MD5('STATUSGROUP_ADD_USER'),
                name = 'STATUSGROUP_ADD_USER',
                description = 'Nutzer wird zu einer Statusgruppe hinzugefügt',
                info_template = '%user fügt %user(%affected) zur %group(%coaffected) hinzu.',
                active = '1',
                expires = '0'
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO log_actions
            SET action_id = MD5('STATUSGROUP_REMOVE_USER'),
                name = 'STATUSGROUP_REMOVE_USER',
                description = 'Nutzer wird aus einer Statusgruppe gelöscht',
                info_template = '%user entfernt %user(%affected) aus %group(%coaffected).',
                active = '1',
                expires = '0'
        ");
    }

    public function down()
    {
        DBManager::get()->exec("
            DELETE FROM log_actions WHERE action_id = MD5('STATUSGROUP_ADD_USER')
        ");
        DBManager::get()->exec("
            DELETE FROM log_actions WHERE action_id = MD5('STATUSGROUP_REMOVE_USER')
        ");
    }
}