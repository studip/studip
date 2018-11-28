<?php
namespace Widgets;

use Icon;

/**
 * This class represent an action that is associated with a certain widget.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
class WidgetAction
{
    protected $label;
    protected $callback;
    protected $attributes;
    protected $has_icon = true;
    protected $admin_mode = false;

    /**
     * Constructs the action.
     *
     * @param string   $label      Label/name of the action
     * @param callable $callback   Callback to execute when the action is executed
     * @param array    $attributes Optional additional attributes for the
     *                             rendered action element
     */
    public function __construct($label, callable $callback = null, array $attributes = [])
    {
        $this->setLabel($label);
        $this->setAttributes($attributes);

        if ($callback !== null) {
            $this->setCallback($callback);
        }
    }

    /**
     * Sets the label/name for this action.
     *
     * @param string $label Label/name of the action
     * @return WidgetAction instance to allow chaining
     */
    public function setLabel($label)
    {
        $this->label = trim($label);
        return $this;
    }

    /**
     * Returns the label/name of this action.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the callback to execute to execute when this action is executed.
     *
     * @param callbacke $callback Callback to execute when this action is
     *                            executed
     * @return WidgetAction instance to allow chaining
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Returns the defined callback of this action.
     *
     * @return WidgetAction or null
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Invokes the defined callback for this action.
     *
     * @param array $arguments Additional arguments to pass to the callback
     * @return mixed whatever the callback might return or null if no callback
     *               was defined
     */
    public function invokeCallback(array $arguments)
    {
        return $this->callback
             ? call_user_func_array($this->callback, $arguments)
             : null;
    }

    /**
     * Sets the attributes for the rendered html element.
     *
     * @param array $attributes Set of attributes
     * @return WidgetAction instance to allow chaining
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Returns the defined attributes of this action.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns whether an icon has been defined if no parameters is passed or
     * sets whether an icon is defined if a parameter is passed.
     *
     * @param mixed $has_icon Optional state to set
     * @return mixed Current state if no parameter is set
     * @todo This seems rather nasty.
     */
    public function hasIcon($has_icon = null)
    {
        if (func_num_args() > 0) {
            $this->has_icon = (bool)$has_icon;
        } else {
            return $this->has_icon;
        }
    }

    /**
     * Defines whether this action requires admin mode.
     *
     * @param bool $state
     */
    public function setAdminMode($state = true)
    {
        $this->admin_mode = $state;
    }

    /**
     * Returns whether this action requires admin mode.
     *
     * @return bool
     */
    public function getAdminMode()
    {
        return $this->admin_mode;
    }

    // Legacy stuff below

    private $icon = null;

    /**
     * Defines an icon for the action.
     *
     * @param Icon $icon Icon for the action
     */
    public function setIcon(Icon $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Returns the defined action for this action.
     *
     * @return Icon instance or null if no icon has been defined.
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
