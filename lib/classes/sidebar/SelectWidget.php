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

    public function setUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $url = str_replace('?' . $query , '', $url);
            parse_str(html_entity_decode($query) ?: '', $query_params);
        } else {
            $query_params = array();
        }

        $this->template_variables['url']    = URLHelper::getLink($url);
        $this->template_variables['params'] = $query_params;
    }

    public function setMaxLength($length)
    {
        $this->template_variables['max_length'] = $length;
    }

    public function setSelectParameterName($name)
    {
        $this->template_variables['name'] = $name;
    }

    public function setSelection($value)
    {
        $this->template_variables['value'] = $value;
    }

    public function setRequestMethod($method)
    {
        $this->template_variables['method'] = $method;
    }

    public function setMultiple($multiple)
    {
        $this->template_variables['multiple'] = $multiple;
    }

    public function setOptions(Array $options, $selected = false)
    {
        $selected = $selected ?: Request::getArray($this->template_variables['name']);
        //if selected is one single value
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        foreach ($options as $key => $label) {
            $element = new SelectElement($key, $label, in_array($key, $selected));
            $this->addElement($element);
        }
    }

    public function render($variables = array())
    {
        $attributes = array();
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

    private static function isArrayAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function arrayToHiddenInput($array, $prefix = '')
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