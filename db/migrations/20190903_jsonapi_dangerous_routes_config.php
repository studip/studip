<?php

class JsonapiDangerousRoutesConfig extends Migration
{
    public function description()
    {
        return 'Adds system config option allowing possibly dangerous JSONAPI routes '
            .'(e.g. root-users may delete users).';
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
                    'JSONAPI_DANGEROUS_ROUTES_ALLOWED', '0', 'boolean', 'global', 'global',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Wenn diese Einstellung gesetzt ist, dürfen auch potentiell gefährliche JSONAPI-Routen genutzt werden. (Zum Beispiel dürfen dann root-Nutzer auch andere Nutzer löschen.)'
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'JSONAPI_DANGEROUS_ROUTES_ALLOWED'";
        DBManager::get()->exec($query);
    }
}
