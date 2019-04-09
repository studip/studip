<?php
class HelpbarWidget extends Widget
{
    public $icon = false;
    
    public function addElement(WidgetElement $element, $index = null)
    {
        parent::addElement($element, $index);
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Renders the widget.
     * The widget will only be rendered if it contains at least one element.
     *
     * @return String The THML code of the rendered sidebar widget
     */
    public function render($variables = [])
    {
        $content = '';

        if ($this->hasElements()) {
            $template = $GLOBALS['template_factory']->open($this->template);
            $template->set_attributes($variables + $this->template_variables);
            $template->elements = $this->elements;
            $content = $template->render();
        }
        
        return $content;
    }
}