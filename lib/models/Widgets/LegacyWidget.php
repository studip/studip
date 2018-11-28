<?php
namespace Widgets;

use PageLayout;
use PluginEngine;
use Range;
use Request;
use StudIPPlugin;

/**
 * This model represents a widget element that is positioned in a container and
 * contains a widget plus additional settings/options.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
class LegacyWidget extends Widget
{
    private $plugin;
    private $template = null;
    private $icons;
    private $head_elements;

    /**
     * Constructs this legacy widget and connects it with the according Stud.IP
     * plugin.
     *
     * @param mixed $id Id of the widget
     * @param StudIPPlugin $plugin Stud.IP plugin that represents the legacy
     *                             widget
     */
    public function __construct($id, StudIPPlugin $plugin)
    {
        parent::__construct($id);

        $this->plugin = $plugin;
    }

    /**
     * Returns the description of this widget. The description is taken from
     * the manifest of the Stud.IP plugin.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->plugin->getMetadata()['description'];
    }

    /**
     * Returns the name of this widget. The name is the name of the Stud.IP
     * plugin.
     *
     * @return string
     */
    public function getName()
    {
        return $this->plugin->getPluginName();
    }

    /**
     * Returns the title of the widget which is either a self defined title or
     * the name of the Stud.IP plugin.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->enabled
             ? $this->renderPlugin()->title ?: $this->getName()
             : $this->getName();
    }

    /**
     * Returns the actions for this widget. The actions are mapped from the
     * icons attribute of the Stud.IP plugin's "icons" attribute which was
     * formerly used to create the actions in the upper right corner of a
     * widget (position is still the same, just the handling has changed).
     *
     * @param Range  $range Range to get the actions for
     * @param string $scope Scope to get the actions for
     */
    public function getActions(Range $range, $scope)
    {
        if (!$this->enabled) {
            return [];
        }

        $this->renderPlugin();

        if (!$this->icons) {
            return [];
        }

        $actions = [];
        foreach ($this->icons as $nav) {
            $action = new WidgetAction($nav->getImage()->getAttributes()['title']);
            $action->setAttributes(array_merge($nav->getLinkAttributes() + [
                'href'        => $nav->getURL(),
                'data-legacy' => true,
            ]));
            $action->setIcon($nav->getImage());
            $actions[$nav->getImage()->getShape()] = $action;
        }

        return $actions;
    }

    /**
     * Return the content of the widget. The content is the rendered template
     * which is returned from the Stud.IP plugin.
     *
     * @param Range  $range Range to get the content for
     * @param string $scope Scope to get the content for
     * @return string
     */
    public function getContent(Range $range, $scope)
    {
        if (!$this->enabled) {
            return '';
        }

        $template = $this->renderPlugin();
        $template->set_layout(null);
        $template->icons = null;
        $result = $template->render();
        if (Request::isXhr()) {
            $result = implode($this->head_elements) . $result;
        }
        return $result;
    }

    /**
     * Renders the Stud.IP plugin's contents. This is a flexi template.
     *
     * This method also watches whether the Stud.IP plugin might add assets to
     * the page layout and will extract them so that they may be injected into
     * the new widget system as well.
     *
     * This method also takes care that the plugin is only rendered once.
     *
     * @return Flexi_Template
     */
    private function renderPlugin()
    {
        if ($this->template === null) {
            $head_elements = preg_split('/(?<=>)\s*(?=<)/', PageLayout::getHeadElements());

            $this->template = $this->plugin->getPortalTemplate();
            $this->icons    = $this->template->icons ?: null;

            $this->head_elements = array_diff(
                preg_split('/(?<=>)\s*(?=<)/', PageLayout::getHeadElements()),
                $head_elements
            );
        }
        return $this->template;
    }

    /**
     * Returns whether this widget instance may be duplicated or used more than
     * once in a container.
     *
     * @return bool
     */
    public function mayBeDuplicated()
    {
        return false;
    }

    /**
     * Returns whether the widget should have a layout or not.
     *
     * @return bool
     * @todo Really neccessary? Seems to got lost in development
     */
    public function hasLayout()
    {
        return true;
    }

    /**
     * Returns a url for an action that is related to this widget. This method
     * is variadic in such a way that you may pass as many strings as you like
     * which will be concatenated to a valid url chunk. Only if the last
     * passed parameter is an array, it will be used as the parameters for
     * the generated url.
     *
     * @param String $to         URL chunk to generate complete url for
     * @param array  $parameters Additional url parameters
     */
    public function url_for($to, $parameters = [])
    {
        if (!$this->enabled) {
            return '';
        }

        $arguments = func_get_args();
        if (is_array(end($arguments))) {
            $parameters = array_pop($arguments);
        } else {
            $parameters = [];
        }

        $to = implode('/', $arguments);

        return PluginEngine::getURL($this->plugin, $parameters, $to);
    }
}
