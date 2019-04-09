<?php
class NavigationWidget extends LinksWidget
{
    public function __construct()
    {
        parent::__construct();

        $this->title = _('Navigation');
        $this->addCSSClass('sidebar-navigation');
    }

    /**
     * Adds a link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param Icon   $icon   (not used)
     * @param array  $attributes Optional attributes fot the generated link
     * @param mixed  $index  Index to use for the element
     * @return String
     */
    public function &addLink($label, $url, $icon = null, $attributes = [], $index = null)
    {
        return parent::addLink($label, $url, null, $attributes, $index);
    }
}
