<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: DONE - not applicable
/**
* eval_summary_export.php
*
* PDF-Export fuer Eval-Auswertungen
*
*
* @author               Jan Kulmann <jankul@zmml.uni-bremen.de>
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// eval_summary_export.php
// Copyright (C) 2007 Jan Kulmann <jankul@zmml.uni-bremen.de>
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


require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);

$perm->check('user');

$EVAL_AUSWERTUNG_GRAPH_FORMAT = Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT ?: 'gif';

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once 'vendor/phplot/phplot.php';
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_OBJECTDB;
require_once 'lib/export/export_tmp_gc.inc.php';
require_once 'lib/export/export_xml_func.inc.php';

// Start of Output
$eval_id = Request::option('eval_id');

// Überprüfen, ob die Evaluation existiert oder der Benutzer genügend Rechte hat
$eval = new Evaluation($eval_id);
$eval->check();
if (EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval) == YES || count($eval->errorArray) > 0) {
    throw new Exception(_("Diese Evaluation ist nicht vorhanden oder Sie haben nicht ausreichend Rechte!"));
}

$tmp_path_export = $GLOBALS['TMP_PATH'];
export_tmp_gc();

// Template vorhanden?
$eval_templates = [];
$has_template   = 0;

$pattern = ["'<img[\s]+[^>]*?src[\s]?=[\s\"\']+(.*?)[\"\']+.*?>'si"];
$replace = ["<fo:external-graphic src=\"url(\\1)\"/>"];


function do_template($column)
{
    global $has_template, $eval_templates;

    return ($has_template==0 || ($has_template==1 && $eval_templates[$column]));
}

/**
 * returning the type of the graph
 *
 * @return string
 */
function do_graph_template()
{
    global $eval_templates, $has_template, $question_type;

    if ($has_template == 1) {
        if ($question_type == 'likertskala') {
            return $eval_templates['likertscale_gfx_type'];
        }
        if ($question_type == 'multiplechoice') {
            return $eval_templates['mchoice_scale_gfx_type'];
        }
        if ($question_type == 'polskala') {
            return $eval_templates['polscale_gfx_type'];
        }
    }
    return 'bars';
}

/**
 * drawing the graph for a evaluation question
 *
 * @param array() $data
 * @param string $evalquestion_id
 */
function do_graph($data, $evalquestion_id)
{
    global $tmp_path_export, $auth;

    $type = do_graph_template();

    //Define the object
    if ($type == "pie") {
        // Beim pie muss die Zeichenflaeche etwas groesser gewaehlt werden...
        $graph = new PHPlot(500,300);
    } else {
        $graph = new PHPlot(300,250);
    }

    if ($type == "pie") {
        // Beim pie muss das Array umgeformt werden. Bug in PHPlot?
        $tmp = [];
        $tmp2 = [];
        $legend = [];
        array_push($tmp,"Test");
        foreach($data as $k=>$d) {
            array_push($tmp, $d[1]);
            array_push($legend, $d[0]);
        }
        array_push($tmp2, $tmp);
        $data = $tmp2;
        $graph->SetLegend($legend);
    }

    //Data Colors
    $graph->SetDataColors(
        ["blue", "green", "yellow", "red", "PeachPuff", "orange", "pink", "lavender",
            "navy", "peru", "salmon", "maroon", "magenta", "orchid", "ivory"],
        ["black"] //Border Colors
    );

    if(!empty($data)) {
        $max_x = max(array_map('next',$data));
        $graph->SetPlotAreaWorld(NULL, 0); // y-achse bei 0 starten
        $graph->SetPrecisionY(0); //anzahl kommastellen y-achse
        $graph->SetYTickIncrement($max_x < 10 ? 1 : round($max_x/10));
        $graph->SetPlotBgColor([222,222,222]);
        $graph->SetDataType("text-data");
        $graph->SetFileFormat(Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT);
        $graph->SetOutputFile($tmp_path_export."/evalsum".$evalquestion_id.$auth->auth["uid"].".".Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT);
        $graph->SetIsInline(true);
        $graph->SetDataValues($data);
        $graph->SetPlotType($type);
        $graph->SetXLabelAngle(count($data) < 10 ? 0 : 90);
        //$graph->SetShading(0); // kein 3D

        $graph->SetLineWidth(1);
        $graph->SetDrawXDataLabels(true);
        //Draw it
        $graph->DrawGraph();
    }
}

