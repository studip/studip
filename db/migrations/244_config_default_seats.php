<?php

class ConfigDefaultSeats extends Migration
{
    public function description()
    {
        return 'add config option for RESOURCES_ROOM_REQUEST_DEFAULT_SEATS';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO config (field, value, type, section, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'RESOURCES_ROOM_REQUEST_DEFAULT_SEATS',
            'description' => 'Vorbelegung der Sitzplatzanzahl einer Raumanfrage, falls der Kurs keine max. Teilnehmerzahl hat',
            'section'     => 'resources',
            'type'        => 'integer',
            'value'       => '0'
        ]);
    }

    public function down()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?');
        $stmt->execute(['RESOURCES_ROOM_REQUEST_DEFAULT_SEATS']);
    }
}
