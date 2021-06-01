<?php
# Lifter002: DONE - no html output in this file
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: DONE - no html output in this file
/**
* ModulesNotification.class.php
*
* check for modules (global and local for institutes and Veranstaltungen), read and write
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      core
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Modules.class.php
// Checks fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen), Schreib-/Lesezugriff
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

require_once 'lib/meine_seminare_func.inc.php';

class ModulesNotification
{

    public $registered_notification_modules = [];
    public $subject;

    function __construct () {
        foreach (MyRealmModel::getDefaultModules() as $id => $module) {
            if (!is_object($module)) continue;
            $this->registered_notification_modules[$id] = [
                'icon' => $module->getMetadata()['icon'],
                'name' => $module->getMetadata()['displayname'] ?: $module->getPluginName()
            ];
            if ($module instanceof CoreOverview) {
                $this->registered_notification_modules[$id]['name'] = _("Ankündigungen");
                $this->registered_notification_modules[$id]['icon'] = Icon::create('news');
            }
            if (!is_object($this->registered_notification_modules[$id]['icon'])) {
                $this->registered_notification_modules[$id]['icon'] = Icon::create($this->registered_notification_modules[$id]['icon']);
            }
        }
        $this->registered_notification_modules[-1] =
            [
                'name' => _("Umfragen und Tests"),
                'icon' => Icon::create('vote')
            ];
        $this->registered_notification_modules[0] =
            [
                'name' => _("Grunddaten der Veranstaltung"),
                'icon' => Icon::create('seminar')
            ];

        $this->subject = _("Stud.IP Benachrichtigung");
    }




    // only range = 'sem' is implemented
    function getAllNotifications ($user_id = NULL) {

        if (is_null($user_id)) {
            $user_id = $GLOBALS['user']->id;
        }

        $my_sem = [];
        $query = "SELECT s.Seminar_id, s.Name, s.chdate, s.start_time, IFNULL(visitdate, :threshold) AS visitdate "
               . "FROM seminar_user_notifications su "
               . "LEFT JOIN seminare s USING (Seminar_id) "
               . "LEFT JOIN object_user_visits ouv ON (ouv.object_id = su.Seminar_id AND ouv.user_id = :user_id AND ouv.plugin_id = 0) "
               . "WHERE su.user_id = :user_id";

        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $seminar_id = $row['Seminar_id'];
            $tools = ToolActivation::findbyRange_id($seminar_id);
            $my_sem[$seminar_id] = [
                    'name'       => $row['Name'],
                    'chdate'     => $row['chdate'],
                    'start_time' => $row['start_time'],
                    'tools'    => new SimpleCollection($tools),
                    'visitdate'  => $row['visitdate'],
                    'notification'=> CourseMemberNotification::find([$user_id, $seminar_id]),

            ];
        }
        $visit_data = get_objects_visits(array_keys($my_sem), 'sem', null, $user_id, array_keys($this->registered_notification_modules));

        $news = [];
        foreach ($my_sem as $seminar_id => $s_data) {
            if (!count($s_data->notification)) continue;
            $navigation = MyRealmModel::getAdditionalNavigations($seminar_id, $s_data, null, $user_id, $visit_data);
            $n_data = [];
            foreach ($this->registered_notification_modules as $id => $m_data) {
                if (in_array($id, $s_data->notification->notification_data)
                    && isset($navigation[$id])
                    && $navigation[$id]->getImage()
                    && $navigation[$id]->getImage()->getRole() === Icon::ROLE_ATTENTION
                ) {
                        $data = $this->getPluginText($navigation, $seminar_id, $id);
                        if ($data) {
                            $n_data[] = $data;
                        }
                    }
            }
            if (count($n_data)) {
                $news[$s_data['name']] = $n_data;
            }
        }
        if (count($news)) {
            $auth_plugin = User::find($user_id)->auth_plugin;
            if (!is_a('StudipAuth' . ucfirst($auth_plugin), 'StudipAuthSSO', true)) {
                $auth_plugin = null;
            }
            $template = $GLOBALS['template_factory']->open('mail/notification_html');
            $template->set_attribute('lang', getUserLanguagePath($user_id));
            $template->set_attribute('news', $news);
            $template->set_attribute('sso', $auth_plugin);

            $template_text = $GLOBALS['template_factory']->open('mail/notification_text');
            $template_text->set_attribute('news', $news);
            $template_text->set_attribute('sso', $auth_plugin);
            return ['text' => $template_text->render(), 'html' => $template->render()];;
        } else {
            return FALSE;
        }
    }

    function getPluginText($nav, $seminar_id, $id)
    {
        $base_url = URLHelper::setBaseURL('');
        UrlHelper::setBaseURl($base_url);
        if ($nav instanceof Navigation && $nav->isVisible(true)) {
                $url = 'seminar_main.php?again=yes&auswahl=' . $seminar_id . '&redirect_to=' . strtr($nav->getURL(), '?', '&');
                $icon = $nav->getImage();
                $text = $nav->getTitle();
                if (!$text) {
                    $text = $this->registered_modules[$id]['name'];
                }
                $text .= ' - ' .  $icon->getAttributes()['title'];
                return compact('text', 'url', 'icon', 'seminar_id');
            }
    }

    // only range = 'sem' is implemented
    function getModuleText ($m_name, $range_id, $r_data, $range) {
        global $SEM_CLASS, $SEM_TYPE;
        $text = '';
        $sem_class = $SEM_CLASS[$SEM_TYPE[$r_data['sem_status']]["class"]];
        $slot_mapper = [
                'files' => "documents",
                'elearning' => "elearning_interface"
            ];
        if ($sem_class) {
            $slot = isset($slot_mapper[$m_name]) ? $slot_mapper[$m_name] : $m_name;
            $module = $sem_class->getModule($slot);
            if (is_a($module, "StandardPlugin")) {
                return $this->getPluginText($module, $range_id, $r_data, $m_name);
            }
        }
        switch ($m_name) {
            case 'participants' :
                if (in_array($r_data['status'], words('dozent tutor'))) {
                    if ($r_data['new_accepted_participants'] > 1) {
                        $text = sprintf(_("%s neue vorläufige Teilnehmende, "), $r_data['newparticipants']);
                    } else if ($r_data['new_accepted_participants'] > 0) {
                        $text = _("1 neue Person, ");
                    }
                    if ($r_data['newparticipants'] > 1) {
                        $text = sprintf(_("%s neue Personen:"), $r_data['newparticipants']);
                    } else if ($r_data['newparticipants'] > 0) {
                        $text = _("1 neue Person:");
                    }
                    if ($sem_class['studygroup_mode']) {
                        $redirect = '&redirect_to=dispatch.php/course/studygroup/members/';
                    } else {
                        $redirect = '&redirect_to=dispatch.php/course/members/index';
                    }
                    $icon = Icon::create("persons", "clickable");
                }
                break;
            case 'documents' :
                if ($r_data['neuedokumente'] > 1) {
                    $text = sprintf(_("%s neue Dokumente hochgeladen:"), $r_data['neuedokumente']);
                } else if ($r_data['neuedokumente'] > 0) {
                    $text = _("1 neues Dokument hochgeladen:");
                }
                $redirect = '&redirect_to=dispatch.php/course/files/flat';
                $icon = Icon::create("files", "clickable");
                break;
            case 'schedule' :
                if ($r_data['neuetermine'] > 1) {
                    $text = sprintf(_("%s neue Termine angelegt:"), $r_data['neuetermine']);
                } else if ($r_data['neuetermine'] > 0) {
                    $text = _("1 neuer Termin angelegt:");
                }
                $redirect = '&redirect_to=dispatch.php/course/dates#a';
                $icon = Icon::create("date", "clickable");
                break;
            case 'elearning_interface' :
                if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
                    if ($r_data['neuecontentmodule'] > 1) {
                        $text = sprintf(_("%s neue Content-Module angelegt"), $r_data['neuecontentmodule']);
                    } else if ($r_data['neuecontentmodule'] > 0) {
                        $text = _("1 neues Content-Modul angelegt");
                    }
                    $redirect = "&redirect_to=dispatch.php/course/elearning/show";
                    $icon = Icon::create("learnmodule", "clickable");
                }
                break;
            case 'wiki' :
                if ($r_data['neuewikiseiten'] > 1) {
                    $text = sprintf(_("%s Wikiseiten wurden angelegt oder bearbeitet:"), $r_data['neuewikiseiten']);
                } else if ($r_data['neuewikiseiten'] > 0) {
                    $text = _("1 Wikiseite wurde angelegt oder bearbeitet:");
                }
                $redirect = '&redirect_to=wiki.php&view=listnew';
                $icon = Icon::create("wiki", "clickable");
                break;
            case 'scm' :
                if ($r_data['neuscmcontent']) {
                    $text = sprintf(_("Die Seite \"%s\" wurde neu angelegt oder bearbeitet:"), $r_data['scmtabname']);
                }
                $redirect = '&redirect_to=dispatch.php/course/scm';
                $icon = Icon::create("infopage", "clickable");
                break;
            case 'votes' :
                if (Config::get()->VOTE_ENABLE) {
                    if ($r_data['neuevotes'] > 1) {
                        $text = sprintf(_("%s neue Umfragen oder Evaluationen wurden angelegt:"), $r_data['neuevotes']);
                    } else if ($r_data['neuevotes'] > 0) {
                        $text = _("1 neue Umfrage oder Evaluation wurde angelegt:");
                    }
                }
                $redirect = '#votes';
                $icon = Icon::create("vote", "clickable");
                break;
            case 'news' :
                if ($r_data['neuenews'] > 1) {
                    $text = sprintf(_("%s neue Ankündigungen wurden angelegt:"), $r_data['neuenews']);
                } else if ($r_data['neuenews']) {
                    $text = _("Eine neue Ankündigung wurde angelegt:");
                }
                $redirect = '';
                $icon = Icon::create("news", "clickable");
                break;
            case 'basic_data' :
                if ($r_data['chdate'] > $r_data['visitdate']) {
                    $text = _("Die Grunddaten wurden geändert:");
                }
                $redirect = '&redirect_to=dispatch.php/course/details/';
                $icon = Icon::create("home", "clickable");
                break;
            default :
                $redirect = '';
        }
        if ($range == 'sem' && $text != '') {
            $url = 'seminar_main.php?again=yes&auswahl='.$range_id.$redirect;
            return compact('text', 'url', 'icon', 'range_id');
        }
        return $text;
    }



}
