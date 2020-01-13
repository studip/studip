<?php
class Step00338Instituteplaning extends Migration
{
    public function description()
    {
        return 'add table for institute planning columns';
    }

    public function up()
    {
        $query = "CREATE TABLE `institute_plan_columns` (
                    `range_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `column` INT(4) NOT NULL,
                    `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL ,
                    `visible` TINYINT(1) NOT NULL DEFAULT 1,
                    `mkdate` INT(11) NOT NULL,
                    `chdate` INT(11) NOT NULL,
                    PRIMARY KEY (`range_id`, `column`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT INTO `datafields` (
                    `datafield_id`, `name`, `object_type`, `edit_perms`, `view_perms`,
                    `priority`, `type`, `typeparam`, `is_required`, `is_userfilter`,
                    `description`, `system`
                  ) VALUES (
                    '69f6485f3c937766866a03d9d642ecbb', 'zugeordnete Planungsspalte', 'sem', 'admin', 'root',
                    0, 'textline', '', 0, 0,
                    'Gibt die zugeordnete Planungsspalte im Veranstaltungsplan an.', 0
                  ), (
                    '41cda2be71fe9efd6e28b853fc0681f3', 'zugeordnete Planungsfarbe', 'sem', 'admin', 'root',
                    0, 'textline', '', 0, 0,
                    'Zugewiesene Farbe im Veranstaltungsplaner', 0
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO config (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`, `description`
                  ) VALUES (
                    :field, :value, 'string', 'global', 'modules',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description
                  )";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':field' => 'INSTITUTE_COURSE_PLAN_START_HOUR',
            ':value' => '08:00',
            'description' => 'The start hour for the default view of the institute course plan.',
        ]);

        $statement->execute([
            ':field' => 'INSTITUTE_COURSE_PLAN_END_HOUR',
            ':value' => '20:00',
            'description' => 'The end hour for the default view of the institute course plan.',
        ]);
    }

    public function down()
    {
        DBManager::get()->exec('DROP TABLE `institute_plan_columns`');

        $query = "DELETE FROM `datafields`
                  WHERE `datafield_id` IN (
                      '69f6485f3c937766866a03d9d642ecbb',
                      '41cda2be71fe9efd6e28b853fc0681f3'
                  )";
        DBManager::get()->exec($query);

        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'INSTITUTE_COURSE_PLAN_START_HOUR',
                      'INSTITUTE_COURSE_PLAN_END_HOUR'
                  )";
        DBManager::get()->exec($query);
    }
}
