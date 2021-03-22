<?php
class MigrationHistoryReworked extends Migration
{
    public function description()
    {
        return 'Reverts migration 20201114 and adds appropriate log actions';
    }

    public function up()
    {
        // Drop columns
        $query = "ALTER TABLE `schema_versions`
                  DROP COLUMN `user_id`,
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);

        // Add log actions
        $query = "INSERT IGNORE INTO log_actions (
                    `action_id`, `name`, `description`, `info_template`, `active`, `expires`, `mkdate`, `chdate`
                  ) VALUES (
                    MD5(:name), :name, :description, :template, :active, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':name'        => 'MIGRATE_UP',
            ':description' => 'Migration wird durchgeführt',
            ':template'    => '%user hat Migration %affected ausgeführt (Domain: %coaffected)',
            ':active'      => 1,
        ]);
        $statement->execute([
            ':name'        => 'MIGRATE_DOWN',
            ':description' => 'Migration wird zurückgenommen',
            ':template'    => '%user hat Migration %affected zurückgenommen (Domain: %coaffected)',
            ':active'      => 1,
        ]);
    }

    public function down()
    {
        // Remove log actions
        $query = "DELETE `log_actions`, `log_events`
                  FROM `log_actions`
                  LEFT JOIN `log_events` USING (`action_id`)
                  WHERE MD5(:name) = `action_id`";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':name' => 'MIGRATE_UP']);
        $statement->execute([':name' => 'MIGRATE_DOWN']);

        // Add columns
        $query = "ALTER TABLE `schema_versions`
                  ADD COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
