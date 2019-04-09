<?php
/**
 * Sidebar widget for lists of selectable items.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 */
class SelectWidget extends SidebarWidget
{
    /**
     * Constructs the widget by defining a special template.
     *
     * @param string  $title    Diplayed title
     * @param string  $url      Target url
     * @param string  $name     Name of the input element
     * @param string  $method   Request method
     * @param boolean $multiple Defines whether selecting multiple values is allowed
     */
    public function __construct($title, $url, $name, $method = 'get', $multiple = false)
    {
        $this->template = 'sidebar/select-widget';

        $this->setTitle($title);
        $this->setUrl($url);
        $this->setSelectParameterName($name);
        $this->setRequestMethod($method);
        $this->setMultiple($multiple);

        $this->template_variables['max_length'] = 30;
    }

    /**
     * Sets the target url
     * @param string $url Target url
     */
    public function setUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $url = str_replace('?' . $query , '', $url);
            parse_str(html_entity_decode($query) ?: '', $query_params);
        } else {
            $query_params = [];
        }

        $this->template_variables['url']    = URLHelper::getLink($url);
        $this->template_variables['params'] = $query_params;
    }

    /**
     * Sets the maximum length of the input
     * @param int $length Maximum length
     */
    public function setMaxLength($length)
    {
        $this->template_variables['max_length'] = $length;
    }

    /**
     * Sets the name of the select input element
     * @param String $name Name of the input element
     */
    public function setSelectParameterName($name)
    {
        $this->template_variables['name'] = $name;
    }

    /**
     * Sets the selected value.
     * @param mixed $value Selected value
     */
    public function setSelection($value)
    {
        $this->template_variables['value'] = $value;
    }

    /**
     * Sets the request method
     * @param string $method [description]
     */
    public function setRequestMethod($method)
    {
        $this->template_variables['method'] = $method;
    }

    /**
     * Sets whether selecting multiple values is allowed or not
     * @param bool $multiple true if selection multiple values should be allowed
     */
    public function setMultiple($multiple)
    {
        $this->template_variables['multiple'] = $multiple;
    }

    /**
     * Sets the options for the select element
     * @param array $options  Options as associative array (value => label)
     * @param mixed $selected The initially selected value
     */
    public function setOptions(array $options, $selected = false)
    {
        $selected = $selected ?: ($this->multiple ? Request::getArray($this->name) : Request::get($this->name));
        //if selected is one single value
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        foreach ($options as $key => $label) {
            $element = new SelectElement($key, $label, in_array($key, $selected));
            $this->addElement($element);
        }
    }

    /**
     * Renders the select widget
     * @param  array  $variables Additional vaiarbles
     * @return string rendered widget as ghtml
     */
    public function render($variables = [])
    {
        $attributes = [];
        foreach ((array) $this->template_variables['attributes'] as $key => $value) {
            $attributes[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
        $this->template_variables['attributes'] = implode(' ', $attributes) ?: '';

        $variables['__is_nested'] = $this->hasNestedElements();

        //submit-upon-select is not helpful if we have the multiple version
        if (!$this->template_variables['multiple']) {
            $this->template_variables['class'] .= ' submit-upon-select';
        }

        return parent::render($variables);
    }

    /**
     * Returns whether this element has nested subelements
     * @return boolean true if element has nested subelements
     */
    protected function hasNestedElements()
    {
        foreach ($this->elements as $element) {
            if ($element instanceof SelectElement
                && ($element->isHeader() || $element->getIndentLevel() > 0))
            {
                return true;
            }
        }

        // use nested if multiple
        return $this->template_variables['multiple'];
    }

    /**
     * Returns whether the given array has associative keys.
     * @param  array   $array Array to test
     * @return boolean true if array keys are not ascending from 0
     */
    private static function isArrayAssoc(array $array)
    {
        if (!$array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Converts the given array to a list of hidden inputs
     * @param  array $array   Array to convert
     * @param  string $prefix Optional prefix for the input name
     * @return string list of hidden inputs as html
     */
    public static function arrayToHiddenInput(array $array, $prefix = '')
    {
        $string = '';

        if (self::isArrayAssoc($array)) {
            foreach ($array as $key => $value) {
                if (empty($prefix)) {
                    $name = $key;
                } else {
                    $name = $prefix.'['.$key.']';
                }
                if (is_array($value)) {
                    $string .= self::arrayToHiddenInput($value, $name);
                } else {
                    $string .= sprintf('<input type="hidden" value="%s" name="%s">'."\n", htmlReady($value), htmlReady($name));
                }
            }
        } else {
            foreach ($array as $i => $item) {
                if (is_array($item)) {
                    $string .= self::arrayToHiddenInput($item, $prefix.'['.((int) $i).']');
                } else {
                    $string .= sprintf('<input type="hidden" name="%s[%d]" value="%s">'."\n", htmlReady($prefix), $i, htmlReady($item));
                }
            }
        }

        return $string;
    }
}
