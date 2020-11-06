<?php
class TransferCalpermission extends Migration {

    function description() {
        return 'Transfers all calpermissions from contacts';
    }

    function up() {
        DBManager::get()->execute("INSERT INTO calendar_user SELECT owner_id, user_id, calpermission as permission, unix_timestamp() as mkdate, unix_timestamp() as chdate FROM contact WHERE calpermission > 0;");
        DBManager::get()->execute('ALTER TABLE contact DROP COLUMN calpermission');
    }

    function down() {
        DBManager::get()->execute("ALTER TABLE contact ADD COLUMN `calpermission` tinyint(1) unsigned NOT NULL DEFAULT '0'");
        DBManager::get()->execute("UPDATE contact JOIN calendar_user USING (owner_id, user_id) SET calpermission = permission");
    }

}
