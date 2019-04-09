<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * elearning.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schroeder <schroeder@data-quest.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class ElearningController extends AuthenticatedController
{
    /**
     * Before filter, set up the page by initializing the session and checking
     * all conditions.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Config::Get()->ELEARNING_INTERFACE_ENABLE ) {
            throw new AccessDeniedException(_('Elearning-Interface ist nicht aktiviert.'));
        } else
            $this->elearning_active = true;

        PageLayout::setHelpKeyword('Basis.Ilias');

        $this->cms_select = Request::quoted('cms_select');
        $GLOBALS['cms_select'] = $this->cms_select;
        $this->cms_list = [];
        if ($_SESSION['elearning_open_close']["type"] != "user") {
            unset($_SESSION['elearning_open_close']);
        }
        $_SESSION['elearning_open_close']["type"] = "user";
        $_SESSION['elearning_open_close']["id"] = $GLOBALS['user']->id;
        if (Request::get('do_open'))
            $_SESSION['elearning_open_close'][Request::get('do_open')] = true;
        elseif (Request::get('do_close'))
            $_SESSION['elearning_open_close'][Request::get('do_close')] = false;

        $this->open_all = Request::get('open_all');
        $this->close_all = Request::get('close_all');
        $this->new_account_cms = Request::get('new_account_cms');
        $this->module_system_type = Request::option('module_system_type');
        $this->module_id = Request::option('module_id');
        $this->module_type = Request::option('module_type');
        $this->anker_target = Request::option('anker_target');

        if (!isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->new_account_cms])) {
            unset($this->new_account_cms);
        }
        if (!isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_select])) {
            unset($this->cms_select);
        }
        if ($this->open_all != "")
            $_SESSION['elearning_open_close']["all open"] = true;
        elseif ($this->close_all != "")
            $_SESSION['elearning_open_close']["all open"] = "";
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/learnmodule-sidebar.png');
        //$this->sidebar->setContextAvatar(CourseAvatar::getAvatar($this->seminar_id));
    }

    /**
     * Displays accounts and elearning modules for active user
     */
    public function my_accounts_action()
    {
        global $connected_cms, $current_module,$messages;
        Navigation::activateItem('/tools/my_elearning');

        PageLayout::setTitle(_("Meine Lernmodule und Benutzer-Accounts"));

        if ($this->new_account_cms != "")
            $this->new_account_form = ELearningUtils::getNewAccountForm($this->new_account_cms);
        foreach($GLOBALS['ELEARNING_INTERFACE_MODULES'] as $cms => $cms_preferences) {
            if (ELearningUtils::isCMSActive($cms)) {
                ELearningUtils::loadClass($cms);
                if ( $cms_preferences["auth_necessary"] == true) {
                    $this->new_module_form[$cms] = ELearningUtils::getNewModuleForm($cms);
                }
                $connection_status = $connected_cms[$cms]->getConnectionStatus($cms);

                foreach ($connection_status as $type => $msg) {
                    if ($msg["error"] != "") {
                        PageLayout::postMessage(MessageBox::error(_("Es traten Probleme bei der Anbindung einzelner Lermodule auf. Bitte wenden Sie sich an Ihren Systemadministrator."), [$cms .': ' . $msg["error"]]));
                        $GLOBALS["ELEARNING_INTERFACE_" . $cms . "_ACTIVE"] = false;
                    }
                }
            }
        }

        $connected_cms = [];
        // prepare cms list
        foreach($GLOBALS['ELEARNING_INTERFACE_MODULES'] as $cms => $cms_preferences) {
            if (ELearningUtils::isCMSActive($cms) AND $cms_preferences["auth_necessary"]) {
                ELearningUtils::loadClass($cms);
                $this->cms_list[$cms] = $cms_preferences;
                $this->cms_list[$cms]['name'] = $connected_cms[$cms]->getName();
                $this->cms_list[$cms]['logo'] = $connected_cms[$cms]->getLogo();
                $this->cms_list[$cms]['modules'] = [];
                if ($this->new_account_cms != $cms)
                    $this->cms_list[$cms]['show_account_form'] = $cms_preferences;
                if ($GLOBALS["module_type_" . $cms] != "")
                    $this->cms_list[$cms]['cms_anker_target'] = true;
                if ($connected_cms[$cms]->user->isConnected())
                    $this->cms_list[$cms]['start_link'] = $connected_cms[$cms]->link->getStartpageLink();

                if ($this->new_account_cms != $cms) {
                    if ($connected_cms[$cms]->user->isConnected()) {
                        $this->cms_list[$cms]['user'] = $connected_cms[$cms]->user->getUsername();
                        $connected_cms[$cms]->soap_client->setCachingStatus(false);
                        $this->user_content_modules = $connected_cms[$cms]->getUserContentModules();
                        $connected_cms[$cms]->soap_client->setCachingStatus(true);
                        if (! ($this->user_content_modules == false)) {
                            foreach ($this->user_content_modules as $key => $connection) {
                                $connected_cms[$cms]->setContentModule($connection, false);
                                $this->cms_list[$cms]['modules'][] = $connected_cms[$cms]->content_module[$current_module]->view->show();
                            }
                        }
                        $this->cms_list[$cms]['new_module_form'] = $this->new_module_form[$cms];
                    }
                } else {
                    $this->cms_list[$cms]['account_form'] = $this->new_account_form;
                }
            }
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');
        $widget = new ActionsWidget();

        if ($GLOBALS['perm']->have_perm('autor') AND count($this->cms_list)) {
            foreach($this->cms_list as $cms_key => $cms_data) {
                if ($connected_cms[$cms_key]->user->isConnected()) {
                    $widget->addLink(
                        sprintf(_('Zur %s Startseite'), $cms_data['name']),
                        URLHelper::getURL($cms_data['start_link']),
                        Icon::create('link-extern', 'clickable'),
                        ['target' => '_blank', 'rel' => 'noopener noreferrer']
                    );
                    $link_count++;
                }
            }
        }
        if ($link_count)
            $sidebar->addWidget($widget);

        // terminate objects
        if (is_array($connected_cms))
            foreach($connected_cms as $system)
                $system->terminate();

       if (is_array($messages)) {
           foreach ($messages as $mtype => $mtext) {
               PageLayout::postMessage(MessageBox::$mtype($mtext));
           }
       }
    }
}
