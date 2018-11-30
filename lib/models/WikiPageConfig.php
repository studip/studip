<?php
/**
 * WikiPageConfig.php - Wiki page permissions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class WikiPageConfig extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'wiki_page_config';

        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['institute'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'range_id',
        ];

        parent::configure($config);
    }

    /**
     * Specialized getValue that returns the course default for edit_restricted.
     *
     * @param  string $field Field to get the value for
     * @return mixed
     */
    public function getValue($field)
    {
        if ($field !== 'edit_restricted' || !$this->isNew() || !$this->range_id) {
            return parent::getValue($field);
        }

        return CourseConfig::get($this->range_id)->WIKI_COURSE_EDIT_RESTRICTED;
    }

    /**
     * Returns whether the current settings are the default settings (db-wise
     * and from course setting).
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->read_restricted === $this->getDefaultValue('read_restricted') &&
               $this->edit_restricted === CourseConfig::get($this->range_id)->WIKI_COURSE_EDIT_RESTRICTED;
    }
}
