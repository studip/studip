<?php
namespace Widgets;

use AccessDeniedException;
use ActionsWidget;
use Comparable;
use DBManager;
use Icon;
use PageLayout;
use PDO;
use Range;
use RangeFactory;
use Renderable;
use Sidebar;
use SimpleORMap;
use SimpleORMapCollection;
use URLHelper;


/**
 * This model represents a widget container that consists of many widget
 * elements that again contain a widget.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
class Container extends SimpleORMap implements Comparable
{
    // Grid width (in widgets)
    const WIDTH = 6;

    // Different modes for the containers
    const MODE_DEFAULT = 'default';
    const MODE_ADMIN = 'admin';

    protected static $mode = self::MODE_DEFAULT;

    protected $default = false;
    protected $default_id = null;

    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'widget_containers';

        $config['has_many']['elements'] = [
            'class_name'        => 'Widgets\\Element',
            'assoc_foreign_key' => 'container_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY `y` ASC, `x` ASC',
        ];

        $config['additional_fields']['range'] = [
            'get' => function (Container $container) {
                return RangeFactory::createRange(
                    $container->range_type,
                    $container->range_id
                );
            },
            'set' => function (Container $container, $field, Range $range) {
                $container->range_type = $range->getRangeType();
                $container->range_id   = $range->getRangeId();
            },
        ];

        $config['additional_fields']['default_path'] = [
            'get' => function (Container $container) {
                return implode('/', [
                    $container->range_type,
                    $container->scope,
                    $container->getDefaultId() ?: 'null',
                ]);
            },
        ];

        parent::configure($config);
    }

    /**
     * Set the global mode for all containers. Seems a little error prone but
     * works pretty well so far.
     *
     * @param string $mode Mode to set
     */
    public static function setMode($mode)
    {
        if ($mode === self::MODE_ADMIN && !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        self::$mode = $mode;
    }

    /**
     * Returns the current mode for all containers.
     *
     * @return string
     */
    public static function getMode()
    {
        return self::$mode;
    }

    /**
     * Creates a container for the specified range and scope. If no container
     * exists, a new one is created.
     *
     * @param Range  $range      Range to create the widget container folder
     * @param String $scope      Optional scope identifier, defaults to 'default'
     * @param mixed  $default_id Optional id for the appropriate default container
     * @return Container
     */
    public static function createForRange(Range $range, $scope = 'default', $default_id = null)
    {
        $container = self::findOneByRange($range, $scope);
        if ($container === null) {
            $container =  new self();
            $container->range = $range;
            $container->scope = $scope;
            $container->store();

            $defaults = self::getDefaultContainerForRange($range->getRangeType(), $default_id, $scope);
            if ($defaults !== null) {
                $container->parent_id = $defaults->id;
                $container->store();
                $defaults->transferElements($container);
            }
        }

        $container->setDefaultId($default_id);

        return $container;
    }

    /**
     * Returns the default container for a specific range identified by type,
     * a default and a scope.
     *
     * @param string $range_type Type of the range
     * @param string $default_id Id for the default entry (often null, for user
     *                           based containers it's the permission)
     * @param string $scope      Selected scope
     * @return Container instance
     */
    public static function getDefaultContainerForRange($range_type, $default_id, $scope)
    {
        if ($default_id === null) {
            $container = self::findOneBySQL("range_type = :range_type AND range_id IS NULL AND scope = :scope", [
                ':range_type' => $range_type,
                ':scope'      => $scope,
            ]);
        } else {
            $container = self::findOneBySQL("range_type = :range_type AND range_id = :default_id AND scope = :scope", [
                ':range_type' => $range_type,
                ':default_id' => $default_id,
                ':scope'      => $scope,
            ]);
        }
        if ($container === null) {
            $container = new self();
            $container->range = RangeFactory::createRange($range_type, $default_id);
            $container->scope = $scope;
        }

        $container->setDefault(true);

        return $container;
    }

    /**
     * Returns a list of all default containers - optionally grouped by range
     * type.
     *
     * @param bool $grouped Return grouped by range type?
     * @return array of containers (1-dimensional, optionally 2-dimensional
     *               grouped by range type)
     */
    public static function getAllDefaultContainers($grouped = false)
    {
        $condition  = "range_type IN ('course', 'institute') AND range_id IS NULL";
        $containers0 = self::findBySQL($condition);

        $condition = "range_type = 'user' AND range_id IN ('user', 'autor', 'tutor', 'dozent', 'admin', 'root')";
        $containers1 = self::findBySQL($condition);

        $containers = SimpleORMapCollection::createFromArray(
            array_merge($containers0, $containers1)
        );
        $containers->setDefault();

        if (!$grouped) {
            return $containers;
        }

        $groups = array_fill_keys(['course', 'institute', 'user'], []);
        $containers->each(function (Container $container) use (&$groups) {
            $groups[$container->range_type][] = $container;
        });
        return array_map('SimpleORMapCollection::createFromArray', $groups);
    }

    /**
     * Finds a collection of container by range and scope.
     *
     * @param Range  $range Range to create the widget container folder
     * @param String $scope Optional scope identifier, defaults to 'default'
     * @return SimpleORMapCollection of Containers
     */
    public static function findByRange(Range $range, $scope = 'default')
    {
        if ($range->getRangeId() === null) {
            $result = self::findBySql('range_type = :range_type AND range_id IS NULL AND scope = :scope', [
                ':range_type' => $range->getRangeType(),
                ':scope'      => $scope,
            ]);
        } else {
            $result = self::findBySql('range_type = :range_type AND range_id = :range_id AND scope = :scope', [
                ':range_type' => $range->getRangeType(),
                ':range_id'   => $range->getRangeId(),
                ':scope'      => $scope,
            ]);
        }
        return SimpleORMapCollection::createFromArray($result);
    }

    /**
     * Finds one container by range and scope.
     *
     * @param Range  $range Range to create the widget container folder
     * @param String $scope Optional scope identifier, defaults to 'default'
     * @return mixed (Container|null)
     */
    public static function findOneByRange(Range $range, $scope = 'default')
    {
        return self::findByRange($range, $scope)->first();
    }

    /**
     * Adds a widget to the container by creating a new Element.
     *
     * Be aware that this method does not do any sanity checks and might lead
     * to a collision with another widget. Usually, the widget is added as a
     * placeholder and before the container is loaded again, the client will
     * have sent a new layout that is collision free (in other words: the
     * widget grid lib in javascript will handle this).
     *
     * @param Widget $widget Widget to add
     * @param int    $width  Width of the widget
     * @param int    $height Height of the widget
     * @param mixed  $x      Optional X position of the widget, defaults to
     *                       first free position at the the end of the grid
     * @param mixed  $y      Optional Y position of the widget, defaults to
     *                       first free position at the the end of the grid
     * @return Element
     */
    public function addWidget(WidgetInterface $widget, $width = 1, $height = 1, $x = null, $y = null)
    {
        $freeslot = $this->getFreeSlot($width, $height);
        if ($x === null) {
            $x = $freeslot[0];
        }
        if ($y === null) {
            $y = $freeslot[1];
        }

        $element = new Element();
        $element->container = $this;
        $element->widget    = $widget;
        $element->x         = $x;
        $element->y         = $y;
        $element->width     = $width;
        $element->height    = $height;
        $element->locked    = false;
        $element->options   = [];
        $element->removable = $widget->mayBeRemoved();
        $element->store();

        return $element;
    }

    /**
     * Returns a list of available widgets that might be added to the container.
     *
     * @return array of Widget
     */
    public function getAvailableWidgets($scope = null)
    {
        $widgets = Widget::listForRange($this->range, $scope);
        $widgets = array_filter($widgets, function ($widget) {
            return !$this->contains($widget)
                || $widget->mayBeDuplicated();
        });
        return $widgets;
    }

    /**
     * Returns whether this container contains the widget in question.
     *
     * @param Widget $widget Widget to test
     * @return bool
     */
    public function contains(WidgetInterface $widget)
    {
        return $this->elements->findOneBy('widget_id', $widget->getId()) !== null;
    }

    /**
     * Returns the next free slot in this container/grid.
     *
     * @param int $width  Width of the requested slot
     * @param int $height Height of the requested slot
     * @return Array with x and y coordinate of the next free slot
     */
    private function getFreeSlot($width, $height)
    {
        $query = "SELECT `x`, `y`, `width`, `height`
                  FROM `widget_elements`
                  WHERE `container_id` = :id
                  ORDER BY `y` DESC, `x` DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $this->id);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [0, 0];
        }

        if ($row['x'] + $row['width'] + $width < self::WIDTH) {
            return [$row['x'] + $row['width'], $row['y']];
        }

        return [0, $row['y'] + 1];
    }

    /**
     * Determines whether this container equals another container.
     * Two container are equal if all of the following conditions match:
     *
     * - Same range type and scope
     * - Same number of elements
     * - Same position of equal widgets (by id/type) with same settings/options
     *
     * @param Container $other Container to test equality against
     * @return bool
     */
    public function equals($other)
    {
        if (!$other instanceof self) {
            return false;
        }

        if ($other->range_type !== $this->range_type || $other->scope !== $this->scope) {
            return false;
        }

        if (count($other->elements) !== count($this->elements)) {
            return false;
        }

        foreach ($this->elements as $index => $element) {
            $other_element = $other->elements[$index];

            if ($element->x != $other_element->x
                || $element->y != $other_element->y
                || !$element->equals($other_element)) {
                    return false;
                }
        }

        return true;
    }

    /**
     * Sets the flag that indicates that this container is a default container.
     * Handle this with care!
     *
     * @param bool $default Default state
     */
    public function setDefault($default = true)
    {
        $this->default = $default;
    }

    /**
     * Returns whether this container is a default container or not.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Sets the default id for this container
     *
     * @param string $id Default id
     */
    public function setDefaultId($id)
    {
        $this->default_id = $id;
    }

    /**
     * Returns the default id for this container.
     *
     * @return string;
     */
    public function getDefaultId()
    {
        return $this->isDefault()
             ? $this->range_id ?: $this->default_id
             : $this->default_id;
    }

    // Incorporate Renderable trait
    use Renderable {
        render as protected parentRender;
    }

    /**
     * Returns the template name for this container.
     *
     * @return string
     */
    protected function getTemplateName()
    {
        return 'widgets-new/container.php';
    }

    /**
     * Returns neccessary variables to render the container template.
     *
     * @param array $variables Optional additional variables
     * @return array of variables
     */
    protected function getTemplateVariables(array $variables = [])
    {
        $variables['container'] = $this;
        return $variables;
    }

    /**
     * Renders this container and sets up the sidebar (if possible).
     *
     * @param array $variables Optional additional variables
     * @return string
     * @todo Should this really be in the model???
     */
    public function render(array $variables = [])
    {
        $this->setupSidebar();

        $variables['mode'] = self::$mode;

        return $this->parentRender($variables);
    }

    /**
     * Initializes the sidebar required for every container. The layout of the
     * sidebar is defined by the global mode of the containers and the user's
     * permission. If the current user may administer the container's range,
     * additional functionality is presented.
     */
    private function setupSidebar()
    {
        // TODO: Adjust this for real usage in 4.2
        if (true || !$this->range->userMayEditRange()) {
            return;
        }

        $actions = Sidebar::get()->addWidget(
            new ActionsWidget(),
            'widget-actions'
        );
        $actions->setTitle(_('Widget-Aktionen'));

        $actions->addLink(
            _('Neues Widget hinzufügen'),
            URLHelper::getURL("dispatch.php/widgets/add/{$this->id}/{$this->scope}", [
                'return_to' => $_SERVER['REQUEST_URI'],
            ]),
            Icon::create('add'),
            ['data-dialog' => 'size=auto', 'class' => 'widget-add-toggle']
        );

        if (!$this->isDefault() && self::$mode !== self::MODE_ADMIN) {
            $actions->addLink(
                _('Widget-Layout zurücksetzen'),
                URLHelper::getURL("dispatch.php/widgets/reset/{$this->id}", [
                    'return_to' => $_SERVER['REQUEST_URI'],
                ]),
                Icon::create('refresh'),
                ['data-confirm' => _('Wollen Sie Ihr Widget-Layout wirklich zurücksetzen?')]
            );
        }

        if ($GLOBALS['user']->perms === 'root') {
            if (self::$mode === self::MODE_ADMIN) {
                $actions->addLink(
                    _('Widget in allen Instanzen einfügen'),
                    URLHelper::getURL("dispatch.php/widgets/add_to_all/{$this->default_path}"),
                    Icon::create('add-circle-full')
                )->asDialog('size=auto');
            } else {
                $actions->addLink(
                    _('Standard-Konfiguration bearbeiten'),
                    URLHelper::getURL("dispatch.php/widgets/defaults/{$this->default_path}"),
                    Icon::create('edit')
                );
            }
            $actions->addLink(
                _('Zur Administration'),
                URLHelper::getLink('dispatch.php/admin/widgets/defaults'),
                Icon::create('link-intern')
            );
        }
    }

    /**
     * Stores the container.
     *
     * If the container is equal to the default container of it's range and
     * scope, the parent id of the default container is preserved. If anything
     * has changed, the default id will be removed and the container will not
     * be changed when the default layout is changed.
     *
     * If no parent id is set, the container is treated as a default container.
     * This means that all containers that have this container as a parent,
     * will be adjusted to match the layout of this container.
     *
     * @return int number of rows stored
     */
    public function store()
    {
        if (!$this->isNew() && $this->parent_id !== null) {
            $defaults = self::getDefaultContainerForRange($this->range_type, $this->getDefaultId(), $this->scope);

            if (!$defaults || !$this->equals($defaults)) {
                $this->parent_id = null;
            }
        } elseif ($this->parent_id === null) {
            self::findeachbySQL(function ($container) {
                $container->elements->delete();
                $this->transferElements($container);
            }, 'parent_id = ?', [$this->id]);
        }
        return parent::store();
    }

    /**
     * Transfers elements from this container to another container.
     * In this process, the elements are cloned since they are unique to
     * a container.
     *
     * @param Container $other Container to transfer the element to.
     */
    public function transferElements(Container $other)
    {
        foreach ($this->elements as $element) {
            $element = Element::build($element);
            $element->id = null;
            $element->container_id = $other->id;
            $element->store();
        }
    }

    /**
     * Return all container ids that are derived from this containers. This
     * implies that the current container is a default container.
     *
     * @param bool $include_self Includes this container in the returned array
     * @return array of container ids
     */
    public function getDerivedContainerIds($include_self = false)
    {
        if (!$this->isDefault()) {
            return [];
        }

        $conditions = ['`parent_id` = ?'];
        $parameters = [$this->id];
        if ($this->range_type === 'user') {
            $condition  = '`range_type` = ? AND `scope` = ? AND `range_id` IN (';
            $condition .= 'SELECT `user_id` FROM `auth_user_md5` WHERE `perms` = ?';
            $condition .= ')';

            $conditions[] = $condition;
            array_push($parameters, $this->range_type, $this->scope, $this->range_id);
        } else {
            $conditions[] = '`range_type` = ? AND `scope` = ?';
            array_push($parameters, $this->range_type, $this->scope);
        }

        $conditions = '(' . implode(') OR (', $conditions) . ')';

        $query = "SELECT `container_id`
                  FROM `widget_containers`
                  WHERE {$conditions}";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        if (!$include_self) {
            $ids = array_diff($ids, [$this->id]);
        } elseif (!in_array($this->id, $ids)) {
            $ids[] = $this->id;
        }

        return $ids;
    }

    /**
     * Renders a preview of this container which displays the raw layout of the
     * widgets with no content and functionality.
     *
     * This method also adds the required squeeze package for the preview to
     * the page layout.
     *
     * @return string containg the preview as HTML
     */
    public function renderPreview()
    {
        return $this->getTemplate('widgets-new/preview.php')->render([
            'width'   => self::WIDTH,
            'preview' => $this->getGridForPreview(),
        ]);
    }

    /**
     * Returns the underlying grid of the container as a two-dimensional array
     * for the preview. Heights are adjusted in a way that the preview should
     * be as narrow as possible. In other words, a widget with a height of 10
     * might be reduced to a height of 1.
     *
     * @return array of rows with elements
     */
    private function getGridForPreview()
    {
        $keys = array_unique($this->elements->pluck('y'));
        $preview = array_fill_keys($keys, []);

        $x = $y = 0;
        foreach ($this->elements as $element) {
            if ($y != $element->y) {
                $x = 0;
            }

            // Fill leading gaps
            if ($x != $element->x) {
                $preview[$element->y][] = [
                    'width'  => $element->x - $x,
                    'height' => 0,
                    'label'  => false,
                ];

                $x = $element->x;
            }

            $preview[$element->y][] = [
                'width'  => $element->width,
                'height' => $element->height,
                'label'  => $element->widget->getName(),
            ];

            $x = ($x + $element->width) % self::WIDTH;
            $y = $element->y;
        }

        // Ensure correctly set heights
        foreach ($preview as $y => $items) {
            if (count($items) === 1) {
                foreach ($items as $index => $item) {
                    $preview[$y][$index]['height'] = 1;
                }
            } else {
                $height = min(array_map(function ($item) {
                    return $item['height'] ?: 1;
                }, $items));

                foreach ($items as $index => $item) {
                    $preview[$y][$index]['height'] = $item['height'] ?: $height;
                }
            }
        }

        return $preview;
    }
}
