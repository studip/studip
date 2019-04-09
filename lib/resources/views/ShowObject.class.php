<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ShowObject.class.php
* 
* shows an object
* 
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       EditResourceData.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowObject.class.php
// stellt ein Ressourcen Objekt da
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

require_once 'lib/resources/views/ShowList.class.php';
require_once 'lib/resources/views/ResourcesBrowse.class.php';

/*****************************************************************************
showObject, Darstellung der Eigenschaften eines Objekts
/*****************************************************************************/

class ShowObject {
    var $resObject;     //Das Oject an dem gearbeitet wird
    
    //Konstruktor
    function __construct($resource_id)
    {
        $this->resObject = ResourceObject::Factory($resource_id);

        $this->list = new ShowList;
        $this->list->setRecurseLevels(0);
        $this->list->setViewHiearchyLevels(TRUE);
        $this->list->setSimpleList(TRUE);
    }

    private function selectProperties()
    {
        $query = "SELECT rp.name, rp.description, rp.type, rp.options, rp.system, rp.property_id, rop.state
                  FROM resources_properties AS rp
                  LEFT JOIN resources_categories_properties AS rcp USING (property_id)
                  LEFT JOIN resources_objects AS ro USING (category_id)
                  LEFT JOIN resources_objects_properties AS rop USING (resource_id, property_id)
                  WHERE ro.resource_id = ?
                  ORDER BY rp.name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->resObject->getId()]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function showProperties()
    {
        $template = $GLOBALS['template_factory']->open('resources/show_object');
        $template->resObject  = $this->resObject;
        $template->children   = $this->resObject->isParent();
        $template->properties = $this->selectProperties();
        $template->list       = $this->list;
        echo $template->render();
    }   
}
