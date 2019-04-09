<?php
class CreateOpenGraphDataTable extends Migration
{
    public function up() 
    {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `opengraphdata` (
                `url` varchar(1000) NOT NULL,
                `is_opengraph` tinyint(2) DEFAULT NULL,
                `title` text,
                `image` varchar(1024) DEFAULT NULL,
                `description` text,
                `type` varchar(64) DEFAULT NULL,
                `data` text NOT NULL,
                `last_update` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`url`)
            ) ENGINE=MyISAM
        ");
        $options[] =
            [
            'name'        => 'OPENGRAPH_ENABLE',
            'type'        => 'boolean',
            'value'       => '1',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'De-/Aktiviert OpenGraph-Informationen und deren Abrufen.'
            ];

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }
    
    public function down()
    {
        DBManager::get()->exec("DROP TABLE TABLE IF EXISTS `opengraph`");
        DBManager::get()->exec("DELETE FROM config WHERE field = 'OPENGRAPH_ENABLE'");
    }
}