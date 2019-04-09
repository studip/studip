<?php

class Step00296AppointmentRequests extends Migration
{

    /**
     * new config options to install
     */
    private $options_new = [
        [
            'name' => 'CALENDAR_GRANT_ALL_INSERT',
            'description' => 'Ermöglicht das Eintragen von Terminen in alle Nutzerkalender, ohne Beachtung des Rechtesystems.',
            'section' => 'modules',
            'type' => 'boolean',
            'value' => '0'
        ]
    ];

    /**
     * short description of this migration
     */
    function description()
    {
        return "Extends the calendar by the possibility to state an appointment in"
            . "another user's calendar as a requested appointment. "
            . "Displaying of course appointments is now optional.";
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
    public function up()
    {
        $this->insertConfig($this->options_new);
    }

    /**
     * revert this migration
     */
    public function down()
    {
        $this->deleteConfig($this->options_new);
    }
}