function freetype_answers ($parent_id, $anz_nutzer) {
    global $fo_file, $pattern, $replace;

    $query = "SELECT `text`
              FROM evalanswer
              INNER JOIN evalanswer_user USING(evalanswer_id)
              WHERE parent_id = ? AND `text` != ''
              ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);

    while ($answer = $statement->fetchColumn()) {
        $counter++;
        fputs($fo_file,"                <fo:table-row>\n");
        fputs($fo_file,"                  <fo:table-cell ><fo:block linefeed-treatment=\"preserve\" font-size=\"8pt\">".$counter.". ".preg_replace($pattern,$replace,smile(xml_escape($answer)))."</fo:block></fo:table-cell>\n");
        fputs($fo_file,"                </fo:table-row>\n");
    }
    fputs($fo_file,"                <fo:table-row>\n");
    fputs($fo_file,"                  <fo:table-cell ><fo:block font-size=\"8pt\">"._("Anzahl der Teilnehmenden").": ".$anz_nutzer."</fo:block></fo:table-cell>\n");
    fputs($fo_file,"                </fo:table-row>\n");
}

function user_answers_residual($parent_id)
{
    $query = "SELECT COUNT(*)
              FROM evalanswer
              JOIN evalanswer_user USING (evalanswer_id)
              WHERE parent_id = ? AND residual = 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);
    return $statement->fetchColumn();
}

function user_answers($evalanswer_id)
{
    $query = "SELECT COUNT(*) FROM evalanswer_user WHERE evalanswer_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$evalanswer_id]);
    return $statement->fetchColumn();
}

