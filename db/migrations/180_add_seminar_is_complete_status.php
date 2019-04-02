<?php
class AddSeminarIsCompleteStatus extends Migration
{
    public function description()
    {
        return 'Adds column "is_complete" to table "seminare".';
    }

    public function up()
    {
        $query = "ALTER TABLE `seminare`
                  ADD COLUMN `is_complete` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        $query = "INSERT INTO `config` (`config_id`, `field`, `value`, `type`,
                                        `range`, `section`, `mkdate`, `chdate`,
                                        `description`, `comment`)
                  VALUES (MD5(CONCAT('CONFIG_', :field)), :field, 0, 'boolean',
                          'global', 'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                          'Definiert, ob auf der Admin-Veranstaltunggseite der Komplett-Status für Veranstaltungen aufgeführt sein soll', '')";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'ADMIN_COURSES_SHOW_COMPLETE');
        $statement->execute();
    }

    public function down()
    {
        $query = "ALTER TABLE `seminare`
                  DROP COLUMN `is_complete`";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `config` WHERE `field` = :field";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'ADMIN_COURSES_SHOW_COMPLETE');
        $statement->execute();
    }
}
