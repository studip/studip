<?php
/**
 * InstitutePlanColumn
 * model class for table institute_plan_columns
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author  Timo Hartge <hartge@data-quest.de>
 * @license GPL2 or any version
 * @since   Stud.IP 4.5
 * *
 * @property string range_id database column
 * @property string column database column
 * @property string name database column
 * @property string visible database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class InstitutePlanColumn extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'institute_plan_columns';
        parent::configure($config);
    }

    public static function findByInstitute($institute_id)
    {
        return self::findBySQL('range_id=?', [$institute_id]);
    }

    /**
     * returns the last column for given institute
     *
     * @param string $institut_id
     *
     * @return InstitutePlanColumn last column
     */
    public static function getLastColumnOfInstitute($institute_id)
    {
        return self::findOneBySQL('range_id=? ORDER BY `column` DESC', [$institute_id]);
    }

    /**
     * Sets the visibility of multiple columns for given institute
     *
     * @param string $institut_id
     * @param array $columns_change column number to be changed
     * @param int $visibility 0 or 1
     *
     * @return int number of changes
     */
    public static function setVisbilities($institute_id, $columns_change, $visibility)
    {
        $changes = 0;
        foreach (self::findBySQL('range_id=?', [$institute_id]) as $plan_column) {
            if (in_array($plan_column->column, $columns_change)) {
                $plan_column->visible = $visibility;
                if ($plan_column->store()) {
                    $changes++;
                }
            }
        }
        return $changes;
    }

}