function answers ($parent_id, $anz_nutzer, $question_type) {
    global $graph_switch, $auth, $ausgabeformat, $fo_file, $has_template, $pattern, $replace;

     // Rueckgabearray, damit die Daten noch aufzutrennen sind...
        $ret_array = ["id"=>$parent_id,                         // Question-ID
                           "txt"=>"",                                // HTML-Ausgabe
                           "antwort_texte"=>[],                 // Antwort-Texte
                           "frage"=>"",                              // Frage-Text
                           "has_residual"=>0,                        // Enthaltungen?
                           "antwort_durchschnitt"=>"",               // Antwort-Durchschnitt
                           "summe_antworten"=>"",                    // Summe der Antworten
                           "anzahl_teilnehmer"=>$anz_nutzer,         // Anzahl der Teilnehmer dieser Frage
                           "auswertung"=>[]                     // 1. Anzahl der Antworten zu einer Antwort
                                                                     // 2. Prozente einer Antwort
                                                                     // 3. Prozente einer Antwort ohne Enthaltungen
                          ];

    $summary =  [];

    $query = "SELECT COUNT(*)
              FROM evalanswer
              JOIN evalanswer_user USING (evalanswer_id)
              WHERE parent_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);
    $answers_sum = $statement->fetchColumn();

    $antwort_nummer = 0;
    $gesamte_antworten = 0;
    $edit = "";
    $txt = "";
    $antwort_durchschnitt = 0;
    $has_residual = user_answers_residual($parent_id);

    $query = "SELECT evalanswer_id, `text`, value, residual FROM evalanswer WHERE parent_id = ? ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);
    while ($answer = $statement->fetch(PDO::FETCH_ASSOC)) {
        $antwort_nummer++;
        $answer_counter = user_answers($answer['evalanswer_id']);
        if ($answer['residual'] == 0) {
            $gesamte_antworten += $answer_counter;
            $antwort_durchschnitt += $answer_counter * $antwort_nummer;
        }
        $prozente = 0;
        if ($answers_sum>0) $prozente = ROUND($answer_counter*100/$anz_nutzer);
        $prozente_wo_residual = 0;
        if ($has_residual && ($answers_sum-$has_residual)>0) $prozente_wo_residual = ROUND($answer_counter*100/($anz_nutzer-$has_residual));
        $edit .= "                <fo:table-row>\n";
        $edit .= "                  <fo:table-cell ><fo:block font-size=\"8pt\">".$antwort_nummer.". ". xml_escape(($answer['text']!="" ? $answer['text'] : $answer['value']))."</fo:block></fo:table-cell>\n";

        if ($has_residual) $edit .= "                  <fo:table-cell ><fo:block font-size=\"8pt\">".$answer_counter." (".$prozente."%) ".($answer['residual'] == 0 ? "(".$prozente_wo_residual."%)*" : "" )."</fo:block></fo:table-cell>\n";
        else $edit .= "                  <fo:table-cell ><fo:block font-size=\"8pt\">".$answer_counter." (".$prozente."%)</fo:block></fo:table-cell>\n";
        $edit .= "                </fo:table-row>\n";

        array_push($summary, [$antwort_nummer."(".$prozente."%)",$answer_counter]);

        array_push($ret_array["antwort_texte"], ($answer['text'] != "" ? $answer['text'] : $answer['value']));
                array_push($ret_array["auswertung"], [$answer_counter, $prozente, ($answer['residual']==0 ? $prozente_wo_residual : null)]);
                if ($has_residual) $ret_array["has_residual"] = 1;

    }
    do_graph($summary, $parent_id);

    if ($gesamte_antworten > 0 && $antwort_durchschnitt > 0) $antwort_durchschnitt = ROUND($antwort_durchschnitt / $gesamte_antworten, 3);

    $ret_array["antwort_durchschnitt"] = $antwort_durchschnitt;
        $ret_array["summe_antworten"] = $gesamte_antworten;

    $txt .= $edit;

    if ($question_type=="multiplechoice") {
        $txt .= "                <fo:table-row>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\">"._("Anzahl der Teilnehmenden").": ".$anz_nutzer."</fo:block></fo:table-cell>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"></fo:block></fo:table-cell>\n";
        $txt .= "                </fo:table-row>\n";

        $txt .= "                <fo:table-row>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"><fo:inline font-family=\"Symbol\">&#x2211;</fo:inline> ".$gesamte_antworten." "._("Antworten").".</fo:block></fo:table-cell>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"></fo:block></fo:table-cell>\n";
        $txt .= "                </fo:table-row>\n";
    } else {
        $txt .= "                <fo:table-row>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\">"._("Anzahl der Teilnehmenden").": ".$anz_nutzer."</fo:block></fo:table-cell>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"></fo:block></fo:table-cell>\n";
        $txt .= "                </fo:table-row>\n";

        $txt .= "                <fo:table-row>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"><fo:inline font-family=\"Symbol\">&#x2205;</fo:inline>-"._("Antwort").": ".$antwort_durchschnitt.($has_residual==0 ? "" : "*")."</fo:block></fo:table-cell>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"><fo:inline font-family=\"Symbol\">&#x2211;</fo:inline> ".$gesamte_antworten." "._("Antworten").".</fo:block></fo:table-cell>\n";
        $txt .= "                </fo:table-row>\n";

    }

    if ($has_residual) {
        $txt .= "                <fo:table-row>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\">*"._("Werte ohne Enthaltungen").".</fo:block></fo:table-cell>\n";
        $txt .= "                  <fo:table-cell ><fo:block space-before.optimum=\"5pt\" font-size=\"8pt\"></fo:block></fo:table-cell>\n";
        $txt .= "                </fo:table-row>\n";
    }

    $ret_array["txt"] = $txt;

        return $ret_array;

}

