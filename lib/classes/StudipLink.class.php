<?php


/**
 * The StudipLink class abstracts Links so that they can be displayed in a
 * uniform manner more easily.
 */
class StudipLink
{
    /**
     * The title that shall be displayed.
     */
    public $title = "";


    /**
     * The icon for the link.
     */
    public $icon = null;


    /**
     * The link itself.
     */
    public $link = "";


    /**
     * Attributes for the link.
     */
    public $attributes = [];


    public function __construct(string $link, string $title, Icon $icon)
    {
        $this->link = $link;
        $this->title = $title;
        $this->icon = $icon;
    }


    public function __toString()
    {
        $template = '<a href="%1$s">%2$s %3$s</a>';
        return sprintf($template, $this->link, $this->title, $this->icon);
    }
}
