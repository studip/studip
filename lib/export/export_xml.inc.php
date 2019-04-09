<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Export-subfile that calls export-functions to create a xml-file.
*
* This file call export_range to get a xml-strem with the wanted data.
* Depending on $o_mode it passes the xml-stream into a file or to the screen.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_xml
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_xml.inc.php
// XML-functions for the Stud.IP database
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de>
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

use Studip\Button, Studip\LinkButton;

if (($o_mode != "direct") AND ($o_mode != "passthrough"))
    $perm->check("tutor");

$export_pagename = _("Datenexport");
require_once 'lib/export/export_xml_vars.inc.php';   // XML-Variablen
require_once 'lib/export/export_xml_func.inc.php';   // XML-Funktionen
require_once 'lib/export/export_studipdata_func.inc.php';   // Studip-Export-Funktionen
require_once 'lib/dates.inc.php';   // Datumsfunktionen

/**
* Checks given parameters
*
* This function checks the given parameters. If some are missing
* it returns false and adds a warning to $export_error.
*
* @access   public
* @return       boolean
*/
function CheckParamXML()
{
global $range_id, $ex_type, $xml_file_id, $o_mode, $export_error, $export_error_num, $export_o_modes, $export_ex_types,$ex_person_details,$ex_tstamp,$ex_hash,$perm;

    if ($ex_person_details && $ex_tstamp && !$perm->have_perm('admin')){
        list($y,$M,$d,$h,$m) = explode('-', $ex_tstamp);
        $tstamp = mktime($h,$m,0,$M,$d,$y);
        $hash = md5(get_config('UNIZENSUSPLUGIN_SHARED_SECRET1') . $ex_tstamp . get_config('UNIZENSUSPLUGIN_SHARED_SECRET2'));
        if ($ex_hash != $hash || $tstamp < (time() - 600)) $ex_person_details = null;
    }

    if ((($range_id != "") OR ($xml_file_id != "")) AND (in_array($o_mode, $export_o_modes) AND (in_array($ex_type, $export_ex_types))))
        return true;
    $export_error .= "<b>" . _("Unzulässiger Seitenaufruf!") . "</b><br>";
    $export_error_num++;
    return false;
}


if (!CheckParamXML()) {
    PageLayout::postError(
        _('Die Parameter, mit denen diese Seite aufgerufen wurde, sind fehlerhaft.')
    );
}


if ($ex_sem == 'current'){
    $ex_sem = Semester::findCurrent()->getId();
}

if ($o_mode != "direct")
{
    if (!(is_dir( $TMP_PATH."/export" )))
    {
        mkdir($TMP_PATH . "/export");
        chmod ($TMP_PATH . "/export", 0777);
    }
    $xml_file_id = md5(uniqid(rand())) . ".xml";
    $xml_file = fopen($TMP_PATH."/" . $xml_file_id, "w");
}

if ($o_mode == 'direct'){
    header("Content-type: text/xml; charset=utf-8");
    if ($export_error_num){
        echo '<?xml version="1.0"?>' . chr(10);
        echo xml_tag('studip_export_error_msg', strip_tags($export_error));
        exit();
    }
}

export_range( $range_id );



if ($o_mode !== "direct") {
    fclose($xml_file);
}

if ($o_mode === 'file' || $o_mode === 'choose') {

    if ($object_counter<1) {
        $xml_export_text = _("Es wurden keine Daten gefunden!");
        $export_error = _("Es wurden keine Daten gefunden! Die übergebene ID ist mit keinen Veranstaltungs- / Personendaten verbunden.");
        $export_pagecontent .= "<form class=\"default\"><footer>"
                            . LinkButton::create('<< ' .  _('Zurück'), URLHelper::getURL("", ['range_id' => $range_id, 'ex_type' => $ex_type, 'ex_sem' => $ex_sem, 'o_mode' => 'start']))
                            . "</footer></form>";
        $export_error_num ++;

    } else {
        $xml_export_text = _("Die Daten wurden erfolgreich exportiert.");
        if ($object_counter == 1) {
            $export_msg = sprintf(_("%s Objekt wurde verarbeitet.") . " ", $object_counter);
        } else {
            $export_msg = sprintf(_("%s Objekte wurden verarbeitet.") . " ", $object_counter);
        }

        $export_pagecontent .= "<form class=\"default\" method=\"POST\" action=\"" . URLHelper::getLink() . "\">";
        $export_pagecontent .= CSRFProtection::tokenTag();
        $export_pagecontent .= "<input type=\"hidden\" name=\"page\" value=\"2\">";
        $export_pagecontent .= "<input type=\"hidden\" name=\"format\" value=\"" . htmlReady($format) . "\">";
        $export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";
        $export_pagecontent .= "<input type=\"hidden\" name=\"ex_type\" value=\"" . htmlReady($ex_type) . "\">";
        $export_pagecontent .= "<input type=\"hidden\" name=\"ex_sem\" value=\"" . htmlReady($ex_sem) . "\">";
        foreach (array_keys($ex_sem_class) as $semclassid) {
            $export_pagecontent .= "<input type=\"hidden\" name=\"ex_sem_class[". htmlReady($semclassid) ."]\" value=\"1\">";
        }
        $export_pagecontent .= "<input type=\"hidden\" name=\"range_id\" value=\"" . htmlReady($range_id) . "\">";
        $export_pagecontent .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"" . htmlReady($xslt_filename) . "\">";

        $export_weiter_button = "<footer>" . Button::create('<< ' . _('Zurück'), 'back') . "</footer></form>";
        $xml_printimage = '<a href="'. FileManager::getDownloadLinkForTemporaryFile($xml_file_id, $xml_filename) .  '">';
        $xml_printimage.= Icon::create($export_icon['xml'], 'clickable');
        $xml_printimage.= '</a>';
        $xml_printlink = '<a href="'. FileManager::getDownloadLinkForTemporaryFile($xml_file_id, $xml_filename). '" class="tree">' . htmlReady($xml_filename) . '</a>';
        $xml_printdesc = _("XML-Daten");
        $xml_printcontent = _("In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags können mit einem XSLT-Script verarbeitet werden.") . "<br>";
    }

    PageLayout::postInfo($xml_export_text);

    if ($object_counter > 0) {
        $link = '<a href="'. FileManager::getDownloadLinkForTemporaryFile($xml_file_id, $xml_filename) .' ">';
        PageLayout::postSuccess(sprintf(
            _('Um die XML-Datei jetzt herunterzuladen klicken Sie %s hier %s.'),
            $link, '</a>'
        ));
    }
}
