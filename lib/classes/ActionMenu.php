<?php
/**
 * This class represents the action menu used to group actions.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 */
class ActionMenu
{
    const THRESHOLD = 1;
    const TEMPLATE_FILE_SINGLE   = 'shared/action-menu-single.php';
    const TEMPLATE_FILE_MULTIPLE = 'shared/action-menu.php';

    /**
     * Returns an instance.
     *
     * @return ActionMenu
     */
    public static function get()
    {
        return new self();
    }

    private $actions = [];
    private $attributes = [];

    private $condition_all = null;
    private $condition     = true;


    /**
     * Private constructur.
     *
     * @see ActionMenu::get()
     */
    private function __construct()
    {
        $this->addCSSClass('action-menu');
    }

    /**
     * Set condition for the next added item. If condition is false,
     * the item will not be added.
     *
     * @param bool $state State of the condition
     * @return ActionMenu instance to allow chaining
     */
    public function condition($state)
    {
        $this->condition = (bool)$state;

        return $this;
    }

    /**
     * Set condition for all the next added items. If condition is false,
     * no items will be added.
     *
     * @param bool $state State of the condition
     * @return ActionMenu instance to allow chaining
     */
    public function conditionAll($state)
    {
        $this->condition_all = $state;

        return $this;
    }

    /**
     * Checks the condition. Takes global and local (conditionAll() &
     * condition()) conditions into account.
     *
     * @return bool indicating whether the condition is met or not
     */
    protected function checkCondition()
    {
        $result = $this->condition;
        if ($this->condition_all !== null) {
            $result = $result && $this->condition_all;
        }

        $this->condition = true;

        return $result;
    }

    /**
     * Adds a link to the list of actions.
     *
     * @param String $link       Link target
     * @param String $label      Textual representation of the link
     * @param mixed  $icon       Optional icon (as Icon object)
     * @param array  $attributes Optional attributes to add to the <a> tag
     * @return ActionMenu instance to allow chaining
     */
    public function addLink($link, $label, Icon $icon = null, array $attributes = [])
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'       => 'link',
                'link'       => $link,
                'icon'       => $icon,
                'label'      => $label,
                'attributes' => $attributes,
            ];
        }

        return $this;
    }

    /**
     * Adds a button to the list of actions.
     *
     * @param String $name       Button name
     * @param String $label      Textual representation of the name
     * @param mixed  $icon       Optional icon (as Icon object)
     * @param array  $attributes Optional attributes to add to the <a> tag
     * @return ActionMenu instance to allow chaining
     */
    public function addButton($name, $label, Icon $icon = null, array $attributes = [])
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'       => 'button',
                'name'       => $name,
                'icon'       => $icon,
                'label'      => $label,
                'attributes' => $attributes,
            ];
        }

        return $this;
    }

    /**
     * Adds a MultiPersonSearch object to the list of actions.
     *
     * @param MultiPersonSearch $mp MultiPersonSearch object
     * @return ActionMenu instance to allow chaining
     */
    public function addMultiPersonSearch(MultiPersonSearch $mp)
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'   => 'multi-person-search',
                'object' => $mp,
            ];
        }

        return $this;
    }

    /**
     * Adds a css classs to the root element in html.
     *
     * @param string $class Name of the css class
     * @return ActionMenu instance to allow chaining
     */
    public function addCSSClass($class)
    {
        $this->addAttribute('class', $class, true);

        return $this;
    }

    /**
     * Adds an attribute to the root element in html.
     *
     * @param string  $key    Name of the attribute
     * @param string  $value  Value of the attribute
     * @param boolean $append Whether a current value should be append or not.
     */
    public function addAttribute($key, $value, $append = false)
    {
        if (isset($this->attributes[$key]) && $append) {
            $this->attributes[$key] .= " {$value}";
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Renders the action menu. If no item was added, an empty string will
     * be returned. If a single item was added, the item itself will be
     * displayed. Otherwise the whole menu will be rendered.
     *
     * @return String containing the html representation of the action menu
     */
    public function render()
    {
        if (count($this->actions) === 0) {
            return '';
        }

        $template_file = count($this->actions) <= self::THRESHOLD
                       ? self::TEMPLATE_FILE_SINGLE
                       : self::TEMPLATE_FILE_MULTIPLE;

        $template = $GLOBALS['template_factory']->open($template_file);
        $template->actions = array_map(function ($action) {
            $disabled = isset($action['attributes']['disabled'])
                     && $action['attributes']['disabled'] !== false;
            if ($disabled && $action['icon']) {
                $action['icon'] = $action['icon']->copyWithRole(Icon::ROLE_INACTIVE);
            }
            return $action;
        }, $this->actions);
        $template->attributes = $this->attributes;
        return $template->render();
    }

    /**
     * Magic method to render the menu as a string.
     *
     * @return String containing the html representation of the action menu
     * @see ActionMenu::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
