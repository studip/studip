<?php
/**
 * @author  Kai Rechtern <kairechtern@gmail.com>
 */
class CourseNumberFormatConfig extends Migration
{
    /**
     * new config options to install
     */
    private $options = [
        [
            'name'        => 'COURSE_NUMBER_FORMAT',
            'description' => 'Erlaubt das Eintragen eines regulären Ausdrucks zur Validierung einer Veranstaltungsnummer. Im Kommentarfeld kann ein entsprechender Hilfetext hinterlegt werden.',
            'section'     => 'global',
            'type'        => 'string',
            'value'       => ''
        ]
    ];

    /**
     * short description of this migration
     */
    public function description()
    {
        return 'Adds the config entry "COURSE_NUMBER_FORMAT". This allows '
            . 'restricting newly entered course numbers to a fixed format.';
    }

    /**
     * perform this migration
     */
    public function up()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ");

        foreach ($this->options as $option) {
            $stmt->execute($option);
        }
    }

    /**
     * revert this migration
     */
    public function down()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("DELETE FROM config WHERE field = :name");

        foreach ($this->options as $option) {
            $stmt->execute(['name' => $option['name']]);
        }
    }
}
