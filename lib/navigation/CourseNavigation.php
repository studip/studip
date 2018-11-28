<?php
# Lifter010: TODO
/*
 * CourseNavigation.php - navigation for course / institute area
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class CourseNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        global $user, $perm;

        // check if logged in
        if (is_object($user) && $user->id != 'nobody') {
            $coursetext = _('Veranstaltungen');
            $courseinfo = _('Meine Veranstaltungen & Einrichtungen');
            $courselink = 'dispatch.php/my_courses';
        } else {
            $coursetext = _('Freie');
            $courseinfo = _('Freie Veranstaltungen');
            $courselink = 'dispatch.php/public_courses';
        }

        parent::__construct($coursetext, $courselink);

        if (is_object($user)) {
            $this->setImage(Icon::create('seminar', 'navigation', ["title" => $courseinfo]));
        }
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $SEM_CLASS, $SEM_TYPE, $user;

        parent::initSubNavigation();

        // list of used modules
        $Modules = new Modules();
        $modules = $Modules->getLocalModules(Context::getId(), Context::getClass(), false, Context::getArtNum());
        if (Context::isCourse()) {
            $sem_class = $SEM_CLASS[$SEM_TYPE[Context::getArtNum()]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $sem_class = SemClass::getDefaultInstituteClass(Context::getArtNum());
        }

        // general information
        if (($modules['overview'] || $sem_class->isSlotMandatory("overview"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("overview"))) {
            foreach ($sem_class->getNavigationForSlot("overview") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // admin area
        if (($modules['admin'] || $sem_class->isSlotMandatory("admin"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("admin"))) {
            foreach ($sem_class->getNavigationForSlot("admin") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // forum
        if (($modules['forum'] || $sem_class->isSlotMandatory("forum"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("forum"))) {
            foreach ($sem_class->getNavigationForSlot("forum") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
        }
            }
        }

        // participants
        if ($user->id != 'nobody') {
            if ($modules['personal']) {
                $navigation = new Navigation(_('Personal'));
                $navigation->setImage(Icon::create('persons', 'info_alt'));
                $navigation->setActiveImage(Icon::create('persons', 'info'));
                $navigation->addSubNavigation('view', new Navigation(_('MitarbeiterInnen'), 'dispatch.php/institute/members'));

                if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId()) && $GLOBALS['perm']->have_perm('admin')) {
                    $navigation->addSubNavigation('edit_groups', new Navigation(_('Funktionen / Gruppen verwalten'), 'dispatch.php/admin/statusgroups'));
                }

                $this->addSubNavigation('faculty', $navigation);
            }
            if (($modules['participants'] || $sem_class->isSlotMandatory("participants"))
                    && $sem_class->isModuleAllowed($sem_class->getSlotModule("participants"))) {
                foreach ($sem_class->getNavigationForSlot("participants") as $nav_name => $navigation) {
                    if ($nav_name && is_a($navigation, "Navigation")) {
                        $this->addSubNavigation($nav_name, $navigation);
                    }
                }
            }
        }

        // files
        if (($modules['documents'] || $sem_class->isSlotMandatory("documents"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("documents"))) {
            foreach ($sem_class->getNavigationForSlot("documents") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }


        // schedule
        if (($modules['schedule'] || $sem_class->isSlotMandatory("schedule"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("schedule"))) {
            foreach ($sem_class->getNavigationForSlot("schedule") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // information page
        if (($modules['scm'] || $sem_class->isSlotMandatory("scm"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("scm"))) {
            foreach ($sem_class->getNavigationForSlot("scm") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // literature
        if (($modules['literature'] || $sem_class->isSlotMandatory("literature"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("literature"))) {
            foreach ($sem_class->getNavigationForSlot("literature") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // wiki
        if (($modules['wiki'] || $sem_class->isSlotMandatory("wiki"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("wiki"))) {
            foreach ($sem_class->getNavigationForSlot("wiki") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
            }
            }
        }

        // resources
        if (($modules['resources'] || $sem_class->isSlotMandatory("resources"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("resources"))) {
            foreach ($sem_class->getNavigationForSlot("resources") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // calendar
        if (($modules['calendar'] || $sem_class->isSlotMandatory("calendar"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("calendar"))) {
            foreach ($sem_class->getNavigationForSlot("calendar") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        // content modules
        if (($modules['elearning_interface'] || $sem_class->isSlotMandatory("elearning_interface"))
                && $sem_class->isModuleAllowed($sem_class->getSlotModule("elearning_interface"))) {
            foreach ($sem_class->getNavigationForSlot("elearning_interface") as $nav_name => $navigation) {
                if ($nav_name && is_a($navigation, "Navigation")) {
                    $this->addSubNavigation($nav_name, $navigation);
                }
            }
        }

        //plugins
        $standard_plugins = PluginManager::getInstance()->getPlugins("StandardPlugin", Context::getId());
        foreach ($standard_plugins as $plugin) {
            if (!$sem_class->isSlotModule(get_class($plugin))) {
                foreach ($sem_class->getNavigationForSlot(get_class($plugin)) as $nav_name => $navigation) {
                    if ($nav_name && is_a($navigation, "Navigation")) {
                        $this->addSubNavigation($nav_name, $navigation);
                    }
                }
            }
        }

    }

}
