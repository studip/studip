<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* persons_preview.inc.php
* 
* 
* 
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       persons_preview
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// persons_preview.inc.php
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

$data_name = _("Name Name");
$data_room = _("Raum 21");
$data_office_hours = _("jeden Tag, 13.00 - 14.00");
$group_data[] = ["group_name" => _("Gruppe A"), "persons" => [
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"],
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-895638", "email" => "name.name@email.com"],
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"]]];
$group_data[] =  ["group_name" => _("Gruppe B"), "persons" => [
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"],
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"],
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"]]];
$group_data[] =  ["group_name" => "Gruppe C", "persons" => [
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"],
["name" => $data_name, "raum" => $data_room, "sprechzeiten" => $data_office_hours,
        "telefon" => "38-374982", "email" => "name.name@email.com"]]];

$repeat_headrow = $this->config->getValue("Main", "repeatheadrow");
$order = $this->config->getValue("Main", "order");
$width = $this->config->getValue("Main", "width");
$alias = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
    $percent = "%";
else
    $percent = "";
$group_colspan = array_count_values($visible);
$grouping = $this->config->getValue("Main", "grouping");

$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");

$set_td_1 = $this->config->getAttributes("TableRow", "td");
$set_td_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra_td = $this->config->getValue("TableRow", "td_zebratd_");

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

$first_loop = TRUE;
foreach ($group_data as $groups) {
    $statusgruppe = $groups["group_name"];
    
    if ($grouping && $repeat_headrow == "beneath") {
    echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
        echo "<td colspan=\"{$group_colspan['1']}\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
    echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
        echo $statusgruppe . "</font>\n</td></tr>\n";
    }
    
    if ($first_loop || ($grouping && $repeat_headrow)) {
        echo "<tr" . $this->config->getAttributes("TableHeadrow", "tr") . ">\n";
        $i = 0;
        reset($order);
        foreach ($order as $column) {
        
            // "zebra-effect" in head-row
            if ($zebra) {
                if ($i % 2)
                    $set = $set_2;
                else
                    $set = $set_1;
            }
            else
                $set = $set_1;
            
            if ($visible[$column]) {
            echo "<th$set width=\"" . $width[$column] . $percent . "\">\n";
                echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . ">";
                if ($alias[$column])
                    echo $alias[$column];
                else
                    echo "&nbsp;";
                echo "</font>\n</th>\n";
            }
            $i++;
        }
        echo "</tr>\n";
    }
    
    if ($grouping && $repeat_headrow != "beneath") {
    echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
        echo "<td colspan=\"{$group_colspan['1']}\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
    echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
        echo $statusgruppe . "</font>\n</td></tr>\n";
    }
    $first_loop = FALSE;
    
    $i = 0;
    foreach ($groups["persons"] as $data) {
    
        $wert_daten = [
            "Nachname"         => sprintf("<a href=\"\"%s><font%s>%s</font></a>",
                                                $this->config->getAttributes("LinkIntern", "a"),
                                                $this->config->getAttributes("LinkIntern", "font"),
                                                htmlReady($data["name"])),
                                                
            "Telefon"      => sprintf("<font%s>%s</font>",
                                                $this->config->getAttributes("TableRow", "font"),
                                                htmlReady($data["telefon"])),
            
            "sprechzeiten" => sprintf("<font%s>%s</font>",
                                                $this->config->getAttributes("TableRow", "font"),
                                                htmlReady($data["sprechzeiten"])),
            
            "raum"         => sprintf("<font%s>%s</font>",
                                                $this->config->getAttributes("TableRow", "font"),
                                                htmlReady($data["raum"])),
            
            "Email"       => sprintf("<a href=\"mailto:%s\"%s><font%s>%s</font></a>",
                                                $data["email"],
                                                $this->config->getAttributes("Link", "a"),
                                                $this->config->getAttributes("Link", "font"),
                                                $data["email"])
        ];
        
        // "horizontal zebra"
        if ($zebra_td == "HORIZONTAL") {
            if ($i % 2)
                $set_td = $set_td_2;
            else
                $set_td = $set_td_1;
        }
        else
            $set_td = $set_td_1;
        
        echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">";
        
        $j = 0;
        foreach ($order as $column) {
            if ($visible[$column]) {
                
                // "vertical zebra"
                if ($zebra_td == "VERTICAL") {
                    if ($j % 2)
                        $set_td = $set_td_2;
                    else
                        $set_td = $set_td_1;
                }
            
                echo "<td$set_td>";
                if ($wert_daten[$this->data_fields[$column]])
                echo $wert_daten[$this->data_fields[$column]];
                else
                    echo "&nbsp";
                echo "</td>\n";
                $j++;
            }
        }
        echo "</tr>\n";
        $i++;
    }
}
    
echo "</table>\n";

?>
