<?php

class Step00184Html5Video extends Migration
{
    /**
     * new config options to install
     */
    private $options_new = [
        [
            'name' => 'LOAD_EXTERNAL_MEDIA',
            'description' => 'Sollen externe Medien über [img/flash/audio/video] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=proxy benutzen.',
            'section' => '',
            'type' => 'string',
            'value' => 'deny'
        ]
    ];

    /**
     * old config options to remove
     */
    private $options_old = [
        [
            'name' => 'EXTERNAL_IMAGE_EMBEDDING',
            'description' => 'Sollen externe Bilder über [img] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=image proxy benutzen',
            'section' => '',
            'type' => 'string',
            'value' => 'deny'
        ], [
            'name' => 'EXTERNAL_FLASH_MOVIE_EMBEDDING',
            'description' => 'Sollen externe Flash-Filme mit Hilfe des [flash]-Tags der Schnellformatierung eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=image proxy benutzen',
            'section' => '',
            'type' => 'string',
            'value' => 'deny'
        ]
    ];

    /**
     * short description of this migration
     */
    function description()
    {
        return 'add database table for generic media proxy';
    }

    /**
     * insert list of options into config table
     */
    function insertConfig($options)
    {
        $db = DBManager::get();
        $time = time();

        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, $time, $time, :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    /**
     * remove list of options from config table
     */
    function deleteConfig($options)
    {
        $db = DBManager::get();

        $stmt = $db->prepare("DELETE FROM config WHERE field = :name");

        foreach ($options as $option) {
            $stmt->execute(['name' => $option['name']]);
        }
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE media_cache (
                    id varchar(32) NOT NULL,
                    type varchar(64) NOT NULL,
                    chdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    expires timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (id)) ENGINE=MyISAM");

        $db->exec("DROP TABLE image_proxy_cache");

        $this->insertConfig($this->options_new);
        $this->deleteConfig($this->options_old);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE image_proxy_cache (
                    id char(32) NOT NULL,
                    type char(10) NOT NULL,
                    length int(10) unsigned NOT NULL,
                    error char(15) NOT NULL,
                    chdate timestamp NOT NULL,
                    PRIMARY KEY (id),
                    KEY chdate (chdate, id)) ENGINE=MyISAM");

        $db->exec("DROP TABLE media_cache");

        $this->insertConfig($this->options_old);
        $this->deleteConfig($this->options_new);
    }
}
