<?php
class ConfigForDownloadCounterDisplay extends Migration
{
    public function description()
    {
        return 'Adds config entries for download counter display';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'DISPLAY_DOWNLOAD_COUNTER', 'flat', 'string', 'global',
                    'files', 'Steuert die Anzeige der Anzahl der Downloads in Dateisichten (\"always\" zeigt die Anzahl immer an, \"flat\" nur in \"Alle Dateien\", jeder andere Wert schaltet die Anzeige komplett aus)',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'DISPLAY_DOWNLOAD_COUNTER'";
        DBManager::get()->exec($query);
    }
}
