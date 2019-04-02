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
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
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
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
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
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
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

        // Check database format
        $query = "SHOW COLUMNS FROM `SprechstundenTerminDesc`
                  WHERE `Field` IN ('note_on_schedule', 'in_calendar')";
        $statement = DBManager::get()->query($query);

        if ($statement->rowCount() !== 2) {
            $this->announce('Unable to migrate SprechstundenPlugin data due to incompatible database format');
            return false;
        }


        // Detect which plugin version was used
        $query = "SHOW COLUMNS FROM SprechstundenTerminDesc LIKE 'size'";
        $has_size = (bool) DBManager::get()->query($query)->fetchColumn();
        $size_col = $has_size ? '`size`' : 1;

        // Migrate blocks
        $query = "INSERT INTO `consultation_blocks` (
                    `block_id`, `teacher_id`, `start`, `end`,
                    `room`, `calendar_events`, `note`, `size`,
                    `mkdate`, `chdate`
                ) VALUES (
                    NULL, :teacher_id, :start, :end,
                    :room, :calendar, :note, :size,
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                )";
        $insert = DBManager::get()->prepare($query);

        $blocks = [];

        $query = "SELECT `id`, `dozent_id`,
                         `ort`, `in_calendar`, `note_on_schedule`, {$size_col} AS `size`,
                         `am`, `intervall`,
                         FROM_UNIXTIME(`start_date`) AS start_date,
                         FROM_UNIXTIME(`end_date`) AS end_date
                  FROM `SprechstundenTerminDesc`";
        $statement = DBManager::get()->query($query);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($statement as $row) {
            extract($row);

            $start_date = strtotime($start_date);
            $end_date   = strtotime($end_date);

            $insert->bindValue(':teacher_id', $dozent_id);
            $insert->bindValue(':room', $ort);
            $insert->bindValue(':calendar', $in_calendar);
            $insert->bindValue(':note', $note_on_schedule);
            $insert->bindValue(':size', $size);

            $current = $start_date;
            while (date('w', $current) != $am) {
                $current = strtotime('+1 day', $current);
            }

            $blocks[$id] = [];
            while ($current <= $end_date) {
                $start = $this->adjustTimestamp($current, $start_date);
                $end   = $this->adjustTimestamp($current, $end_date);

                $insert->bindValue(':start', $start);
                $insert->bindValue(':end', $end);
                $insert->execute();

                $blocks[$id][] = [
                    'start' => $start,
                    'end'   => $end,
                    'id'    => DBManager::get()->lastInsertId(),
                ];

                $current = strtotime("+{$intervall} weeks", $current);
            }
        }

        // Migrate slots
        $query = "INSERT INTO `consultation_slots` (
                    `slot_id`, `block_id`,
                    `start_time`,
                    `end_time`,
                    `note`, `teacher_event_id`,
                    `mkdate`, `chdate`
                ) VALUES (
                    :slot_id, :block_id, :start, :end, :note, :teacher_event_id,
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                )";
        $insert = DBManager::get()->prepare($query);

        $query = "SELECT szs.`id`, st.`desc_id`,
                         szs.`position`, std.`dauer`,
                         FROM_UNIXTIME(st.`start_time`) AS start_time,
                         FROM_UNIXTIME(st.`end_time`) AS end_time,
                         IFNULL(szs.`note_on_schedule`, '') AS `note`,
                         sa.`event_id_dozent`
                  FROM `SprechstundenTermin` AS st
                  JOIN `SprechstundenZeitSlot` AS szs ON szs.`termin_id` = st.`id`
                  JOIN `SprechstundenTerminDesc` AS std ON st.`desc_id` = std.`id`
                  LEFT JOIN `SprechstundenAnmeldung` AS sa ON sa.`zeitslot_id` = szs.`id`
                  GROUP BY szs.`id`";
        $statement = DBManager::get()->query($query);
        $statement->setFetchMode(PDO::FETCH_ASSOC);


        $used = [];
        foreach ($statement as $row) {
            extract($row);

            // get block id
            if (!isset($blocks[$desc_id])) {
                continue;
            }

            $duration = $dauer * ($position - 1);
            $start = strtotime("+{$duration} minutes", strtotime($start_time));
            $end   = strtotime("+{$dauer} minutes", $start);

            $block_id = false;
            foreach ($blocks[$desc_id] as $block) {
                if ($start <= $block['end'] && $end >= $block['start']) {
                    $block_id = $block['id'];
                    break;
                }
            }

            if (!in_array($block_id, $used)) {
                $used[] = $block_id;
            }

            $insert->bindValue(':slot_id', $id);
            $insert->bindValue(':block_id', $block_id);
            $insert->bindValue(':start', $start);
            $insert->bindValue(':end', $end);
            $insert->bindValue(':note', $note);
            $insert->bindValue(':teacher_event_id', $event_id_dozent);
            $insert->execute();
        }

        // Remove empty blocks
        $query = "DELETE FROM `consultation_blocks`
                  WHERE `block_id` NOT IN (?)";
        DBManager::get()->execute($query, [$used ?: '']);

        // Migrate bookings
        $query = "INSERT INTO `consultation_bookings` (
                    `booking_id`, `slot_id`, `user_id`,
                    `reason`, `student_event_id`,
                    `mkdate`, `chdate`
                  )
                  SELECT `id`, `zeitslot_id`, `user_id`,
                         `grund`, `event_id_student`,
                         UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
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

    private function adjustTimestamp($current, $other)
    {
        $time = date('H:i', $other);
        $current = strtotime("today {$time}", $current);
        return $current;
    }
}
