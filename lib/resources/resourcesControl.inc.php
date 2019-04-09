<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter005: TODO
# Lifter007: TODO
# Lifter010: TODO
/**
 * resourcesControl.php - the controlling body of the resource-management
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @copyright   2003-2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
*/

$view = Request::option('view');
$view_mode = Request::option('view_mode');
$quick_view_mode = Request::option('quick_view_mode');
/*****************************************************************************
Requires & Registers
/*****************************************************************************/


require_once 'lib/resources/views/Msg.class.php';

$_SESSION['resources_data'] = @unserialize($_SESSION['resources_data']);
if (empty($_SESSION['resources_data'])) {
    $temp_semester = SemesterData::getCurrentSemesterData() ?: end(SemesterData::getAllSemesterData());
    $_SESSION['resources_data'] = [
        'view'                     => 'search',
        'view_mode'                => false,
        'sem_schedule_semester_id' => $temp_semester['semester_id'],
        'sem_schedule_timespan'    => 'course_time'
    ];
}

$globalPerm = getGlobalPerms($user->id);
$msg = new Msg;


/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

// if directly editing a request from request list,
// set working_pos and reload for the request
if ($view == "edit_request") {
    if (Request::option('edit')) {
        foreach ($_SESSION['resources_data']['requests_working_on'] as $key => $val) {
            if ($val['request_id'] == Request::option('edit')) {
                $_SESSION['resources_data']['requests_working_pos'] = $key;
                break;
            }
        }
    }

    $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["reload"] = TRUE;
    //hmm, zu früh an dieser Stelle. Notwendig?
    //page_close(NULL);
}

//handle values
include ("lib/resources/lib/evaluate_values.php");
/*****************************************************************************
Navigation aufbauen
/*****************************************************************************/

$resources_nav = Navigation::getItem('/resources');

// Reiter "Uebersicht"
$navigation = new Navigation(_('Übersicht'));
$navigation->addSubNavigation('hierarchy', new Navigation(_('Struktur'), 'resources.php#a', ['view' => 'resources']));

if (get_config('RESOURCES_ENABLE_GROUPING')) {
    $navigation->addSubNavigation('group_schedule_daily', new Navigation(_('Gruppen-Belegungspläne'), 'resources.php', ['view' => 'view_group_schedule_daily']));
    $navigation->addSubNavigation('group_schedule', new Navigation(_('Gruppen-Belegungspläne (Semester)'), 'resources.php', ['view' => 'view_group_schedule']));
}

if (get_config('RESOURCES_ALLOW_CREATE_TOP_LEVEL') || getGlobalPerms($user->id) == 'admin') {
    $navigation->addSubNavigation('create_entry', new Navigation(_('Neue Hierarchieebene erzeugen'), 'resources.php#a', ['view' => 'create_hierarchie']));
}

$resources_nav->addSubNavigation('view', $navigation);

// Reiter "Listen"
if ($_SESSION['resources_data']['list_open']) {
    $navigation = new Navigation(_('Liste'));
    $navigation->addSubNavigation('show', new Navigation(_('Listenausgabe'), 'resources.php#a', ['view' => 'lists']));
    $resources_nav->addSubNavigation('lists', $navigation);
}

// Reiter "Objekt"
if ($_SESSION['resources_data']['actual_object']) {
    $navigation = new Navigation(_('Ressource'));
    $navigation->addSubNavigation('view_details', new Navigation(_('Eigenschaften'), 'resources.php', ['view' => 'view_details']));

    if ($ActualObjectPerms->havePerm('admin')) {
        $navigation->addSubNavigation('edit_properties', new Navigation(_('Eigenschaften bearbeiten'), 'resources.php', ['view' => 'edit_object_properties']));
        $navigation->addSubNavigation('edit_perms', new Navigation(_('Rechte bearbeiten'), 'resources.php', ['view' => 'edit_object_perms']));
    }

    if (getResourceObjectCategory($_SESSION['resources_data']['actual_object']) &&
            ResourceObject::isScheduleViewAllowed($_SESSION['resources_data']['actual_object'])) {
        $navigation->addSubNavigation('view_schedule', new Navigation(_('Belegungsplan'), 'resources.php', ['view' => 'view_schedule']));

        if (get_config('RESOURCES_ENABLE_SEM_SCHEDULE')) {
            $navigation->addSubNavigation('view_sem_schedule', new Navigation(_('Semester-Belegungsplan'), 'resources.php', ['view' => 'view_sem_schedule']));
        }

        if ($ActualObjectPerms->havePerm('autor')) {
            $navigation->addSubNavigation('edit_assign', new Navigation(_('Belegung bearbeiten'), 'resources.php', ['view' => 'edit_object_assign']));
        } else {
            $navigation->addSubNavigation('edit_assign', new Navigation(_('Belegung anzeigen'), 'resources.php', ['view' => 'edit_object_assign']));
        }
    }

    $resources_nav->addSubNavigation('objects', $navigation);
}

