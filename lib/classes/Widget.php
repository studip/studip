<?php
/**
 * Generic Widget
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since 3.1
 */
class Widget
{
    /**
     * Contains the elements of the widget.
     */
    protected $elements = [];

    /**
     * Contains the template used to render the widget.
     */
    protected $template = 'widgets/widget';

    /**
     * Contains additional template variables
     */
    protected $template_variables = [];

    /**
     * Layout for this widget
     */
    protected $layout = 'widgets/widget-layout';

    /**
     * Forced rendering?
     */
    protected $forced_rendering = false;

    /**
     * @var array classes for the layout <div> around the widget
     */
    protected $layout_css_classes = [];

    /**
     * Add an element to the widget.
     *
     * @param WidgetElement $element The actual element
     * @param String        $index   Index/name of the element
     */
    public function addElement(WidgetElement $element, $index = null)
    {
        $index = $index ?: $this->guessIndex($element);
        
        $this->elements[$index] = $element;
    }

    /**
     * Insert an element before a specific other element or at the end of the
     * list if the specified position is invalid.
     *
     * @param WidgetElement $element      The actual element
     * @param String        $before_index Insert element before this element.
     * @param String        $index        Index/name of the element
     */
    public function insertElement(WidgetElement $element, $before_index, $index = null)
    {
        $index = $index ?: $this->guessIndex($element);
        
        $inserted = false;
        
        $elements = [];
        foreach ($this->elements as $idx => $elmnt) {
            if ($idx === $index) {
                $inserted = true;
                $elements[$index] = $element;
            }
            $elements[$idx] = $elmnt;
        }

        if (!$inserted) {
            $elements[$index] = $element;
        }

        $this->elements = $elements;
    }
    
    /**
     * Tries to guess an appropriate index name for the element.
     *
     * @param WidgetElement $element The element in question
     * @return String Appropriate index name
     */
    protected function guessIndex(WidgetElement $element)
    {
        $class = get_class($element);
        if ($class !== 'WidgetElement') {
            $index = mb_strtolower($class);
            $index = str_replace('element', '', $index);
            $index .= '-' . md5(serialize($element));
        } else {
            $index = md5(serialize($element));
        }

        $temp    = $index;
        $counter = 0;
        while (array_key_exists($temp, $this->elements)) {
            $temp = sprintf('%s-%u', $index, $counter++);
        }
        $index = $temp;

        return $index;
    }
    
    /**
     * Retrieve the element at the specified position.
     *
     * @param String $index Index/name of the element to retrieve.
     * @return WidgetElement The element at the specified position.
     * @throws Exception if the specified position is invalid
     */
    public function getElement($index)
    {
        if (!isset($this->elements[$index])) {
            throw new Exception('Trying to retrieve unknown widget element "' . $index . '"');
        }
        return $this->elements[$index];
    }

    /**
     * Returns all elements of the widget.
     * @return array of WidgetElement
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Removes the element at the specified position.
     *
     * @param String $index Index/name of the element to remove.
     * @throws Exception if the specified position is invalid
     */ 
    public function removeElement($index)
    {
        if (!isset($this->elements[$index])) {
            throw new Exception('Trying to remove unknown widget element "' . $index . '"');
        }
        unset($this->elements[$index]);
    }
    
    /**
     * Returns whether this widget has any elements.
     *
     * @return bool True if the widget has at least one element, false
     *              otherwise.
     */
    public function hasElements()
    {
        return count($this->elements) > 0;
    }

    /**
     * Returns whether an element exists at the given index.
     *
     * @param String $index Index/name of the element to check for.
     * @return bool Does a widget exist at the given index?
     */
    public function hasElement($index)
    {
        return isset($this->elements[$index]);
    }

    /**
     * Force rendering
     *
     * @param bool $state Whether to force rendering or not
     */
    public function forceRendering($state = true)
    {
        $this->forced_rendering = $state;
    }

    /**
     * Adds a css class to the layout <div> around the widget.
     */
    public function addLayoutCSSClass($css_class)
    {
        if (!in_array($css_class, $this->layout_css_classes)) {
            $this->layout_css_classes[] = $css_class;
        }
    }

    /**
     * Removes a css class from the layout <div> around the widget.
     */
    public function removeLayoutCSSClass($css_class)
    {
        $this->layout_classes = array_diff($this->layout_css_class, [$css_class]);
    }

    /**
     * Renders the widget.
     * The widget will only be rendered if it contains at least one element.
     *
     * @return String The THML code of the rendered sidebar widget
     */
    public function render($variables = [])
    {
        $content = '';

        if ($this->hasElements() || $this->forced_rendering) {

            $template = $GLOBALS['template_factory']->open($this->template);
            $template->set_attributes($variables + $this->template_variables);
            $template->elements = $this->elements;

            if ($this->layout) {
                $layout = $GLOBALS['template_factory']->open($this->layout);
                $layout->layout_css_classes = $this->layout_css_classes;
                $template->set_layout($layout);
            }

            $content = $template->render();
        }
        
        return $content;
    }
    
    public function __isset($offset)
    {
        return isset($this->template_variables[$offset]);
    }
    
    public function __get($offset)
    {
        return $this->template_variables[$offset];
    }
    
    public function __set($offset, $value)
    {
        $this->template_variables[$offset] = $value;
    }
    
    public function __unset($offset)
    {
        unset($this->template_variables[$offset]);
    }
}
