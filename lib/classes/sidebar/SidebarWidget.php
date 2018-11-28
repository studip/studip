<?php
/**
 * A widget for the sidebar.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 * @see     Sidebar
 */
class SidebarWidget extends Widget
{
    public function __construct()
    {
        $this->layout = 'sidebar/widget-layout.php';
    }

    /**
     * Sets the title of the widget.
     *
     * @param String $title The title of the widget
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the title of the widget
     *
     * @return mixed The title of the widget of false if no title has been set
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Removes the title of the widget.
     */
    public function removeTitle()
    {
        $this->title = false;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }
    
    public function getExtra()
    {
        return $this->extra;
    }

    public function removeExtra()
    {
        $this->extra = false;
    }

    /**
     * Renders the widget.
     * The widget will only be rendered if it contains at least one element.
     *
     * @return String The THML code of the rendered sidebar widget
     */
    public function render($variables = array())
    {
        return parent::render($variables);
    }
}