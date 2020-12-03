<?php
/**
 * SemesterSelectorWidget
 *
 * This class defines a standard sidebar widget for choosing a semester.
 * The selector is derived from the more generic SelecWidget.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @see       SelectWidget
 * @since     Stud.IP 3.2
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 */
class SemesterSelectorWidget extends SelectWidget
{
    protected $include_all = false;


    /**
     * The timestamp of the first semester that shall be selectable.
     */
    protected $semester_range_begin = 0;


    /**
     * The timestamp of the last semester that shall be selectable.
     */
    protected $semester_range_end = 0;


    /**
     * Overrides parent constructor by setting a default title and default
     * name.
     */
    public function __construct($url, $name = 'semester_id', $method = 'get')
    {
        parent::__construct(_('Semester auswählen'), $url, $name, $method);
    }

    /**
     * Should the list include an option for all semesters which results in
     * an option with a value of '0'.
     */
    public function includeAll($state = true)
    {
        $this->include_all = $state;
    }


    /**
     * Sets the range of semesters to be displayed.
     *
     * @param $semester_range_begin The timestamp of the first semester.
     *
     * @param $semester_range_end The timestamp of the end semester.
     */
    public function setRange($semester_range_begin, $semester_range_end)
    {
        if ($semester_range_end >= $semester_range_begin) {
            $this->semester_range_begin = $semester_range_begin;
            $this->semester_range_end = $semester_range_end;
        }
    }


    /**
     * Populates and renders the widget according to the previously made
     * settings.
     */
    public function render($variables = [])
    {
        $current_id = Request::get($this->template_variables['name']);
        if (!$current_id) {
            if ($this->template_variables['value']) {
                $current_id = $this->template_variables['value'];
            } elseif (!$this->include_all) {
                $current_id = Semester::findCurrent()->id;
            }
        }

        if ($this->include_all) {
            $element = new SelectElement(0, _('Alle Semester'), !$current_id);
            $this->addElement($element);
        }

        $semesters = [];
        if ($this->semester_range_begin && $this->semester_range_end) {
            $semesters = Semester::findBySql(
                '`beginn` BETWEEN :begin AND :end
                OR
                `ende` BETWEEN :begin AND :end
                ORDER BY `beginn` DESC',
                [
                    'begin' => $this->semester_range_begin,
                    'end' => $this->semester_range_end
                ]
            );
        } else {
            $semesters = array_reverse(Semester::getAll());
        }
        foreach ($semesters as $semester) {
            $element = new SelectElement($semester->id, $semester->name, $current_id && $semester->id == $current_id);
            $this->addElement($element);
        }

        return parent::render($variables);
    }
}
