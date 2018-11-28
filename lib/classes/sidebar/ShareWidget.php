<?php
/**
 * Special widget for sharing links
 *
 * This widget provides functionality to display links and copy the url to the
 * user's clipboard when clicked.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class ShareWidget extends LinksWidget
{
    const INDEX = 'actions';

    public function __construct()
    {
        parent::__construct();

        $this->title = _('Teilen');
    }

    /**
     * Adds a copyable link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param Icon   $icon   instance of class Icon for the link
     * @param bool   $active Pass true if the link is currently active,
     *                       defaults to false
     */
    public function &addCopyableLink($label, $url, $icon = null, $attributes = [], $index = null)
    {
        if ($index === null) {
            $index = 'link-' . md5($url);
        }

        if (isset($attributes['class'])) {
            $attributes['class'] .= ' copyable-link';
        } else {
            $attributes['class'] = 'copyable-link';
        }

        $element = new LinkElement($label, $url, $icon, $attributes);
        $this->addElement($element, $index);
        return $element;
    }
}
