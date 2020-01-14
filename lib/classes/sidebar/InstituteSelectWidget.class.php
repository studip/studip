<?php
/**
 * InstituteSelectWidget
 *
 * This is a specialisation of the select widget for institutes
 * and their faculties.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @see       SelectWidget
 * @since     4.5
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 */
class InstituteSelectWidget extends SelectWidget
{
    protected $include_all_option;

    /**
     * The IDs of the selected institutes.
     */
    protected $selected_element_ids;

    public function __construct($url, $name, $method = 'get', $multiple = false)
    {
        parent::__construct(_('Einrichtung'), $url, $name, $method, $multiple);

        $this->include_all_option = false;
        $this->selected_element_ids = [];
    }


    /**
     * Sets the $include_all_option attribute that specifies whether an option
     * for selecting all institutes shall be provided (true) or not (false).
     * This defaults to true.
     */
    public function includeAllOption($include_all_option = true)
    {
        $this->include_all_option = $include_all_option;
    }


    /**
     * This method allows setting the selected elements from other sources
     * than the select's name from a request.
     *
     * @param Array|string $element_ids The ID of one element (as string)
     *     or the IDs of more than one element (as array)
     *     which have been selected.
     */
    public function setSelectedElementIds($element_ids = [])
    {
        if ($element_ids && !is_array($element_ids)) {
            $element_ids = [$element_ids];
        }
        $this->selected_element_ids = $element_ids;
    }


    public function render($variables = [])
    {
        if (!$this->selected_element_ids) {
            if ($this->multiple) {
                $this->selected_element_ids = Request::getArray($this->template_variables['name']);
            } else {
                $this->selected_element_ids = [Request::get($this->template_variables['name'])];
            }
        }

        $institutes = Institute::getMyInstitutes($GLOBALS['user']->id);

        if ($this->include_all_option) {
            $element = new SelectElement(
                'all',
                (
                    $GLOBALS['perm']->have_perm('root')
                    ? _('Alle')
                    : _('Alle meine Einrichtungen')
                ),
                in_array('all', $this->selected_element_ids)
            );
            $element->setAsHeader(true);
            $this->addElement($element);
        }

        foreach ($institutes as $institute) {
            $element = new SelectElement(
                $institute['Institut_id'],
                $institute['Name'],
                in_array($institute['Institut_id'], $this->selected_element_ids)
            );
            if ($institute['is_fak']) {
                $element->setAsHeader(true);
            } else {
                $element->setIndentLevel(1);
            }
            $this->addElement($element);

            if ($institute['is_fak']) {
                $sub_element_id = $institute['Institut_id'] . '_withinst';
                $sub_element = new SelectElement(
                    $sub_element_id,
                    sprintf(
                        _('%s + Institute'),
                        $institute['Name']
                    ),
                    in_array($sub_element_id, $this->selected_element_ids)
                );
                $this->addElement($sub_element);
            }
        }

        return parent::render($variables);
    }
}