// Reiter "Raumplanung"
if ($perm->have_perm('admin')) {
    $resList = new ResourcesUserRoomsList($user_id, TRUE, FALSE);
    if ($resList->roomsExist() && get_config('RESOURCES_ALLOW_ROOM_REQUESTS')) {
        $navigation = new Navigation(_('Raumplanung'));
        $navigation->addSubNavigation('start', new Navigation(_('Übersicht'), 'resources.php?cancel_edit_request_x=1', ['view' => 'requests_start']));

        $edit_nav = new Navigation(_('Anfragen bearbeiten'), 'resources.php', ['view' => 'edit_request']);
        $list_nav = new Navigation(_('Anfragenliste'), 'resources.php', ['view' => 'list_requests']);
        $view_nav = new Navigation(_('Anfragenplan'), 'resources.php', ['view' => 'view_requests_schedule']);
        $navigation->addSubNavigation('edit', $edit_nav);
        $navigation->addSubNavigation('list', $list_nav);
        $navigation->addSubNavigation('schedule', $view_nav);

        if (!$_SESSION['resources_data']['requests_working_on']) {
            $edit_nav->setEnabled(false);
            $list_nav->setEnabled(false);
            $view_nav->setEnabled(false);
        }

        $resources_nav->addSubNavigation('room_requests', $navigation);

        // CSV EXPORT
        if ($view == "list_requests") {
            // Set default sidebar image
            $sidebar = Sidebar::get();
            $sidebar->setImage('sidebar/person-sidebar.png');

            if (Config::get()->EXPORT_ENABLE) {
                $widget = new ExportWidget();
                $link = URLHelper::getURL('dispatch.php/resources/helpers/export_requestlist');
                $widget->addLink(_('Anfragenliste als CSV-Dokument exportieren'), $link, Icon::create('file-office'));
                $sidebar->addWidget($widget);
            }
        }
    }
}

// Reiter "Anpassen": Grundlegende Einstellungen fuer alle Ressourcen Admins
if ((getGlobalPerms($user->id) == 'admin') || ($perm->have_perm('root'))) {
    $navigation = new Navigation(_('Anpassen'));
    $navigation->addSubNavigation('edit_types', new Navigation(_('Typen verwalten'), 'resources.php', ['view' => 'edit_types']));
    $navigation->addSubNavigation('edit_properties', new Navigation(_('Eigenschaften verwalten'), 'resources.php', ['view' => 'edit_properties']));
    $navigation->addSubNavigation('edit_settings', new Navigation(_('globale Einstellungen verwalten'), 'resources.php', ['view' => 'edit_settings']));

    if ($perm->have_perm('root')) {
        $navigation->addSubNavigation('edit_perms', new Navigation(_('globale Rechte verwalten'), 'resources.php', ['view' => 'edit_perms']));
    }

    $resources_nav->addSubNavigation('settings', $navigation);
}

/*****************************************************************************
Belegungen exportieren, views: export_list
/*****************************************************************************/
if (Request::submitted('export_list')) {
    require_once 'lib/resources/views/ShowSchedules.class.php';
    if ($_SESSION['resources_data']['actual_object']) {
        $ViewSchedules=new ShowSchedules($_SESSION['resources_data']['actual_object']);
        $ViewSchedules->setStartTime($_SESSION['resources_data']['schedule_start_time']);
        $ViewSchedules->setEndTime($_SESSION['resources_data']['schedule_end_time']);
        $ViewSchedules->setLengthFactor($_SESSION['resources_data']['schedule_length_factor']);
        $ViewSchedules->setLengthUnit($_SESSION['resources_data']['schedule_length_unit']);
        $ViewSchedules->setWeekOffset($_SESSION['resources_data']['schedule_week_offset']);
        $ViewSchedules->exportScheduleList();

        $_SESSION['resources_data'] = serialize($_SESSION['resources_data']);
        page_close();
        die();
    }
}

