<?php
namespace RESTAPI\Routes;

/**
 * This file contains API routes related to ResourceProperty objects.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ResourceProperties extends \RESTAPI\RouteMap
{
    /**
     * Returns all resource property definitions.
     *
     * @get /resources/properties
     */
    public function getAllResourcePropertyDefinitions()
    {
        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $properties = \ResourcePropertyDefinition::findBySql('TRUE ORDER BY name ASC');

        $result = [];

        if ($properties) {
            foreach ($properties as $p) {
                $result[] = $p->toRawArray();
            }
        }

        return $result;
    }


    /**
     * Creates a new resource property definition.
     *
     * @post /resources/add_property
     */
    public function addResourcePropertyDefinition()
    {
        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        $defined_types = \ResourcePropertyDefinition::getDefinedTypes();

        $name = \Request::get('name');
        $description = \Request::i18n('description');
        $type = \Request::get('type');
        $write_permission_level = \Request::get('write_permission_level');
        $options = \Request::get('options');
        $range_search = \Request::get('range_search');

        if (!$name) {
            $this->halt(
                400,
                'The field \'name\' must not be empty!'
            );
            return;
        }
        if (!in_array($type, $defined_types)) {
            $this->halt(
                400,
                'Invalid property type specified!'
            );
            return;
        }
        if (!in_array($write_permission_level, ['user', 'autor', 'tutor', 'admin'])) {
            $this->halt(
                400,
                'Invalid permission level in field \'write_permission_level\'!'
            );
            return;
        }

        $property = new \ResourcePropertyDefinition();
        $property->name = $name;
        $property->description = $description;
        $property->type = $type;
        if ($options) {
            $property->options = $options;
        } else {
            $property->options = '';
        }
        $property->range_search = (
            $range_search
            ? '1'
            : '0'
        );
        $property->write_permission_level = $write_permission_level;

        if (!$property->store()) {
            $this->halt(
                500,
                'Error while saving the property!'
            );
        }
        return $property->toRawArray();
    }


    /**
     * Get a resource property definition object.
     *
     * @get /resources/property/:property_id
     */
    public function getResourcePropertyDefinition($property_id)
    {
        $property = \ResourcePropertyDefinition::find($property_id);
        if (!$property) {
            $this->notFound('ResourcePropertyDefinition object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        return $property->toRawArray();
    }


    /**
     * Modifies a resource property definition.
     *
     * @put /resources/property/:property_id
     */
    public function editResourcePropertyDefinition($property_id)
    {
        $property = \ResourcePropertyDefinition::find($property_id);
        if (!$property) {
            $this->notFound('ResourcePropertyDefinition object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            $this->halt(403);
            return;
        }

        if ($property->system) {
            $this->halt(
                403,
                'System properties must not be edited!'
            );
            return;
        }

        $name = $this->data['name'];
        $description = $this->data['description'];
        $type = $this->data['type'];
        $write_permission_level = $this->data['write_permission_level'];
        $options = $this->data['options'];
        $range_search = $this->data['range_search'];

        if ($name) {
            $property->name = $name;
        }

        if ($description) {
            $property->description = $description;
        }

        if ($type) {
            if (!in_array($type, $defined_types)) {
                $this->halt(
                    400,
                    'Invalid property type specified!'
                );
                return;
            }
            $property->type = $type;
        }

        if ($write_permission_level) {
            if (!in_array($write_permission_level, ['user', 'autor', 'tutor', 'admin'])) {
                $this->halt(
                    400,
                    'Invalid permission level in field \'write_permission_level\'!'
                );
                return;
            }
            $property->write_permission_level = $write_permission_level;
        }

        if ($options) {
            $property->options = $options;
        }

        if ($range_search) {
            $property->range_search = $range_search;
        }

        if ($property->isDirty()) {
            if ($property->store()) {
                return $property->toRawArray();
            } else {
                $this->halt(
                    500,
                    'Error while saving the property!'
                );
                return;
            }
        }

        return $property->toRawArray();
    }


    /**
     * Deletes a resource property definition object.
     *
     * @delete /resources/property/:property_id
     */
    public function deleteResourcePropertyDefinition($property_id)
    {
        $property = \ResourcePropertyDefinition::find($property_id);
        if (!$property) {
            $this->notFound('ResourcePropertyDefinition object not found!');
        }

        if (!\ResourceManager::userHasGlobalPermission(\User::findCurrent(), 'admin')) {
            $this->halt(403);
            return;
        }

        //Check if the property is in use:

        if ($property->isInUse()) {
            $this->halt(
                403,
                'The property is in use and can therefore not be deleted!'
            );
            return;
        }

        if ($property->delete()) {
            return "OK";
        } else {
            $this->halt(
                500,
                'Error while deleting resource property definition!'
            );
        }
    }
}
