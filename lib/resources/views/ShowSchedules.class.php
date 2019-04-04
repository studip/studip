<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
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

use Studip\Button,
    Studip\LinkButton;

require_once 'lib/resources/views/ScheduleWeek.class.php';

/*****************************************************************************
ShowSchedules - schedule view
/*****************************************************************************/

class ShowSchedules
{
    var $ressource_id;      //viewed ressource object
    var $user_id;           //viewed user
    var $range_id;          //viewed range
    var $start_time;        //time to start
    var $end_time;          //time to end
    var $length_factor;     //the used length factor for calculations, only used for viewing
    var $length_unit;       //the used length unit for calculations, only used for viewing
    var $week_offset;       //offset for the week view
    var $used_view;         //the used view, submitted to the sub classes


    //Konstruktor
    public function __construct($resource_id = '', $user_id = '', $range_id = '')
    {
        $this->resource_id=$resource_id;
        $this->user_id=$user_id;
        $this->range_id=$range_id;
    }

    public function setLengthFactor ($value)
    {
        $this->length_factor = $value;
    }

    public function setLengthUnit ($value)
    {
        $this->length_unit = $value;
    }

    public function setStartTime ($value)
    {
        $this->start_time = $value;
    }

    public function setEndTime ($value)
    {
        $this->end_time = $value;
    }

    public function setWeekOffset ($value)
    {
        $this->week_offset = $value;
    }

    public function setUsedView($value)
    {
        $this->used_view = $value;
    }

    public function navigator()
    {
        global $view_mode;

        //match start_time & end_time for a whole week
        $dow = date("w", $this->start_time);
        if ($dow > 1) {
            $offset = 1 - $dow;
        }
        if ($dow < 1) {
            $offset = -6;
        }
        $start_time = mktime(0, 0, 0,
                             date('n', $this->start_time),
                             date('j', $this->start_time) + $offset + $this->week_offset * 7,
                             date('Y', $this->start_time));
        $end_time = mktime(23, 59, 0,
                           date('n', $start_time),
                           date('j', $start_time) + 6,
                           date('Y', $start_time));

        ?>
        <table class="default nohover">
            <form method="POST" action="<?echo URLHelper::getLink('?navigate=TRUE&quick_view=view_schedule&quick_view_mode='.$view_mode)?>">
            <?= CSRFProtection::tokenTag() ?>
            <tr>
                <td width="4%">&nbsp;</td>
                <td width="96%" colspan="2"><b><?=_("Zeitraum:")?></b></td>
            </tr>
            <tr>
                <td width="4%" rowspan="2">&nbsp;</td>
                <td width="30%" rowspan="2" valign="middle">
                    <?= _('Beginn') ?>:
                    <input type="text" id="startTime" name="startTime" size="8" value="<?if($start_time) : ?><?=date('j.n.Y', $start_time)?><?endif;?>">
                    <script>
                        jQuery("#startTime").datepicker();
                    </script>
                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;<?= Button::create(_('Auswählen'), 'jump') ?>
                </td>
                <td width="66%" valign="bottom">
                    <input type="text" name="schedule_length_factor" size=2 maxlength=2 / value="<? if (!$this->length_factor) echo "1"; else echo $this->length_factor; ?>">
                    &nbsp; <select name="schedule_length_unit">
                        <option <? if ($this->length_unit  == "d") echo "selected" ?> value="d"><?=_("Tag(e)")?></option>
                        <option <? if ($this->length_unit  == "w") echo "selected" ?> value="w"><?=_("Woche(n)")?></option>
                        <option <? if ($this->length_unit  == "m") echo "selected" ?> value="m"><?=_("Monat(e)")?></option>
                        <option <? if ($this->length_unit  == "y") echo "selected" ?> value="y"><?=_("Jahre(e)")?></option>
                    </select>
                    <?= Button::create(_('Als Liste ausgeben'), 'start_list') ?>
                    <?= Button::create(_('Liste exportieren'), 'export_list') ?>
                </td>
            </tr>
            <tr>
                <td width="66%" valign="bottom">
                    <em><?= _('oder') ?></em>
                    <?= Button::create(_('Eine Woche grafisch ausgeben'), 'start_graphical') ?>
                </td>
            </tr>
        </table>
    <?
    }