//load content, text, pictures and stuff
include ("lib/resources/views/page_intros.inc.php");
//save messages from
$pmessages = PageLayout::getMessages();
ob_start();
?>
<script type="text/javascript">
function check_opener(obj){
    if (window.opener && obj.href){
        window.opener.location.href = obj.href;
        window.opener.focus();
        return false;
    }
    return true;
}
</script>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<? if (!Request::get('print_view')): ?>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
                <?
                    if ($msg->checkMsgs()) {
                        $msg->displayAllMsg("line");
                    }
                    if ($page_intro) {
                        echo $page_intro;
                    }
                        echo join("\n", $pmessages);
                ?>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
<? endif; ?>
    <tr>
        <td class="blank" valign ="top">

    <?

/*****************************************************************************
Treeview, die Strukturdarstellung, views: resources, make_hierarchie
/*****************************************************************************/

$edit_structure_object = Request::option('edit_structure_object');
if ($view == "resources"){
    require_once 'lib/resources/views/ShowThread.class.php';


    if ($edit_structure_object) {
        echo"<form method=\"POST\" action=\"".URLHelper::getLink()."\">";
        echo CSRFProtection::tokenTag();
    }

    $range_id = $user->id;

    $resUser=new ResourcesUserRoots($range_id);
    $thread=new ShowThread();

    $roots=$resUser->getRoots();
    if (is_array($roots)) {
        foreach ($roots as $a) {
            $thread->showThreadLevel($a);
        }
        echo "<br>&nbsp;";
    } else {
        echo "</td></tr>";
        $msg->displayMsg(12);
    }

    if ($edit_structure_object) {
        echo "</form>";
    }

}

/*****************************************************************************
Listview, die Listendarstellung, views: lists, openobject_main
/*****************************************************************************/
if ($view == "lists" || $view == "openobject_main") {
    require_once "lib/resources/views/ShowList.class.php";

    $list=new ShowList();

    if ($_SESSION['resources_data']["list_recurse"])
        $list->setRecurseLevels(-1);
    else
        $list->setRecurseLevels(0);

    if ($view != "openobject_main")
        $list->setAdminButtons(TRUE);

    if ($edit_structure_object) {
        echo"<form method=\"POST\" action=\"".URLHelper::getLink()."\">";
        echo CSRFProtection::tokenTag();
    }

    if ($view == "openobject_main") {
        if (!$list->showRangeList(Context::getId())) {
            echo "</td></tr>";
            $msg->displayMsg(13);
        }
    } else {
        if (!$list->showListObjects($_SESSION['resources_data']["list_open"])) {
            echo "</td></tr>";
            $msg->displayMsg(14);
        }
    }

    if ($edit_structure_object) {
        echo "</form>";
    }
}

/*****************************************************************************
Objecteigenschaften bearbeiten, views: edit_object_properties
/*****************************************************************************/
if ($view == "edit_object_properties" || $view == "objects") {
    require_once "lib/resources/views/EditResourceData.class.php";

    if ($_SESSION['resources_data']["actual_object"]) {
        $editObject=new EditResourceData($_SESSION['resources_data']["actual_object"]);
        $editObject->showPropertiesForms();
    } else {
        echo "</td></tr>";
        $msg->displayMsg(15);
    }
}


/*****************************************************************************
Objecteigenschaften anzeigen, views: openobject_details
/*****************************************************************************/
if (($view == "openobject_details")  || ($view == "view_details")) {
    require_once 'lib/resources/views/ShowObject.class.php';

    //$perms = new ResourceObjectPerms($_SESSION['resources_data']["actual_object"]);
    //echo $perms->getUserPerm();

    if ($_SESSION['resources_data']["actual_object"]) {
        $viewObject = new ShowObject($_SESSION['resources_data']["actual_object"]);
        $viewObject->showProperties();
    } else {
        echo "</td></tr>";
        $msg->displayMsg(16);
    }
}

