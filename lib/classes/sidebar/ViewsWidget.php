<?php
class ViewsWidget extends LinksWidget
{
    public function __construct()
    {
        parent::__construct();
        
        $this->title = _('Ansichten');
        $this->addCSSClass('sidebar-views');
    }

    /**
     * Adds a link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param String $icon   Icon for the link, defaults to blank.gif (will be discarded)
     * @param bool   $active Pass true if the link is currently active,
     *                       defaults to false
     */
    public function &addLink($label, $url, $icon = null, $attributes = [], $index = null)
    {
        return parent::addLink($label, $url, null, $attributes, $index);
    }
}