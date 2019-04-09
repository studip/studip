<?php
/**
 * A special sidebar widget for link list.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 */
class LinksWidget extends ListWidget
{
    /**
     * Constructs the widget by defining a special template.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addCSSClass('widget-links');
    }

    /**
     * Adds a link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param Icon   $icon   instance of class Icon for the link
     * @param bool   $active Pass true if the link is currently active,
     *                       defaults to false
     */
    public function &addLink($label, $url, $icon = null, $attributes = [], $index = null)
    {
        if ($index === null) {
            $index = 'link-' . md5($url);
        }
        $element = new LinkElement($label, $url, $icon, $attributes);
        $this->addElement($element, $index);
        return $element;
    }

    public function addLinkFromHTML($html, Icon $icon = null)
    {
        $this->addElement(LinkElement::fromHTML($html, $icon));
    }
}
