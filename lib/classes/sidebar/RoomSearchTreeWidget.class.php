<?php

/**
 * This class provides a resource tree view for the room search sidebar.
 *
 * @author  Timo Hartge <hartge@data-quest.de>
 * @license GNU General Public License v2 or later.
 * @since   4.5
 */
class RoomSearchTreeWidget extends ResourceTreeWidget
{
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
        $parameter_name = null
    )
    {
        parent::__construct($root_resources, $title, $parameter_name);
        $this->addLayoutCSSClass('room-search-tree-widget');
        $this->template = 'sidebar/room-search-tree-widget';

        if ($title) {
            $this->title = $title;
        } else {
            $this->title = _('Ressourcenbaum');
        }
    }

}
