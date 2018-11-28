<?
# Lifter002: TODO - showRequest() left undone (cause it's horrible)
# Lifter003: TEST
# Lifter005: TODO
# Lifter007: TODO
# Lifter010: TODO
/**
 * ShowToolsRequests.class.php
 *
 * room-management tool for room-admins
 *
 *
 * @author           Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access           public
 * @modulegroup      resources
 * @module           ToolsRequestResolve.class.php
 * @package          resources
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowToolsRequests.class.php
// die Suchmaschine fuer Ressourcen
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
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

/**
 * ShowToolsRequests, room-management tool for room-admin
 *
 * @access   public
 * @author   Cornelis Kater <kater@data-quest.de>
 * @package  resources
 **/
class ShowToolsRequests
{
    var $requests;          //the requests i'am responsibel for
    var $semester_id;
    var $show_requests_no_time = false;
    var $sem_type;
    var $faculty;
    var $tagged;
    var $regular;

    public function __construct($semester_id, $resolve_requests_no_time = null, $sem_type = null, $faculty = null, $tagged = null, $regular = null)
    {
        $this->semester_id = $semester_id ?: SemesterData::GetSemesterIdByDate(time());
        if (!is_null($resolve_requests_no_time)) {
            $this->show_requests_no_time = !$resolve_requests_no_time;
        }
        if (!is_null($sem_type)) {
            $this->sem_type = $sem_type;
        }
        if (!is_null($faculty)) {
            $this->faculty = $faculty;
        }
        if (!is_null($tagged)) {
            $this->tagged = $tagged;
        }
        if (!is_null($regular)) {
            $this->regular = $regular;
        }
    }

