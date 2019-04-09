<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class LinkElement extends WidgetElement implements ArrayAccess
{
    /**
     * Create link by parsing a html chunk.
     *
     * @param String $html HTML chunk to parse
     * @param Icon $icon Optional icon
     * @return LinkElement Link element from parsed html
     * @throws Exception if html can not be parsed
     */
    public static function fromHTML($html, \Icon $icon = null)
    {
        $matched = preg_match('~(<a(?P<attributes>(?:\s+\w+=".*?")+)>\s*(?P<label>.*?)\s*</a>)~s', $html, $match);
        if (!$matched) {
            throw new Exception('Could not parse html');
        }

        $attributes = self::parseAttributes($match['attributes']);
        $url        = $attributes['href'] ?: '#';
        unset($attributes['href']);

        return new self($match['label'], $url, $icon, $attributes);
    }

    /**
     * Parse a string of html attributes into an associative array.
     *
     * @param String $text String of html attributes
     * @return Array parsed attributes as key => value pairs
     * @see https://gist.github.com/rodneyrehm/3070128
     */
    protected static function parseAttributes($text)
    {
        $attributes = [];
        $pattern = '#(?(DEFINE)
                       (?<name>[a-zA-Z][a-zA-Z0-9-:]*)
                       (?<value_double>"[^"]+")
                       (?<value_single>\'[^\']+\')
                       (?<value_none>[^\s>]+)
                       (?<value>((?&value_double)|(?&value_single)|(?&value_none)))
                     )
                     (?<n>(?&name))(=(?<v>(?&value)))?#xs';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[$match['n']] = isset($match['v'])
                                         ? trim($match['v'], '\'"')
                                         : null;
            }
        }
        return $attributes;
    }

    public $url;
    public $label;
    public $icon;
    public $active = false;
    public $attributes = [];
    public $as_button = false;

    /**
     * create a link for a widget
     *
     * @param String $label    Label/content of the link
     * @param String $url      URL/Location of the link (raw url, no entities)
     * @param Icon $icon       Icon for the link
     * @param array  $attributes HTML-attributes for the a-tag in an associative array.
     */
    public function __construct($label, $url, \Icon $icon = null, $attributes = [])
    {
        parent::__construct();

        $this->label      = $label;
        $this->url        = $url;
        $this->attributes = $attributes;
        $this->icon       = $icon;
    }

    /**
     * Sets the active state of the element.
     *
     * @param bool $active Active state (optional, defaults to true)
     * @return LinkElement instance to allow chaining
     */
    public function setActive($active = true)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Sets the dialog options for the element. Passing false as $state will
     * reset the dialog options to "none".
     *
     * @param mixed $active Dialog options (optional, defaults to blank/standard
     *                      dialog)
     * @return LinkElement instance to allow chaining
     */
    public function asDialog($state = '')
    {
        if ($state !== false) {
            $this->attributes['data-dialog'] = $state;
        } else {
            unset($this->attributes['data-dialog']);
        }
        return $this;
    }

    /**
     * Defines whether the link should be rendered as a button/form with POST
     * method.
     *
     * @param bool $active State (optional, defaults to true)
     * @return LinkElement instance to allow chaining
     */
    public function asButton($state = true)
    {
        $this->as_button = $state;
        return $this;
    }

    /**
     * Sets the target attribute of the element.
     *
     * @param string $target Target attribute
     * @return LinkElement instance to allow chaining
     */
    public function setTarget($target)
    {
        if ($target) {
            $this->attributes['target'] = $target;
        } else {
            unset($this->attributes['target']);
        }
        return $this;
    }

    /**
     * Adds a css class to the rendered element.
     *
     * @param string $clas CSS class to add
     * @return LinkElement instance to allow chaining
     */
    public function addClass($class)
    {
        $this->attributes['class'] = $this->attributes['class']
            ? $this->attributes['class'] . " " . $class
            : $class;
        return $this;
    }

    /**
     * Returns whether the element is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return isset($this->attributes['disabled']) && $this->attributes['disabled'] !== false;
    }

    /**
     * Renders the element.
     *
     * @return string
     */
    public function render()
    {
        // TODO: Remove this some versions after 4.3
        if ($this->url !== html_entity_decode($this->url)) {
            $this->url = html_entity_decode($this->url);
        }

        $disabled = $this->isDisabled();

        if ($this->as_button && !$disabled) {
            return $this->renderButton();
        }

        if ($this->active) {
            $this->addClass('active');
        }

        $attributes = (array) $this->attributes;

        if ($disabled) {
            $tag = 'span';
        } else {
            $tag = 'a';
            $attributes['href'] = $this->url;
        }

        return sprintf(
            '<%1$s %2$s>%3$s</%1$s>',
            $tag,
            arrayToHtmlAttributes($attributes),
            htmlReady($this->label)
        );
    }

    /**
     * Renders the element as a button/form.
     *
     * @return string
     */
    protected function renderButton()
    {
        return sprintf(
            '<form action="%1$s" method="post" %2$s class="link-form">%3$s<button type="submit">%4$s</button></form>',
            $this->url,
            arrayToHtmlAttributes((array) $this->attributes),
            CSRFProtection::tokenTag(),
            htmlReady($this->label)
        );
    }

    /**
     * Returns whether the given url is valid.
     *
     * @param string $url URL to test
     * @return bool
     */
    protected function isURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // Array access for attributes

    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
