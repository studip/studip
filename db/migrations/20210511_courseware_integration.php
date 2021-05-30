<?php

class CoursewareIntegration extends \Migration
{
    public function description()
    {
        return 'Create all Courseware database tables and settings';
    }

    public function up()
    {
        $db = \DBManager::get();

        $db->exec("CREATE TABLE `cw_blocks` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `container_id` int(11) NOT NULL,
              `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
              `position` int(11) NOT NULL,
              `block_type` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NULL,
              `visible` tinyint(1) NOT NULL,
              `payload` MEDIUMTEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`id`),
               INDEX index_container_id (`container_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_block_comments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `block_id` int(11) NOT NULL,
              `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `comment` MEDIUMTEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`id`),
               INDEX index_block_id (`block_id`),
               INDEX index_user_id (`user_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_bookmarks` (
              `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `element_id` int(11) NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`user_id`,`element_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_containers` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `structural_element_id` int(11) NOT NULL,
              `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
              `position` int(11) NOT NULL,
              `site` int(11) NOT NULL,
              `container_type` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `visible` tinyint(1) NOT NULL,
              `payload` MEDIUMTEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`id`),
               INDEX index_structural_element_id (`structural_element_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_structural_elements` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) NULL DEFAULT NULL,
              `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `range_type` ENUM('course', 'user') COLLATE latin1_bin,
              `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
              `position` int(11) NOT NULL,
              `title` varchar(255) NOT NULL,
              `image_id`  CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
              `purpose` ENUM('content', 'template', 'oer', 'portfolio', 'draft', 'other') COLLATE latin1_bin,
              `payload` MEDIUMTEXT NOT NULL,
              `public` tinyint(1) NOT NULL,
              `release_date` int(11) NOT NULL,
              `withdraw_date` int(11) NOT NULL,
              `read_approval` TEXT NOT NULL,
              `write_approval` TEXT NOT NULL,
              `copy_approval` TEXT NOT NULL,
              `external_relations` TEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`id`),
               INDEX index_parent_id (`parent_id`),
               INDEX index_range_id (`range_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_user_data_fields` (
              `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `block_id` int(11) NOT NULL,
              `payload` TEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`user_id`,`block_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_user_progresses` (
              `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `block_id` int(11) NOT NULL,
              `grade` float NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`user_id`,`block_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_block_feedbacks` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `block_id` int(11) NOT NULL,
              `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `feedback` MEDIUMTEXT NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
               PRIMARY KEY (`id`),
               INDEX index_block_id (`block_id`),
               INDEX index_user_id (`user_id`)
            )
        ");

        $query = 'INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = $db->prepare($query);

        $statement->execute([
            ':name'        => 'COURSEWARE_FAVORITE_BLOCK_TYPES',
            ':description' => 'In dieser Konfigurationseinstellung können Nutzende ihre Lieblingsblocktypen speichern.',
            ':range'       => 'user',
            ':type'        => 'array',
            ':value'       => '[]'
        ]);
        $statement->execute([
            ':name'        => 'COURSEWARE_SEQUENTIAL_PROGRESSION',
            ':description' => 'Mit dieser Konfigurationseinstellung wird für eine Courseware festgelegt, ob Lernende sequentiell durch die Inhalte gehen müssen.',
            ':range'       => 'range',
            ':type'        => 'boolean',
            ':value'       => '0'
        ]);
        $statement->execute([
            ':name'        => 'COURSEWARE_EDITING_PERMISSION',
            ':description' => 'Mit dieser Konfigurationseinstellung wird für eine Courseware festgelegt, welche Rechtestufe Inhalte editieren dürfen.',
            ':range'       => 'range',
            ':type'        => 'string',
            ':value'       => 'dozent'
        ]);
        $statement->execute([
            ':name'        => 'COURSEWARE_LAST_ELEMENT',
            ':description' => 'In dieser Konfigurationseinstellung werden die zuletzt besuchten Elemente in allen Coursewares abgelegt.',
            ':range'       => 'user',
            ':type'        => 'array',
            ':value'       => '[]'
        ]);

        $db->exec("INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
            VALUES ('CoursewareModule', 'Courseware', 'CorePlugin,StudipModule,SystemPlugin', 'yes', 1)
        ");

        $sql = "INSERT INTO roles_plugins (roleid, pluginid)
            SELECT roleid, ?
            FROM roles
            WHERE `system` = 'y'
        ";
        $db->execute($sql, [$db->lastInsertId()]);
    }

    public function down()
    {
        $db = \DBManager::get();

        $db->exec("DROP TABLE IF EXISTS `cw_blocks`, `cw_block_comments`, `cw_bookmarks`, `cw_containers`, `cw_structural_elements`, `cw_user_data_fields`, `cw_user_progresses`, `cw_block_feedbacks`");


        $query = "DELETE `config`, `config_values`
                FROM `config`
                LEFT JOIN `config_values` USING (`field`)
                WHERE `field` = :field";
        $statement = $db->prepare($query);

        $statement->execute( [ ':field' => 'COURSEWARE_FAVORITE_BLOCK_TYPES' ] );
        $statement->execute( [ ':field' => 'COURSEWARE_SEQUENTIAL_PROGRESSION' ] );
        $statement->execute( [ ':field' => 'COURSEWARE_EDITING_PERMISSION' ] );
        $statement->execute( [ ':field' => 'COURSEWARE_LAST_ELEMENT' ] );

        $db->exec("DELETE plugins, roles_plugins FROM plugins LEFT JOIN roles_plugins USING(pluginid)
            WHERE pluginclassname = 'CoursewareModule'
        ");
    }
}
