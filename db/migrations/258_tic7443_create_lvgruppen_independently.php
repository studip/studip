<?php
class Tic7443CreateLvgruppenIndependently extends Migration
{
    public function description()
    {
        return 'add config switch to allow creation of lvgruppen independently';
    }

    public function up()
    {
        $config_data = [
            'name'        => 'MVV_ALLOW_CREATE_LVGRUPPEN_INDEPENDENTLY',
            'range'       => 'global',
            'type'        => 'boolean',
            'description' => 'Soll das Anlegen von LV-Gruppen unabhängig von bestehenden Modulteilen auf der Verwaltungsseite für LV-Gruppen möglich sein?',
            'value'       => 0
        ];

        $stmt = DBManager::get()->prepare("
            REPLACE INTO config
            (field, value, `type`, `range`, mkdate, chdate, description)
            VALUES
            (:name, :value, :type, :range, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
        ");

        $stmt->execute($config_data);
    }

    public function down()
    {
        DBManager::get()->exec("DELETE FROM config WHERE field = 'MVV_ALLOW_CREATE_LVGRUPPEN_INDEPENDENTLY'");
        DBManager::get()->exec("DELETE FROM config_values WHERE field = 'MVV_ALLOW_CREATE_LVGRUPPEN_INDEPENDENTLY'");
    }
}
