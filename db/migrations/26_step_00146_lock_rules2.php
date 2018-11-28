<?php
class Step00146LockRules2 extends Migration
{
    function description () {
        return 'adds field `permission` to table lock_rules, adds new config entries';
    }

    function up () {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `lock_rules` ADD `permission`
                 ENUM( 'tutor', 'dozent', 'admin', 'root' )
                  NOT NULL DEFAULT 'dozent' AFTER `lock_id` ;");
        $db->exec(" INSERT IGNORE INTO `config` 
                ( `config_id` , `parent_id` , `field` , `value` ,
                 `is_default` , `type` , `range` , `section` ,
                  `position` , `mkdate` , `chdate` , `description` ,
                   `comment` , `message_template` )
                VALUES (
                MD5( 'RANGE_TREE_ADMIN_PERM' ) , '', 'RANGE_TREE_ADMIN_PERM',
                 'admin', '1', 'string', 'global', '', '0',
                  UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) ,
                   'mit welchem Status darf die Einrichtungshierarchie bearbeitet werden (admin oder root)', '', ''
                ), (
                MD5( 'SEM_TREE_ADMIN_PERM' ) , '', 'SEM_TREE_ADMIN_PERM',
                 'admin', '1', 'string', 'global', '', '0', UNIX_TIMESTAMP( ) ,
                  UNIX_TIMESTAMP( ) , 'mit welchem Status darf die Veranstaltungshierarchie bearbeitet werden (admin oder root)', '', ''
                ), (
                MD5( 'SEMESTER_ADMINISTRATION_ENABLE' ) , '', 'SEMESTER_ADMINISTRATION_ENABLE',
                 '1', '1', 'boolean', 'global', '', '0', UNIX_TIMESTAMP( ) ,
                  UNIX_TIMESTAMP( ) , 'schaltet die Semesterverwaltung ein oder aus', '', ''
                ) ");
      
    }

    function down () {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `lock_rules` DROP `permission`");
        
        $db->exec("DELETE FROM config WHERE field = 'RANGE_TREE_ADMIN_PERM'");
        $db->exec("DELETE FROM config WHERE field = 'SEM_TREE_ADMIN_PERM'");
    }
}
?>