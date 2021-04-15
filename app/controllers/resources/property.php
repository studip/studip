<?php

/**
 * property.php - contains Resources_PropertyController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018-2019
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * Resources_PropertyController contains actions for resource properties.
 */
class Resources_PropertyController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->current_user = User::findCurrent();

        if (!ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'admin'
        )) {
            throw new AccessDeniedException();
        }
    }

    public function add_action()
    {
        $this->defined_types = ResourcePropertyDefinition::getDefinedTypes();

        $this->write_permission_level = 'autor';
        $this->type = 'bool';

        $property = new ResourcePropertyDefinition();
        $this->description = $property->description;
        $this->display_name = $property->display_name;

        $this->show_form = true;
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->name = Request::get('name');
            $this->description = Request::i18n('description');
            $this->type = Request::get('type');
            $this->write_permission_level = Request::get('write_permission_level');
            $this->searchable = Request::get('searchable');
            $this->options = Request::get('options');
            $this->display_name = Request::i18n('display_name');
            $this->range_search = Request::get('range_search');
            $this->info_label = Request::get('info_label');

            if (!$this->name) {
                PageLayout::postError(
                    _('Es wurde kein Name angegeben!')
                );
                return;
            }
            if (!in_array($this->type, $this->defined_types)) {
                PageLayout::postError(
                    _('Der angegebene Typ ist ungültig!')
                );
                return;
            }

            if (!in_array($this->write_permission_level, ['user', 'autor', 'tutor', 'admin', 'admin-global'])) {
                PageLayout::postError(
                    _('Die angegebene Rechtestufe ist ungültig!')
                );
                return;
            }

            $property->name = $this->name;
            $property->description = $this->description;
            $property->type = $this->type;
            $property->searchable = ($this->searchable ? '1' : '0');
            $property->options = $this->options;
            $property->display_name = $this->display_name;
            $property->range_search = (
                $this->range_search
                ? '1'
                : '0'
            );
            $property->write_permission_level = $this->write_permission_level;
            $property->info_label = (
                $this->info_label
                ? '1'
                : '0'
            );

            if ($property->store()) {
                $this->show_form = false;
                PageLayout::postSuccess(
                    _('Die Eigenschaft wurde gespeichert!')
                );
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern der Eigenschaft!')
                );
            }
        }
    }

    public function edit_action($property_id = null)
    {
        $this->property = ResourcePropertyDefinition::find($property_id);
        if (!$this->property) {
            PageLayout::postError(
                _('Die gewählte Eigenschaft wurde nicht gefunden!')
            );
            return;
        }

        $this->defined_types = ResourcePropertyDefinition::getDefinedTypes();

        $this->show_form = true;
        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->name = Request::get('name');
            $this->description = Request::i18n('description');
            $this->type = Request::get('type');
            $this->write_permission_level = Request::get('write_permission_level');
            $this->searchable = Request::get('searchable');
            $this->options = Request::get('options');
            $this->display_name = Request::i18n('display_name');
            $this->range_search = Request::get('range_search');
            $this->info_label = Request::get('info_label');

            if (!$this->property->system) {
                //For non-system properties we must check the following fields
                //since they are editable for non-system properties.
                if (!$this->name) {
                    PageLayout::postError(
                        _('Es wurde kein Name angegeben!')
                    );
                    return;
                }
                if (!in_array($this->type, $this->defined_types)) {
                    PageLayout::postError(
                        _('Der angegebene Typ ist ungültig!')
                    );
                    return;
                }
                if (!in_array($this->write_permission_level, ['user', 'autor', 'tutor', 'admin', 'admin-global'])) {
                    PageLayout::postError(
                        _('Die angegebene Rechtestufe ist ungültig!')
                    );
                    return;
                }
            }

            $this->property->description = $this->description;
            $this->property->display_name = $this->display_name;
            $this->property->info_label = $this->info_label ? '1' : '0';

            if (!$this->property->system) {
                //The following fields may only be edited
                //if the property is not a system property:
                $this->property->name = $this->name;
                $this->property->type = $this->type;
                $this->property->searchable = ($this->searchable ? '1' : '0');
                $this->property->options = $this->options;
                $this->property->range_search = (
                    $this->range_search
                    ? '1'
                    : '0'
                );
                $this->property->write_permission_level = $this->write_permission_level;

            }

            $success = $this->property->store();

            if ($success) {
                PageLayout::postSuccess(
                    _('Die Eigenschaft wurde gespeichert!')
                );
            } elseif ($success === false) {
                PageLayout::postError(
                    _('Fehler beim Speichern der Eigenschaft!')
                );
            }
            $this->show_form = !$success;
        }

        $this->name = $this->property->name;
        $this->description = $this->property->description;
        $this->type = $this->property->type;
        $this->write_permission_level = $this->property->write_permission_level;
        $this->searchable = $this->property->searchable;
        $this->options = $this->property->options;
        $this->display_name = $this->property->display_name;
        $this->range_search = $this->property->range_search;
        $this->info_label = $this->property->info_label;
    }

    public function delete_action($property_id = null)
    {
        $this->property = ResourcePropertyDefinition::find($property_id);
        if (!$this->property) {
            PageLayout::postError(
                _('Die gewählte Eigenschaft wurde nicht gefunden!')
            );
            $this->redirect(URLHelper::getURL('dispatch.php/resources/admin/properties'));
            return;
        }

        CSRFProtection::verifyUnsafeRequest();

        $property_name = $this->property->name;

        if ($this->property->delete()) {
            PageLayout::postSuccess(
                sprintf(
                    _('Die Eigenschaft "%s" wurde gelöscht!'),
                    htmlReady($property_name)
                )
            );
        } else {
            PageLayout::postError(
                sprintf(
                    _('Fehler beim Löschen der Eigenschaft "%s"!'),
                    htmlReady($property_name)
                )
            );
        }
        $this->redirect(URLHelper::getURL('dispatch.php/resources/admin/properties'));
    }
}
