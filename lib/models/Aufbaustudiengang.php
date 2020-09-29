<?php
/**
 * Aufbaustudiengang.php
 * Model class for postgraduate study courses (table mvv_aufbaustudiengang)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

class Aufbaustudiengang extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_aufbaustudiengang';

        $config['belongs_to']['grund_studiengang'] = array(
            'class_name' => 'Studiengang',
            'foreign_key' => 'grund_stg_id',
            'assoc_func' => 'findCached',
        );
        $config['has_one']['aufbau_studiengang'] = array(
            'class_name' => 'Studiengang',
            'foreign_key' => 'aufbau_stg_id',
            'assoc_func' => 'findCached',
        );

        $config['i18n_fields']['kommentar'] = true;

        parent::configure($config);
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        return $this->aufbau_studiengang->getDisplayName($options);
    }

    public function validate()
    {
        $ret = parent::validate();
        $types = $GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'];
        /*
        if (!$types[$this->typ]) {
            $ret['typ'] = true;
            $messages = array(_('Unbekannter Typ des Aufbaustudiengangs'));
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
         *
         */
        return $ret;
    }

    /**
     * Inherits the status of the parent study course.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->aufbau_studiengang) {
            return $this->aufbau_studiengang->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_STUDIENGANG']['STATUS']['default'];
        }
        return parent::getStatus();
    }

}
