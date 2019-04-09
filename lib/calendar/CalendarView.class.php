<?php
# Lifter010: TODO

 /**
 * CalendarView.class.php - generates a calendar
 *
 * This class takes and checks all necessary parameters to display a calendar/schedule/time-table.
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

/**
 * Kind of bean class for the calendar view.
 *
 * Example of use:
 * 
 *  // create a calendar-view and add a column
 *  $plan = new CalendarView();
 *  $plan->addColumn(_('Spalte 1'))
 *      ->addEntry(array(
 *          'id'    => 1,
 *          'color' => '#5C2D64',
 *          'start' => '0930',
 *          'end'   => '1100',
 *          'title' => 'Mathe 2',
 *          'content' => 'Die Mathematiker kreiden sich mal wieder was an.'
 *      )
 *  );
 *   
 *  // display the calendar (containing one column)
 *  print $plan->render();
 *
 * @since      2.0
 */

class CalendarView
{

    protected $entries        = [];
    protected $entry_columns  = [];
    protected $height         = 40;
    protected $grouped        = false;
    protected $start_hour     = 8;
    protected $end_hour       = 21;
    protected $insertFunction = "";
    protected $templates      = [];
    protected $read_only      = false;

    static protected $number_of_instances = 1;
    protected $view_id;


    /**
     * You need to pass an instance of this class to the template. The constructor
     * expects an array of entries of the following type:
     *  array(
     *   $day_number => array(array (
     *    'color' => the color in hex (css-like, without the #)
     *    'start' => the (start hour * 100) + (start minute)
     *    'end'   => the (end hour * 100) + (end minute)
     *    //'day'   => day of week (0 = Sunday, ... , 6 = Saturday)
     *    'title' => the entry`s title
     *    'content' => whatever shall be the content of the entry as a string
     *   ) ...) ...
     *  )
     *
     * @param  mixed  $entries     an array of entries (see above)
     * @param  string $controller  the name of the controller. Used to create links.
     */
    public function __construct($entries = [])
    {
        if (!is_array($entries)) {
            throw new Exception('You need to pass some entries to the CalendarView!');
        }
        $this->view_id = self::$number_of_instances++;
        $this->checkEntries($entries);
        $this->entries = $entries;
    }

    /**
     * set the height for one hour. This value is used to calculate the whole height of the schedule.
     *
     * @param  int  $entry_height  the height of one hour in the schedule
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * set the range of hours to be displayed. the start_hour has to be smaller than the end_hour
     *
     * @param  int  $start_hour  the hour to start displaying at
     * @param  int  $end_hour    the hour to stop displaying at
     */
    public function setRange($start_hour, $end_hour)
    {
        $this->start_hour = $start_hour;
        $this->end_hour = $end_hour;
    }

    /**
     * does some plausability checks on an array of calendar-entries
     *
     * @param  mixed  $entries  an array of calendar-entries
     *
     * @return  bool  false if check failed, true otherwise
     */
    protected function checkEntries($entries)
    {
        foreach ($entries as $column) {
            if (!$column instanceof CalendarColumn) {
                throw new Exception('A column of the entries in the CalenarView is not of type CalendarColumn.');
            }
        }
        return true;
    }

    /**
     * adds a new column to this view. All entries created with addEntry will be
     * added to this column.
     * 
     * @param string  $title  like "monday" to be displayed on top of the column
     * @param string  $url    to be called when clicked on the title of the column
     * @param string  $id     any kind of id of the column
     * @return CalendarView
     */
    public function addColumn($title, $url = "", $id = null)
    {
        $this->entries[] = CalendarColumn::create($id)
                        ->setTitle($title)
                        ->setURL($url);
        return $this;
    }


    /**
     * adds a new entry to the last current column. The entry needs to be an 
     * associative array with parameters as follows:
     * @param array $entry_array: associative array for an entry in the column like
     * array (
     *    'color' => the color in hex (css-like, without the #)
     *    'start' => the (start hour * 100) + (start minute)
     *    'end'   => the (end hour * 100) + (end minute)
     *    'title' => the entry`s title
     *    'content' => whatever shall be the content of the entry as a string
     * )
     * @return CalendarView
     */
    public function addEntry($entry_array)
    {
        if (count($this->entries)) {
            $this->entries[count($this->entries)-1]->addEntry($entry_array);
        } else {
            throw new InvalidArgumentException(_("Es existiert noch keine Spalte in der Ansicht, zu der der Eintrag hinzugefügt werden kann."));
        }
        return $this;
    }


