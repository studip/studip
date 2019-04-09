<?php
/**
 * Model for a select group element of the select widget.
 *
 * @author  Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 */
class SelectGroupElement extends SelectElement
{
    protected $elements;

    /**
     * Constructs the element with an id (value of the according option
     * element) and a label (text content of the according option
     * element).
     *
     * @param String $label  Label content of the element
     * @param Array $elements SelectElement-Objects for Optgroup
     */
    public function __construct($label, $elements = [])
    {
        $this->label    = $label;
        $this->elements = $elements;
    }

    /**
     * Sets the SelectElement-Objects for this Element
     * 
     * @param Array $elements
     */
    public function setElements($elements) 
    {
        $this->elements = $elements;
    }
    
    /**
     * 
     * @return Array with SelectElements
     */
    public function getElements()
    {
        return $this->elements;
    }
    /**
     * adds a single element of type SelectElement
     * 
     * @param SelectElement $element
     */
    public function addElement(SelectElement $element)
    {
        $this->elements[] = $element;
    }
}