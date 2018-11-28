<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface ScorePlugin
{
    /**
     * Returns null or an array of associated arrays - each one to
     * indicate a db-table in which the plugin stores user activities.
     *
     * For a bullitin-board-plugin this array could look like this:
     *
     * return array(
     *     array(
     *         'table' => "bullitin_board_entries",
     *         'user_id_column' => "user_id",
     *         'date_column' => "mkdate",
     *         'where' => "public = '1'" //only public entries should be counted
     *     )
     * );
     *
     * @return null|array   of associated arrays
     */
    function getPluginActivityTables();
}