/*****************************************************************************
Objectberechtigungen bearbeiten, views: edit_object_perms
/*****************************************************************************/
if ($view == "edit_object_perms") {
    require_once "lib/resources/views/EditResourceData.class.php";

    if ($_SESSION['resources_data']["actual_object"]) {
        $editObject=new EditResourceData($_SESSION['resources_data']["actual_object"]);
        $editObject->showPermsForms();
    } else {
        echo "</td></tr>";
        $msg->displayMsg(15);
    }
}

/*****************************************************************************
Objectbelegung bearbeiten, views: edit_object_assign, openobject_assign
/*****************************************************************************/
if ($view == "edit_object_assign" || $view == "openobject_assign") {
    require_once "lib/resources/views/EditResourceData.class.php";

    if ($view == "edit_object_assign") {
        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
            <?
        }

    if ($_SESSION['resources_data']["actual_object"]) {
        $editObject=new EditResourceData($_SESSION['resources_data']["actual_object"]);
        $editObject->setUsedView($view);

        if (Request::option('edit_assign_object')){
            $_SESSION['resources_data']["actual_assign"] = Request::option('edit_assign_object');
        }
        $editObject->showScheduleForms($_SESSION['resources_data']["actual_assign"]);
    } else {
        echo "</td></tr>";
        $msg->displayMsg(15);
    }
    ?>
                </td>
            </tr>
            </table>
        <?
}

