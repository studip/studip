<?php

/**
 * This class provides a resource tree view for the sidebar.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */
class ResourceTreeWidget extends SidebarWidget
{
    /**
     * $root_resource is the resource whose resource tree
     * shall be displayed by this widget.
     * The resource itself and all its children are displayed.
     */
    protected $root_resources = [];
    protected $parameter_name = '';
    protected $foldable = false;
    protected $current_resource_id = null;

    /**
     * This widget must be initialised by providing at least one
     * Resource object in an array.
     *
     * @param array $root_resources The root resource objects which will be
     *     displayed by this tree view.
     * @param string $title The title of this widget.
     * @param string|null $parameter_name The name of the URL parameter which
     *     will be set when one of the resources in the tree is selected.
     *     If parameter_name is set to null the items in the resource tree
     *     widget will link to the resource's details page.
     */
    public function __construct(
        array $root_resources = [],
        $title = '',
        $parameter_name = 'tree_selected_resource'
    )
    {
        parent::__construct();

        if (!$root_resources) {
            throw new InvalidArgumentException(
                'ResourceTreeWidget instances must be initalised with at least one resource object!'
            );
        }

        //Extra check to make sure the root_resources attribute of this instance
        //is an array containing only Resource objects or objects derived
        //from the Resource class:
        foreach ($root_resources as $root_resource) {
            if ($root_resource instanceof Resource) {
                $this->root_resources[] = $root_resource;
            }
        }

        if (!$this->root_resources) {
            throw new InvalidArgumentException(
                'No Resource object has been provided to the constructor of the ResourceTreeWidget class!'
            );
        }

        $this->root_resources = SimpleORMapCollection::createFromArray(
            $this->root_resources
        );
        $this->root_resources->orderBy('sort_position DESC, name ASC, mkdate ASC');

        $this->template = 'sidebar/resource-tree-widget';

        if ($title) {
            $this->title = $title;
        } else {
            $this->title = _('Ressourcenbaum');
        }

        $this->parameter_name = $parameter_name;
    }

    /**
     * The render method will attach the root resource
     * of this object to the set of variables which is
     * passed to the template.
     */
    public function render($variables = [])
    {
        if (!is_array($variables)) {
            $variables = [];
        }

        $variables['resources'] = $this->root_resources;
        $variables['title'] = $this->title;
        $variables['parameter_name'] = $this->parameter_name;
        if ($this->parameter_name) {
            $variables['selected_resource'] = Request::get($this->parameter_name);
        } else {
            $variables['selected_resource'] = $this->current_resource_id;
        }

        $resource_path = [];
        //If a resource is selected we get the IDs of all parent resources
        //so that we know in the template which tree items shall be visible.
        if ($variables['selected_resource']) {
            $resource = Resource::find($variables['selected_resource']);
            if ($resource) {
                $resource_path[] = $resource->id;
                $current_parent = $resource->parent;
                while ($current_parent) {
                    $resource_path[] = $current_parent->id;
                    $current_parent = $current_parent->parent;
                }
            }
        }
        $variables['resource_path'] = $resource_path;
        $variables['max_open_depth'] = 0;
        $variables['layout_css_classes'] = $this->layout_css_classes;

        $template = $GLOBALS['template_factory']->open(
            $this->template
        );
        $template->set_attributes($variables);
        $template->set_layout('sidebar/widget-layout');
        return $template->render();
    }

    public function setCurrentResource(Resource $resource)
    {
        $this->current_resource_id = $resource->id;
    }

    public function setCurrentResourceId($resource_id = null)
    {
        if ($resource_id) {
            $this->current_resource_id = $resource_id;
        }
    }

    public function setFoldable($foldable = false)
    {
        $this->foldable = (bool)$foldable;
    }

    public function isFoldable()
    {
        return $this->foldable;
    }
}
