<?php
# Lifter010: TODO
/**
 * CalendarColumn.class.php - a column for a CalendarView
 *
 * This class represents an entry-column like "monday" in the calendar
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class CalendarColumn {
    protected static $number = 0;
    protected $title = "";
    protected $id = "";
    public    $entries = array();
    protected $url = "";
    protected $grouped    = false;

    /**
     * creates instance of type CalendarColumn
     *
     * @param string  $id  necessary if you want JavaScript enabled for this calendar
     * @return CalendarColumn
     */
    static public function create($id = null) {
        $column = new CalendarColumn($id);
        return $column;
    }

    /**
     * constructor
     *
     * @param string  $id  necessary if you want JavaScript enabled for this column
     */
    public function __construct($id = null) {
        $id !== null || $id = md5(uniqid("CalendarColumn_".self::$number++));
        $this->setId($id);
    }

    /**
     * returns the id of the column
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * sets the id for this column, which is only necessary if you want
     * Javascript to be enabled for this calendar
     *
     * @param string  $id  new id for this column
     * @return CalendarColumn
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * sets a title like "monday" for this column, which will be displayed in the calendar
     *
     * @param string  $new_title  new title
     * @return CalendarColumn
     */
    public function setTitle($new_title) {
        $this->title = $new_title;
        return $this;
    }

    /**
     * returns the title of this column like "monday"
     *
     * @return string title of column
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * sets the url to be directed to when clicking on the title of the column.
     * Usually this is a single-day-view of the calendar.
     *
     * @param string  $new_url  an url
     * @return CalendarColumn
     */
    public function setURL($new_url) {
        $this->url = $new_url;
        return $this;
    }

    /**
     * returns the URL of the column (see setURL)
     *
     * @return string an url
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * adds a new entry in the column. The entry needs to be an associative array
     * with parameters as follows:
     *
     * @param array  $entry_array  associative array for an entry in the column like
     * array (
     *    'color' => the color in hex (css-like, without the #)
     *    'start' => the (start hour * 100) + (start minute)
     *    'end'   => the (end hour * 100) + (end minute)
     *    'title' => the entry`s title
     *    'content' => whatever shall be the content of the entry as a string
     * )
     */
    public function addEntry($entry_array) {
        if (!isset($entry_array['start']) || !isset($entry_array['end'])
                || !isset($entry_array['title']) ) {
            throw new InvalidArgumentException('The entry '. print_r($entry_array, true) .' does not follow the specifications!');
        } else {
            $this->entries[] = $entry_array;
        }
        return $this;
    }

    /**
     * adds many entries to the column. For the syntax of an entry see addEntry()
     *
     * @param array  $entries_array
     * @return CalendarColumn
     */
    public function addEntries($entries_array = array()) {
        foreach ($entries_array as $entry_array) {
            $this->addEntry($entry_array);
        }
        return $this;
    }

    /**
     * returns all entries of this column
     *
     * @return array of arrays like
     * array (
     *    'color' => the color in hex (css-like, without the #)
     *    'start' => the (start hour * 100) + (start minute)
     *    'end'   => the (end hour * 100) + (end minute)
     *    'title' => the entry`s title
     *    'content' => whatever shall be the content of the entry as a string
     * )
     */
    public function getEntries() {
        return $this->entries;
    }

    /**
     * deletes all entries of this column. So the only way to edit an entry is
     * getting all entries with getEntries, edit this entry, eraseEntries() and
     * addEntries(). Not very short, but at least it works.
     *
     * @return CalendarColumn
     */
    public function eraseEntries() {
        $this->entries = array();
        return $this;
    }

    /**
     * Returns an array of calendar-entries, grouped by day and additionally grouped by same start and end
     * if groupEntries(true) has been called.
     *
     * @return  mixed  the (double-)grouped entries
     */
    public function getGroupedEntries()
    {
        if (!$this->sorted_entries) {
            if ($this->isGrouped()) {
                $this->sorted_entries = $this->sortAndGroupEntries();
            } else {
                $this->sorted_entries = $this->sortEntries();
            }
        }

        return $this->sorted_entries;
    }

    /**
     * sorts and groups entries and returns them
     * only used by columns with grouped entries like instituteschedules
     *
     * @return array
     */
    public function sortAndGroupEntries()
    {
        $day = $this->getTitle();

        $entries_for_column = $this->getEntries();
        $result = array();

        // 1st step - group all entries with the same duration
        foreach ($entries_for_column as $entry_id => $entry) {
            $new_entries[$entry['start'] .'_'. $entry['end']][] = $entry;
        }

        $column = 0;

        // 2nd step - optimize the groups
        while (sizeof($new_entries) > 0) {
            $lstart = 2399; $lend = 0;

            foreach ($new_entries as $time => $grouped_entries) {
                list($start, $end) = explode('_', $time);
                if ($start < $lstart /*&& ($end - $start) >= ($lend - $lstart)*/ )  {
                    $lstart = $start;
                    $lend = $end;
                }
            }

            $result['col_'. $column][] = $new_entries[$lstart .'_'. $lend];
            unset($new_entries[$lstart .'_'. $lend]);

            $hit = true;

            while ($hit) {
                $hit = false;
                $hstart = 2399; $hend = 2399;

                // check, if there is something, that can be placed after
                foreach ($new_entries as $time => $grouped_entries) {
                    list($start, $end) = explode('_', $time);

                    if ( ($start >= $lend) && ($start < $hstart) ) {
                        $hstart = $start;
                        $hend = $end;
                        $hit = true;
                    }
                }

                if ($hit) {
                    $lend = $hend;
                    $result['col_'. $column][] = $new_entries[$hstart .'_'. $hend];
                    unset($new_entries[$hstart .'_'. $hend]);
                }
            }

            $column++;
        } // 2nd step

        return $result;

    }

    /**
     * sorts entries and returns them
     *
     * @return array
     */
    public function sortEntries()
    {
        $entries_for_column = $this->getEntries();

        $result = array();
        $column = 0;

        // 2nd step - optimize the groups
        while (sizeof($entries_for_column) > 0) {
            $lstart = 2399; $lend = 0; $lkey = null;

            foreach ($entries_for_column as $entry_key => $entry) {
                if ($entry['start'] < $lstart /*&& ($end - $start) >= ($lend - $lstart)*/ )  {
                    $lstart = $entry['start'];
                    $lend = $entry['end'];
                    $lkey = $entry_key;
                }
            }

            $result['col_'. $column][] = $entries_for_column[$lkey];
            unset($entries_for_column[$lkey]);

            $hit = true;

            while ($hit) {
                $hit = false;
                $hstart = 2399; $hend = 2399; $hkey = null;

                // check, if there is something, that can be placed after
                foreach ($entries_for_column as $entry_key => $entry) {
                    if ( ($entry['start'] >= $lend) && ($entry['start'] < $hstart) ) {
                        // && (($end - $start) > ($hend - $hstart)) ) {
                        $hstart = $entry['start'];
                        $hend = $entry['end'];
                        $hkey = $entry_key;
                        $hit = true;
                    }
                }

                if ($hit) {
                    $lend = $hend;
                    $result['col_'. $column][] = $entries_for_column[$hkey];
                    unset($entries_for_column[$hkey]);
                }
            }

            $column++;
        } // 2nd step
        return $result;

    }

    /**
     * returns a matrix that tells the number of entries for a given timeslot
     *
     * @return array 
     */
    public function getMatrix() {
        $group_matrix = array();
        foreach ($this->getGroupedEntries() as $groups) {
            foreach ($groups as $group) {
                if (is_array($group[0])) {
                    $data = $group[0];
                } else {
                    $data = $group;
                }

                for ($i = floor($data['start'] / 100); $i <= floor($data['end'] / 100); $i++) {
                    for ($j = 0; $j < 60; $j++) {
                        if (($i * 100) + $j >= $data['start'] && ($i * 100) + $j < $data['end']) {
                            $group_matrix[($i * 100) + $j]++;
                        }
                    }
                }
            }
        }
        return $group_matrix;
    }

    /**
     * check, if a grouped view of the entries is requested
     *
     * @return bool true if grouped, false otherwise
     */
    public function isGrouped()
    {
        return $this->grouped;
    }

    /**
     * Call this function th enable/disable the grouping of entries with the same start and end.
     *
     * @param  bool  $group optional, defaults to true
     * @return void
     */
    public function groupEntries($grouped = true)
    {
        $this->grouped = $grouped;
    }

}