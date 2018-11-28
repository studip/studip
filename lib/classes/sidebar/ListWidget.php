<?php
/**
 * 
 */
class ListWidget extends SidebarWidget
{
    /**
     * 
     */
    protected $css_classes = array();

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->template = 'sidebar/list-widget';
        $this->addCSSClass('widget-list');
    }

    /**
     *
     */
    public function addCSSClass($css_class)
    {
        if (!in_array($css_class, $this->css_classes)) {
            $this->css_classes[] = $css_class;
        }
    }

    /**
     *
     */
    public function removeCSSClass($css_class)
    {
        $this->classes = array_diff($this->css_class, array($css_class));
    }

    /**
     * 
     */
    public function render($variables = array())
    {
        $this->template_variables['css_classes'] = $this->css_classes;

       return parent::render($variables);
    }
}