function groups ($parent_id) {
    global $ausgabeformat, $fo_file, $auth, $global_counter, $local_counter, $tmp_path_export, $pattern, $replace;

    $query = "SELECT group_type FROM eval_group_template WHERE evalgroup_id = ?";
    $type_statement = DBManager::get()->prepare($query);

    $query = "SELECT LOCATE('Freitext', `text`) > 0 FROM evalquestion WHERE evalquestion_id = ?";
    $freetext_statement = DBManager::get()->prepare($query);

    $query = "SELECT evalquestion_id, `text`, type FROM evalquestion WHERE parent_id = ? ORDER BY position";
    $questions_statement = DBManager::get()->prepare($query);

    $query = "SELECT COUNT(DISTINCT user_id)
              FROM evalanswer
              JOIN evalanswer_user USING(evalanswer_id)
              WHERE parent_id = ?";
    $question_users_statement = DBManager::get()->prepare($query);

    $query = "SELECT evalgroup_id, child_type, title, template_id FROM evalgroup WHERE parent_id = ? ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);

    while ($group = $statement->fetch(PDO::FETCH_ASSOC)) {
        // Heraussuchen, ob es sich um ein Freitext-Template handelt...
        $freetext_statement->execute([$group['template_id']]);
        $freetype = $freetext_statement->fetchColumn();
        $freetext_statement->closeCursor();

        if ($group['child_type'] == 'EvaluationGroup') {
            $global_counter += 1;
            $local_counter   = 0;
            fputs($fo_file,"    <!-- Groupblock -->\n");
            fputs($fo_file,"    <fo:block font-variant=\"small-caps\" font-weight=\"bold\" text-align=\"start\" space-after.optimum=\"2pt\" background-color=\"lightblue\" space-before.optimum=\"10pt\">\n");
            if (do_template("show_group_headline"))
                fputs($fo_file,"      ".$global_counter.". ". xml_escape($group['title']) ."\n");
            fputs($fo_file,"    </fo:block>\n");
        } else {
            $local_counter += 1;

            $type_statement->execute([$group['evalgroup_id']]);
            $group_type = $type_statement->fetchColumn() ?: 'normal';
            $type_statement->closeCursor();

            fputs($fo_file,"    <!-- Questionblock -->\n");
            fputs($fo_file,"    <fo:block font-variant=\"small-caps\" font-weight=\"bold\" text-align=\"start\" background-color=\"grey\" color=\"white\" space-after.optimum=\"10pt\">\n");
            if (do_template("show_questionblock_headline"))
                fputs($fo_file,"      ".$global_counter.".".$local_counter.". ". xml_escape($group['title']) ."\n");
            fputs($fo_file,"    </fo:block>\n");
        }

        if ($group['child_type'] == 'EvaluationQuestion') {

            $local_question_counter = 0;
            $answer_arr = [];

            $questions_statement->execute([$group['evalgroup_id']]);
            while ($question = $questions_statement->fetch(PDO::FETCH_ASSOC)) {
                $question_users_statement->execute([$question['evalquestion_id']]);
                $question_users = $question_users_statement->fetchColumn();
                $question_users_statement->closeCursor();

                if ($group_type=="normal") {

                $local_question_counter += 1;
                fputs($fo_file,"    <!-- Question -->\n");
                fputs($fo_file,"    <fo:block text-align=\"start\" font-weight=\"bold\" space-before.optimum=\"10pt\" space-after.optimum=\"10pt\">\n");
                if (do_template("show_questions")) {
                    fputs($fo_file,"      ".$global_counter.".".$local_counter.".".$local_question_counter.". ". xml_escape($question['text']) ."\n");
                }
                fputs($fo_file,"    </fo:block>\n");
                fputs($fo_file,"    <!-- table start -->\n");
                fputs($fo_file,"    <fo:table table-layout=\"fixed\" border-width=\".1mm\" space-after.optimum=\"10pt\">\n");
                if (!($freetype)) {
                    fputs($fo_file,"      <fo:table-column column-width=\"100mm\"/>\n");
                    fputs($fo_file,"      <fo:table-column column-width=\"60mm\"/>\n");
                } else {
                    fputs($fo_file,"      <fo:table-column column-width=\"160mm\"/>\n");
                }
                fputs($fo_file,"      <fo:table-body>\n");
                fputs($fo_file,"        <fo:table-row >\n");
                fputs($fo_file,"          <fo:table-cell ><fo:block start-indent=\"3mm\" end-indent=\"3mm\" padding-left=\"3mm\" padding-right=\"3mm\" padding-top=\"4mm\" padding-bottom=\"4mm\">\n");
                fputs($fo_file,"            <!-- table start -->\n");
                fputs($fo_file,"            <fo:table table-layout=\"fixed\">\n");
                if (!($freetype)) {
                    fputs($fo_file,"              <fo:table-column column-width=\"60mm\"/>\n");
                    fputs($fo_file,"              <fo:table-column column-width=\"40mm\"/>\n");
                } else {
                    fputs($fo_file,"              <fo:table-column column-width=\"160mm\"/>\n");
                }
                fputs($fo_file,"              <fo:table-body>\n");


                } // ($group_type=="normal")

                if (!($freetype)) {
                    // Keine Freitext-Eingabe
                    $ret = answers($question['evalquestion_id'], $question_users, $question['type']);
                    $ret["frage"] = $question['text'];
                    array_push($answer_arr, $ret);
                    if ($group_type=="normal") fputs($fo_file, $ret["txt"]);
                } else {
                    // Freitext
                    freetype_answers($question['evalquestion_id'], $question_users);
                }


                if ($group_type=="normal") {

                    fputs($fo_file,"              </fo:table-body>\n");
                    fputs($fo_file,"            </fo:table>\n");
                    fputs($fo_file,"            <!-- table end -->\n");
                    fputs($fo_file,"          </fo:block></fo:table-cell>\n");
                    if (!($freetype)) {
                        fputs($fo_file,"          <fo:table-cell ><fo:block start-indent=\"3mm\" end-indent=\"3mm\" padding-left=\"3mm\" padding-right=\"3mm\" padding-top=\"4mm\" padding-bottom=\"4mm\">\n");
                        if (do_template("show_graphics")) {
                            fputs($fo_file,"            <fo:external-graphic content-width=\"70mm\" content-height=\"60mm\" src=\"url('file:///".$tmp_path_export."/evalsum".$question['evalquestion_id'].$auth->auth["uid"].".".$GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT']."')\"/>\n");
                        }
                        fputs($fo_file,"          </fo:block></fo:table-cell>\n");
                    }
                    fputs($fo_file,"        </fo:table-row>\n");
                    fputs($fo_file,"      </fo:table-body>\n");
                    fputs($fo_file,"    </fo:table>\n");
                    fputs($fo_file,"  <!-- table end -->\n");

                } // ($group_type=="normal")



            }

            if (!($freetype) && $group_type=="table") {

                $antworten_angezeigt = FALSE;
                                $i = 0;
                                $has_residual = 0;
                $col_count = count($answer_arr[0]["antwort_texte"]);

                fputs($fo_file,"    <!-- table start -->\n");
                                fputs($fo_file,"    <fo:table table-layout=\"fixed\" border-width=\".1mm\" border-style=\"solid\" space-after.optimum=\"10pt\">\n");
                fputs($fo_file,"              <fo:table-column/>\n");
                for ($a=1; $a<=$col_count; $a++)
                    fputs($fo_file,"              <fo:table-column  column-width=\"15mm\"/>\n");
                fputs($fo_file,"              <fo:table-column  column-width=\"8mm\"/>\n");
                fputs($fo_file,"              <fo:table-column  column-width=\"8mm\"/>\n");
                fputs($fo_file,"              <fo:table-column  column-width=\"15mm\"/>\n");


                fputs($fo_file,"      <fo:table-body>\n");

                                foreach ($answer_arr as $k1=>$questions) { // Oberste Ebene, hier sind die Questions abgelegt
                    if (!($antworten_angezeigt)) {
                                            $i = 1;
                        fputs($fo_file,"        <fo:table-row >\n");
                        fputs($fo_file,"          <fo:table-cell ><fo:block space-before.optimum=\"10pt\">\n");
                        fputs($fo_file,"          </fo:block></fo:table-cell >");
                        foreach ($questions["antwort_texte"] as $k2=>$v2) { // 1. Unterebene, hier sind die Antworttexte abgelegt
                            fputs($fo_file,"          <fo:table-cell ><fo:block space-before.optimum=\"10pt\" font-size=\"7pt\">\n");
                            fputs($fo_file, xml_escape($v2));
                            fputs($fo_file,"          </fo:block></fo:table-cell >");
                        }

                        fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" space-before.optimum=\"10pt\" font-size=\"7pt\" font-family=\"Symbol\">\n");
                        fputs($fo_file, "&#x2211;");
                        fputs($fo_file,"          </fo:block></fo:table-cell >");
                        fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" space-before.optimum=\"10pt\" font-size=\"7pt\" font-family=\"Symbol\">\n");
                        fputs($fo_file, "&#x2205;");
                        fputs($fo_file,"          </fo:block></fo:table-cell >");
                        fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" space-before.optimum=\"10pt\" font-size=\"7pt\">\n");
                        fputs($fo_file, _("Teilnehmende"));
                        fputs($fo_file,"          </fo:block></fo:table-cell >");

                        fputs($fo_file,"        </fo:table-row>\n");
                        $antworten_angezeigt = TRUE;
                    }

                    fputs($fo_file,"        <fo:table-row >\n");

                    fputs($fo_file,"          <fo:table-cell ><fo:block font-size=\"6pt\" start-indent=\"3mm\">\n");
                    fputs($fo_file, $questions["frage"]);
                    fputs($fo_file,"          </fo:block></fo:table-cell >");

                    foreach ($questions["auswertung"] as $k3=>$v3) {
                        fputs($fo_file,"          <fo:table-cell ><fo:block font-size=\"7pt\">\n");
                        fputs($fo_file, $v3[0]." (".$v3[1]."%)"); // 2. Unterebene, hier sind die Zahlen abgelegt
                        if ($v3[2]) fputs($fo_file, " (".$v3[2]."%)*");
                        fputs($fo_file,"          </fo:block></fo:table-cell >");
                    }

                    $i=0;
                    if ($questions["has_residual"]) $has_residual = 1;

                    fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" font-size=\"7pt\">\n");
                    fputs($fo_file, $questions["summe_antworten"]);
                    fputs($fo_file,"          </fo:block></fo:table-cell >");

                    fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" font-size=\"7pt\">\n");
                    fputs($fo_file, $questions["antwort_durchschnitt"].($questions["has_residual"]?"*":""));
                    fputs($fo_file,"          </fo:block></fo:table-cell >");

                    fputs($fo_file,"          <fo:table-cell ><fo:block text-align=\"center\" font-size=\"7pt\">\n");
                    fputs($fo_file, $questions["anzahl_teilnehmer"]);
                    fputs($fo_file,"          </fo:block></fo:table-cell >");

                    fputs($fo_file,"        </fo:table-row>\n");

                }

                fputs($fo_file,"        <fo:table-row >\n");
                fputs($fo_file,"          <fo:table-cell ><fo:block start-indent=\"3mm\" space-after.optimum=\"10pt\" font-size=\"7pt\">\n");
                if ($has_residual) fputs($fo_file, "* "._("Werte ohne Enthaltungen").".");
                fputs($fo_file,"          </fo:block></fo:table-cell >");
                fputs($fo_file,"        </fo:table-row >\n");

                fputs($fo_file,"      </fo:table-body>\n");
                fputs($fo_file,"    </fo:table>\n");
                                fputs($fo_file,"  <!-- table end -->\n");

            }
        }

        groups($group['evalgroup_id']);

    }

}


