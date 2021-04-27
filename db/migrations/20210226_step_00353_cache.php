<?php

class Step00353Cache extends Migration
{
    public function description()
    {
        return 'Manage available caches and their settings.';
    }

    public function up()
    {
        // Create database table for available system cache types
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `cache_types` (
            `cache_id` INT NOT NULL AUTO_INCREMENT,
            `class_name` VARCHAR(255) NOT NULL,
            `chdate` INT(11) DEFAULT NULL,
            `mkdate` INT(11) DEFAULT NULL,
            PRIMARY KEY (`cache_id`),
            UNIQUE KEY (`class_name`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $types = [
            'StudipDbCache',
            'StudipFileCache',
            'StudipMemcachedCache',
            'StudipRedisCache'
        ];

        // Insert pre-defined cache types in to database
        foreach ($types as $type) {
            DBManager::get()->execute(
                "INSERT IGNORE INTO `cache_types` (`class_name`, `mkdate`, `chdate`)
                VALUES (?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())", [$type]);
        }

        // Remove other set cache config entries.
        $fields = ['cache_class', 'cache_class_file', 'cache_init_args'];
        DBManager::get()->execute("DELETE FROM `config_values` WHERE `field` IN (:fields)", ['fields' => $fields]);
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` IN (:fields)", ['fields' => $fields]);

        // Set StudipDbCache as (possibly new) default
        $cache = [
            'type' => 'StudipDbCache',
            'config' => []
        ];
        DBManager::get()->execute("INSERT IGNORE INTO `config` VALUES
            (:field, :value, 'array', 'global', 'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :desc)",
            [
                'field' => 'SYSTEMCACHE',
                'value' => json_encode($cache),
                'desc' => 'Typ und Konfiguration des zu verwendenden Systemcaches'
            ]
        );
    }

    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `cache_types`");
        DBManager::get()->execute("DELETE FROM `config_values` WHERE `field` = :field", ['field' => 'SYSTEMCACHE']);
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` = :field", ['field' => 'SYSTEMCACHE']);
    }
}
