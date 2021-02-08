<?php

/**
 * search.php - contains Resources_SearchController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_SearchController contains search actions for resources.
 */
class Resources_SearchController extends AuthenticatedController
{
    public function rooms_action()
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(
            _('Raumsuche')
        );

        if (Navigation::hasItem('/search/rooms')) {
            Navigation::activateItem('/search/rooms');
        }

        //Build sidebar:
        $sidebar = Sidebar::get();

        $room_search_widget = new RoomSearchWidget(
            URLHelper::getLink(
                'dispatch.php/resources/search/rooms'
            )
        );

        $sidebar->addWidget($room_search_widget);

        $resource_tree_widget = new RoomSearchTreeWidget(
            Location::findAll()
        );
        $search_ressource = explode('_', Request::get('special__building_location'));
        if ($room_search_widget->searchResetRequested() || count($search_ressource) != 2) {
            $resource_tree_widget->setCurrentResourceId();
        } else {
            $resource_tree_widget->setCurrentResourceId($search_ressource[1]);
        }
        $sidebar->addWidget($resource_tree_widget);

        $this->current_user = User::findCurrent();
        if (ResourceManager::userHasGlobalPermission($this->current_user)) {
            $room_clipboard_widget = new RoomClipboardWidget();
            $sidebar->addWidget($room_clipboard_widget);
            $this->clipboard_widget_id = $room_clipboard_widget->getClipboardWidgetId();
        }

        $this->tree_selected_resource = Request::get('tree_selected_resource');

        $this->form_submitted = false;

        if ($this->tree_selected_resource) {
            $resource = Resource::find($this->tree_selected_resource);
            if (!$resource) {
                PageLayout::postError(
                    _('Die gewählte Ressource wurde nicht in der Datenbank gefunden!')
                );
                return;
            }

            $resource = $resource->getDerivedClassInstance();

            if ($resource) {
                $this->redirect(
                    $resource->getActionURL('show')
                );
            }
        } else {
            $this->form_submitted = $room_search_widget->searchRequested();
            $this->rooms = $room_search_widget->getResults();
            $messages = PageLayout::getMessages();
            $this->has_errors = false;
            foreach ($messages as $message) {
                if ($message->class == 'error') {
                    $this->has_errors = true;
                }
                PageLayout::postMessage($message);
            }
        }
    }
}
