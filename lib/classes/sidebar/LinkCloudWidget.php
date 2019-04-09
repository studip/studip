<?php
/**
 * A special sidebar widget for link list.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 */
class LinkCloudWidget extends LinksWidget
{
    /**
     * Constructs the widget by defining a special template.
     */
    public function __construct()
    {
        $this->setTitle(_("Cloud"));
        $this->template = 'sidebar/linkcloud-widget';
    }

    /**
     * Adds a link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param String $icon   Icon for the link, defaults to blank.gif
     * @param bool   $active Pass true if the link is currently active,
     *                       defaults to false
     */
    public function &addLink($label, $url, $icon = null, $attributes = [], $index = null)
    {
        $content = sprintf('<a href="%s" class="%s">%s</a>',
                           $url,
                           'weigh-'.((int) $icon > 0 ? (int) $icon : 1),
                           htmlReady($label));

        $element = new WidgetElement($content);
        $this->addElement($element, 'cloudlink-' . md5(uniqid('link', true)));
    }
}