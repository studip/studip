<?php
/**
 * MvvFileRange.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */

class MvvFileRange extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_files_ranges';

        $config['belongs_to']['mvv_file'] = array(
            'class_name' => 'MvvFile',
            'foreign_key' => 'mvvfile_id',
            'assoc_func' => 'findCached',
        );

        $config['additional_fields']['range_type']['get'] = 'getRangeType';


        parent::configure($config);
    }

    /**
     * Returns the rangetype of the document based on its foldertype.
     *
     * @return bool|string Returns false on failure, otherwise the name of the range.
     */
    public function getRangeType()
    {
        return $this->range_type;
    }

}
