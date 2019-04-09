<?
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementMainNewsticker.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementMainNewsticker
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainNews.class.php
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


class ExternElementMainNewsticker extends ExternElementMain {

    /**
    * Constructor
    *
    */
    function __construct($module_name, &$data_fields, &$field_names, &$config) {
        $this->attributes = [
                'name', 'rows', 'length', 'pause', 'frequency',
                'starttext', 'endtext', 'nodatatext', 'automaticstart', 'jsonly', 'style'];
        $this->real_name = _("Grundeinstellungen");
        $this->description = _("In den Grundeinstellungen können Sie allgemeine Daten des Moduls ändern.");
        parent::__construct($module_name, $data_fields, $field_names, $config);
    }

    /**
    *
    */
    function getDefaultConfig () {

        $config = [
            "name" => "",
            "rows" => "3",
            "length" => "40",
            "pause" => "2000",
            "frequency" => "15",
            "starttext" => _("Der Ticker wird geladen..."),
            "endtext" => _("Ende des Tickers."),
            "nodatatext" => _("Keine aktuellen News"),
            "automaticstart" => "1",
            "style" => ""
        ];

        return $config;
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $headline = $edit_form->editHeadline(_("Name der Konfiguration"));
        $table = $edit_form->editName("name");
        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $content_table .= $this->getSRIFormContent($edit_form);

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $title = _("Tick-Frequenz:");
        $info = _("Geben Sie an, wieviele Zeichen pro Sekunde ausgegeben werden sollen.");
        $table = $edit_form->editTextfieldGeneric("frequency", $title, $info, 2, 2);

        $title = _("Pause zwischen News:");
        $info = _("Geben Sie an, wie lange der Ticker warten soll (in Millisekunden), bis er die nächste News ausgibt.");
        $table .= $edit_form->editTextfieldGeneric("pause", $title, $info, 4, 4);

        $title = _("Text am Anfang der Ausgabe:");
        $info = _("Dieser Text wird ausgegeben, während die News in den Ticker geladen werden, also am Anfang des ersten Durchlaufs.");
        $table .= $edit_form->editTextfieldGeneric("starttext", $title, $info, 50, 200);

        $title = _("Text am Ende der Ausgabe:");
        $info = _("Dieser Text wird ausgegeben, nachdem alle News angezeigt wurden, also am Ende jedes Durchlaufs.");
        $table .= $edit_form->editTextfieldGeneric("endtext", $title, $info, 50, 200);

        $title = _("Keine News:");
        $info = _("Dieser Text wird ausgegeben, wenn keine News verfügbar sind.");
        $table .= $edit_form->editTextfieldGeneric("nodatatext", $title, $info, 50, 200);

        $title = _("Ticker sofort starten?");
        $info = _("Wählen Sie diese Option, wenn das Modul den Ticker automatisch starten soll. Bei längeren Ladezeiten der Seite, in der Sie den Ticker integriert haben, kann es sinnvoll sein, den Ticker erst zu starten, wenn die Seite komplett geladen ist. Deaktivieren Sie dafür diese Option, und tragen Sie im <body>-Tag der Seite das Attribut onLoad=\"newsticker\" ein.");
        $table .= $edit_form->editCheckboxGeneric("automaticstart", $title, $info, "1", "");

        $title = _("Nur JavaScript-Funktion ausgeben?");
        $info = _("Wählen Sie diese Option, wenn das Modul nur die JavaScript-Funktion ausgeben soll. Die Funktionsname ist newsticker(). Sie kann z.B. innerhalb von <textarea> eingesetzt werden. Beispiel:");
        $info .= "\n<!DOCTYPE html>\n";
        $info .= "<html>\n\t<head>\n\t\t";
        $info .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
        $info .= "\t\t<title>Newsticker</title>\n\t</head>\n";
        $info .= "\t\t<script src=\"Link to SRI-Interface for newsticker. Look at info-page (i).\" type=\"text/javascript\">\n";
        $info .= "\t<body>\n\t\t<form name=\"tickform\">\n\t\t\t";
        $info .= "<textarea name=\"tickfield\" rows=\"5\" cols=\"50\">Loading ticker...</textarea>\n";
        $info .= "\t\t</form>\n\t\t<script type=\"text/javascript\">\n\t\t\t";
        $info .= "newsticker();\n\t\t</script>\n\t</body>\n</html>";
        $table .= $edit_form->editCheckboxGeneric("jsonly", $title, $info, "1", "");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Angaben zum HTML-Tag &lt;textarea&gt;"));

        $title = _("Anzahl Zeilen im Ausgabefenster:");
        $info = _("Geben Sie die Anzahl der Zeilen an. Es sind nur Werte zwischen 1 und 10 erlaubt.");
        $table = $edit_form->editTextfieldGeneric("rows", $title, $info, 2, 2);

        $title = _("Anzahl der Zeichen pro Zeile:");
        $info = _("Geben Sie die Anzahl der Zeichen pro Zeile an. Es sind nur Werte zwischen 10 und 200 erlaubt.");
        $table .= $edit_form->editTextfieldGeneric("length", $title, $info, 3, 3);

        $table .= $edit_form->editStyle("style");

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        switch ($attribute) {
            case "rows" :
                return !(preg_match("'^\d{1,2}$'", $value) && $value > 0 && $value < 11);
            case "length" :
                return !(preg_match("'^\d{1,3}$'", $value) && $value > 9 && $value < 201);
            case "automaticstart" :
            case "jsonly" :
                if (!isset($_POST[$this->name . "_" . $attribute])) {
                    $_POST[$this->name . "_" . $attribute] = 0;
                    return FALSE;
                }
                return !($value == "1" || $value == "0");
            case "frequency" :
                return !(preg_match("'^\d{1,2}$'", $value) && $value > 0);
            case "pause" :
                return !(preg_match("'^\d{1,4}$'", $value) && $value > 0);
            case "starttext" :
            case "endtext" :
            case "nodatatext" :
                return mb_strlen($value) > 200;
        }

        return FALSE;
    }

}

?>