$query = "SELECT eval_id, title, author_id, anonymous
          FROM eval
          WHERE eval_id = ?";
$statement = DBManager::get()->prepare($query);
$statement->execute([
    $eval_id
]);

if ($evaluation = $statement->fetch(PDO::FETCH_ASSOC)) {
    // Evaluation existiert auch...

    $query = "SELECT t.*
              FROM eval_templates AS t
              JOIN eval_templates_eval AS te USING (template_id)
              WHERE te.eval_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$eval_id]);
    $eval_templates = $statement->fetch(PDO::FETCH_ASSOC);

    $has_template = !empty($eval_templates);

    $db_owner = User::find($evaluation['author_id'])->getFullName('no_title');

    $global_counter = 0;
    $local_counter  = 0;

    $query = "SELECT COUNT(DISTINCT user_id) FROM eval_user WHERE eval_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$eval_id]);
    $number_of_votes = $statement->fetchColumn();

    $query = "SELECT range_id FROM eval_range WHERE eval_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$eval_id]);
    $eval_ranges = $statement->fetchAll(PDO::FETCH_COLUMN);

    $eval_ranges_names = [];
    foreach ($eval_ranges as $eval_range) {
      $o_type = get_object_type($eval_range, ['studip','user','sem','inst']);
      switch($o_type) {
      case 'global':
          $name = _("Systemweite Evaluationen");
          break;
      case 'sem':
          $name = _('Veranstaltung') . ':';
          $seminar = Seminar::getInstance($eval_range);
          $name .= ' ' . $seminar->getName();
          $name .= ' (' . Semester::findByTimestamp($seminar->semester_start_time)->name;
          if ($seminar->semester_duration_time == -1) {
              $name .= ' - ' . _('unbegrenzt');
          }
          if ($seminar->semester_duration_time > 0) {
              $name .= ' - ' . Semester::findByTimestamp($seminar->semester_start_time + $seminar->semester_duration_time)->name;
          }
          $name .= ')';
          $dozenten = array_map(function($v){return $v['Nachname'];}, $seminar->getMembers('dozent'));
          $name .= ' (' . join(', ' , $dozenten) . ')';
          break;
      case 'user':
          $name = _('Profil') . ':';
          $name .= ' ' . get_fullname($eval_range);
          break;
      case 'inst':
      case 'fak':
          $name = _('Einrichtung') . ':';
          $name .= ' ' . Institute::find($eval_range)->name;
          break;
      default:
          $name = _('unbekannt');
      }
      $eval_ranges_names[] = $name;
    }
    sort($eval_ranges_names);
    if (file_exists($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".fo")) unlink($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".fo");
    if (file_exists($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".pdf")) unlink($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".pdf");

    $fo_file = fopen($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".fo","w");

    // ----- START HEADER -----

    fputs($fo_file,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
    fputs($fo_file,"<fo:root xmlns:fo=\"http://www.w3.org/1999/XSL/Format\">\n");
    fputs($fo_file,"  <!-- defines the layout master -->\n");
    fputs($fo_file,"  <fo:layout-master-set>\n");
    fputs($fo_file,"    <fo:simple-page-master master-name=\"first\" page-height=\"29.7cm\" page-width=\"21cm\" margin-top=\"1cm\" margin-bottom=\"2cm\" margin-left=\"2.5cm\" margin-right=\"2.5cm\">\n");
    fputs($fo_file,"      <fo:region-body margin-top=\"1cm\" margin-bottom=\"1.5cm\"/>\n");
    fputs($fo_file,"      <fo:region-before extent=\"1cm\"/>\n");
    fputs($fo_file,"      <fo:region-after extent=\"1.5cm\"/>\n");
    fputs($fo_file,"      </fo:simple-page-master>\n");
    fputs($fo_file,"    </fo:layout-master-set>\n");
    fputs($fo_file,"  <!-- starts actual layout -->\n");
    fputs($fo_file,"  <fo:page-sequence master-reference=\"first\">\n");
    fputs($fo_file,"  <fo:static-content flow-name=\"xsl-region-after\">\n");
    fputs($fo_file,"    <fo:block text-align=\"center\" font-size=\"8pt\" font-family=\"serif\" line-height=\"14pt\" >\n");
    fputs($fo_file,"    "._("Erstellt mit Stud.IP")." " . xml_escape($SOFTWARE_VERSION) . " - ". _("Seite")." <fo:page-number/>\n");
    fputs($fo_file,"    </fo:block>\n");
    fputs($fo_file,"    <fo:block text-align=\"center\" font-size=\"8pt\" font-family=\"serif\" line-height=\"14pt\" >\n");
    fputs($fo_file,"      <fo:basic-link color=\"blue\" external-destination=\"$ABSOLUTE_URI_STUDIP\">". xml_escape(Config::get()->UNI_NAME_CLEAN) . "</fo:basic-link>\n");
    fputs($fo_file,"    </fo:block>\n");
    fputs($fo_file,"  </fo:static-content>\n");
    fputs($fo_file,"  <fo:flow flow-name=\"xsl-region-body\">\n");
    fputs($fo_file,"    <!-- this defines a title level 1-->\n");
    fputs($fo_file,"    <fo:block font-size=\"18pt\" font-variant=\"small-caps\" font-family=\"sans-serif\" line-height=\"24pt\" space-after.optimum=\"15pt\" background-color=\"blue\" color=\"white\" text-align=\"center\" padding-top=\"3pt\">\n");
    fputs($fo_file,"      "._("Stud.IP Evaluationsauswertung")."\n");
    fputs($fo_file,"    </fo:block>\n");
    fputs($fo_file,"    <!-- this defines a title level 2-->\n");

    fputs($fo_file,"    <fo:block font-size=\"16pt\" font-weight=\"bold\" font-family=\"sans-serif\" space-before.optimum=\"10pt\" space-after.optimum=\"15pt\" text-align=\"center\">\n");
    fputs($fo_file,"      ". xml_escape($evaluation['title'])."\n");
    fputs($fo_file,"    </fo:block>\n");
    fputs($fo_file,"    <fo:block text-align=\"start\" line-height=\"10pt\" font-size=\"8pt\">\n");
    fputs($fo_file,    _("Diese Evaluation ist folgenden Bereichen zugeordnet:"));
    fputs($fo_file,"    </fo:block>\n");
    foreach($eval_ranges_names as $n) {
        fputs($fo_file,"    <fo:block text-align=\"start\" margin-left=\"0.5cm\" line-height=\"10pt\" font-size=\"8pt\">\n");
        fputs($fo_file, xml_escape($n));
        fputs($fo_file,"    </fo:block>\n");
    }



    if (do_template("show_total_stats")) {
        fputs($fo_file,"    <fo:block text-align=\"start\" space-before.optimum=\"10pt\" line-height=\"10pt\" font-size=\"8pt\">\n");
        fputs($fo_file,"      ". xml_escape($number_of_votes." "._("Teilnehmende insgesamt")).".\n");
        fputs($fo_file,"      ". xml_escape(($evaluation['anonymous']==0  ? _('Die Teilnahme war nicht anonym.') : _('Die Teilnahme war anonym.'))."\n"));
        fputs($fo_file,"      " . xml_escape(_("Eigentümer").": ".$db_owner.". "._("Erzeugt am").": ".date("d.m.Y H:i:s"))."\n");
        fputs($fo_file,"    </fo:block>\n");
    }

    // ----- ENDE HEADER -----

    groups($evaluation['eval_id']);

    // ----- START FOOTER -----

    fputs($fo_file,"    </fo:flow>\n");
    fputs($fo_file,"  </fo:page-sequence>\n");
    fputs($fo_file,"</fo:root>\n");

    // ----- ENDE FOOTER -----

    fclose($fo_file);

    $pdffile = "$tmp_path_export/" . md5($evaluation['eval_id'].$auth->auth["uid"]);

    $str = $FOP_SH_CALL." $tmp_path_export/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".fo $pdffile";

    $err = exec($str);

    if (file_exists($pdffile) && filesize($pdffile)) {
        header('Location: ' . FileManager::getDownloadURLForTemporaryFile( basename($pdffile), "evaluation.pdf", 2, 'force'));
        unlink($tmp_path_export."/evalsum".$evaluation['eval_id'].$auth->auth["uid"].".fo");
    } else {
        echo "Fehler beim PDF-Export!<BR>".$err;
        echo "<BR>\n".$str;
    }
} else {
    // Evaluation existiert nicht...
    echo _("Evaluation NICHT vorhanden oder keine Rechte!");
}
// Save data back to database.
page_close();
?>
