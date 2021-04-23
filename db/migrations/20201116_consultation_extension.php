<?php
class ConsultationExtension extends Migration
{
    public function __construct($verbose = false)
    {
        parent::__construct($verbose);

        require_once __DIR__ . '/../../app/routes/Consultations.php';
    }

    public function description()
    {
        return 'Extends consultations as described in TIC #9614';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES (
                    'CONSULTATION_GARBAGE_COLLECT', '0', 'boolean', 'range', 'Terminvergabe',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Sollen abgelaufene Termine automatisch abgeräumt werden?'
                  ), (
                    'CONSULTATION_SHOW_GROUPED', '1', 'boolean', 'user', 'Terminvergabe',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Sollen die Termine nach Blöcken sortiert angezeigt werden?'
                  ), (
                    'CONSULTATION_TAB_TITLE', 'Terminvergabe', 'i18n', 'range', 'Terminvergabe',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Der Name des Reiters für die Terminvergabe'
                  )";
        DBManager::get()->exec($query);

        $query = "UPDATE `config`
                  SET `section` = 'Terminvergabe'
                  WHERE `section` = 'Sprechstunden'";
        DBManager::get()->exec($query);

        // Allow participants to be visible to other participants
        $query = "ALTER TABLE `consultation_blocks`
                  ADD COLUMN `show_participants` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `calendar_events`,
                  ADD COLUMN `require_reason` ENUM('no', 'optional', 'yes') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'optional'  AFTER `show_participants`,
                  ADD COLUMN `confirmation_text` TEXT NULL DEFAULT NULL AFTER `require_reason`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_bookings`
                  CHANGE COLUMN `reason` `reason` TEXT NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        // Allow more ranges
        $query = "ALTER TABLE `consultation_blocks`
                  DROP INDEX `teacher_id`,
                  CHANGE COLUMN `teacher_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  ADD COLUMN `range_type` ENUM('user', 'course', 'institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL AFTER `range_id`,
                  ADD COLUMN `teacher_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `range_type`,
                  ADD INDEX `range` (`range_id`, `range_type`),
                  ADD INDEX `teacher_id` (`teacher_id`)";
        DBManager::get()->exec($query);

        $query = "UPDATE `consultation_blocks`
                  SET `range_type` = 'user'";
        DBManager::get()->exec($query);

        $query = "UPDATE `consultation_blocks` AS cb0
                  JOIN `consultation_blocks` AS cb1 USING (`block_id`)
                  SET cb0.`range_id` = cb1.`course_id`,
                      cb0.`range_type` = 'course',
                      cb0.`teacher_id` = cb1.`range_id`
                  WHERE cb0.`course_id` IS NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_blocks`
                  DROP COLUMN `course_id`";
        DBManager::get()->exec($query);

        // install as core plugin
        $query = "INSERT IGNORE INTO `plugins` (
                    `pluginclassname`, `pluginname`, `plugintype`, `enabled`, `navigationpos`
                  ) VALUES (
                    'ConsultationModule', 'Terminvergabe', 'StandardPlugin,SystemPlugin,PrivacyPlugin,HomepagePlugin', 'yes', 1
                 )";
        DBManager::get()->exec($query);

        $plugin_id = DBManager::get()->lastInsertId();

        $sql = "INSERT IGNORE INTO `roles_plugins` (`roleid`, `pluginid`)
                SELECT `roleid`, ?
                FROM `roles`
                WHERE `system` = 'y'";
        DBManager::get()->execute($sql, [$plugin_id]);

        $sql = "INSERT IGNORE INTO `plugins_activated`
                SELECT DISTINCT ?, 'user', `range_id`, 1
                FROM `consultation_blocks`
                WHERE range_type = 'user'";
        DBManager::get()->execute($sql, [$plugin_id]);

        $sql = "INSERT IGNORE INTO `plugins_activated`
                SELECT DISTINCT ?, 'sem', `range_id`, 1
                FROM `consultation_blocks`
                WHERE range_type = 'course'";
        DBManager::get()->execute($sql, [$plugin_id]);

        RESTAPI\ConsumerPermissions::get('global')->activateRouteMap(
            new RESTAPI\Routes\Consultations()
        );
    }

    public function down()
    {
        $query = "DELETE FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'CONSULTATION_GARBAGE_COLLECT',
                      'CONSULTATION_SHOW_GROUPED',
                      'CONSULTATION_TAB_TITLE'
                  )";
        DBManager::get()->exec($query);

        // Allow participants to be visible to other participants
        $query = "ALTER TABLE `consultation_blocks`
                  DROP COLUMN `show_participants`,
                  DROP COLUMN `require_reason`,
                  DROP COLUMN `confirmation_text`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_bookings`
                  CHANGE COLUMN `reason` `reason` TEXT NOT NULL";
        DBManager::get()->exec($query);

        // Disallow more ranges
        $query = "ALTER TABLE `consultation_blocks`
                  ADD COLUMN `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `consultation_blocks`
                  WHERE `range_type` = 'institute'";
        DBManager::get()->exec($query);

        $query = "UPDATE `consultation_blocks` AS cb0
                  JOIN `consultation_blocks` AS cb1 USING (`block_id`)
                  SET cb0.`course_id` = cb1.`range_id`,
                      cb0.`range_id` = cb1.`teacher_id`
                  WHERE cb0.`course_id` IS NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_blocks`
                  DROP INDEX `teacher_id`,
                  DROP INDEX `range`,
                  DROP COLUMN `teacher_id`,
                  DROP COLUMN `range_type`,
                  CHANGE COLUMN `range_id` `teacher_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  ADD INDEX `teacher_id` (`teacher_id`)";
        DBManager::get()->exec($query);

        // Delete core plugin
        $query = "DELETE `plugins`, `roles_plugins`
                  FROM `plugins`
                  LEFT JOIN `roles_plugins` USING (`pluginid`)
                  WHERE `pluginclassname` = 'ConsultationModule'";
        DBManager::get()->exec($query);

        // Deactivate routemap
        RESTAPI\ConsumerPermissions::get('global')->deactivateRouteMap(
            new RESTAPI\Routes\Consultations()
        );
    }
}
