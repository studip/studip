<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ShowSemSchedules.class.php
*
* view schedule/assigns for a ressource-object
*
*
* @author       André Noack <noack@data-quest.de>, Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
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

use Studip\Button,
    Studip\LinkButton;

require_once 'lib/resources/views/ShowSchedules.class.php';
require_once 'lib/resources/views/SemScheduleWeek.class.php';

/*****************************************************************************
ShowSchedules - schedule view
/*****************************************************************************/

class ShowSemSchedules extends ShowSchedules {

    var $semester = null;
    var $only_course_time = true;

    //Konstruktor
    function __construct($resource_id, $semester_id = null, $timespan = 'sem_time') {
        if (!$semester_id){
            $this->semester = SemesterData::getCurrentSemesterData();
        } else {
            $this->semester = SemesterData::getSemesterData($semester_id);
        }
        $this->timespan = $timespan;
        if  ($this->timespan == 'sem_time'){
            $next_sem = SemesterData::getNextSemesterData($this->semester['vorles_ende']);
            $this->start_time = $this->semester['vorles_ende'];
            $this->end_time = is_array($next_sem) ? $next_sem['vorles_beginn'] : $this->semester['ende'];
        } else {
            $this->start_time = $this->semester['vorles_beginn'];
            $this->end_time = $this->semester['vorles_ende'];
        }
        parent::__construct($resource_id);
    }

    function navigator ($print_view = false) {
        global $view_mode;
        $semester = SemesterData::GetSemesterArray();
        unset($semester[0]);
        if (!$print_view){
        ?>
        <form method="POST" name="schedule_form" action="<?echo URLHelper::getLink('?navigate=TRUE&quick_view=view_sem_schedule&quick_view_mode='.$view_mode) ?>">
            <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <colgroup>
                <col width="4%">
                <col width="40%">
                <col width="30%">
                <col width="26%">
            </colgroup>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3"><b><?=_("Semester:")?></b></td>
            </tr>
            <tr>
                <td rowspan="2">&nbsp;</td>
                <td valign="bottom">
                    <?=SemesterData::GetSemesterSelector(['name' => 'sem_schedule_choose', 'class' => 'submit-upon-select'], $this->semester['semester_id'],'semester_id',false)?>
                    <?= Button::create(_('Auswählen'), 'jump') ?>
                </td>
                <td valign="middle">
                    <?= _('Ein Semester als Liste ausgeben') ?>
                </td>
                <td>
                    <?= Button::create(_('Ausgeben'), 'sem_schedule_start_list') ?>
                </td>
            </tr>
            <tr>
                <td valign="middle">
                    <label>
                    <input type="radio" onChange="document.schedule_form.submit()" style="vertical-align:bottom" <?=($this->timespan == 'course_time' ? 'checked' : '')?> name="sem_time_choose" value="course_time">
                    <?=_("Vorlesungszeit")?>
                    </label>
                    <label>
                    <input type="radio" onChange="document.schedule_form.submit()" style="vertical-align:bottom" <?=($this->timespan == 'sem_time' ? 'checked' : '')?> name="sem_time_choose" value="sem_time">
                    <?=_("vorlesungsfreie Zeit")?>
                    </label>
                </td>
                <td valign="middle">
                    <?= _('<i>oder</i> ein Semester grafisch ausgeben') ?>
                </td>
                <td>
                    <?= Button::create(_('Ausgeben'), 'sem_schedule_start_graphical') ?><br>
                </td>
            </tr>
        </table>
    <?
        }
    }

