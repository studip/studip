<?php

/**
 * category.php - contains Resources_CategoryController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017
 * @category    Stud.IP
 * @since       4.1
 */


/**
 * Resources_CategoryController contains actions for resource categories.
 */
class Resources_CategoryController extends AuthenticatedController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
    }

    public function index_action($category_id = '')
    {
        $this->category = ResourceCategory::find($category_id);
    }

    public function details_action(ResourceCategory $category)
    {
        PageLayout::setTitle(_('Details anzeigen'));
    }

    public function add_action()
    {
        //These three variables are needed in the _add_edit_form template:
        $this->mode = 'add';
        $this->previously_set_properties = [];
        $this->show_form = false;

        $this->class_names = ResourceManager::getAllResourceClassNames();
        $this->available_properties = ResourcePropertyDefinition::findBySql(
            'TRUE ORDER BY name ASC'
        );
        //Load the properties:
        if ($this->category->property_links) {
            foreach ($this->category->property_links as $link) {
                //We want to make sure that only properties that are
                //defined are displayed.
                if ($link->definition) {
                    $this->previously_set_properties[$link->definition->id] = [
                        'id' => $link->definition->id,
                        'name' => $link->definition->__toString(),
                        'system' => $link->system,
                        'requestable' => $link->requestable,
                        'protected' => $link->protected
                    ];
                }
            }
        }

        $this->show_form = true;

        if (Request::submitted('confirmed')) {
            CSRFProtection::verifyUnsafeRequest();

            //Process submitted form:
            $this->name = Request::get('name');
            $this->description = Request::get('description');
            $this->class_name = Request::get('class_name');
            $this->iconnr = Request::int('iconnr');

            $set_properties = Request::getArray('prop');
            $properties_requestable = Request::getArray('prop_requestable');
            $properties_protected = Request::getArray('prop_protected');

            $this->set_properties = [];
            foreach (array_keys($set_properties) as $key) {
                $this->set_properties[$key] = [
                    'id' => $key,
                    'requestable' => $properties_requestable[$key],
                    'protected' => $properties_protected[$key]
                ];
            }

            //validation:
            if (!$this->name) {
                PageLayout::postError(
                    _('Der Name der Kategorie ist leer!')
                );
                return;
            }

            if (!is_a($this->class_name, 'Resource', true)) {
                PageLayout::postError(
                    _('Es wurde keine gültige Ressourcen-Datenklasse ausgewählt!')
                );
                return;
            }

            if ($this->category->system) {
                //Special validation rules for system categories:
                if ($this->class_name != $this->category->class_name) {
                    PageLayout::postError(
                        _('Der Klassenname darf bei Systemkategorien nicht geändert werden!')
                    );
                    return;
                }

                //Check if one of the system properties has been deleted:
                $system_properties = ResourcePropertyDefinition::findBySql(
                    "INNER JOIN resource_category_properties rcp
                    USING (property_id)
                    WHERE category_id = :category_id
                    AND rcp.`system` = '1'",
                    [
                        'category_id' => $this->category->id
                    ]
                );
                if ($system_properties) {
                    $removed_system_property_names = [];
                    foreach ($system_properties as $property) {
                        if (!in_array($property->id, array_keys($set_properties))) {
                            $removed_system_property_names[] = $property->name;
                        }
                    }

                    if ($removed_system_property_names) {
                        asort($removed_system_property_names);
                        PageLayout::postError(
                            _('Die folgenden Systemeigenschaften sind zwingend erforderlich und können nicht entfernt werden:'),
                            $removed_system_property_names
                        );
                        return;
                    }
                }
            }

            $this->category = null;
            switch ($this->class_name) {
                case 'Location':
                    $this->category = ResourceManager::createLocationCategory(
                        $this->name,
                        $this->description
                    );
                    break;
                case 'Building':
                    $this->category = ResourceManager::createBuildingCategory(
                        $this->name,
                        $this->description
                    );
                    break;
                case 'Room':
                    $this->category = ResourceManager::createRoomCategory(
                        $this->name,
                        $this->description
                    );
                    break;
                default:
                    $this->category = ResourceManager::createCategory(
                        $this->name,
                        $this->description,
                        $this->class_name,
                        false,
                        $this->iconnr
                    );
            }
            if ($this->category instanceof ResourceCategory) {
                //Now we store all optional properties:
                foreach ($this->set_properties as $set_property) {
                    $property = ResourcePropertyDefinition::find(
                        $set_property['id']
                    );
                    if ($property) {
                        $this->category->addProperty(
                            $property->name,
                            $property->type,
                            $set_property['requestable'],
                            $set_property['protected']
                        );
                    }
                }
                $this->show_form = false;
                PageLayout::postSuccess(_('Die Kategorie wurde gespeichert!'));
            } else {
                PageLayout::postError(_('Fehler beim Speichern der Kategorie!'));
            }
        }
    }

    public function edit_action($category_id = null)
    {
        //These three variables are needed in the _add_edit_form template:
        $this->mode = 'edit';
        $this->previously_set_properties = [];
        $this->show_form = false;

        $this->category = ResourceCategory::find($category_id);
        if (!$this->category) {
            PageLayout::postError(
                _('Die angegebene Kategorie wurde nicht gefunden!')
            );
            return;
        }

        $this->class_names = ResourceManager::getAllResourceClassNames();
        $this->available_properties = ResourcePropertyDefinition::findBySql(
            'TRUE ORDER BY name ASC'
        );
        //Load the properties:
        if ($this->category->property_links) {
            foreach ($this->category->property_links as $link) {
                //We want to make sure that only properties that are
                //defined are displayed.
                if ($link->definition) {
                    $this->previously_set_properties[$link->definition->id] = [
                        'id' => $link->definition->id,
                        'name' => $link->definition->__toString(),
                        'system' => $link->system,
                        'requestable' => $link->requestable,
                        'protected' => $link->protected
                    ];
                }
            }
        }

        $this->show_form = true;

        if (Request::submitted('confirmed')) {
            CSRFProtection::verifyUnsafeRequest();
            //Process submitted form:
            $this->name = Request::get('name');
            $this->description = Request::get('description');
            $this->class_name = Request::get('class_name');
            $this->iconnr = Request::int('iconnr');

            $set_properties = Request::getArray('prop');
            $properties_requestable = Request::getArray('prop_requestable');
            $properties_protected = Request::getArray('prop_protected');

            $this->set_properties = [];
            foreach (array_keys($set_properties) as $key) {
                $this->set_properties[$key] = [
                    'id' => $key,
                    'requestable' => $properties_requestable[$key],
                    'protected' => $properties_protected[$key]
                ];
            }

            //validation:
            if (!$this->name) {
                PageLayout::postError(
                    _('Der Name der Kategorie ist leer!')
                );
                return;
            }

            if (!is_a($this->class_name, 'Resource', true)) {
                PageLayout::postError(
                    _('Es wurde keine gültige Ressourcen-Datenklasse ausgewählt!')
                );
                return;
            }

            if ($this->category->system) {
                //Special validation rules for system categories:
                if ($this->class_name != $this->category->class_name) {
                    PageLayout::postError(
                        _('Der Klassenname darf bei Systemkategorien nicht geändert werden!')
                    );
                    return;
                }

                //Check if one of the system properties has been deleted:
                $system_properties = ResourcePropertyDefinition::findBySql(
                    "INNER JOIN resource_category_properties rcp
                    USING (property_id)
                    WHERE category_id = :category_id
                    AND rcp.`system` = '1'",
                    [
                        'category_id' => $this->category->id
                    ]
                );
                if ($system_properties) {
                    $removed_system_property_names = [];
                    foreach ($system_properties as $property) {
                        if (!in_array($property->id, array_keys($set_properties))) {
                            $removed_system_property_names[] = $property->name;
                        }
                    }

                    if ($removed_system_property_names) {
                        asort($removed_system_property_names);
                        PageLayout::postError(
                            _('Die folgenden Systemeigenschaften sind zwingend erforderlich und können nicht entfernt werden:'),
                            $removed_system_property_names
                        );
                        return;
                    }
                }
            }
            $this->category->name = $this->name;
            $this->category->description = $this->description;
            $this->category->class_name = $this->class_name;
            $this->category->iconnr = $this->iconnr;

            if ($this->category->isDirty()) {
                $successfully_stored = $this->category->store();
            } else {
                $successfully_stored = true;
            }

            if ($successfully_stored) {
                //After we have stored the category we must store
                //the properties or create them, if necessary.
                //First we store all set properties, if they are new
                //or we modify existing properties:
                foreach ($this->set_properties as $set_property) {
                    $property = ResourcePropertyDefinition::find(
                        $set_property['id']
                    );
                    if ($property) {
                        $this->category->addProperty(
                            $property->name,
                            $property->type,
                            $set_property['requestable'],
                            $set_property['protected']
                        );
                    }
                }

                //Now we must delete all properties which have not been
                //set in the form but which may still be in the database:

                if (count($this->set_properties)) {
                    ResourceCategoryProperty::deleteBySql(
                        'category_id = :category_id
                        AND property_id NOT IN ( :set_property_ids )',
                        [
                            'category_id' => $this->category->id,
                            'set_property_ids' => array_keys($this->set_properties)
                        ]
                    );
                } else {
                    ResourceCategoryProperty::deleteBySql(
                        'category_id = :category_id',
                        [
                            'category_id' => $this->category->id
                        ]
                    );
                }
                $this->show_form = false;
                PageLayout::postSuccess(_('Die Kategorie wurde gespeichert!'));
            } else {
                PageLayout::postError(_('Fehler beim Speichern der Kategorie!'));
            }
        }

        //Show form with current data:
        $this->name = $this->category->name;
        $this->description = $this->category->description;
        $this->class_name = $this->category->class_name;
        $this->iconnr = $this->category->iconnr;
    }


    public function delete_action($category_id = null)
    {
        $this->show_form = false;
        $this->alternative_categories = [];
        $this->category_has_resources = false;
        $this->resource_handling = 'reassign';

        $this->category = ResourceCategory::find($category_id);
        if (!$this->category) {
            PageLayout::postError(
                _('Die angegebene Kategorie wurde nicht gefunden!')
            );
            return;
        }

        if ($this->category->system) {
            //The category is a system category.
            //Such categories cannot be deleted!
            PageLayout::postError(
                _('Systemkategorien dürfen nicht gelöscht werden!')
            );
            return;
        }
        $this->alternative_categories = ResourceCategory::findBySql(
            'class_name = :class_name
            AND id <> :this_id
            ORDER BY name ASC',
            [
                'class_name' => $this->category->class_name,
                'this_id' => $this->category->id
            ]
        );
        //Check if there are resources attached to the category:
        $this->category_has_resources = Resource::countByCategory_id($this->category->id) > 0;

        if (!$this->alternative_categories && $this->category_has_resources) {
            PageLayout::postError(
                _('Die Kategorie darf nicht gelöscht werden, da es keine alternative Kategorie für die Ressourcenklasse gibt, der die Ressourcen dieser Kategorie zugewiesen werden könnten!')
            );
            return;
        }

        $this->show_form = true;
        if (Request::submitted('confirmed')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->resource_handling = Request::get('resource_handling');
            $this->new_category_id = Request::get('new_category_id');
            if ($this->resource_handling == 'delete') {
                //All resources from this resource category shall be deleted:
                Resource::deleteBySql(
                    'category_id = :category_id',
                    [
                        'category_id' => $this->category->id
                    ]
                );
            } elseif ($this->resource_handling == 'reassign') {
                //Check if the selected category is a valid one:
                $new_category = ResourceCategory::find($this->new_category_id);
                if (!$new_category instanceof ResourceCategory) {
                    PageLayout::postError(
                        _('Die gewählte Kategorie wurde nicht gefunden!')
                    );
                    return;
                }
                if ($new_category->class_name != $this->category->class_name) {
                    PageLayout::postError(
                        sprintf(
                            _('Die gewählte Kategorie gehört nicht zur Ressourcenklasse %1$s, sondern zur Klasse %2$s!'),
                            $this->category->class_name,
                            $new_category->class_name
                        )
                    );
                    return;
                }
                //Reassign the resources:
                $db = DBManager::get();
                $stmt = $db->prepare(
                    'UPDATE resources SET category_id = :new_category_id
                    WHERE category_id = :old_category_id'
                );
                $stmt->execute(
                    [
                        'new_category_id' => $new_category->id,
                        'old_category_id' => $this->category->id
                    ]
                );
            } elseif ($this->category_has_resources) {
                PageLayout::postError(
                    _('Es wurde keine Behandlung für die Ressourcen ausgewählt, die der zu löschenden Kategorie zugewiesen sind!')
                );
                return;
            }

            if ($this->category->delete()) {
                $this->show_form = false;
                PageLayout::postSuccess(_('Die Kategorie wurde gelöscht!'));
            } else {
                PageLayout::postError(_('Fehler beim Löschen der Kategorie!'));
            }
        }
    }

    public function show_resources_action($category_id = null)
    {
        $this->category = ResourceCategory::find($category_id);
        if (!$this->category) {
            PageLayout::postError(
                _('Die angegebene Kategorie wurde nicht gefunden!')
            );
            return;
        }

        $this->resources = Resource::findBySql(
            'category_id = :category_id
            ORDER BY name, mkdate ASC',
            [
                'category_id' => $this->category->id
            ]
        );
    }
}
