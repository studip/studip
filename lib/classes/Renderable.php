<?php
/**
 * Generic trait for renderable objects.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
trait Renderable
{
    /**
     * Returns the template name for this renderable.
     *
     * @return string
     */
    abstract protected function getTemplateName();


    /**
     * Returns neccessary variables to render the element template.
     *
     * @param array $variables Optional additional variables
     * @return array of variables
     */
    abstract protected function getTemplateVariables($variables = []);

    // Reference to template factory to use
    protected static $factory = null;

    /**
     * Sets the template factory for all renderables.
     *
     * @param Flexi_TemplateFactory $factory The factory to use
     */
    public static function setTemplateFactory(Flexi_TemplateFactory $factory)
    {
        self::$factory = $factory;
    }

    /**
     * Returns the layout for this renderable's template. Return null if no
     * layout should be used.
     *
     * @return mixed (Flexi_Template|null)
     */
    protected function getTemplateLayout()
    {
        return null;
    }

    /**
     * Returns a template from the defined factory.
     *
     * @param string $name      Template name
     * @param array  $variables Optional template variables
     * @return Flexi_Template
     * @throws Exception when no factory has been set
     */
    protected function getTemplate($name, array $variables = [])
    {
        if (self::$factory === null) {
            throw new Exception('No template factory has been set');
        }

        $template = self::$factory->open($name);
        $template->set_attributes($variables);
        return $template;
    }

    /**
     * Renders this renderable.
     *
     * @param array $variables Optional additional variables
     * @return string
     */
    public function render(array $variables = [])
    {
        $template  = $this->getTemplate($this->getTemplateName());
        $variables = $this->getTemplateVariables($variables);
        return $template->render($variables, $this->getTemplateLayout());
    }

    /**
     * Magic method for converting the object into a string. Invokes the render
     * method of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
