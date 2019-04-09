<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class OptionsWidget extends ListWidget
{
    const INDEX = 'options';

    /**
     * @param String $title Optional alternative title
     */
    public function __construct($title = null)
    {
        parent::__construct();

        $this->addCSSClass('widget-options');
        $this->title = $title ?: _('Einstellungen');
    }

    /**
     * @param String $label
     * @param bool   $state
     * @param String $toggle_url     Url to execute the action
     * @param String $toggle_url_off Optional alternative url to explicitely
     *                               turn off the checkbox ($toggle_url will
     *                               then act as $toggle_url_on)
     * @param Array  $attributes  Optional additional attributes for the anchor
     */
    public function addCheckbox($label, $state, $toggle_url, $toggle_url_off = null, array $attributes = [])
    {
        // Prepare attributes
        $attr = [];
        foreach ($attributes as $key => $value) {
            $attr[] = sprintf('%s="%s"', $key, htmlReady($value));
        }

        $content = sprintf(
            '<a href="%s" class="options-checkbox options-%s" %s>%s</a>',
            ($state && $toggle_url_off !== null) ? $toggle_url_off : $toggle_url,
            $state ? 'checked' : 'unchecked',
            implode(' ', $attr),
            htmlReady($label)
        );
        $this->addElement(new WidgetElement($content));
    }

    /**
     * @param String $label
     * @param String $url
     * @param bool   $checked
     */
    public function addRadioButton($label, $url, $checked = false)
    {
        $content = sprintf('<a href="%s" class="options-radio options-%s">%s</a>',
                           $url,
                           $checked ? 'checked' : 'unchecked',
                           htmlReady($label));
        $this->addElement(new WidgetElement($content));
    }

    /**
     * Adds a select element to the widget.
     *
     * @param String $label
     * @param String $url
     * @param String $name            Attribute name
     * @param array  $options         Array of associative options (value => label)
     * @param mixed  $selected_option Currently selected option
     * @param array  $attributes      Additional attributes
     */
    public function addSelect($label, $url, $name, $options, $selected_option = false, $attributes = [])
    {
        $widget = new SelectWidget($label, $url, $name);
        $widget->layout = false;

        foreach ($options as $value => $option_label) {
            $widget->addElement(new SelectElement($value, $option_label, $value === $selected_option));
        }

        if (isset($widget->attributes) && is_array($widget->attributes)) {
            $widget->attributes = array_merge($widget->attributes, $attributes);
        } else {
            $widget->attributes = $attributes;
        }

        $content = $widget->render();

        $this->addElement(new WidgetElement($content));
    }
}
