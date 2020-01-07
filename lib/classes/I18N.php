<?php
/**
 * Translation class
 *
 * Automatic generation of inputs and textareas for i18n
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Peter Thienel <pthienel@data-quest.de>
 * @author  Elmar Ludwig <elmar.ludwig@uos.de>
 * @author  Florian Bieringer
 * @license GPL2 or any later version
 */
class I18N
{
    /**
     * Creates a set of HTML input elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Inputfild
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted input
     */
    public static function input($name, $value, array $attributes = [])
    {
        return new static('i18n/input.php', $name, $value, $attributes);
    }

    /**
     * Create a set of HTML textarea elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Textarea
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted textarea
     */
    public static function textarea($name, $value, array $attributes = [])
    {
        return new static('i18n/textarea.php', $name, $value, $attributes);
    }

    /**
     * Returns whether the i18n functionality is enabeld i.e. more than the
     * default language is configured in config_defaults.inc.php.
     *
     * @return bool True if i18n is enabled.
     */
    public static function isEnabled()
    {
        return is_array($GLOBALS['CONTENT_LANGUAGES']) &&
            count($GLOBALS['CONTENT_LANGUAGES']) > 1;
    }

    protected $template;
    protected $name;
    protected $value;
    protected $attributes;

    /**
     * Protected constructor in order to always force a specific input type.
     *
     * @param string|Flexi_Template $template   Template to use
     * @param string                $name       Name of the element
     * @param string|I18NString     $value      Value of the element
     * @param array_merge           $attributes Additional variables for the
     *                                          element
     */
    protected function __construct($template, $name, $value, array $attributes)
    {
        $this->template = $GLOBALS['template_factory']->open($template);
        $this->name     = $name;

        $this->value = $value instanceof I18NString
                     ? $value
                     : new I18NString($value);

        $this->attributes = $attributes;
    }

    /**
     * Sets the readonly state of the element.
     *
     * @param bool $state State of the readonly attribute (default true)
     * @return I18N object to allow chaining
     */
    public function setReadonly($state = true)
    {
        $this->attributes['readonly'] = (bool) $state;

        return $this;
    }

    /**
     * MVV: Check user's permissions for an object and set the readonly state
     * accordingly.
     *
     * @param ModuleManagementModel $object Object to check permissions for
     * @param string                $perm   Permission to check (default create)
     * @return I18N object to allow chaining
     */
    public function checkMvvPerms(ModuleManagementModel $object, $perm = MvvPerm::PERM_WRITE)
    {
        $may_edit = MvvPerm::get($object)->haveFieldPerm($field ?: $this->name, $perm);
        return $this->setReadOnly(!$may_edit);
    }

    /**
     * Renders the element as html.
     *
     * @param array $attributes Additional attributes
     * @return string
     */
    public function render(array $attributes = [])
    {
        $template = $this->template;

        if (self::isEnabled()) {
            $template->set_layout('i18n/group.php');
        }

        // Merge initially set attributes with current attributes
        $attributes = array_merge($this->attributes, $attributes);

        return $template->render([
            'languages'  => $GLOBALS['CONTENT_LANGUAGES'],
            'base_lang'  => key($GLOBALS['CONTENT_LANGUAGES']),
            'wysiwyg'    => in_array('wysiwyg', words($attributes['class'])),
            'name'       => $this->name,
            'value'      => $this->value,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Converts the object to a string by rendering it.
     *
     * @return string
     * @see I18N::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
