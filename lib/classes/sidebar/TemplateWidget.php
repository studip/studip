<?php
/**
 * This widget type consists of a template and associated variables and will
 * render the template content as the widget content.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.0
 */
class TemplateWidget extends SidebarWidget
{
    protected $template;

    /**
     * Constructor of the widget.
     *
     * @param String         $title     Title of the widget
     * @param Flexi_Template $template  Template for the widget
     * @param array          $variables Associated variables for the template
     */
    public function __construct($title, Flexi_Template $template, array $variables = [])
    {
        parent::__construct();

        $this->title    = $title;
        $this->template = $template;
        $this->template->set_attributes($variables);
    }

    /**
     * Renders the template and widget.
     *
     * @param array $variables Additional variables for rendering
     * @return string containing the rendered widget as html
     */
    public function render($variables = [])
    {
        $this->template->set_attributes($variables);
        $layout = $GLOBALS['template_factory']->open($this->layout);
        $layout->layout_css_classes = $this->layout_css_classes;
        return $this->template->render(
            $this->template_variables,
            $layout
        );
    }
}