/*****************************************************************************
Typen verwalten, views: edit_types
/*****************************************************************************/
if ($view == "edit_types") {
    require_once "lib/resources/views/EditSettings.class.php";

    $editSettings=new EditSettings;
    $editSettings->showTypesForms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_properties
/*****************************************************************************/
if ($view == "edit_properties") {
    require_once "lib/resources/views/EditSettings.class.php";

    $editSettings=new EditSettings;
    $editSettings->showPropertiesForms();
}

/*****************************************************************************
Berechtigungen verwalten, views: edit_perms
/*****************************************************************************/
if ($view == "edit_perms") {
    require_once "lib/resources/views/EditSettings.class.php";

    $editSettings=new EditSettings;
    $editSettings->showPermsForms();
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($view == "view_schedule" || $view == "openobject_schedule") {
    require_once "lib/resources/views/ShowSchedules.class.php";
    if ($_SESSION['resources_data']["actual_object"] &&
            ResourceObject::isScheduleViewAllowed($_SESSION['resources_data']["actual_object"])) {
        $ViewSchedules=new ShowSchedules($_SESSION['resources_data']["actual_object"]);
        $ViewSchedules->setStartTime($_SESSION['resources_data']["schedule_start_time"]);
        $ViewSchedules->setEndTime($_SESSION['resources_data']["schedule_end_time"]);
        $ViewSchedules->setLengthFactor($_SESSION['resources_data']["schedule_length_factor"]);
        $ViewSchedules->setLengthUnit($_SESSION['resources_data']["schedule_length_unit"]);
        $ViewSchedules->setWeekOffset($_SESSION['resources_data']["schedule_week_offset"]);
        $ViewSchedules->setUsedView($view);

        if (!Request::get('print_view')) {
            $ViewSchedules->navigator();
        }

        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
                <? if ($_SESSION['resources_data']['schedule_start_time'] && $_SESSION['resources_data']['schedule_end_time']) {
                    if ($_SESSION['resources_data']['schedule_mode'] === 'list') { //view List
                        $ViewSchedules->showScheduleList((bool)Request::get('print_view'));
                    } else {
                        $ViewSchedules->showScheduleGraphical((bool)Request::get('print_view'));
                        $ViewSchedules->ShowLegend();
                    }
                } ?>
                </td>
            </tr>
            </table>
            <?
                } else {
        echo "</td></tr>";
        $msg->displayMsg(15);
    }
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule, openobject_schedule
/*****************************************************************************/
if ($view == "view_sem_schedule") {
    require_once "lib/resources/views/ShowSemSchedules.class.php";
    if ($_SESSION['resources_data']["actual_object"]) {
        $ViewSchedules = new ShowSemSchedules($_SESSION['resources_data']["actual_object"], $_SESSION['resources_data']['sem_schedule_semester_id'],$_SESSION['resources_data']['sem_schedule_timespan']);
        $ViewSchedules->setUsedView($view);
        $ViewSchedules->navigator(Request::option('print_view'));
        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
            <? if ($_SESSION['resources_data']['sem_schedule_semester_id'] && $_SESSION['resources_data']['sem_schedule_timespan']) {
                if ($_SESSION['resources_data']["schedule_mode"] === 'list') { //view List
                    $ViewSchedules->showScheduleList(Request::option('print_view'));
                } else {
                    $ViewSchedules->showScheduleGraphical(Request::option('print_view'));
                    $ViewSchedules->ShowLegend();
                }
            } ?>
                </td>
            </tr>
            </table>
        <?
    } else {
        echo "</td></tr>";
        $msg->displayMsg(15);
    }
}

if ($view == "view_group_schedule" || $view == "view_group_schedule_daily") {
    $room_group = RoomGroups::GetInstance();
    if(!$room_group->isGroup($_SESSION['resources_data']["actual_room_group"])){
        $_SESSION['resources_data']["actual_room_group"] = 0;
    }
    if ($room_group->getGroupCount($_SESSION['resources_data']["actual_room_group"])) {
        if ($view == "view_group_schedule") {
            require_once "lib/resources/views/ShowGroupSchedules.class.php";
            $ViewSchedules = new ShowGroupSchedules($_SESSION['resources_data']['actual_room_group'], $_SESSION['resources_data']['sem_schedule_semester_id'],$_SESSION['resources_data']['sem_schedule_timespan'], $_SESSION['resources_data']['group_schedule_dow']);
        } elseif ($view == "view_group_schedule_daily"){
            require_once "lib/resources/views/ShowGroupSchedulesDaily.class.php";
            $ViewSchedules = new ShowGroupSchedulesDaily($_SESSION['resources_data']['actual_room_group'], $_SESSION['resources_data']["schedule_start_time"],$room_group);
        }
        $ViewSchedules->setUsedView($view);
        $ViewSchedules->navigator(Request::option('print_view'));
        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
                <? if (isset($_SESSION['resources_data']['actual_room_group']))
                    $ViewSchedules->showScheduleGraphical(Request::option('print_view'));
                    $ViewSchedules->ShowLegend();
                } else {
                    $msg->displayMsg(25);
                }?>
                </td>
            </tr>
        </table>
<?
}

if ($view == "openobject_group_schedule") {
    $resources_groups = ResourcesOpenObjectGroups::GetInstance(Context::getId());
    if(!$resources_groups->isGroup($_SESSION['resources_data']["actual_room_group"])){
        $_SESSION['resources_data']["actual_room_group"] = 0;
    }

    if ($resources_groups->getGroupCount($_SESSION['resources_data']["actual_room_group"])) {
        require_once "lib/resources/views/ShowGroupSchedulesDaily.class.php";
        $ViewSchedules = new ShowGroupSchedulesDaily($_SESSION['resources_data']['actual_room_group'], $_SESSION['resources_data']["schedule_start_time"],$resources_groups);
        $ViewSchedules->setUsedView($view);
        $ViewSchedules->navigator(Request::option('print_view'));
        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
            <?
        if (isset($_SESSION['resources_data']['actual_room_group']))
            $ViewSchedules->showScheduleGraphical(Request::option('print_view'));
    } else {
        echo "</td></tr>";
        $msg->displayMsg(25);
    }
}


/*****************************************************************************
persoenliche Einstellungen verwalten, views: edit_personal_settings
/*****************************************************************************/
if ($view == "edit_settings") {
    require_once "lib/resources/views/EditSettings.class.php";

    $editSettings=new EditSettings;
    $editSettings->showSettingsForms();
}

/*****************************************************************************
Search
/*****************************************************************************/
if ($view == "search") {
    require_once "lib/resources/views/ResourcesBrowse.class.php";

    $search=new ResourcesBrowse;
    $search->setStartLevel('');
    $search->setMode($_SESSION['resources_data']["search_mode"]);
    $search->setCheckAssigns($_SESSION['resources_data']["check_assigns"]);
    $search->setSearchOnlyRooms($_SESSION['resources_data']["search_only_rooms"]);
    $search->setSearchArray($_SESSION['resources_data']["search_array"]);

    if ($_SESSION['resources_data']["browse_open_level"])
        $search->setOpenLevel($_SESSION['resources_data']["browse_open_level"]);
    $search->showSearch();
}

/*****************************************************************************
Roomplanning
/*****************************************************************************/
if ($view == "requests_start") {
    require_once "lib/resources/views/ShowToolsRequests.class.php";
    $toolReq=new ShowToolsRequests($_SESSION['resources_data']["sem_schedule_semester_id"],$_SESSION['resources_data']["resolve_requests_no_time"],$_SESSION['resources_data']["resolve_requests_sem_type"],$_SESSION['resources_data']["resolve_requests_faculty"], $_SESSION['resources_data']["resolve_requests_tagged"], $_SESSION['resources_data']["resolve_requests_regular"]);
    $toolReq->showToolStart();
}

if ($view == "edit_request") {
    require_once "lib/resources/views/ShowToolsRequests.class.php";

    $toolReq=new ShowToolsRequests($_SESSION['resources_data']["sem_schedule_semester_id"]);
    $toolReq->showRequest($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["request_id"]);
}

if ($view == "list_requests") {
        require_once "lib/resources/views/ShowToolsRequests.class.php";
        $toolReq=new ShowToolsRequests($_SESSION['resources_data']["sem_schedule_semester_id"],$_SESSION['resources_data']["resolve_requests_no_time"],$_SESSION['resources_data']["resolve_requests_sem_type"],$_SESSION['resources_data']["resolve_requests_faculty"], $_SESSION['resources_data']["resolve_requests_tagged"], $_SESSION['resources_data']["resolve_requests_regular"]);
        $toolReq->showRequestList();
}
if ($view == "view_requests_schedule") {
    require_once "lib/resources/views/ShowSchedulesRequests.class.php";
    if ($_SESSION['resources_data']["resolve_requests_one_res"]) {
        $ViewSchedules=new ShowSchedulesRequests($_SESSION['resources_data']["resolve_requests_one_res"]);
        $ViewSchedules->setStartTime($_SESSION['resources_data']["schedule_start_time"]);
        $ViewSchedules->setEndTime($_SESSION['resources_data']["schedule_end_time"]);
        $ViewSchedules->setWeekOffset($_SESSION['resources_data']["schedule_week_offset"]);
        $ViewSchedules->setUsedView($view);

        $ViewSchedules->navigator();
        ?>                      </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank" valign ="top">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign ="top">
            <?
        if ($_SESSION['resources_data']["schedule_start_time"])
            $ViewSchedules->showScheduleGraphical($schedule_start_time, $schedule_end_time);
    } else {
        echo "</td></tr>";
        $msg->displayMsg(49);
    }
}


/*****************************************************************************
Seite abschliessen und Infofenster aufbauen
/*****************************************************************************/
?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?
if ($quick_view_mode === 'no_nav' || Request::get('print_view')) {
    PageLayout::disableHeader();
}
if (Request::get('print_view')){
    PageLayout::removeStylesheet('studip-base.css');
    PageLayout::addStylesheet('print.css'); // use special stylesheet for printing
}

Sidebar::get()->setImage('sidebar/resources-sidebar.png');

// Add clipboard if neccessary (another hack meets the core)
if (is_object($clipObj))  {
    $form   = $clipObj->getFormObject();
    $action = $quick_view
            ? URLHelper::getLink('', compact('quick_view', 'quick_view_mode'))
            : URLHelper::getLink('', compact('view', 'quick_view_mode'));

    ob_start();
    echo $form->getFormStart($action);
    $clipObj->showClip(false, '100%');
    echo $form->getFormEnd();
    $content = ob_get_clean();

    $widget = new SidebarWidget();
    $widget->setTitle(_('Merkliste'));
    $widget->addElement(new WidgetElement($content));
    Sidebar::get()->addWidget($widget);
}

$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();

$_SESSION['resources_data'] = serialize($_SESSION['resources_data']);
page_close();
