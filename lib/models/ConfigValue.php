<?php
/**
 * ConfigValue.php - model class for table config_value.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig <elmar.ludwig@uos.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @category    Stud.IP
 */

class ConfigValue extends SimpleORMap
{
    const RANGE_STUDIP = 'studip';

    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'config_values';
        $config['belongs_to']['entry'] = [
            'class_name' => \ConfigEntry::class,
            'foreign_key' => 'field',
        ];
        parent::configure($config);
    }

    public function isGlobalRange(): bool
    {
        return $this->content['range_id'] === self::RANGE_STUDIP;
    }

    public function getConfigObject(): ?\Config
    {
        if ($this->isGlobalRange()) {
            return \Config::get();
        }

        return ($range = $this->getRange()) ? $range->getConfiguration() : null;
    }

    public function getRange(): ?\Range
    {
        if ($this->isGlobalRange()) {
            return null;
        }

        return \RangeFactory::find($this->content['range_id']);
    }

    public function getTypedValue()
    {
        $config = $this->getConfigObject();

        return $config ? $config->getValue($this->content['field']) : $this->content['value'];
    }

    public function getTypedDefaultValue()
    {
        return \Config::get()->{$this->content['field']};
    }
}
