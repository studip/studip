<?php
/**
 * Model for a select element of the select widget.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.1
 */
class SelectElement extends WidgetElement
{
    protected $id;
    protected $label;
    protected $active;
    protected $tooltip;
    protected $is_header = false;
    protected $indent_level = null;

    /**
     * Constructs the element with an id (value of the according option
     * element) and a label (text content of the according option
     * element).
     *
     * @param String      $id      Id of the element
     * @param String      $label   Label/text content of the element
     * @param bool        $active  Indicates whether the element is active
     * @param String|null $tooltip Optional Title attribute for the element
     */
    public function __construct($id, $label, $active = false, $tooltip = null)
    {
        $this->id      = $id;
        $this->label   = $label;
        $this->active  = $active;
        $this->tooltip = $tooltip;
    }

    /**
     * Sets the id of the element.
     *
     * @param String $id Id of the element
     * @return SelectElement instance to allow chaining
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the label/text content of the element.
     *
     * @param String $label Label/text content of the element
     * @return SelectElement instance to allow chaining
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the id of the element.
     *
     * @return String Id of the element
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the label/text content of the element. The label is stripped
     * of all leading whitespace.
     *
     * @return String Label/text content of the element
     * @see SelectElement::getIndentLevel
     */
    public function getLabel()
    {
        return ltrim($this->label);
    }

    /**
     * Returns the tooltip content of the element. It is stripped
     * of all leading whitespace.
     *
     * @return String tooltip content of the element
     * @see SelectElement::getIndentLevel
     */
    public function getTooltip()
    {
        return $this->tooltip !== null ? ltrim($this->tooltip) : null;
    }

    /**
     * Sets the activate of the element.
     *
     * @param bool $active Indicates whether the element is active (optional,
     *                     defaults to true)
     * @return SelectElement instance to allow chaining
     */
    public function setActive($active = true)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Returns whether the element is active.
     *
     * @return bool indicating whether the element is active
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Sets the flag indicating whether this element should be displayed
     * as a header element.
     *
     * @param bool $is_header "Is header" flag
     * @return SelectElement instance to allow chaining
     */
    public function setAsHeader($is_header = true)
    {
        $this->is_header = (bool)$is_header;
        return $this;
    }

    /**
     * Returns whether the elements should be displayed as a header element.
     *
     * @return bool indicating whether the element should be displayed as a
     *              header element
     */
    public function isHeader()
    {
        return $this->is_header;
    }

    /**
     * Sets the indentation level of the element.
     *
     * @param int $level Indentation level
     * @return SelectElement instance to allow chaining
     */
    public function setIndentLevel($level)
    {
        $this->indent_level = (int)$level;
        return $this;
    }

    /**
     * Returns the indentation level of the element. If the level has not
     * been set explicitely, it is calculated based on the number of
     * leading whitespace characters. This is used to indent the label in
     * the according option element.
     *
     * @return int Indentation level
     */
    public function getIndentLevel()
    {
        if ($this->indent_level !== null) {
            return $this->indent_level;
        }
        return mb_strlen($this->label) - mb_strlen(ltrim($this->label));
    }

    /**
     * Renders the element (well, not really - this returns it's label).
     *
     * @return String The label/text content of the element
     * @todo   What should this method actually do?
     */
    public function render()
    {
        return $this->getLabel();
    }
}
