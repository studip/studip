<?php
# Lifter010: TODO
/**
 * datafields.php - controller class for the datafields
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */

class Admin_DatafieldsController extends AuthenticatedController
{
    public $user_status = [
        'user'   =>  1,
        'autor'  =>  2,
        'tutor'  =>  4,
        'dozent' =>  8,
        'admin'  => 16,
        'root'   => 32,
    ];

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set navigation
        Navigation::activateItem('/admin/config/datafields');
        PageLayout::setTitle(_('Verwaltung von generischen Datenfeldern'));

        // Set variables used by (almost) all actions
        $this->allclasses   = DataField::getDataClass();
        $this->class_filter = Request::option('class_filter', null);

        $this->createSidebar($action);
    }

    /**
     * Maintenance view for the datafield view
     *
     * @param $class static types for datafields
     */
    public function index_action($class = null)
    {
        if ($this->class_filter) {
            $this->datafields_list = [
                $this->class_filter => DataField::getDataFields($this->class_filter),
            ];
        } else {
            $this->datafields_list = [
                'sem'                 => DataField::getDataFields('sem'),
                'inst'                => DataField::getDataFields('inst'),
                'user'                => DataField::getDataFields('user'),
                'userinstrole'        => DataField::getDataFields('userinstrole'),
                'usersemdata'         => DataField::getDataFields('usersemdata'),
                'roleinstdata'        => DataField::getDataFields('roleinstdata'),
                'moduldeskriptor'     => DataField::getDataFields('moduldeskriptor'),
                'modulteildeskriptor' => DataField::getDataFields('modulteildeskriptor')
            ];
        }

        // set variables for view
        $this->current_class = $class;
        $this->allclass = array_keys($this->allclasses);
    }

    /**
     * Edit a datatyp
     *
     * @param md5 $datafield_id
     */
    public function edit_action($datafield_id)
    {
        PageLayout::setTitle(_('Datenfeld ändern'));

        $datafield = new DataField($datafield_id);
        $datafield_default = clone $datafield;
        $datafield_default->name = _('Standardwert');
        $datafield_default->description = _('Hier können Sie den Standardwert für dieses Datenfeld festlegen.');
        $datafield_default->is_required = 0;
        $datafield_entry = DataFieldEntry::createDataFieldEntry($datafield_default);

        if (Request::submitted('uebernehmen')) {
            if (Request::get('datafield_name')) {
                $datafield->name          = Request::get('datafield_name');
                if ($datafield->object_type === 'moduldeskriptor'
                        || $datafield->object_type === 'modulteildeskriptor') {
                    $object_class = implode(',', Request::getArray('object_class'));
                    $datafield->object_class  = (trim($object_class) && $object_class != 'NULL') ? $object_class : null;
                } else {
                    $datafield->object_class  = array_sum(Request::getArray('object_class')) ?: null;
                }
                $datafield->edit_perms    = Request::get('edit_perms');
                $datafield->view_perms    = Request::get('visibility_perms');
                $datafield->system        = Request::int('system') ?: 0;
                $datafield->priority      = Request::int('priority') ?: 0;
                $datafield->type          = Request::get('datafield_type');
                $datafield->is_required   = Request::int('is_required') ?: 0;
                $datafield->description   = Request::get('description', $datafield->description);
                $datafield->is_userfilter = Request::int('is_userfilter') ?: 0;
                $datafield_entry->setValueFromSubmit(Request::getInstance()->offsetGet('default_value'));
                $datafield->default_value = $datafield_entry->getValue();
                $datafield->store();

                PageLayout::postSuccess(_('Die Änderungen am generischen Datenfeld wurden übernommen.'));
                $this->redirect('admin/datafields/index/' . $datafield->object_type . '#item_'.$datafield_id);
            } else {
                PageLayout::postError(_('Es wurde keine Bezeichnung eingetragen!'));
            }

        }

        // set variables for view
        $this->item         = $datafield;
        $this->datafield_id = $datafield->id;
        $this->type         = $datafield->type;
        $this->datafield_entry = $datafield_entry;
    }

    /**
     * Create a new Datafield
     *
     * @param $type static types for datafields
     */
    public function new_action($type = null)
    {
        PageLayout::setTitle(_('Neues Datenfeld anlegen'));

        if (Request::submitted('anlegen')) {

            if (Request::get('datafield_name')) {
                $datafield = new DataField();
                $datafield->name          = Request::get('datafield_name');
                $datafield->object_type   = $type;
                if ($type === 'moduldeskriptor' || $type === 'modulteildeskriptor') {
                    $object_class = implode(',', Request::getArray('object_class'));
                    $datafield->object_class  = (trim($object_class) && $object_class != 'NULL') ? $object_class : null;
                } else {
                    $datafield->object_class  = array_sum(Request::getArray('object_class')) ?: null;
                }
                $datafield->edit_perms    = Request::get('edit_perms');
                $datafield->view_perms    = Request::get('visibility_perms');
                $datafield->system        = Request::int('system') ?: 0;
                $datafield->priority      = Request::int('priority') ?: 0;
                $datafield->type          = Request::get('datafield_type');
                $datafield->is_userfilter = Request::int('is_userfilter') ?: 0;
                if ($type === 'sem') {
                    $datafield->description = Request::get('description', '');
                    $datafield->is_required = Request::int('is_required') ?: 0;
                } else {
                    $datafield->description = '';
                    $datafield->is_required = Request::int('is_required') ?: 0;
                }
                $datafield->store();

                PageLayout::postSuccess(_('Das neue generische Datenfeld wurde angelegt.'));
                $this->redirect('admin/datafields/index/' . $datafield->object_type . '#item_' . $datafield->id);
                return;
            } else {
                PageLayout::postError(_('Es wurde keine Bezeichnung eingetragen!'));
            }
        }

        $type = $type ?: Request::get('datafield_typ');

        $this->type_name  = $this->allclasses[$type];
        $this->object_typ = $type;

        if (!$this->object_typ) {
            $this->render_action('type_select');
        }

        if (Request::isXhr() && $this->type_name) {
            PageLayout::setTitle(sprintf(_('Einen neuen Datentyp für die Kategorie "%s" erstellen'), $this->type_name));
        }
    }

    /**
     * Delete a datafield
     *
     * @param md5 $datafield_id
     * @param string $name
     */
    public function delete_action($datafield_id)
    {
        $datafield = DataField::find($datafield_id);
        $type = $datafield->object_type;
        $name = $datafield->name;
        if (Request::int('delete') == 1) {
            $datafield->delete();

            PageLayout::postSuccess(_('Das Datenfeld wurde erfolgreich gelöscht!'));
        } elseif (!Request::get('back')) {
            $this->datafield_id = $datafield_id;
            $this->flash['delete'] = compact('datafield_id', 'name');
        }

        $this->redirect('admin/datafields/index/' . $type . '#' . $type);
    }

    /**
     * Configures a datafield
     *
     * @param String $datafield_id Datafield id
     */
    public function config_action($datafield_id)
    {
        $datafield = DataField::find($datafield_id);

        if (Request::get('typeparam')) {
            $datafield->typeparam = Request::get('typeparam');
        }

        if (Request::isPost() && Request::submitted('store')) {
            $datafield->store();

            PageLayout::postSuccess(_('Die Parameter wurden übernommen.'));

            $this->redirect('admin/datafields/index/' . $datafield_id->object_type . '#item_' . $datafield_id);
        }

        $this->struct = $datafield;

        if (Request::submitted('preview')) {
            $this->preview = DataFieldEntry::createDataFieldEntry($datafield);
            $this->render_action('preview');
        }
    }

    /**
     * Creates the sidebar.
     *
     * @param String $action Currently called action
     */
    private function createSidebar($action)
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/admin-sidebar.png');
        $sidebar->setTitle(_('Datenfelder'));

        $actions = new ActionsWidget();
        $actions->addLink(_('Neues Datenfeld anlegen'),
                          $this->url_for('admin/datafields/new/' . $this->class_filter),
                          Icon::create('add', 'clickable'))
                ->asDialog();
        $sidebar->addWidget($actions);

        $filter = new SelectWidget(_('Filter'), $this->url_for('admin/datafields'), 'class_filter');
        $filter->addElement(new SelectElement('', _('alle anzeigen')));
        $filter->setOptions($this->allclasses, $this->class_filter);
        $sidebar->addWidget($filter);
    }
}
