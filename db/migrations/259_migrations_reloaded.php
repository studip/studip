<?php
// TODO: Remove PHP7 anonymous class for production
return new class() extends Migration
{
    public function description()
    {
        return 'switch from a single successive migration version number to '
             . 'a collection of already executed migrations';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `schema_versions` (
                    `domain` VARCHAR(255) COLLATE latin1_bin NOT NULL,
                    `version` BIGINT(20) UNSIGNED NOT NULL,
                    PRIMARY KEY `domain` (`domain`, `version`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "SELECT `domain`, `version`
                  FROM `schema_version`
                  WHERE `version` > 0";
        $rows = DBManager::get()->query($query)->fetchAll(PDO::FETCH_NUM);

        $query = "INSERT INTO `schema_versions`
                  VALUES (:domain, :version)";
        $statement = DBManager::get()->prepare($query);
        foreach ($rows as list($domain, $version)) {
            $statement->bindValue(':domain', $domain);

            for ($i = 1; $i <= $version; $i += 1) {
                $statement->bindValue(':version', $i);
                $statement->execute();
            }
        }

       // TODO: Remove for production
       $query = "DROP TABLE IF EXISTS `schema_version`";
       DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "CREATE TABLE IF NOT EXISTS `schema_version` (
                    `domain` VARCHAR(255) COLLATE latin1_bin NOT NULL,
                    `version` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`domain`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `schema_version`
                  SELECT `domain`, MAX(`version`)
                  FROM `schema_versions`
                  GROUP BY `domain`";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `schema_versions`";
        DBManager::get()->exec($query);
    }
};
