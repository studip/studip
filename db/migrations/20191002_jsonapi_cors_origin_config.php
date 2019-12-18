<?php

class JsonapiCorsOriginConfig extends Migration
{
    public function description()
    {
        return 'Adds system config option allowing given domains as allowed CORS origins.';
    }

    /**
     * @SuppressWarnings(ShortMethodName)
     */
    public function up()
    {
        $query = "INSERT INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES (
                    'JSONAPI_CORS_ORIGIN', '[]', 'array', 'global', 'global',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Diese Einstellung definiert URIs, die mittels CORS auf die JSONAPI zugreifen dürfen.'
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'JSONAPI_CORS_ORIGIN'";
        DBManager::get()->exec($query);
    }
}
