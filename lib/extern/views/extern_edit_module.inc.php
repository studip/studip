<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* extern_edit_module.inc.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern_edit_module
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_edit_module.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

// it's forbidden to use the command "new" with a given config_id
if (Request::option('com') == 'new') {
    $config_id = '';
}

$module = FALSE;
if (Request::option('com') == 'new') {
    foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $key => $type) {
        if ($type['module'] == Request::quoted('mod')) {
            $configurations = ExternConfig::GetAllConfigurations($range_id, $key);
            if (!isset($configurations[$type['module']]) || sizeof($configurations[$type['module']]) < $GLOBALS['EXTERN_MAX_CONFIGURATIONS']) {
                $module = ExternModule::GetInstance($range_id, $type['module'], '', 'NEW');
            }
            else {
                echo MessageBox::error(sprintf(_('Es wurden bereits %s Konfigurationen angelegt. Sie können für dieses Module keine weiteren Konfigurationen anlegen.')
                    , $GLOBALS['EXTERN_MAX_CONFIGURATIONS']));

                echo LinkButton::create("<< " . _("Zurück"), URLHelper::getURL('?list=TRUE'));
                $template = $GLOBALS['template_factory']->open('layouts/base.php');
                $template->content_for_layout = ob_get_clean();
                echo $template->render();
                page_close();
                die;
            }
        }
    }
}
else {
    foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $type) {
        if ($type["module"] == $mod) {
            $module = ExternModule::GetInstance($range_id, $mod, $config_id);
        }
    }
}

if (!$module)
    die("Unknown module type");

$element_command = FALSE;
$edit = Request::option('edit');
if ($edit) {
    $element_commands = ['show', 'hide', 'move_left', 'move_right', 'show_group', 'hide_group', 'do_search_x'];
    foreach ($element_commands as $element_command) {
        $element_command_form = $edit . "_" . $element_command;
        if ($_POST[$element_command_form]) {
            if ($element_command == 'show_group') {
                $pos = $_POST[$element_command_form];
            } else if (is_array($_POST[$element_command_form])) {
                $pos_tmp = array_keys($_POST[$element_command_form]);
                $pos = $pos_tmp[0];
            }
            $module->executeCommand($edit, $element_command, $pos);
        }
    }
}

$elements = $module->getAllElements();

// the first parameter of printOutEdit() has to be an array, because it is
// possible to open more than one element form
$edit_open = "";

foreach ($elements as $element) {
    if ($edit == $element->getName()) {
        $edit_open = ["$edit" => (Request::option('com') != 'close')];
    }
}
if (Request::option('com') == 'new' || Request::option('com') == 'edit' || Request::option('com') == 'open' || Request::option('com') == 'close') {
    $module->printoutEdit($edit_open, $_POST, "", $edit);
}

if (Request::option('com') == 'store') {

    $faulty_values = $module->checkFormValues($edit);
    $fault = FALSE;
    foreach ($faulty_values as $faulty) {
        if (in_array(TRUE, $faulty)) {
            echo MessageBox::info(_("Bitte korrigieren Sie die mit * gekennzeichneten Werte!"));
            $module->printoutEdit($edit_open, $_POST,
                    $faulty_values, $edit);
            $fault = TRUE;
            break;
        }
    }
    if (!$fault) {
        // This is the right place to trigger some functions by special
        // POST_VARS-values. At the moment there is only one: If the name of the
        // configuration was changed, setup the extern_config table.
        if ($edit == "Main" && $_POST["Main_name"] != $module->config->config_name) {
            if (!ExternConfig::ChangeName($module->config->range_id, $module->getType(), $module->config->getId(),
                    $module->config->config_name, $_POST["Main_name"])) {
                PageLayout::postError(_('Der Konfigurationsname wurde bereits für eine Konfiguration dieses Moduls vergeben. Bitte geben Sie einen anderen Namen ein.'));
                $module->printoutEdit($edit_open, "$_POST", "", $edit);
            }
            $module->store($edit, $_POST);
            PageLayout::postSuccess(_('Die eingegebenen Werte wurden übernommen und der Name der Konfiguration geändert.'));
            $module->printoutEdit($edit_open, "", "", $edit);
        } else {
            $module->store($edit, $_POST);
            PageLayout::postSuccess(_('Die eingegebenen Werte wurden übernommen.'));
            $module->printoutEdit($edit_open, "", "", $edit);
        }
    }
}


if (!$edit_open[$edit]) {
    echo  LinkButton::create("<< " . _("Zurück"), URLHelper::getURL('?list=TRUE'));;
}

Helpbar::get()->addPlainText(_('Information'), _('Um die Werte eines einzelnen Elements zu ändern, klicken Sie bitte den "Übernehmen"-Button innerhalb des jeweiligen Elements.'));

// the type of this module is not Global
if ($module->getType() != 0) {
    $url = sprintf("%sextern.php?module=%s&range_id=%s&preview=1&config_id=%s",
                   $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'],
                   $module->getName(),
                   $module->config->range_id,
                   $module->config->getId()
    );
    if ($global_config = ExternConfig::GetGlobalConfiguration($module->config->range_id)) {
        $url .= "&global_id=$global_config";
    }

    $actions = new ActionsWidget();
    $actions->addLink(_('Vorschau'), $url, Icon::create('question-circle', 'clickable', ['title' => _('Vorschau')]), ['target' => '_blank']);
    Sidebar::get()->addWidget($actions);
}

?>