    public function exportScheduleList()
    {
        $room = ResourceObject::Factory($this->resource_id);
        $name = preg_replace('/\W/', '_', $room->getName());
        $stdout = fopen('php://output', 'w');
        $assign_events = new AssignEventList($this->start_time, $this->end_time, $this->resource_id, '', '', true);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; ' . encode_header_parameter('filename', $name . '.csv'));
        header('Pragma: public');

        while ($event = $assign_events->nextEvent()) {
            $date_begin = strftime('%d.%m.%Y %H:%M', $event->getBegin());
            $date_end   = strftime('%d.%m.%Y %H:%M', $event->getEnd());
            $sem_nr     = '';
            $info       = trim($event->getName(Config::get()->RESOURCES_SCHEDULE_EXPLAIN_USER_NAME));

            if ($event->getOwnerType() === 'date') {
                $sem_obj = Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
                $sem_nr = $sem_obj->getNumber();
                $date = new SingleDate($event->getAssignUserId());

                $dozenten = array_intersect_key(
                    $sem_obj->getMembers('dozent'),
                    array_flip($date->getRelatedPersons())
                );
                $sem_doz_names = array_map(function ($a) {
                    return $a['Nachname'];
                }, array_slice($dozenten, 0, 3, true));

                $info .= ' (' . join(', ' , $sem_doz_names) . ')';
            }

            fputcsv($stdout, [$date_begin, $date_end, $sem_nr, $info], ';');
        }
    }