    function showScheduleGraphical($print_view = false) {
        global $view_mode, $ActualObjectPerms;

        $categories["na"] = 4;
        $categories["sd"] = 4;
        $categories["y"] = 3;
        $categories["m"] = 3;
        $categories["w"] = 0;
        $categories["d"] = 2;

        //an assign for a date corresponding to a (seminar-)metadate
        $categories["meta"] = 1;


         //select view to jump from the schedule
         if ($this->used_view == "openobject_schedule" && Context::get())
            $view = "openobject_assign";
         else
            $view = "edit_object_assign";

        $start_time = $this->start_time;
        $end_time = $this->end_time;

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

        $schedule=new SemScheduleWeek($start_hour, $end_hour,false, $start_time);
        $num_rep_events = 0;
        $num_single_events = 0;
        if ($ActualObjectPerms->havePerm("autor"))
            $schedule->add_link = "resources.php?cancel_edit_assign=1&quick_view=$view&quick_view_mode=".$view_mode."&add_ts=";
        if ($_SESSION['resources_data']["show_repeat_mode"] == 'repeated' || $_SESSION['resources_data']["show_repeat_mode"] == 'all'){
            $events = createNormalizedAssigns($this->resource_id, $start_time, $end_time, get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME'));
            foreach($events as $id => $event){
                $repeat_mode = $event['repeat_mode'];
                $add_info = ($event['sem_doz_names'] ? '('.$event['sem_doz_names'].') ' : '');
                $add_info .= ($repeat_mode == 'w' && $event['repeat_interval'] == 1 ? '('._("wöchentlich").')' : '');
                $add_info .= ($repeat_mode == 'w' && $event['repeat_interval'] > 1 ? '('.$event['repeat_interval'].'-'._("wöchentlich").')' : '');
                $name = $event['name'];
                $schedule->addEvent(null, $name, $event['begin'], $event['end'],
                            URLHelper::getLink('?cancel_edit_assign=1&quick_view='.$view.'&quick_view_mode='.$view_mode.'&edit_assign_object='.$event['assign_id']), $add_info, $categories[$repeat_mode]);
            }
            $num_rep_events = count($events);
        }
        // nur zukünftige Einzelbelegungen
        if ( ($end_time > time()) && ($_SESSION['resources_data']["show_repeat_mode"] == 'single' || $_SESSION['resources_data']["show_repeat_mode"] == 'all')){
            $a_start_time = ($start_time > time() ? $start_time : time());
            $a_end_time = $end_time;
            $assign_events = new AssignEventList ($a_start_time, $a_end_time, $this->resource_id, '', '', TRUE, 'semschedulesingle');
            $num = 1;
            while ($event = $assign_events->nextEvent()) {
                if(in_array($event->repeat_mode, ['d','m','y'])){
                    $assign = AssignObject::Factory($event->getAssignId());
                    switch($event->repeat_mode){
                        case 'd':
                        $add_info = '('.sprintf(_("täglich, %s bis %s"), strftime('%x',$assign->getBegin()), strftime('%x',$assign->getRepeatEnd())).')';
                        break;
                        case 'm':
                        if($assign->getRepeatInterval() == 1) $add_info = '('._("monatlich").')';
                        else  $add_info = '('.$assign->getRepeatInterval().'-'._("monatlich").')';
                        break;
                        case 'y':
                        if($assign->getRepeatInterval() == 1) $add_info = '('._("jährlich").')';
                        else  $add_info = '('.$assign->getRepeatInterval().'-'._("jährlich").')';
                        break;
                    }
                } else {
                    $add_info = '';
                }
                $schedule->addEvent(null, 'EB'.$num++.':' . $event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')), $event->getBegin(), $event->getEnd(),
                        URLHelper::getLink('?cancel_edit_assign=1&quick_view='.$view.'&quick_view_mode='.$view_mode.'&edit_assign_object='.$event->getAssignId()), $add_info, $categories[$event->repeat_mode]);
            }
            $num_single_events = $assign_events->numberOfEvents();
        }
        if(!$print_view){
        ?>
        <table class="default">
            <colgroup>
                <col width="4%">
                <col width="10%">
                <col width="76%">
                <col width="10%">
            </colgroup>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&previous_sem=1')?>">
                        <?= Icon::create('arr_2left', 'clickable', ['title' => _("Vorheriges Semester anzeigen")])->asImg(16, ["alt" => _("Vorheriges Semester anzeigen"), "border" => 0]) ?>
                    </a>
                </td>
                <td align="center">
                    <b>
                        <?
                            printf(_("Anzeige des Semesters: %s"), htmlReady($this->semester['name']));
                            echo '<br>' . date ("d.m.Y", $start_time), " - ", date ("d.m.Y", $end_time);
                        ?>
                    </b>
                </td>
                <td align="center">&nbsp;
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&next_sem=1')?>">
                        <?= Icon::create('arr_2right', 'clickable', ['title' => _("Nächstes Semester anzeigen")])->asImg(16, ["alt" => _("Nächstes Semester anzeigen"), "border" => 0]) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td align="center" valign="bottom">
                <? if ((!$_SESSION['resources_data']["schedule_time_range"]) || ($_SESSION['resources_data']["schedule_time_range"] == 1)): ?>
                    <a href="<?= URLHelper::getLink('', ['quick_view' => $this->used_view,
                                                              'quick_view_mode' => $view_mode,
                                                              'time_range' => $_SESSION['resources_data']['schedule_time_range'] ? 'FALSE' : -1]) ?>">
                        <?= Icon::create('arr_2up', 'clickable', ['title' => _('Frühere Belegungen anzeigen')])->asImg(['class' => 'middle']) ?>
                    </a>
                <? endif; ?>
                </td>
                <td colspan="2">
                    <?
                    if ($_SESSION['resources_data']["show_repeat_mode"] == 'repeated' || $_SESSION['resources_data']["show_repeat_mode"] == 'all'){
                        echo _("Anzahl der regelmäßigen Belegungen in diesem Zeitraum:") . " " . $num_rep_events;
                    }
                    if ($_SESSION['resources_data']["show_repeat_mode"] == 'single' || $_SESSION['resources_data']["show_repeat_mode"] == 'all'){
                        echo _("Anzahl der Einzelbelegungen in diesem Zeitraum:") . " " . $num_single_events;
                    }
                    ?>
                </td>
                <td nowrap>
                    <?
                    print "<select style=\"font-size:10px;\" name=\"show_repeat_mode\">";
                    printf ("<option style=\"font-size:10px;\" %s value=\"all\">"._("alle Belegungen")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "all") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"single\">"._("nur Einzeltermine")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "single") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"repeated\">"._("nur Wiederholungstermine")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "repeated") ? "selected" : "");
                    print "</select>";
                    print "&nbsp;".Icon::create('accept', 'accept', ['title' => _('Ansicht umschalten')])->asInput(["type" => "image", "class" => "middle", "name" => "send_schedule_repeat_mode"]);
                    ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3">
                    <? $schedule->showSchedule('html'); ?>
                </td>
            </tr>
            <tr>
                <td align="center" valign="bottom">
                <? if ((!$_SESSION['resources_data']['schedule_time_range']) || ($_SESSION['resources_data']['schedule_time_range'] == -1)): ?>
                    <a href="<?= URLHelper::getLink('', ['quick_view' => $this->used_view,
                                                              'quick_view_mode' => $view_mode,
                                                              'time_range' => $_SESSION['resources_data']['schedule_time_range'] ? 'FALSE' : 1]) ?>">
                        <?= Icon::create('arr_2down', 'clickable', ['title' => _('Spätere Belegungen anzeigen')])->asImg() ?>
                    </a>
                <? endif; ?>
                </td>
                <td colspan="3">&nbsp;</td>
            </tr>
            <?php
            if (($_SESSION['resources_data']["show_repeat_mode"] == 'single' || $_SESSION['resources_data']["show_repeat_mode"] == 'all') && $num_single_events){
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="3">
                        <strong><?=_("Einzelbelegungen:")?></strong>
                        <br>
                        <?php
                        reset($assign_events->events);
                        $num = 1;
                        while($event = $assign_events->nextEvent()) {
                            echo LinkButton::create(_('Eigenschaften'), URLHelper::getURL('?quick_view='
                                . $view . '&quick_view_mode=' . $view_mode . '&edit_assign_object=' . $event->getAssignId()));
                            printf ("&nbsp; <font size=-1>"._("%s ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")."</font><br>",'EB'.$num++, strftime("%A, %d.%m.%Y %H:%M", $event->getBegin()), strftime("%A, %d.%m.%Y %H:%M", $event->getEnd()), $event->getName());
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        </form>
    <?
        } else {
            $room = ResourceObject::Factory($this->resource_id);
            ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
            <tr>
                <td align="center">
                <div style="font-size:150%;font-weight:bold;">
                <?=htmlReady($room->getName().' - ' .$this->semester['name'])?>
                </div>
                <div style="font-size:100%;font-weight:bold;margin-bottom:10px;">
                <?=date ("d.m.Y", $start_time). " - ". date ("d.m.Y", $end_time)?>
                &nbsp;(<?=($this->timespan == 'course_time' ? _("Vorlesungszeit") : _("vorlesungsfreie Zeit"))?>)
                </div>
                </td>
            </tr>
            <tr>
                <td>
                <?
                $schedule->showSchedule("html", true);
                ?>
                </td>
            </tr>
            <?
            if (($_SESSION['resources_data']["show_repeat_mode"] == 'single' || $_SESSION['resources_data']["show_repeat_mode"] == 'all') && $num_single_events){
            ?>
            <tr>
                <td>
                <strong>
                <?=_("Einzelbelegungen:")?>
                &nbsp;(<?=strftime("%d.%m.%Y",$a_start_time) . ' - ' . strftime("%d.%m.%Y",$a_end_time)?>)
                </strong>
                <br>
                <?
                reset($assign_events->events);
                $num = 1;
                while($event = $assign_events->nextEvent()) {
                    printf ("<font size=-1>"._("%s ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")."</font><br>",'EB'.$num++, strftime("%A, %d.%m.%Y %H:%M", $event->getBegin()), strftime("%A, %d.%m.%Y %H:%M", $event->getEnd()), htmlready($event->getName()));
                }
                ?>
                </td>
            </tr>
            <?}?>
            </table>
            <?
        }
    }
}
?>
