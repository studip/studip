<?php
class StEP00331IliasInterface extends Migration
{

    function description()
    {
        return 'Adds new ilias interface module to Stud.IP';
    }

    function up()
    {
        $db = DBManager::get();
        $ilias_interface_config = [
                        'moduletitle' => _('ILIAS'),
                        'edit_moduletitle' => false,
                        'search_active' => true,
                        'show_offline' => false,
                        'cache' => true
        ];
        $sql = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('ILIAS_INTERFACE_BASIC_SETTINGS', ?, 'array', 'global', 'modules', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '')";
        $db->execute($sql, [json_encode($ilias_interface_config)]);
        $sql = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('ILIAS_INTERFACE_SETTINGS', '[]', 'array', 'global', 'modules', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '')";
        $db->exec($sql);
        $sql = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('ILIAS_INTERFACE_ENABLE', '0', 'boolean', 'global', 'modules', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '')";
        $db->exec($sql);
        $sql = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('ILIAS_INTERFACE_MODULETITLE', 'ILIAS', 'string', 'course', 'modules', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '')";
        $db->exec($sql);
        $db->exec("ALTER TABLE `auth_extern` DROP PRIMARY KEY");
        $db->exec("ALTER TABLE `auth_extern` ADD PRIMARY KEY (`studip_user_id`, `external_user_system_type`, `external_user_type`)");

        $db->exec("ALTER TABLE `auth_extern` ADD `external_user_token` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '' AFTER `external_user_password`");
        $db->exec("ALTER TABLE `auth_extern` ADD `external_user_token_valid_until` INT(11) NOT NULL DEFAULT '0' AFTER `external_user_token`");

        $db->exec("ALTER TABLE `object_user_visits` CHANGE `type` `type` ENUM('vote','documents','forum','literature','schedule','scm','sem','wiki','news','eval','inst','elearning_interface','ilias_interface','participants') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'vote'");

        // install as core plugin
        $sql = "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
                VALUES ('IliasInterfaceModule', 'Ilias-Interface', 'StandardPlugin,SystemPlugin', 'yes', 1)";
        $db->exec($sql);
        $sql = "INSERT INTO roles_plugins (roleid, pluginid) SELECT roleid, ? FROM roles WHERE `system` = 'y' AND roleid < 7";
        $db->execute($sql, [$db->lastInsertId()]);

    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `auth_extern` DROP PRIMARY KEY");
        $db->exec("ALTER TABLE `auth_extern` ADD PRIMARY KEY (`studip_user_id`, `external_user_system_type`)");
        $db->exec("ALTER TABLE `auth_extern` DROP `external_user_token`");
        $db->exec("ALTER TABLE `auth_extern` DROP `external_user_token_valid_until` ");
        $db->exec("DELETE FROM `config` WHERE `field` LIKE 'ILIAS_INTERFACE%'");
        $db->exec("DELETE FROM `config_values` WHERE `field` LIKE 'ILIAS_INTERFACE%'");
        $db->exec("DELETE FROM `plugins` WHERE `pluginclassname` = 'IliasInterfaceModule'");
    }
}