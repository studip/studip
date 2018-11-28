<?php
use Widgets\Container;
use Widgets\Widget;

class WidgetsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        // Sanitize arguments ("null" -> null)
        foreach ($args as $index => $arg) {
            if ($arg === 'null') {
                $args[$index] = null;
            }
        }

        parent::before_filter($action, $args);

        $this->return_to = Request::get('return_to');
    }

    public function add_action($container_id, $scope)
    {
        $this->container = Container::find($container_id);
        $this->widgets = $this->container->getAvailableWidgets($scope);
    }

    public function reset_action($container_id)
    {
        Container::find($container_id)->delete();

        PageLayout::postSuccess(_('Das Widget-Layout wurde zurückgesetzt'));
        $this->redirect($this->return_to);
    }

    public function defaults_action($range_type, $scope, $default_id = null)
    {
        $default_id = $default_id ?: null;

        Container::setMode('admin');
        $container = Container::getDefaultContainerForRange($range_type, $default_id, $scope);

        if ($container === null) {
            throw new Trails_UnknownAction('There is no default layout for specified range');
        }

        PageLayout::setTitle(sprintf(
            _('Widget-Konfiguration bearbeiten für Range "%s", Scope "%s" und Standard-Id "%s"'),
            $range_type,
            $scope,
            $default_id
        ));

        $this->render_text_with_layout($container->render());
    }

    public function execute_action($container_id, $element_id, $action, $admin = false)
    {
        $parameters = array_slice(func_get_args(), 3);
        $container  = Container::find($container_id);
        if ($admin) {
            Container::setMode(Container::MODE_ADMIN);
        }
        $element    = $container->elements->find($element_id);

        $response = $element->executeAction($action, $parameters, $admin);
        $response->connectWithTrailsResponse($this->response);

        $this->render_text($response->getContent());
    }

    public function add_to_all_action($range_type, $scope, $default_id = null, $from_admin = false)
    {
        PageLayout::setTitle(_('Widget in allen Instanzen hinzufügen'));

        $default_id = $default_id ?: null;
        $container = Container::getDefaultContainerForRange($range_type, $default_id, $scope);

        if ($container === null) {
            throw new Trails_UnknownAction('There is no default layout for specified range');
        }

        if (Request::isPost()) {
            $widget_id = Request::int('widget_id');
            $widget    = Widget::create($widget_id);

            $position  = Request::option('position');
            $height    = Request::int('height', 1);
            $removable = Request::int('removable', 1);
            $locked    = Request::int('locked', 0);

            $ids = $container->getDerivedContainerIds(true);
            $result = Container::findAndMapMany(function (Container $container) use (
                $widget, $position, $height, $removable, $locked
            ) {
                if ($container->contains($widget)) {
                    return 0;
                }

                if ($position === 'above') {
                    $container->elements->each(function ($element) use ($height) {
                        $element->y = $element->y + $height;
                        $element->store();
                    });
                    $y = 0;
                } elseif ($position === 'below') {
                    $y = max($container->elements->pluck('y')) + 1;
                }
                $element = $container->addWidget(
                    $widget,
                    Container::WIDTH, $height,
                    0, $y
                );

                $element->removable = $removable;
                $element->locked    = $locked;
                $element->store();

                return 1;
            }, $ids);

            PageLayout::postSuccess(sprintf(
                _('Das Plugin wurde erfolgreich in %u Instanz(en) eingefügt'),
                array_sum($result)
            ));
            if ($from_admin && $GLOBALS['perm']->have_perm('root')) {
                $this->redirect("admin/widgets/defaults/{$container->default_path}");
            } else {
                $this->redirect("widgets/defaults/{$container->default_path}");
            }
            return;
        }

        $this->container = $container;
        $this->widgets   = $this->container->getAvailableWidgets($scope);
    }
}
