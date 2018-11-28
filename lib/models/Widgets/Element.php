<?php
namespace Widgets;

use Comparable;
use Exception;
use PageLayout;
use Range;
use Renderable;
use SimpleORMap;


/**
 * This model represents a widget element that is positioned in a container and
 * contains a widget plus additional settings/options.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
class Element extends SimpleORMap implements Comparable
{
    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'widget_elements';

        $config['belongs_to']['container'] = [
            'class_name'        => 'Widgets\\Container',
            'foreign_key'       => 'container_id',
            'assoc_foreign_key' => 'container_id',
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        $config['additional_fields']['widget'] = [
            'get' => function (Element $element) {
                // TODO: Cache this or implement any kind of lazy loading
                $widget = Widget::create($element->widget_id);
                $widget->setOptions($element->options->getArrayCopy());
                $widget->connectWithElement($element);
                return $widget;
            },
            'set' => function (Element $element, $field, WidgetInterface $widget) {
                $element->widget_id = $widget->getId();
                $element->options   = $widget->getOptions();
            },
        ];

        parent::configure($config);
    }

    /**
     * Returns whether this element/widget supports the requested action.
     *
     * @param String $action Requested action
     * @return bool
     */
    public function hasAction($action)
    {
        $container = $this->container;

        $actions = $this->getActions($container->range, $container->scope);
        return isset($actions[$action]);
    }

    /**
     * Returns a list of actions that the element/widget supports. Each action
     * is defined by a label and a callback and may have additional attributes
     * for the html representation of the action.
     *
     * @return array of WidgetAction
     */
    public function getActions(Range $range, $scope)
    {
        $actions = ($this->widget->enabled && Container::getMode() !== Container::MODE_ADMIN)
                 ? $this->widget->getActions($range, $scope)
                 : [];

        if (Container::getMode() === Container::MODE_ADMIN) {
            $actions['lock'] = new WidgetAction(_('Widget fixieren'));
            $actions['lock']->setAdminMode();
            $actions['lock']->setCallback(function (Element $element, Response $response) {
                $element->locked = !$element->locked;
                $element->store();

                $response->addHeaders([
                    'X-Widget-Execute' => sprintf(
                        'STUDIP.WidgetSystem.get(%u).lockElement(%u, %s)',
                        $element->container_id,
                        $element->id,
                        json_encode((bool)$element->locked)
                    ),
                ]);

                return false;
            });

            $actions['removable'] = new WidgetAction(_('Widget entfernbar?'));
            $actions['removable']->setAdminMode();
            $actions['removable']->setCallback(function (Element $element, Response $response) {
                $element->removable = !$element->removable;
                $element->store();

                $response->addHeaders([
                    'X-Widget-Execute' => sprintf(
                        'STUDIP.WidgetSystem.get(%u).setRemovableElement(%u, %s)',
                        $element->container_id,
                        $element->id,
                        json_encode((bool)$element->removable)
                    ),
                ]);

                return false;
            });
        }

        if (($this->widget->mayBeRemoved() && $this->removable) || Container::getMode() === Container::MODE_ADMIN) {
            $actions['remove'] = new WidgetAction(_('Widget entfernen'));
            $actions['remove']->setCallback(function (Element $element, Response $response) {
                $element_id   = $element->id;
                $container_id = $element->container_id;
                $widget       = $element->widget;

                $element->delete();

                $response->addHeader('X-Widget-Execute', sprintf(
                    'STUDIP.WidgetSystem.get(%u).removeElement(%u);',
                    $container_id,
                    $element_id
                ));

                $response->addHeader('X-Widget-Id', $widget->id);
                $response->addHeader('X-Refresh', !$widget->mayBeDuplicated());

                return false;
            });
            $actions['remove']->setAttributes([
                'data-confirm' => _('Soll das Widget wirklich entfernt werden?'),
            ]);
        }

        return $actions;
    }

    /**
     * Executes an action on this element/widget. Since the execution is always
     * invoked by a REST API call, the method is given the routemap that invoked
     * the action call.
     *
     * @param string   $action     Action to execute
     * @param array    $parameters Parameters to call the action method with
     * @param mixed Result of the action call
     */
    public function executeAction($action, array $parameters, $admin_mode = false)
    {
        $response = new Response([
            'element_id'   => $this->id,
            'container_id' => $this->container_id,
        ]);

        $container = $this->container;
        if ($admin_mode) {
            $container->setMode(Container::ADMIN_MODE);
        }

        $actions = $this->getActions($container->range, $container->scope);
        if (!isset($actions[$action])) {
            throw new Exception('Unknown action called');
        }
        array_unshift($parameters, $this, $response);

        $response->setContent($actions[$action]->invokeCallback($parameters));
        return $response;
    }

    /**
     * Returns whether this element is equal to another element. Two elements
     * are equal if all of the following conditions match:
     *
     * - Same widget (by id/type)
     * - Same width and height
     * - Same options/settings
     *
     * @param Element $other Element to test equality against
     * @return bool
     */
    public function equals($other)
    {
        if (!$other instanceof self) {
            return false;
        }

        return $other->widget_id == $this->widget_id
            && $other->options == $this->options
            && $other->locked == $this->locked
            && $other->removable == $this->removable
            && $other->width == $this->width
            && $other->height == $this->height;
    }

    // Incorporate Renderable trait
    use Renderable;

    /**
     * Returns the template name for this element.
     *
     * @return string
     */
    protected function getTemplateName()
    {
        return 'widgets-new/widget.php';
    }

    /**
     * Returns the layout for this element's template.
     *
     * @return Flexi_Template
     */
    protected function getTemplateLayout()
    {
        return $this->getTemplate('widgets-new/wrapper.php', [
            'element' => $this,
        ]);
    }

    /**
     * Returns neccessary variables to render the element template.
     *
     * @param array $variables Optional additional variables
     * @return array of variables
     */
    protected function getTemplateVariables(array $variables = [])
    {
        $container = $this->container;

        // TODO: Remove debug
        if (!($container->range instanceof Range)) {
            var_dump($container->range);die;
        }

        $actions = $this->getActions($container->range, $container->scope);
        $actions = array_filter($actions, function (WidgetAction $action) {
            return $action->hasIcon();
        });

        $variables['widget']    = $this->widget;
        $variables['container'] = $container;
        $variables['actions'] = $this->getTemplate('widgets-new/widget-actions.php', $variables)->render([
            'actions' => $actions,
            'id'      => $this->id,
        ]);
        return $variables;
    }

    /**
     * Stores this element. If the element was actually stored, store the
     * connected container as well. This is neccessary to correctly adjust
     * the parent id of the conainer.
     *
     * @return int number of changed rows
     */
    public function store()
    {
        if ($result = parent::store()) {
            $this->container->store();
        }
        return $result;
    }
}
