<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class Admin_SemClassesController extends AuthenticatedController
{
    function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        PageLayout::setHelpKeyword("Admins.SemClasses");
        PageLayout::setTitle(_('Veranstaltungskategorien'));
    }

    public function overview_action()
    {
        Navigation::activateItem("/admin/locations/sem_classes");
        if (count($_POST) && Request::submitted('delete') && Request::get("delete_sem_class")) {
            $sem_class = $GLOBALS['SEM_CLASS'][Request::get("delete_sem_class")];
            if ($sem_class->delete()) {
                PageLayout::postMessage(MessageBox::success(_("Veranstaltungskategorie wurde gelöscht.")));
                $GLOBALS['SEM_CLASS'] = SemClass::refreshClasses();
            }
        }
        if (count($_POST) && Request::get("add_name")) {
            $statement = DBManager::get()->prepare(
                "SELECT 1 FROM sem_classes WHERE name = :name"
            );
            $statement->execute(['name' => Request::get("add_name")]);
            $duplicate = $statement->fetchColumn();
            if ($duplicate) {
                $message = sprintf(_("Es existiert bereits eine Veranstaltungskategorie mit dem Namen \"%s\""),
                                   htmlReady(Request::get("add_name")));
                PageLayout::postMessage(MessageBox::error($message));
                $this->redirect('admin/sem_classes/overview');
            } else {
                $statement = DBManager::get()->prepare(
                    "INSERT INTO sem_classes SET name = :name, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()"
                );
                NotificationCenter::postNotification('SeminarClassDidCreate', Request::get("add_name"), $GLOBALS['user']->id);
                $statement->execute(['name' => Request::get("add_name")]);
                $id = DBManager::get()->lastInsertId();
                if (Request::get("add_like")) {
                    $sem_class = clone $GLOBALS['SEM_CLASS'][Request::get("add_like")];
                    $sem_class->set('name', Request::get("add_name"));
                    $sem_class->set('id', $id);
                    $sem_class->store();
                }
                $this->redirect(URLHelper::getURL($this->url_for('admin/sem_classes/details'), ['id' => $id]));
                PageLayout::postMessage(MessageBox::success(_("Veranstaltungskategorie wurde erstellt.")));
                $GLOBALS['SEM_CLASS'] = SemClass::refreshClasses();
            }
        }
    }

    public function details_action()
    {
        Navigation::activateItem("/admin/locations/sem_classes");

        $plugins = PluginManager::getInstance()->getPlugins("StudipModule");
        $this->sem_class = SemClass::getClasses()[Request::get("id")];
        $modules = [];
        foreach ($this->sem_class->getActivatedModuleObjects() as $plugin) {
            $modules[get_class($plugin)] = [
                'name' => $plugin->getPluginName(),
                'id' => $plugin->getPluginId(),
                'enabled' => $plugin->isEnabled(),
                'activated' => true
            ];
        }
        foreach ($plugins as $plugin) {
            if (!$plugin->isActivatableForContext(new Course)) continue;
            if (isset($modules[get_class($plugin)])) continue;
            $modules[get_class($plugin)] = [
                'name' => $plugin->getPluginName(),
                'id' => $plugin->getPluginId(),
                'enabled' => $plugin->isEnabled(),
                'activated' => false
            ];
        }

        $this->modules = $modules;
        $this->overview_url = $this->url_for("admin/sem_classes/overview");
    }

    public function save_action()
    {
        if (count($_POST) === 0) {
            throw new Exception("Kein Zugriff über GET");
        }
        $sem_class = $GLOBALS['SEM_CLASS'][Request::int("sem_class_id")];
        foreach (Request::getArray("core_module_slots") as $slot => $module) {
            $sem_class->setSlotModule($slot, $module);
        }
        $sem_class->setModules(Request::getArray("modules"));
        $sem_class->set('name', Request::get("sem_class_name"));
        $sem_class->set('description', Request::get("sem_class_description"));
        $sem_class->set('title_dozent', Request::get("title_dozent") ? Request::get("title_dozent") : null);
        $sem_class->set('title_dozent_plural', Request::get("title_dozent_plural") ? Request::get("title_dozent_plural") : null);
        $sem_class->set('title_tutor', Request::get("title_tutor") ? Request::get("title_tutor") : null);
        $sem_class->set('title_tutor_plural', Request::get("title_tutor_plural") ? Request::get("title_tutor_plural") : null);
        $sem_class->set('title_autor', Request::get("title_autor") ? Request::get("title_autor") : null);
        $sem_class->set('title_autor_plural', Request::get("title_autor_plural") ? Request::get("title_autor_plural") : null);
        $sem_class->set('studygroup_mode', Request::int("studygroup_mode"));
        $sem_class->set('only_inst_user', Request::int("only_inst_user"));
        $sem_class->set('default_read_level', Request::int("default_read_level"));
        $sem_class->set('default_write_level', Request::int("default_write_level"));
        $sem_class->set('bereiche', Request::int("bereiche"));
        $sem_class->set('module', Request::int("module"));
        $sem_class->set('show_browse', Request::int("show_browse"));
        $sem_class->set('write_access_nobody', Request::int("write_access_nobody"));
        $sem_class->set('topic_create_autor', Request::int("topic_create_autor"));
        $sem_class->set('visible', Request::int("visible"));
        $sem_class->set('course_creation_forbidden', Request::int("course_creation_forbidden"));
        $sem_class->set('create_description', Request::get("create_description"));
        $sem_class->set('admission_prelim_default', Request::int("admission_prelim_default"));
        $sem_class->set('admission_type_default', Request::int("admission_type_default"));
        $sem_class->set('show_raumzeit', Request::int("show_raumzeit"));
        $sem_class->set('is_group', Request::int("is_group"));
        $sem_class->store();
        if (!count($sem_class->getSemTypes())) {
            $notice = "<br>"._("Beachten Sie, dass es noch keine Veranstaltungstypen gibt!");
        }
        $output = [
            'html' => (string) MessageBox::success(_("Änderungen wurden gespeichert."." ".'<a href="'.URLHelper::getLink("dispatch.php/admin/sem_classes/overview").'">'._("Zurück zur Übersichtsseite.").'</a>').$notice)
        ];
        $this->render_json($output);
    }

    public function add_sem_type_action() {
        if (Request::get('name') && Request::get("sem_class") && count($_POST)) {
            $name = Request::get('name');
            $statement = DBManager::get()->prepare(
                "INSERT INTO sem_types SET name = :name, class = :sem_class, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()"
            );
            $statement->execute([
                'name' => $name,
                'sem_class' => Request::get("sem_class")
            ]);
            NotificationCenter::postNotification('SeminarTypeDidCreate', $name, $GLOBALS['user']->id);
            $id = DBManager::get()->lastInsertId();
            $GLOBALS['SEM_TYPE'] = SemType::refreshTypes();
            $this->sem_type = $GLOBALS['SEM_TYPE'][$id];

            $this->render_template(
                "admin/sem_classes/_sem_type.php"
            );
        }
    }

    public function rename_sem_type_action() {
        $sem_type = $GLOBALS['SEM_TYPE'][Request::get("sem_type")];
        if ($sem_type) {
            $sem_type->set('name', Request::get("name"));
            $sem_type->store();
        }
        $this->render_nothing();
    }

    public function delete_sem_type_action() {
        if (count($_POST)) {
            $sem_type = $GLOBALS['SEM_TYPE'][Request::int("sem_type")];
            if (!$sem_type->delete()) {
                throw new Exception("Could not delete sem_type because it' still in use.");
            }
        }
        $this->render_nothing();
    }

}
