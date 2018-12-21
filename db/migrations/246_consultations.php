<?php
class Consultations extends Migration
{
    public function up()
    {
        // Create tables
        $query = "CREATE TABLE IF NOT EXISTS `consultation_blocks` (
                    `block_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `teacher_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `start` INT(11) UNSIGNED NOT NULL,
                    `end` INT(11) UNSIGNED NOT NULL,
                    `room` VARCHAR(128) NOT NULL,
                    `calendar_events` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Create events for slots',
                    `note` TEXT NOT NULL,
                    `size` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'How many people may book a slot',
                    `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                    PRIMARY KEY (`block_id`),
                    KEY `teacher_id` (`teacher_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `consultation_slots` (
                    `slot_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `block_id` INT(11) UNSIGNED NOT NULL,
                    `start_time` INT(11) UNSIGNED NOT NULL,
                    `end_time` INT(11) UNSIGNED NOT NULL,
                    `note` TEXT NOT NULL,
                    `teacher_event_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                    PRIMARY KEY (`slot_id`),
                    KEY `block_id` (`block_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `consultation_bookings` (
                    `booking_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `slot_id` INT(11) UNSIGNED NOT NULL,
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `reason` TEXT NOT NULL,
                    `student_event_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                    PRIMARY KEY (`booking_id`),
                    KEY `block_id` (`slot_id`),
                    KEY `user_id` (`user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        // Add config entries (global and user)
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'CONSULTATION_ENABLED', '0', 'boolean', 'global',
                      'Sprechstunden', 'Schaltet die Sprechstunden global ein',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'CONSULTATION_REQUIRED_PERMISSION', 'dozent', 'string', 'global',
                      'Sprechstunden', 'Ab welcher Rechtestufe dürfen Nutzer Sprechstunden anlegen (user, autor, tutor, dozent, admin, root)',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'CONSULTATION_ALLOW_DOCENTS_RESERVING', '0', 'boolean', 'global',
                      'Sprechstunden', 'Dozenten können sich bei anderen Dozenten anmelden',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'CONSULTATION_SEND_MESSAGES', '1', 'boolean', 'user',
                      'Sprechstunden', 'Nachrichten empfangen über Buchungen/Stornierungen',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $this->migratePlugin();
    }

    public function down()
    {
        // Remove tables
        $query = "DROP TABLE IF EXISTS `consultation_blocks`,
                                       `consultation_slots`,
                                       `consultation_bookings`";
        DBManager::get()->exec($query);

        // Remove config entries
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'CONSULTATION_ENABLED',
                      'CONSULTATION_REQUIRED_PERMISSION'
                  )";
        DBManager::get()->exec($query);
    }

    protected function migratePlugin()
    {
        // Detect plugin by tables
        $query = "SHOW TABLES LIKE 'SprechstundenAnmeldung'";
        $statement = DBManager::get()->query($query);

        if ($statement->rowCount() === 0) {
            // No plugin data
            return;
        }

        // Detect which plugin version was used
        $query = "SHOW COLUMNS FROM SprechstundenTerminDesc LIKE 'size'";
        $has_size = (bool) DBManager::get()->query($query)->fetchColumn();
        $size_col = $has_size ? '`size`' : 1;

        // Migrate blocks
        $query = "INSERT INTO `consultation_blocks` (
                    `block_id`, `teacher_id`, `start`, `end`,
                    `room`, `calendar_events`, `note`, `size`
                  )
                  SELECT `id`, `dozent_id`, `start_date`, `end_date`,
                         `ort`, `in_calendar`, `note_on_schedule`, {$size_col}
                  FROM `SprechstundenTerminDesc`";
        DBManager::get()->exec($query);

        // Migrate slots
        $query = "INSERT INTO `consultation_slots` (
                    `slot_id`, `block_id`,
                    `start_time`,
                    `end_time`,
                    `note`, `teacher_event_id`
                  )
                  SELECT szs.`id`, st.`desc_id`,
                         st.`start_time` + szs.`position` * std.`dauer` * 60,
                         st.`start_time` + (szs.`position` + 1) * std.`dauer` * 60,
                         szs.`note_on_schedule`,
                         sa.`event_id_dozent`
                  FROM `SprechstundenTermin` AS st
                  JOIN `SprechstundenZeitSlot` AS szs ON szs.`termin_id` = st.`id`
                  JOIN `SprechstundenTerminDesc` AS std ON st.`desc_id` = std.`id`
                  LEFT JOIN `SprechstundenAnmeldung` AS sa ON sa.`zeitslot_id` = szs.`id`
                  GROUP BY szs.`id`";
        DBManager::get()->exec($query);

        // Migrate bookings
        $query = "INSERT INTO `consultation_bookings` (
                    `booking_id`, `slot_id`, `user_id`,
                    `reason`, `student_event_id`
                  )
                  SELECT `id`, `zeitslot_id`, `user_id`,
                         `grund`, `event_id_student`
                  FROM `SprechstundenAnmeldung`";
        DBManager::get()->exec($query);

        // Activate consultations if plugin was enabled
        $query = "SELECT `enabled` = 'yes'
                  FROM `plugins`
                  WHERE `pluginclassname` = 'SprechstundenPlugin'";
        $enabled = (bool) DBManager::get()->query($query)->fetchColumn();

        if (!$enabled) {
            return;
        }

        $query = "INSERT INTO `config_values` (
                    `field`, `range_id`, `value`,
                    `mkdate`, `chdate`, `comment`
                  ) VALUES (
                    'CONSULTATION_ENABLED', 'studip', '1',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''
                  )";
        DBManager::get()->exec($query);

        // Deactivate plugin
        $query = "UPDATE `plugins`
                  SET `enabled` = 'no'
                  WHERE `pluginclassname` = 'SprechstundenPlugin'";
        DBManager::get()->exec($query);
    }
}
