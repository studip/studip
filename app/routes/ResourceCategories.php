<?php
namespace RESTAPI\Routes;

/**
 * This file contains API routes related to ResourceCategory objects.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ResourceCategories extends \RESTAPI\RouteMap
{
    /**
     * Returns all defined resource categories.
     *
     * @get /resources/categories
     */
    public function getAllResourceCategories()
    {
        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $categories = \ResourceCategory::findBySql('TRUE ORDER BY name ASC');

        $result = [];

        if ($categories) {
            foreach ($categories as $c) {
                $result[] = $c->toRawArray();
            }
        }
        return $result;
    }


    /**
     * Get a resource category object.
     *
     * @get /resources/category/:category_id
     */
    public function getResourceCategory($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        return $category->toRawArray();
    }


    /**
     * Creates a resource category object.
     *
     * @post /resources/new_category
     */
    public function addResourceCategory()
    {
        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $this->name = \Request::get('name');
        $this->description = \Request::get('description');
        $this->class_name = \Request::get('class_name');
        $this->iconnr = \Request::int('iconnr');

        $properties_name = \Request::getArray('properties_name');
        $properties_type = \Request::getArray('properties_type');
        $properties_requestable = \Request::getArray('properties_requestable');
        $properties_protected = \Request::getArray('properties_protected');

        $this->set_properties = [];
        foreach ($properties_name as $key => $property_name) {
            $this->set_properties[] = [
                'name' => $property_name,
                'type' => $properties_type[$key],
                'requestable' => $properties_requestable[$key],
                'protected' => $properties_protected[$key]
            ];
        }

        //validation:
        if (!$this->name) {
            $this->halt(
                400,
                _('Der Name der Kategorie ist leer!')
            );
            return;
        }

        if (!is_a($this->class_name, 'Resource', true)) {
            $this->halt(
                400,
                _('Es wurde keine gültige Ressourcen-Datenklasse ausgewählt!')
            );
            return;
        }

        switch ($this->class_name) {
            case 'Location': {
                $this->category = \ResourceManager::createLocationCategory(
                    $this->name,
                    $this->description
                );
                break;
            } case 'Building': {
                $this->category = \ResourceManager::createBuildingCategory(
                    $this->name,
                    $this->description
                );
                break;
            } case 'Room': {
                $this->category = \ResourceManager::createRoomCategory(
                    $this->name,
                    $this->description
                );
                break;
            } default: {
                $this->category = \ResourceManager::createCategory(
                    $this->name,
                    $this->description,
                    $this->class_name,
                    false,
                    $this->iconnr
                );
            }
        }

        $successfully_stored = false;
        if ($this->category->isDirty()) {
            $successfully_stored = $this->category->store();
        } else {
            $successfully_stored = true;
        }

        if ($successfully_stored) {
            //After we have stored the category we must store
            //the properties or create them, if necessary:

            $properties_successfully_stored = false;
            foreach ($this->set_properties as $set_property) {
                $this->category->addProperty(
                    $set_property['name'],
                    $set_property['type'],
                    $set_property['requestable'],
                    $set_property['protected']
                );
            }

            $this->show_form = false;
            return $this->category;
        } else {
            $this->halt(
                500,
                _('Fehler beim Speichern der Kategorie!')
            );
        }
    }


    /**
     * Modifies a resource category.
     *
     * @put /resources/category/:category_id
     */
    public function editResourceCategory($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            $this->halt(403);
            return;
        }

        if ($category->system) {
            $this->halt(403, 'System categories must not be modified!');
            return;
        }

        $name = $this->data['name'];
        $description = $this->data['description'];
        $iconnr = intval($this->data['iconnr']);

        //validation:
        if ($name) {
            $category->name = $name;
        }
        if ($description) {
            $category->description = $description;
        }
        if ($iconnr) {
            $category->iconnr = $iconnr;
        }

        if ($category->isDirty()) {
            if ($category->store()) {
                return $category->toRawArray();
            } else {
                $this->halt(
                    500,
                    'Error while saving the category!'
                );
                return;
            }
        }

        return $category->toRawArray();
    }


    /**
     * Deletes a resource category.
     *
     * @delete /resources/category/:category_id
     */
    public function deleteResourceCategory($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            $this->halt(403);
        }

        if ($category->system) {
            $this->halt(403,'System resource categories must not be deleted!');
            return;
        }

        if ($category->delete()) {
            return 'OK';
        } else {
            $this->halt(
                500,
                'Error while deleting the resource category!'
            );
        }
    }


    /**
     * Get all resource category property objects for a resource category.
     *
     * @get /resources/category/:category_id/properties
     */
    public function getResourceCategoryProperties($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $result = [];
        $properties = \ResourceCategoryProperty::findBySql(
            'INNER JOIN resource_property_definitions rpd
            USING (property_id)
            WHERE category_id = :category_id ORDER BY rpd.name ASC',
            [
                'category_id' => $category->id
            ]
        );

        if ($properties) {
            foreach ($properties as $property) {
                $data = $property->toRawArray();
                $data['name'] = $property->definition->name;
                $data['type'] = $property->definition->type;
                $result[] = $data;
            }
        }

        return $result;
    }


    /**
     * Returns all resources which belong to the specified category.
     * The result set can be limited by the parameters 'offset' and 'limit'.
     * If the parameter 'with_full_name' is set to 1, the resources full name
     * as provided by its responsible class, is added to the result set.
     *
     * @get /resources/category/:category_id/resources
     */
    public function getResourceCategoryResources($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $offset = \Request::int('offset');
        $limit = \Request::int('limit');
        $with_full_name = \Request::get('with_full_name');

        $result = [];

        $sql = 'category_id = :category_id ORDER BY name ASC ';
        $sql_array = ['category_id' => $category->id];

        if ($limit > 0) {
            $sql .= 'limit :limit ';
            $sql_array['limit'] = $limit;
            if ($offset > 0) {
                $sql .= 'offset :offset ';
                $sql_array['offset'] = $offset;
            }
        }

        $resources = \Resource::findBySql($sql, $sql_array);

        if ($resources) {
            foreach ($resources as $r) {
                if ($with_full_name) {
                    $r = $r->getDerivedClassInstance();
                    $data = $r->toRawArray();
                    $data['full_name'] = $r->getFullName();
                    $result[] = $data;
                } else {
                    $result[] = $r->toRawArray();
                }
            }
        }

        return $result;
    }


    /**
     * Creates a resource.
     *
     * @post /resources/category/:category_id/create_resource
     */
    public function createResource($category_id)
    {
        $category = \ResourceCategory::find($category_id);
        if (!$category) {
            $this->notFound('ResourceCategory object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $name = \Request::get('name');
        $description = \Request::get('description');
        $parent_id = \Request::get('parent_id');
        $properties = \Request::getArray('properties');

        if (!$name) {
            $this->halt(
                400,
                'The parameter \'name\' is not set!'
            );
        }

        try {
            $resource = $category->createResource(
                $name,
                $description,
                $parent_id,
                $properties
            );

            return $resource;
        } catch (\Exception $e) {
            $this->halt(
                400,
                $e->getMessage()
            );
        }
    }
}
