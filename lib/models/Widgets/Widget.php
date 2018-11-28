<?php
namespace Widgets;

use DBManager;
use Exception;
use Flexi_TemplateFactory;
use PDO;
use Range;
use ReflectionObject;
use SimpleORMap;
use URLHelper;

/**
 * This model represents an abstract widget.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
abstract class Widget extends SimpleORMap implements WidgetInterface
{
    const EXECUTION_BASE_URL = 'dispatch.php/widgets/execute';

    private $element = null;
    private $options = [];

    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'widgets';

        $config['has_many']['elements'] = [
            'class_name'        => 'Widgets\\Element',
            'assoc_foreign_key' => 'widget_id',
        ];
        $config['additional_fields']['count_elements'] = [
            'get' => function ($object, $field) {
                        return Element::countBySQL('widget_id = :widget_id',
                                        [':widget_id' => $object->widget_id]);
                    },
            'set' => false,
        ];
        parent::configure($config);
    }

    // Cached widget mapping (id -> classname)
    private static $widget_mapping = null;

    /**
     * Returns the widget_mapping (id -> infos) for a specific or all widgets.
     *
     * @param mixed $id Mapping for a specific id or all (for id = null)
     * @return array
     * @throws Exception when no widget is found with the given id
     */
    private static function getWidgetMapping($id = null)
    {
        if (self::$widget_mapping === null) {
            $query = "SELECT `w`.`widget_id`, `w`.`class`, `w`.`filename`, `w`.`enabled`
                      FROM `widgets` AS `w`
                      LEFT JOIN `plugins` AS `p`
                        ON (`w`.`filename` IS NULL AND `p`.`pluginid` = `class`)
                      WHERE `p`.`pluginid` IS NULL
                         OR `p`.`enabled` IN ('yes', 'no')
                      ORDER BY `w`.`widget_id` ASC";
            self::$widget_mapping = DBManager::get()->fetchGrouped($query);
        }

        if ($id === null) {
            return self::$widget_mapping;
        }

        if (!isset(self::$widget_mapping[$id])) {
            throw new Exception("Unknown widget id '{$id}'");
        }

        return self::$widget_mapping[$id];
    }

    /**
     * Creates a widget by id.
     *
     * @param int   $id    Id of the widgtet
     * @param mixed $range Optional range to check suitability for
     * @return Widget subclass
     * @throws Exception when widget is not enabled and $allow_disabled is false
     */
    public static function create($id, Range $range_to_check = null)
    {
        $info = self::getWidgetMapping($id);

        // Legacy widget?
        if ($info['filename'] === null) {
            $manager = \PluginManager::getInstance();
            $plugin  = $manager->getPluginById($info['class']);
            return new LegacyWidget($id, $plugin);
        }

        if (!class_exists($info['class'])) {
            require_once $info['filename'];
        }

        $widget = new $info['class']($id);
        return $widget;
    }

    /**
     * Return all available widgets.
     *
     * @return array of widget instances
     */
    public static function findAll()
    {
        return array_map(
            'self::create',
            array_keys(self::getWidgetMapping())
        );
    }

    /**
     * Registers a widget for use.
     *
     * @param Widget $widget Widget to register
     * @return Widget subclass (copy of given widget)
     */
    public static function registerWidget(Widget $widget)
    {
        $reflection = new ReflectionObject($widget);
        $class      = $reflection->getName();

        // Widget already registered?
        $query = "SELECT `widget_id`
                  FROM `widgets`
                  WHERE `class` = :class";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':class', $class);
        $statement->execute();
        $widget_id = $statement->fetchColumn();

        if ($widget_id) {
            $sorm = self::create($widget_id);
        } else {
            $sorm = $reflection->newInstance();
            $sorm->class = $class;
        }

        $sorm->filename = str_replace(
            rtrim($GLOBALS['STUDIP_BASE_PATH'], '/') . '/',
            '',
            $reflection->getFileName()
        );

        $sorm->store();

        return $sorm;
    }

    /**
     * Lists all available widgets for a certain range.
     *
     * @param Range $range Range to list widgets for
     * @param mixed $scope Optional scope to test for
     * @return array of widgets
     * @todo   Permission check
     */
    public static function listForRange(Range $range, $scope = null)
    {
        $query = "SELECT widget_id
                  FROM widgets
                  WHERE `enabled` = 1";
        $ids = DBManager::get()->fetchFirst($query);

        $result = [];
        foreach ($ids as $id) {
            $widget = self::create($id);
            if ($widget->suitableForRange($range, $scope)) {
                $result[$id] = $widget;
            }
        }

        return $result;
    }

    /**
     * Returns whether this widget is suitable for the given range and scope.
     *
     * @param Range $range Range to check
     * @param mixed $scope Scope to check (may be null)
     * @return bool indicating whether this widget is suitable
     */
    public function suitableForRange(Range $range, $scope = null)
    {
        return true;
    }

    /**
     * Connect this widget with an actual element of a container.
     *
     * @param Element $element Element to connect with
     */
    public function connectWithElement(Element $element)
    {
        $this->element = $element;
    }

    /**
     * Returns the connected element of this widget.
     *
     * @return Element or null if no element has been set.
     */
    public function getElement()
    {
        return $this->element;
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
        if ($this->element === null) {
            throw new Exception('No connect element');
        }

        $arguments = func_get_args();
        if (is_array(end($arguments))) {
            $parameters = array_pop($arguments);
        } else {
            $parameters = [];
        }

        array_unshift(
            $arguments,
            self::EXECUTION_BASE_URL,
            $this->element->container_id,
            $this->element->id
        );
        $to = implode('/', $arguments);

        return URLHelper::getURL($to, $parameters);
    }

    /**
     * Returns the title of the widgets. Defaults to specified name.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getName();
    }

    /**
     * Sets the defined options for this widget instance. This method is
     * usually only called when a widget container is loaded and the elements
     * are initialized.
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        if ($this->element !== null) {
            $this->element->options = $options;
            $this->element->store();
        }
    }

    /**
     * Returns the options for this widget instance. This might and should be
     * overwritten by a subclass.
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
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
     * Returns whether this widget instance may be removed from a container.
     *
     * @return bool
     */
    public function mayBeRemoved()
    {
        return true;
    }

    /**
     * Returns whether this widget instance may be duplicated or used more than
     * once in a container.
     *
     * @return bool
     */
    public function mayBeDuplicated()
    {
        return true;
    }

    /**
     * Returns a list of possible widget actions.
     *
     * @return array of WidgetAction
     */
    public function getActions(Range $range, $scope)
    {
        return [];
    }

    /**
     * Opens and returns a template. Optionally, variables can be assigned.
     *
     * @param string $name      Name of the template
     * @param array  $variables Optional initial variables
     * @return Flexi_Template
     */
    public function getTemplate($name, array $variables = [])
    {
        // TODO: Too costly?
        $reflection = new ReflectionObject($this);
        $dirname    = dirname($reflection->getFileName());

        $factory  = new Flexi_TemplateFactory($dirname . '/templates');
        $template = $factory->open($name);
        $template->set_attributes($variables);
        $template->widget = $this;
        return $template;
    }
}