    /**
     * Call this function to enable/disable the grouping of entries with the same start and end.
     *
     * @param  bool  $group  optional, defaults to true
     */
    public function groupEntries($grouped = true)
    {
        $this->grouped = $grouped;
        foreach($this->getColumns() as $entry_column) {
            $entry_column->groupEntries();
        }
    }

    /**
     * When a column is clicked at no entry this function is called.
     * First the templates generates a new entry at the clicked time. Then this
     * js-function is called which gets the parameters
     *   "function (new_entry_dom_object, column_id, hour) { ... }"
     *   with new_entry_dom_object: a real dom-object of the div of the entry
     *   column_id: id of the column
     *   hour: integer number from 0 to 23
     * If js_function_object is an empty string, nothing will be done.
     * 
     * @param string  $js_function_object  name of js-function or anonymous js-function
     * @return CalendarView
     */
    public function setInsertFunction($js_function_object)
    {
        $this->insertFunction = $js_function_object;
        return $this;
    }

    /**
     * outputs the CalendarView with all (grouped) dates in columns.
     *
     * @param  array  $params  you can pass some additional variables to the templates
     *
     * @return string
     */
    public function render($params = [])
    {
        $style_parameters = [
            'whole_height' => $this->getOverallHeight(),
            'entry_height' => $this->getHeight()
        ];
        $factory = new Flexi_TemplateFactory(dirname(__file__).'/../../app/views');
        PageLayout::addStyle($factory->render('calendar/stylesheet', $style_parameters));

        $template = $GLOBALS['template_factory']->open("calendar/calendar_view.php");
        $template->set_attribute("calendar_view", $this);
        $template->set_attribute("view_id", $this->view_id);
        return $template->render($params);
    }


    /* * * * * * * * * * * * * * *
     * * *   G E T T E R S   * * *
     * * * * * * * * * * * * * * */

    /**
     * Returns an array of calendar-entries, grouped by day and additionally grouped by same start and end
     * if groupEntries(true) has been called.
     *
     * @return  mixed  the (double-)grouped entries
     */
    public function getEntries()
    {
        $this->sorted_entries = [];
        foreach ($this->getColumns() as $entry_column) {
            $this->sorted_entries['day_'. $entry_column->getId()] = $entry_column->getGroupedEntries();
        }
        return $this->sorted_entries;
    }

    /**
     * Returns an array where for each hour the number of concurrent entries is denoted.
     * Used by the calendar to display the entries in parallel.
     *
     * @return  mixed  concurrent entries at each hour
     */
    public function getMatrix()
    {
        $matrix = [];
        foreach ($this->getColumns() as $day => $entry_column) {
            $matrix['day_'.$day] = $entry_column->getMatrix();
        }
        return $matrix;
    }


    /**
     * returns the previously set start- and end-hour, denoting the
     * range of entries to be displayed in the current calendar-view
     *
     * @return array consisting of the start and end hour
     */
    public function getRange()
    {
        return [$this->start_hour, $this->end_hour];
    }

    /**
     * the calendar can be used in two modes. Use this function to check,
     * if the grouping-mode is enabled for the current calendar-view
     *
     * @return bool true if grouped, false otherwise
     */
    public function isGrouped()
    {
        return $this->grouped;
    }

    /**
     * returns the previously set height for one hour
     *
     * @return mixed the height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * returns the overall height of the calendar
     *
     * @return mixed the overall height
     */
    public function getOverallHeight()
    {
        return $this->height * ($this->end_hour - $this->start_hour) + $this->height
                + 2 + ($this->end_hour - $this->start_hour) * 2;
    }

    /**
     * returns the previously set javasscript insert-function
     *
     * @return  string  name of js-function or anonymous js-function
     */
    public function getInsertFunction() {
        return $this->insertFunction;
    }

    /**
     * returns all columns of the calendar-view
     *
     * @return  array  of CalendarColumn
     */
    public function getColumns() {
        return $this->entries;
    }

    /**
     * Set the read-only status of the calendar
     *
     * @param bool  $readonly  true to make it read only, false otherwise
     *
     * @return void
     */
    public function setReadOnly($readonly = true)
    {
        $this->read_only = $readonly;
    }

    /**
     * Return the read-only status of this calendar
     *
     * @return bool the read-only status of this calendar
     */
    public function getReadOnly() {
        return $this->read_only;
    }

}
