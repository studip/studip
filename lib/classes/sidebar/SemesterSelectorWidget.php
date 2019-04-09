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
     * Populates and renders the widget according to the previously made
     * settings.
     */
    public function render($variables = [])
    {
        $current_id = Request::get($this->template_variables['name']);
        if (!$current_id && !$this->include_all) {
            $current_id = Semester::findCurrent()->id;
        }

        if ($this->include_all) {
            $element = new SelectElement(0, _('Alle Semester'), !$current_id);
            $this->addElement($element);
        }

        $semesters = array_reverse(Semester::getAll());
        foreach ($semesters as $semester) {
            $element = new SelectElement($semester->id, $semester->name, $current_id && $semester->id == $current_id);
            $this->addElement($element);
        }

        return parent::render($variables);
    }
}