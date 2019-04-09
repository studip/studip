<?
# Lifter002: TEST
# Lifter003: TEST
# Lifter007: TODO - needs documentation
# Lifter010: TEST (see included templates)
/**
* EditSettings.class.php
*
* all the forms/views to edit the settings
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       EditSettings.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// EditSettings.class.php
// enthaelt alle Forms/Views zum Bearbeiten der Einstellungen
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

use Studip\Button, Studip\LinkButton;

class EditSettings
{
    private function getDependingResources($category_id)
    {
        $query = "SELECT COUNT(resource_id)
                  FROM resources_objects
                  WHERE category_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$category_id]);
        return $statement->fetchColumn();
    }

    private function getDependingTypes($property_id)
    {
        $query = "SELECT COUNT(category_id)
                  FROM resources_categories_properties
                  WHERE property_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$property_id]);
        return $statement->fetchColumn();
    }

    private function selectTypes() {
        $query = "SELECT category_id, name, iconnr, is_room, system
                  FROM resources_categories
                  ORDER BY name";
        $statement = DBManager::get()->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function selectRootUser()
    {
        $query = "SELECT user_id, perms
                  FROM resources_user_resources
                  WHERE resource_id ='all'";
        $statement = DBManager::get()->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function selectProperties($category_id = null)
    {
        if ($category_id === null) {
            $query = "SELECT property_id, name, type, system, options, info_label
                      FROM resources_properties
                      ORDER BY name";
            $statement = DBManager::get()->query($query);
        } else {
            $query = "SELECT property_id, name, type, rp.system, category_id, requestable, protected, options, info_label
                      FROM resources_categories_properties
                      LEFT JOIN resources_properties AS rp USING (property_id)
                      WHERE category_id = ?
                      ORDER BY name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$category_id]);
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function selectLocks($type)
    {
        $query = "SELECT lock_id, lock_begin, lock_end
                  FROM resources_locks
                  WHERE type = ?
                  ORDER BY lock_begin";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$type]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function showPermsForms()
    {
        $template = $GLOBALS['template_factory']->open('resources/edit_perms');

        if (!Request::submitted('reset_search_root_user')) {
            $template->search_string_search_root_user = Request::get('search_string_search_root_user');
        }
        
        $template->users = $this->selectRootUser();
        $template->resObject = ResourceObject::Factory();
        echo $template->render();
    }

    function showTypesForms()
    {
        $types = $this->selectTypes();
        foreach ($types as $index => $type) {
            $types[$index]['depRes']     = $this->getDependingResources($type['category_id']);
            $types[$index]['properties'] = $this->selectProperties($type['category_id']);
        }

        $template = $GLOBALS['template_factory']->open('resources/edit_types');
        $template->types               = $types;
        $template->properties          = $this->selectProperties();
        $template->created_category_id = $GLOBALS['created_category_id'];
        echo $template->render();
    }

    function showPropertiesForms()
    {
        $properties = $this->selectProperties();
        foreach ($properties as $index => $property) {
            $properties[$index]['depTyp'] = $this->getDependingTypes($property['property_id']);
        }

        $template = $GLOBALS['template_factory']->open('resources/edit_properties');
        $template->properties = $properties;
        echo $template->render();
    }

    function showSettingsForms()
    {
        $template = $GLOBALS['template_factory']->open('resources/edit_settings');
        $template->locks      = [
            'edit'   => $this->selectLocks('edit'),
            'assign' => $this->selectLocks('assign'),
        ];
        echo $template->render();
    }
}