    public function showScheduleList($print_view = false)
    {
        global $view_mode;

        //select view to jump from the schedule
        if ($this->used_view === 'openobject_schedule' && Context::get()) {
           $view = 'openobject_assign';
        } else {
           $view = 'edit_object_assign';
        }
        ?>
        <table class="default nohover">
            <colgroup>
                <col width="4%">
                <col width="96%">
            </colgroup>
            <tr>
                <td>&nbsp;</td>
                <td align="center">
                    <b>
                    <?
                    if ($print_view){
                        $room = ResourceObject::Factory($this->resource_id);
                        echo htmlReady($room->getName().' - ' .$this->semester['name']);
                    } else {
                        if ($this->semester){
                            printf(_("Anzeige des Semesters: %s"), htmlReady($this->semester['name']));
                        } else {
                            echo _("Anzeige des Zeitraums:");
                        }
                    }
                    echo '<br>' . date ("d.m.Y", $this->start_time), " - ", date ("d.m.Y", $this->end_time);
                    ?>
                    </b>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <?
                    $assign_events=new AssignEventList ($this->start_time, $this->end_time, $this->resource_id, '', '', TRUE);
                    echo "<br><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
                    echo "<br><br>";
                    $num = 1;
                    while ($event = $assign_events->nextEvent()) {
                        $add_info = '';
                        if ($event->getOwnerType() == 'date') {
                                $sem_obj = Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
                                $date = new SingleDate($event->getAssignUserId());
                                $dozenten = array_intersect_key($sem_obj->getMembers('dozent'), array_flip($date->getRelatedPersons()));
                                $sem_doz_names = array_map(function ($a) {
                                    return $a['Nachname'];
                                }, array_slice($dozenten, 0, 3, true));
                                $add_info = '(' . join(', ', $sem_doz_names) . ')';
                        }
                        if (!$print_view){
                            echo LinkButton::create(_('Eigenschaften'), URLHelper::getURL('?quick_view='
                                . $view . '&quick_view_mode=' . $quick_view_mode . '&edit_assign_object=' . $event->getAssignId()));
                        } else {
                            echo '<font size=-1>' . sprintf("%02d", $num++) . '.';
                        }
                        printf ("&nbsp;"
                                ._("Belegung ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")
                                ."</font><br>", strftime("%A, %d.%m.%Y %H:%M", $event->getBegin())
, strftime("%A, %d.%m.%Y %H:%M", $event->getEnd())
, $event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')) . $add_info);
                    }
                    ?>
                </td>
            </tr>
        </table>
        </form>
        <br><br>
    <?
    }

    /**
     * Returns the event categories.
     * @return array categories
     */
    private function getCategories()
    {
        $categories['na'] = 4;
        $categories['sd'] = 4;
        $categories['y']  = 3;
        $categories['m']  = 3;
        $categories['w']  = 0;
        $categories['d']  = 2;

        //an assign for a date corresponding to a (seminar-)metadate
        $categories['meta'] = 1;

        return $categories;
    }

    public function showScheduleGraphical($print_view = false)
    {
        global $view_mode, $ActualObjectPerms;

        $categories = $this->getCategories();

        //match start_time & end_time for a whole week
        $dow = date ("w", $this->start_time);
        if ($dow > 1) {
            $offset = 1 - $dow;
        }
        if ($dow < 1) {
            $offset = -6;
        }

        //select view to jump from the schedule
        if ($this->used_view === 'openobject_schedule' && Context::get()) {
            $view = 'openobject_assign';
        } else {
            $view = 'edit_object_assign';
        }

        $start_time = mktime(0, 0, 0,
                             date('n', $this->start_time),
                             date('j', $this->start_time) + $offset + $this->week_offset * 7,
                             date('Y', $this->start_time));
        $end_time = mktime(23, 59, 59,
                           date('n', $start_time),
                           date('j', $start_time) + 6,
                           date('Y', $start_time));

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

        $schedule = new ScheduleWeek($start_hour, $end_hour, FALSE, $start_time, true);

        if ($ActualObjectPerms->havePerm('autor')) {
            $schedule->add_link = "resources.php?cancel_edit_assign=1&quick_view=$view&quick_view_mode=".$view_mode."&add_ts=";
        }

        //fill the schedule
        $assign_events=new AssignEventList ($start_time, $end_time, $this->resource_id, '', '', TRUE, $_SESSION['resources_data']["show_repeat_mode"]);
        while ($event=$assign_events->nextEvent()) {
            $repeat_mode = $event->getRepeatMode(TRUE);
            $add_info = '';
            if ($event->getOwnerType() == 'date') {
                $sem_obj = Seminar::GetInstance(Seminar::GetSemIdByDateId($event->getAssignUserId()));
                $date = new SingleDate($event->getAssignUserId());
                $dozenten = array_intersect_key($sem_obj->getMembers('dozent'), array_flip($date->getRelatedPersons()));
                $sem_doz_names = array_map(function ($a) {
                    return $a['Nachname'];
                }, array_slice($dozenten, 0, 3, true));
                $add_info = '(' . join(', ', $sem_doz_names) . ')';
            }
            $schedule->addEvent(null, $event->getName(get_config('RESOURCES_SCHEDULE_EXPLAIN_USER_NAME')), $event->getBegin(), $event->getEnd(),
                        URLHelper::getLink('?cancel_edit_assign=1&quick_view=' . $view . '&quick_view_mode='.$view_mode.'&edit_assign_object='.$event->getAssignId()), $add_info, $categories[$repeat_mode]);
        }
        ?>
        <table class="default nohover">
            <colgroup>
                <col width="4%">
                <col width="10%">
                <col width="76%">
                <col width="10%">
            </colgroup>
            <tr>
                <td>&nbsp;</td>
                <td align="left">
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&previous_week=TRUE') ?> ">
                        <?= Icon::create('arr_2left', 'clickable', ['title' => _("Vorherige Woche anzeigen")])->asImg(16, ["alt" => _("Vorherige Woche anzeigen"), "border" => 0]) ?>
                    </a>
                </td>
                <td align="center" style="font-weight:bold">
                    <? printf(_("Anzeige der Woche vom %s bis %s (KW %s)"), strftime("%x", $start_time), strftime("%x", $end_time),strftime("%V", $start_time));?>
                    <br>
                    <?php
                    $this->showSemWeekNumber($start_time);
                    ?>
                    <br>
                    <?php
                    $room = ResourceObject::Factory($this->resource_id);
                    echo "Raum: ".htmlReady($room->getName());
                    ?>
                </td>
                <td align="center">
                    <a href="<?= URLHelper::getLink('?quick_view='.$this->used_view.'&quick_view_mode='.$view_mode.'&next_week=TRUE')?>"><?= Icon::create('arr_2right', 'clickable', ['title' => _("Nächste Woche anzeigen")])->asImg(16, ["alt" => _("Nächste Woche anzeigen"), "border" => 0]) ?></a>
                </td>
            </tr>
            <tr>
                <td class="hidden" align="center" valign="bottom">&nbsp;
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
                    echo "&nbsp;<font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font><br>&nbsp;";
                    ?>
                </td>
                <td nowrap>
                    <?
                    print "<select style=\"font-size:10px;\" name=\"show_repeat_mode\">";
                    printf ("<option style=\"font-size:10px;\" %s value=\"all\">"._("alle Belegungen")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "all") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"single\">"._("nur Einzeltermine")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "single") ? "selected" : "");
                    printf ("<option %s style=\"font-size:10px;\" value=\"repeated\">"._("nur Wiederholungstermine")."</option>", ($_SESSION['resources_data']["show_repeat_mode"] == "repeated") ? "selected" : "");
                    print "</select>";
                    print "&nbsp;" . Icon::create('accept', 'accept', ['title' => _("Ansicht umschalten")])->asInput(["type" => "image", "class" => "middle", "name" => "send_schedule_repeat_mode"]);
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
                <td class="hidden" align="center" valign="bottom">
                <? if ((!$_SESSION['resources_data']['schedule_time_range']) || ($_SESSION['resources_data']['schedule_time_range'] == -1)): ?>
                    <a href="<?= URLHelper::getLink('', array('quick_view' => $this->used_view,
                                                              'quick_view_mode' => $view_mode,
                                                              'time_range' => $_SESSION['resources_data']['schedule_time_range'] ? 'FALSE' : 1)) ?>">
                        <?= Icon::create('arr_2down', 'clickable', ['title' => _('Spätere Belegungen anzeigen')])->asImg() ?>
                    </a>
                <? endif; ?>
                </td>
                <td nowrap colspan="3">&nbsp;</td>
            </tr>

        </table>
        </form>
    <?
    }

    /**
     * Displays the event category legend as a SidebarWidget
     */
    public function ShowLegend()
    {
        $schedule = new ScheduleWeek();
        $cats = $schedule->categories;
        $eventcat_names = array(
            'na'   => _('Keine'),
            'd'    => _('Täglich'),
            'w'    => ucfirst(_('wöchentlich')),
            'sd'   => _('Mehrtägig'),
            'm'    => _('Monatlich'),
            'y'    => _('Jährlich'),
            'meta' => _('Einzeltermin zu regelmäßigen Veranstaltungszeiten')
        );
        $sidebar = Sidebar::get();
        $legende_widget = new SidebarWidget();
        $legende_widget->setTitle(_('Art der Wiederholung'));
        $html = '<div class="legende">';
        foreach ($this->getCategories() as $event_cat => $schedule_cat) {
            $cat = $cats[$schedule_cat];
            $html .= '<div style="
                                    background-color: ' . $cat['border-color'] . ';
                                    background-image: url(' . $cat['bg-picture'] . ');
                                    height: 8px; width: 16px;
                                    border-style: solid;
                                    border-width: 1px;
                                    border-top-width: 5px;
                                    border-color: ' . $cat['border-color'] . ';
                                    display: inline-block;
                    " ></div>';
            $html .= '<div style="margin-left: 5px; display: inline-block; width: calc(100% - 23px); vertical-align: top;">'
                  . htmlReady(!empty($eventcat_names[$event_cat])?$eventcat_names[$event_cat]: $event_cat)
                  . '</div>';
            $html .= '<br>';
        }
        $html .= '</div>';
        $legende_widget->addElement(new WidgetElement($html));
        $sidebar->addWidget($legende_widget);
    }

    public function showSemWeekNumber($start_time)
    {
        $semester = Semester::FindByTimestamp($start_time);
        if ($semester) {
            echo htmlready($semester['name']) . ' - ';
            $sem_week_number = $semester->getSemWeekNumber($start_time);
            if (is_int($sem_week_number)) {
                printf(_('%s. Vorlesungswoche'), $sem_week_number);
            } else {
                echo _('vorlesungsfreie Zeit');
            }
        } else {
            echo _('kein Semester verfügbar');
        }
    }
}
