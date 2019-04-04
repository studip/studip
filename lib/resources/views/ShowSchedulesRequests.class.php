<?php
# Lifter002: TODO
# Lifter010: TODO
/**
* ShowSchedules.class.php
*
* view schedule/assigns for a ressource-object
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ShowSchedules.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowSchedule.class.php
// stellt Assign/graphische Uebersicht der Belegungen dar
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

use Studip\Button;

require_once 'lib/resources/views/ScheduleWeekRequests.class.php';
require_once 'lib/resources/views/ShowSchedules.class.php';
require_once 'lib/resources/views/ShowToolsRequests.class.php';

/*****************************************************************************
ShowSchedules - schedule view
/*****************************************************************************/

class ShowSchedulesRequests extends ShowSchedules{

    //Konstruktor
    function __construct($resource_id='', $start_time = null) {
        $this->resource_id = $resource_id;
        if($start_time ){
            $this->start_time = strtotime('this monday', $start_time);
        }
    }

    function navigator () {
        global $view;

        //match start_time & end_time for a whole week
        $dow = date ("w", $this->start_time);
        if (date ("w", $this->start_time) >1)
            $offset = 1 - date ("w", $this->start_time);
        if (date ("w", $this->start_time) <1)
            $offset = -6;

        $start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
        $end_time = mktime (23, 59, 0, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

        ?>
        <form method="POST" action="<?= URLHelper::getLink('?navigate=TRUE&quick_view='.$view) ?>">
            <?= CSRFProtection::tokenTag() ?>

        <table class="default">
            <thead>
                <tr>
                    <th><?= _('Zeitraum') ?>:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= _('Beginn') ?>:
                        <input type="text" name="schedule_begin_day" size=2 maxlength=2 value="<?= date('d', $start_time ?: time()) ?>">.
                        <input type="text" name="schedule_begin_month" size=2 maxlength=2 value="<?= date('m', $start_time ?: time()) ?>">.
                        <input type="text" name="schedule_begin_year" size=4 maxlength=4 value="<?= date('Y', $start_time ?: time()) ?>">
                        <br>
                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;<?= Button::create(_('Auswählen'), 'jump') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        </form>
    <?
    }


    function showScheduleGraphical($print_view = false) {
        global $view_mode;

        $categories["na"] = 4;
        $categories["sd"] = 4;
        $categories["y"] = 3;
        $categories["m"] = 3;
        $categories["w"] = 0;
        $categories["d"] = 2;

        //an assign for a date corresponding to a (seminar-)metadate
        $categories["meta"] = 1;

        //match start_time & end_time for a whole week
        $dow = date ("w", $this->start_time);
        if (date ("w", $this->start_time) >1)
            $offset = 1 - date ("w", $this->start_time);
        if (date ("w", $this->start_time) <1)
            $offset = -6;


        $start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
        $end_time = mktime (23, 59, 59, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

        if ($_SESSION['resources_data']["schedule_time_range"] == -1) {
            $start_hour = 0;
            $end_hour = 12;
        } elseif ($_SESSION['resources_data']["schedule_time_range"] == 1) {
            $start_hour = 12;
            $end_hour = 23;
        } else {
            $start_hour = 8;
            $end_hour = 22;
        }

        $schedule=new ScheduleWeekRequests($start_hour, $end_hour, FALSE, $start_time, true);

        //fill the schedule
        $assign_events=new AssignEventList ($start_time, $end_time, $this->resource_id, '', '', TRUE, 'all');
        while ($event=$assign_events->nextEvent()) {
            $repeat_mode = $event->getRepeatMode(TRUE);
            $add_info = '';
            if (in_array($event->getOwnerType(), array('sem','date'))){
                $sem_doz_names = array();
                if ($event->getOwnerType() == 'sem'){
                    $sem_obj = Seminar::GetInstance($event->getAssignUserId());
                } else {
                    $sem_obj = Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
                }
                foreach($sem_obj->getMembers('dozent') as $dozent){
                    $sem_doz_names[] = $dozent['Nachname'];
                    if (++$c > 2) break;
                }
                $add_info = '(' . join(', ', $sem_doz_names) . ')';
            }
            $schedule->addEvent(null, $event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')), $event->getBegin(), $event->getEnd(),
                        URLHelper::getLink('?show_object='.$this->resource_id.'&cancel_edit_assign=1&quick_view=edit_object_assign&edit_assign_object='.$event->getAssignId()), $add_info, $categories[$repeat_mode]);
        }
        foreach($_SESSION['resources_data']["requests_working_on"] as $req){
            if($_SESSION['resources_data']['skip_closed_requests'] && $req['closed']) continue;
            $reqObj = RoomRequest::find($req["request_id"]);
            $assignObjects = array();
            if ($reqObj) {
                $semResAssign = new VeranstaltungResourcesAssign($reqObj->getSeminarId());
                if ($reqObj->getType() == 'date' && $_SESSION['resources_data']["show_repeat_mode_requests"] != 'repeated') {
                    $assignObjects[] = $semResAssign->getDateAssignObject($reqObj->getTerminId());
                } else if ($reqObj->getType() == 'cycle' && $_SESSION['resources_data']["show_repeat_mode_requests"] != 'single') {
                    $assignObjects = $semResAssign->getMetaDateAssignObjects($reqObj->getMetadateId());
                } else if ($reqObj->getType() == 'course' && $_SESSION['resources_data']["show_repeat_mode_requests"] != 'single') {
                    $assignObjects = $semResAssign->getDateAssignObjects(TRUE);
                }
            }
            if (Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES) {
                $assignObjects = array_filter($assignObjects, function ($a) {
                    return $a->getBegin() > time() - 3600;
                });
            }
            $check = new CheckMultipleOverlaps();
            $check->setAutoTimeRange($assignObjects);
            $check->addResource($this->resource_id);
            $events = array();
            $result = array();
            foreach ($assignObjects as $ao) {
                foreach ($ao->getEvents() as $event) {
                    $events[$event->getId()] = $event;
                }
            }
            uasort($events, function ($a, $b) {
                return $a->getBegin() - $b->getBegin();
            });
            $check->checkOverlap($events, $result, "assign_id");
            $assignObjectsWeek = array_filter($assignObjects, function ($a) use ($start_time, $end_time) {
                return $a->getBegin() > $start_time && $a->getEnd() < $end_time;
            });
            foreach($assignObjectsWeek as $ao){
                $name = $ao->getOwnerName();
                if($reqObj->getTerminId()){
                    $add_info = _("(Einzeltermin)");
                    $color = 6;
                } else {
                    $add_info = '(' . _("Sammelanfrage");
                    $seminar = Seminar::GetInstance($reqObj->getSeminarId());
                    $date = SingleDateDB::restoreSingleDate($ao->getAssignUserId());
                    if($date['metadate_id']){
                        $add_info .= ',' . _("regelmäßig");
                        if ($seminar->getTurnus() == 1) {
                            $add_info .= "," . _("zweiwöchentlich");
                        }
                    }
                    $add_info .= ')';
                    $color = 5;
                }
                foreach($ao->getEvents() as $event){
                    $current_events = array_filter($events, function ($a) use ($event) {
                        return date('wHi', $a->getBegin()) == date('wHi', $event->getBegin())
                            && date('wHi', $a->getEnd()) == date('wHi', $event->getEnd());
                    });
                    if(count($current_events) > 1){
                        $ce = array_values($current_events);
                        $add_info .= '<br>' . sprintf(_("%s Termine, %s - %s"), count($ce), date("d.m", $ce[0]->getBegin()), date("d.m", $ce[count($ce) - 1]->getBegin()));
                    }
                    $overlaps = array();
                    if(count($result)){
                        foreach(array_map('array_shift', $result[$this->resource_id]) as $one){
                            if(isset($current_events[$one['event_id']])) $overlaps[] = $one;
                        }
                    }
                    $overlaps_info = ShowToolsRequests::showOverlapStatus(count($overlaps) ? $overlaps : null, count($current_events), count($overlaps));
                    $add_info .= '</a>&nbsp;' .$overlaps_info['html'] . '<a>';
                    $schedule->addEvent(null, $name, $event->getBegin(), $event->getEnd(),
                        URLHelper::getLink('?view=edit_request&edit='.$req["request_id"]), $add_info, $color);
                    $requested_events++;
                }

            }

        }
        $semester = SemesterData::getSemesterDataByDate($start_time);
        ?>
            <form method="POST" action="<?echo URLHelper::getLink()?>?quick_view=<?=$view?>">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <colgroup>
                <col width="4%">
                <col width="10%">
                <col width="76%">
                <col width="10%">
            </colgroup>
            <tr>
                <td >&nbsp;</td>
                <td align="left">
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&previous_week=TRUE')?>">
                        <?= Icon::create('arr_2left', 'clickable', ['title' => _("Vorherige Woche anzeigen")])->asImg(16, ["alt" => _("Vorherige Woche anzeigen"), "border" => 0]) ?>
                    </a>
                </td>
                <td align="center" style="font-weight:bold">
                    <?= sprintf(_("Anzeige der Woche vom %s bis %s (KW %s)"), strftime("%x", $start_time), strftime("%x",$end_time), strftime("%V", $start_time));?>
                    <br>
                    <?php $this->showSemWeekNumber($start_time); ?>
                </td>
                <td align="center">
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&next_week=TRUE')?>">
                        <?= Icon::create('arr_2right', 'clickable', ['title' => _("Nächste Woche anzeigen")])->asImg(16, ["alt" => _("Nächste Woche anzeigen"), "border" => 0]) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td align="center" valign="bottom">
                <? if ((!$_SESSION['resources_data']["schedule_time_range"]) || ($_SESSION['resources_data']["schedule_time_range"] == 1)): ?>
                    <a href="<?= URLHelper::getLink('', array('quick_view' => $this->used_view,
                                                              'quick_view_mode' => $view_mode,
                                                              'time_range' => $_SESSION['resources_data']['schedule_time_range'] ? 'FALSE' : -1)) ?>">
                        <?= Icon::create('arr_2up', 'clickable', ['title' => _('Frühere Belegungen anzeigen')])->asImg(['class' => 'middle']) ?>
                    </a>
                <? endif; ?>
                </td>
                <td colspan="2">
                    <?
                    echo "&nbsp;"._("Anzahl der Belegungen in diesem Zeitraum:")." ". $assign_events->numberOfEvents()."<br>";
                    echo "&nbsp;"._("Anzahl der gwünschten Belegungen in diesem Zeitraum:")." ". (int)$requested_events;
                    ?>
                </td>
                <td nowrap>
                    <?
                    print "<select style=\"font-size:10px;\" name=\"show_repeat_mode_requests\">";
                    printf ("<option style=\"font-size:10px;\" %s value=\"all\">"._("alle Anfragen")."</option>", ($_SESSION['resources_data']["show_repeat_mode_requests"] == "all") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"single\">"._("nur Anfragen zu Einzelterminen")."</option>", ($_SESSION['resources_data']["show_repeat_mode_requests"] == "single") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"repeated\">"._("nur Anfragen zu Wiederholungsterminen")."</option>", ($_SESSION['resources_data']["show_repeat_mode_requests"] == "repeated") ? "selected" : "");
                    print "</select>";
                    print "&nbsp;".Icon::create('accept', 'accept', ['title' => _('Ansicht umschalten')])->asInput(["type" => "image", "class" => "middle", "name" => "send_schedule_repeat_mode"]);
                    ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                    <? $schedule->showSchedule("html", $print_view); ?>
                </td>
            </tr>
            <tr>
                <td align="center" valign="bottom">
                <? if ((!$_SESSION['resources_data']['schedule_time_range']) || ($_SESSION['resources_data']['schedule_time_range'] == -1)): ?>
                    <a href="<?= URLHelper::getLink('', array('quick_view' => $this->used_view,
                                                              'quick_view_mode' => $view_mode,
                                                              'time_range' => $_SESSION['resources_data']['schedule_time_range'] ? 'FALSE' : 1)) ?>">
                        <?= Icon::create('arr_2down', 'clickable', ['title' => _('Spätere Belegungen anzeigen')])->asImg() ?>
                    </a>
                <? endif; ?>
                </td>
                <td colspan="3">&nbsp;</td>
            </tr>
        </table>
        </form>
    <?
    }
}
