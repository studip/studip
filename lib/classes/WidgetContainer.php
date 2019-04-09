<?php
/**
 * Generic widget container
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since 3.1
 * @abstract
 */
abstract class WidgetContainer
{
    /**
     * The singleton instance of the container
     */
    protected static $instances = null;

    /**
     * Returns the instance of this container to ensure there is only one
     * instance.
     *
     * @return WidgetContainer The container instance
     * @static
     */
    public static function Get()
    {
        $class = get_called_class();
        if (static::$instances[$class] === null) {
            static::$instances[$class] = new static;
        }
        return static::$instances[$class];
    }
    
    /**
     * Private constructor to ensure that the singleton Get() method is always
     * used.
     *
     * @see WidgetContainer::Get
     */
    protected function __construct()
    {
    }
    
    /**
     * Contains the widgets of the container
     */
    protected $widgets = [];
    
    /**
     * Add a widget to the container.
     *
     * @param Widget $widget The actual widget
     * @param String $index  Optional index/name of the widget, defaults to
     *                       class name without "widget"
     * @return Widget The added widget to allow for easier handling
     */
    public function addWidget(Widget $widget, $index = null)
    {
        $index = $index ?: $this->guessIndex($widget);

        $this->widgets[$index] = $widget;

        return $widget;
    }

    /**
     * Insert a widget before a specific other widget or at the end of the
     * list if the specified position is invalid.
     *
     * @param Widget $widget      The actual widget
     * @param String $before_index Insert widget before this widget
     * @param String $index  Optional index/name of the widget, defaults to
     *                       class name without "widget"
     * @return Widget The inserted widget to allow for easier handling
     */
    public function insertWidget(Widget $widget, $before_index, $index = null)
    {
        $index = $index ?: $this->guessIndex($widget);
        
        $inserted = false;
        
        $widgets = [];
        foreach ($this->widgets as $idx => $wdgt) {
            if ($idx === $before_index) {
                $inserted = true;
                $widgets[$index] = $widget;
            }
            $widgets[$idx] = $wdgt;
        }

        if (!$inserted) {
            if ($before_index === ':first') {
                $widgets = array_merge([$index => $widget], $widgets);
            } else {
                $widgets[$index] = $widget;
            }
        }

        $this->widgets = $widgets;
        
        return $widget;
    }

    /**
     * Tries to guess an appropriate index name for the widget.
     *
     * @param Widget $widget The widget in question
     * @return String Appropriate index name
     */
    private function guessIndex(Widget $widget)
    {
        $index = mb_strtolower(get_class($widget));
        $index = str_replace('widget', '', $index);

        $temp    = $index;
        $counter = 0;
        while (array_key_exists($temp, $this->widgets)) {
            $temp = sprintf('%s-%u', $index, $counter++);
        }
        $index = $temp;

        return $index;
    }

    /**
     * Retrieve the widget at the specified position.
     *
     * @param String $index Index/name of the widget to retrieve.
     * @return WidgetElement The widget at the specified position.
     * @throws Exception if the specified position is invalid
     */
    public function getWidget($index)
    {
        if (!isset($this->widgets[$index])) {
            throw new Exception('Trying to retrieve unknown widget "' . $index . '"');
        }
        return $this->widgets[$index];
    }

    /**
     * Returns all widgets of the container.
     *
     * @return array of Widget
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Removes the widget at the specified position.
     *
     * @param String $index Index/name of the widget to remove.
     * @throws Exception if the specified position is invalid
     */ 
    public function removeWidget($index)
    {
        if (!isset($this->widgets[$index])) {
            throw new Exception('Trying to remove unknown widget "' . $index . '"');
        }
        unset($this->widgets[$index]);
    }

    /**
     * Returns whether this container has any widget.
     *
     * @return bool True if the container has at least one widget, false
     *              otherwise.
     */
    public function hasWidgets()
    {
        return count($this->widgets) > 0;
    }

    /**
     * Returns whether a widget exists at the given index.
     *
     * @param String $index Index/name of the widget to check for.
     * @return bool Does a widget exist at the given index?
     */
    public function hasWidget($index)
    {
        return isset($this->widgets[$index]);
    }

    /**
     * Renders the container.
     *
     * @abstract
     */
    abstract public function render();
}