    public function getMyOpenSemRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['my_sem'];
    }

    public function getMyOpenNoTimeRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['no_time'];
    }

    public function getMyOpenResRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['my_res'];
    }

    public function getMyOpenRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['sum'];
    }

    public function restoreOpenRequests()
    {
        if (is_null($this->requests)) {
            $this->requests = (array)getMyRoomRequests($GLOBALS['user']->id, $this->semester_id, true, null, $this->sem_type, $this->faculty, $this->tagged, $this->regular);//MOD_BREMEN
            foreach ($this->requests as $val) {
                $this->requests_stats_open['sum'] += !$val["closed"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['my_res'] += !$val["closed"] && $val["my_res"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['my_sem'] += !$val["closed"] && $val["my_sem"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['no_time'] += !$val["closed"] && !$val["have_times"];
            }
        }
    }

    public function getMyRequestedRooms()
    {
        $no_time      = (int)$this->show_requests_no_time;
        $res_requests = array_filter($this->requests, function ($val) use ($no_time) {
            return !$val['closed'] && $val['my_res'] && ($val['have_times'] || $no_time);
        });

        if (count($res_requests) > 0) {
            $query     = "SELECT ro.resource_id, ro.name, COUNT(ro.resource_id) as anzahl
                      FROM resources_requests rr
                      INNER JOIN resources_objects ro USING (resource_id)
                      WHERE rr.request_id IN (?)
                      GROUP BY ro.resource_id
                      ORDER BY ro.name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([array_keys($res_requests),]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function showToolStart()
    {
        $template                    = $GLOBALS['template_factory']->open('resources/planning/start');
        $template->semester_id       = $this->semester_id;
        $template->open_requests     = $this->getMyOpenRequests();
        $template->open_sem_requests = $this->getMyOpenSemRequests();
        $template->open_res_requests = $this->getMyOpenResRequests();
        $template->no_time           = $this->getMyOpenNoTimeRequests();
        $template->display_no_time   = $this->show_requests_no_time;
        $template->display_sem_type  = $this->sem_type;//MOD_BREMEN
        $template->display_faculty   = $this->faculty;//MOD_BREMEN
        $template->display_tagged    = $this->tagged;//MOD_BREMEN
        $template->display_regular   = $this->regular;
        $template->rooms             = $this->getMyRequestedRooms();
        echo $template->render();
    }

    public function showRequestList()
    {
        if (!isset($_SESSION['resources_data']['requests_working_on'])) {
            header('location:' . URLHelper::getLink('resources.php', ['cancel_edit_request_x' => '1',
                                                                      'view'                  => 'requests_start']));
            return;
        }

        $template                  = $GLOBALS['template_factory']->open('resources/planning/request_list.php');
        $template->license_to_kill = (Config::get()->RESOURCES_ALLOW_DELETE_REQUESTS && getGlobalPerms($GLOBALS['user']->id) == 'admin');

        echo $template->render();
    }

    /**
     *
     * @param $request_id
     */
    public function showRequest($request_id)
    {
        $reqObj = new RoomRequest($request_id);
        $semObj = new Seminar($reqObj->getSeminarId());

        $template           = $GLOBALS['template_factory']->open('resources/planning/request.php');
        $template->reqObj   = $reqObj;
        $template->semObj   = $semObj;
        $template->modifier = $reqObj->last_modified_by ? User::find($reqObj->last_modified_by) : $reqObj->user;
        $template->sem_link = $GLOBALS['perm']->have_studip_perm('tutor', $semObj->getId()) ? "seminar_main.php?auswahl=" . $semObj->getId() : "dispatch.php/course/details/?sem_id=" . $semObj->getId() . "&send_from_search=1&send_from_search_page=" . URLHelper::getLink("resources.php?working_on_request=$request_id");
        echo $template->render();
    }

    /**
     *
     * @param $overlaps
     * @param $events_count
     * @param $overlap_events_count
     * @param $group_dates
     */
    public static function showGroupOverlapStatus($overlaps, $events_count, $overlap_events_count, $group_dates)
    {
        $style = ['style' => 'display: inline-block'];
        if ($overlap_events_count) {
            $lock_desc = '';
            foreach ($overlaps as $val) {
                if ($val['lock']) {
                    $lock_desc .= sprintf(_('%s, %s Uhr bis %s, %s Uhr') . "\n", date('d.m.Y', $val['begin']), date('H:i', $val['begin']), date('d.m.Y', $val['end']), date('H:i', $val['end']));
                }
            }
            if ($lock_desc) {
                $lock_desc = _("Sperrzeit(en):\n") . $lock_desc;
            }
            $desc = '';
            if ($overlap_events_count >= round($events_count * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100)) {
                if ($overlap_events_count == 1) if ($lock_desc) $desc .= sprintf(_("Es besteht eine Belegungssperre zur gewünschten Belegungszeit.") . "\n" . $lock_desc); else
                    $desc .= sprintf(_("Es existieren Überschneidungen zur gewünschten Belegungszeit.") . "\n"); else
                    $desc .= sprintf(_("Es existieren Überschneidungen oder Belegungssperren zu mehr als %s%% aller gewünschten Belegungszeiten.") . "\n" . $lock_desc, Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE);
                $html   = Icon::create('decline-circle', 'status-red', ['title' => $desc] + $style)->asImg();
                $status = 2;
            } else {
                $desc .= sprintf(_("Einige der gewünschten Belegungszeiten überschneiden sich mit eingetragenen Belegungen bzw. Sperrzeiten:\n"));
                foreach ($group_dates as $key => $val) {
                    if ($overlaps[$key]) foreach ($overlaps[$key] as $key2 => $val2) if ($val2["lock"]) $desc .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)") . "\n", date("d.m.Y", $val2["begin"]), date("H:i", $val2["begin"]), date("d.m.Y", $val2["end"]), date("H:i", $val2["end"])); else
                        $desc .= sprintf(_("%s von %s bis %s Uhr") . "\n", date("d.m.Y", $val2["begin"]), date("H:i", $val2["begin"]), date("H:i", $val2["end"]));
                }
                $html   = Icon::create('exclaim-circle', 'status-yellow', ['title' => $desc] + $style)->asImg();
                $status = 1;
            }
        } else {
            $html   = Icon::create('check-circle', 'status-green', ['title' => _('Es existieren keine Überschneidungen')] + $style)->asImg();
            $status = 0;
        }
        return ["html" => $html, "status" => $status];
    }


    public static function showOverlapStatus($overlaps, $events_count, $overlap_events_count)
    {
        if (is_array($overlaps)) {
            $lock_desc = '';
            foreach ($overlaps as $val) {
                if ($val["lock"]) {
                    $lock_desc .= sprintf(_('%s, %s Uhr bis %s, %s Uhr') . "\n", date('d.m.Y', $val['begin']), date('H:i', $val['begin']), date('d.m.Y', $val['end']), date('H:i', $val['end']));
                }
            }
            if ($lock_desc) {
                $lock_desc = _("Sperrzeit(en):\n") . $lock_desc;
            }
            $desc = '';
            if ($overlap_events_count >= round($events_count * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100)) {
                if ($overlap_events_count == 1) if ($overlaps[0]["lock"]) $desc .= sprintf(_("Es besteht eine Belegungssperre zur gewünschten Belegungszeit.") . "\n" . $lock_desc); else
                    $desc .= sprintf(_("Es existieren Überschneidungen zur gewünschten Belegungszeit.") . "\n"); else
                    $desc .= sprintf(_("Es existieren Überschneidungen oder Belegungssperren zu mehr als %s%% aller gewünschten Belegungszeiten.") . "\n" . $lock_desc, Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE);
                $html   = Icon::create('decline-circle', 'status-red', ['title' => $desc])->asImg();
                $status = 2;
            } else {
                $desc .= sprintf(_("Einige der gewünschten Belegungszeiten überschneiden sich mit eingetragenen Belegungen bzw. Sperrzeiten:\n"));
                foreach ($overlaps as $val) {
                    if ($val["lock"]) $desc .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)") . "\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("d.m.Y", $val["end"]), date("H:i", $val["end"])); else
                        $desc .= sprintf(_("%s von %s bis %s Uhr") . "\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("H:i", $val["end"]));
                }
                $html   = Icon::create('exclaim-circle', 'status-yellow', ['title' => $desc])->asImg();
                $status = 1;
            }
        } else {
            $html   = Icon::create('check-circle', 'status-green', ['title' => _('Es existieren keine Überschneidungen')])->asImg();
            $status = 0;
        }
        return ["html" => $html, "status" => $status];
    }
}
