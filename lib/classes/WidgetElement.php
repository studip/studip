<?php
/**
 * Generic widget element
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since 3.1
 */
class WidgetElement
{
    /**
     * The content of the element
     */
    protected $content;

    /**
     * Constructs the element (with an optional content).
     *
     * @param mixed $content Optional content, defaults to empty string
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * Renders the element.
     *
     * @return String The rendered content
     */
    public function render()
    {
        return $this->content;
    }
}
