<?php
# Lifter002: TODO
# Lifter007: TEST
# Lifter003: TEST
# Lifter010: TODO
/**
 * eval_summary.php - Hauptseite fuer Eval-Auswertungen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @copyright   2007-2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


require '../lib/bootstrap.php';

require_once 'vendor/phplot/phplot.php';
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_OBJECTDB;
require_once 'lib/export/export_tmp_gc.inc.php';

ob_start();
page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);

$EVAL_AUSWERTUNG_GRAPH_FORMAT = Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT ?: 'gif';

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

$eval_id = Request::option('eval_id');
/*
    1 = normale HTML-Ansicht in Stud.IP
    2 = Druckansicht, ohne HTML-Elemente
*/
$ausgabeformat = Request::int('ausgabeformat', 1);
$cmd = Request::option('cmd');
$evalgroup_id = Request::option('evalgroup_id');
$group_type = Request::option('group_type');

// Überprüfen, ob die Evaluation existiert oder der Benutzer genügend Rechte hat
$eval = new Evaluation($eval_id);
$eval->check();
if (EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval) == YES || count($eval->errorArray) > 0) {
    throw new Exception(_("Diese Evaluation ist nicht vorhanden oder Sie haben nicht ausreichend Rechte!"));
}

// Template vorhanden?
$has_template   = 0;
$eval_templates = [];
$question_type  = "";

$tmp_path_export = $GLOBALS['TMP_PATH'];
export_tmp_gc();

if (isset($cmd)) {
    if ($cmd=="change_group_type" && isset($evalgroup_id) && isset($group_type)) {
        $query = "SELECT 1 FROM eval_group_template WHERE evalgroup_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$evalgroup_id]);
        $present = $statement->fetchColumn();

        if ($present) { // Datensatz schon vorhanden --> UPDATE
            if ($group_type == "normal") {
                $query = "DELETE FROM eval_group_template WHERE group_type = 'table' AND evalgroup_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$evalgroup_id]);
            } else {
                $query = "UPDATE eval_group_template SET group_type = ? WHERE evalgroup_id = ? AND user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$group_type, $evalgroup_id, $GLOBALS['user']->id]);
            }
        } else { // Datensatz nicht vorhanden --> INSERT
            $query = "INSERT INTO eval_group_template (evalgroup_id, user_id, group_type)
            VALUES (?, ?, ?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$evalgroup_id, $GLOBALS['user']->id, $group_type]);
        }
    }
}


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

function freetype_answers($parent_id, $anz_nutzer)
{
    $query = "SELECT `text`
              FROM evalanswer
              INNER JOIN evalanswer_user USING(evalanswer_id)
              WHERE parent_id = ? AND `text` != ''
              ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$parent_id]);

    echo "  <tr>\n";
    echo "    <td colspan=\"2\">\n";
    echo "      <table border=\"0\" width=\"100%\">\n";
    echo "        <tr><td colspan=\"2\" class=\"blank\"><font size=\"-1\"><b>"._("Antworten")."</b></font></td></tr>\n";

    $counter = 1;
    while (false !== ($answer = $statement->fetchColumn())) {
        echo "      <tr>\n";
        echo "        <td width=\"1%\" valign=\"TOP\"><font size=\"-1\"><b>".$counter.".</b></font></td><td><font size=\"-1\">".formatReady($answer)."</font></td>\n";
        echo "      </tr>\n";
        $counter++;
    }

    echo "      </table>\n";
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr><td colspan=\"2\"><font size=\"-1\">"._("Anzahl der Teilnehmenden").": ".$anz_nutzer."</font></td></tr>\n";
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

function answers($parent_id, $anz_nutzer, $question_type)
{
    global $graph_switch, $auth, $ausgabeformat, $has_template;

    // Rueckgabearray, damit die Daten noch aufzutrennen sind...
    $ret_array = ["id"=>$parent_id,                         // Question-ID
               "txt"=>"",                                // HTML-Ausgabe
               "antwort_texte"=>[],                 // Antwort-Texte
               "frage"=>"",                              // Frage-Text
               "has_residual"=>0,                // Enthaltungen?
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
    $edit = "";
    $txt = "";
    $gesamte_antworten = 0;
    $antwort_durchschnitt = 0;
    $has_residual = user_answers_residual($parent_id);
    $i = 1;
    $edit .= "<tr class=\"table_row_even\"><td width=\"1%\">&nbsp;</td><td width=\"70%\"><font size=\"-1\"><b>"._("Antworten")."</b></font></td><td width=\"29%\"><font size=\"-1\"><b>"._("Auswertung")."</b></font></td></tr>\n";

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
        $prozente_wo_residual = 0;
        if ($has_residual && ($answers_sum - $has_residual)>0) $prozente_wo_residual = ROUND($answer_counter*100/($anz_nutzer-$has_residual));
        $prozente = 0;
        if ($answers_sum > 0) $prozente = ROUND($answer_counter*100/$anz_nutzer);
        $edit .= "<tr ".($i==1?'class="content_body"':'')."><td width=\"1%\"><font size=\"-1\"><b>".$antwort_nummer.".&nbsp;</b></font></td><td width=\"70%\"><font size=\"-1\">".($answer['text'] != '' ? formatReady($answer['text']) : $answer['value'])."</font></td>";
        if ($has_residual) $edit .= "<td width=\"29%\"><font size=\"-1\">".$answer_counter." (".$prozente."%) ".($answer['residual'] == 0 ? "(".$prozente_wo_residual."%)<b>*</b>" : "" )."</font></td></tr>\n";
        else $edit .= "<td width=\"29%\"><font size=\"-1\">".$answer_counter." (".$prozente."%)</font></td></tr>\n";
        array_push($summary, [$antwort_nummer."(".$prozente."%)",$answer_counter]);

        array_push($ret_array["antwort_texte"], ($answer['text'] != '' ? formatReady($answer['text']) : $answer['value']));
        array_push($ret_array["auswertung"], [$answer_counter, $prozente, ($answer['residual'] == 0 ? $prozente_wo_residual : null)]);
        if ($has_residual) $ret_array["has_residual"] = 1;

        $i = 0;
    }
    do_graph($summary, $parent_id);

    if ($gesamte_antworten > 0 && $antwort_durchschnitt > 0) $antwort_durchschnitt = ROUND($antwort_durchschnitt / $gesamte_antworten, 3);

    $ret_array["antwort_durchschnitt"] = $antwort_durchschnitt;
    $ret_array["summe_antworten"] = $gesamte_antworten;

    $txt .= "  <tr>\n";
    $txt .= "    <td width=\"70%\" valign=\"TOP\">\n";
    $txt .= "      <table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
    $txt .= $edit."\n";
    $txt .= "        <tr class=\"blank\"><td colspan=\"3\"><font size=\"-1\">&nbsp;</font></td></tr>";
    $txt .= "        <tr class=\"blank\"><td colspan=\"3\"><font size=\"-1\"><b>&#x2211;</b>=".$gesamte_antworten." "._("Antworten")."</font></td></tr>";

    $txt .= "        <tr class=\"blank\">";
    if ($question_type=="multiplechoice") {
        $txt .= "        <td colspan=\"3\">";
    } else {
        $txt .= "<td colspan=\"2\"><font size=\"-1\"><b>&#x2205;</b>-"._("Antwort").": ".$antwort_durchschnitt.($has_residual==0 ? "" : "<b>*</b>")."</font></td><td>";
    }
    $txt .= "          <font size=\"-1\">"._("Anzahl der Teilnehmenden").": ".$anz_nutzer."</font></td></tr>";

    if ($has_residual) $txt .= "        <tr class=\"blank\"><td colspan=\"3\"><font size=\"-1\"><b>*</b>"._("Werte ohne Enthaltungen").".</font></td></tr>";
    $txt .= "      </table>";
    $txt .= "    </td>\n";
    $txt .= "    <td width=\"30%\" valign=\"TOP\" align=\"RIGHT\">\n";
    if (do_template("show_graphics")) {
        $txt .= '<IMG SRC="' . FileManager::getDownloadLinkForTemporaryFile(
            'evalsum'.$parent_id.$auth->auth['uid'].'.'.Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT,
            'evalsum'.$parent_id.$auth->auth['uid'].'.'.Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT) .'">'."\n";
    } else $txt .= "&nbsp;\n";
    $txt .= "    </td>\n";
    $txt .= "  </tr>\n";

    $ret_array['txt'] = $txt;

    return $ret_array;

}

function groups($parent_id)
{
    global $ausgabeformat, $global_counter, $local_counter, $question_type, $eval_id, $evalgroup_id;

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

            echo "  <tr><td class=\"".($ausgabeformat==1 ? "table_header_bold" : "blank")."\" align=\"LEFT\" colspan=\"2\">\n";
            if (do_template("show_group_headline"))
                echo "    <b>".$global_counter.". ".formatReady($group['title'])."</b>&nbsp;\n";
            else echo "&nbsp;";
        } else {
            $local_counter += 1;

            $type_statement->execute([$group['evalgroup_id']]);
            $group_type = $type_statement->fetchColumn() ?: 'normal';
            $type_statement->closeCursor();

            echo "  <tr><td class=\"".($ausgabeformat==1 ? "table_row_odd" : "blank")."\" colspan=\"2\">\n";
            if (do_template("show_questionblock_headline")) {
                echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td align=\"left\"><b>".$global_counter.".".$local_counter.". ".formatReady($group['title'])."</b></td>";
                echo "<td align=\"RIGHT\">";
                if ($ausgabeformat==1 && !$freetype) {
                    if ($group_type === 'normal') {
                        echo '<a href="' . URLHelper::getLink('?eval_id=' . $eval_id . '&evalgroup_id=' . $group['evalgroup_id'] . '&group_type=table&cmd=change_group_type#anker') . '">';
                        echo Icon::create('vote-stopped', 'clickable', ['title' => _('Zum Darstellungstyp Tabelle wechseln')])->asImg();
                        echo '</a>';
                    } else {
                        echo '<a href="' . URLHelper::getLink('?eval_id=' . $eval_id . '&evalgroup_id=' . $group['evalgroup_id'] . '&group_type=normal&cmd=change_group_type#anker') . '">';
                        echo Icon::create('vote', 'clickable', ['title' => _('Zum Darstellungstyp Normal wechseln')])->asImg();
                    }
                } else {
                    echo '&nbsp;';
                }
                echo "</td>";
                echo "</tr></table>\n";
            }
            if ($evalgroup_id == $group['evalgroup_id']) {
                echo "  <a name=\"anker\"></a>\n";
            }
        }

        echo "  </td></tr>";

        if ($group['child_type'] == 'EvaluationQuestion') {
            echo "  <tr><td class=\"blank\" colspan=\"2\">\n";

            echo "<table border=\"". ($group_type=="normal" || $ausgabeformat==1 ? "0" : "1") ."\" width=\"100%\" cellspacing=\"0\">\n";

            $local_question_counter = 0;
            $answer_arr = [];

            $questions_statement->execute([$group['evalgroup_id']]);
            while ($question = $questions_statement->fetch(PDO::FETCH_ASSOC)) {
                $question_type = $question['type'];

                $question_users_statement->execute([$question['evalquestion_id']]);
                $question_users = $question_users_statement->fetchColumn();
                $question_users_statement->closeCursor();

                $local_question_counter += 1;

                if (do_template("show_questions") && $group_type=="normal") {
                    echo "    <tr><td class=\"blank\" colspan=\"2\">\n";
                    echo "      <b>".$global_counter.".".$local_counter.".".$local_question_counter.". ".formatReady($question['text'])."</b></font>\n";
                    echo "    </td></tr>\n";
                }

                if (!($freetype)) {
                    // Keine Freitext-Eingabe
                    $ret = answers($question['evalquestion_id'], $question_users, $question['type']);
                    $ret["frage"] = $question['text'];
                    array_push($answer_arr, $ret);
                    if ($group_type=="normal") echo $ret["txt"];
                } else {
                    // Freitext
                    freetype_answers($question['evalquestion_id'], $question_users);
                }
            }
            $questions_statement->closeCursor();

            if (!($freetype) && $group_type=="table") {
                $antworten_angezeigt = FALSE;
                $i = 0;
                $has_residual = 0;
                foreach ($answer_arr as $k1=>$questions) { // Oberste Ebene, hier sind die Questions abgelegt

                    if (!($antworten_angezeigt)) {
                        $i = 1;
                                            echo "  <tr class=\"table_row_even\"><td><font size=\"-1\">&nbsp;</font></td>";
                                            foreach ($questions["antwort_texte"] as $k2=>$v2) { // 1. Unterebene, hier sind die Antworttexte abgelegt
                                                echo "<td><font size=\"-1\">".$v2."</font></td>";
                                            }
                        echo "<td align=\"center\"><font size=\"-1\"><b>&#x2211;</b></font></td><td align=\"center\"><font size=\"-1\"><b>&#x2205;</b></font></td><td align=\"center\"><font size=\"-1\">"._("Teilnehmende")."</font></td>";
                                            echo "</tr>";
                                            $antworten_angezeigt = TRUE;
                                        }

                    echo "<tr ". ($i==1?'class="content_body"':'').">";
                    echo "  <td><font size=\"-1\">".$questions["frage"]."</font></td>";
                    foreach ($questions["auswertung"] as $k3=>$v3) {
                        echo "<td width=\"10%\" valign=\"TOP\"><font size=\"-1\">";
                        echo $v3[0]." (".$v3[1]."%)"; // 2. Unterebene, hier sind die Zahlen abgelegt
                        if ($v3[2]) echo " (".$v3[2]."%)<b>*</b>";
                        echo "</font></td>";
                    }

                    $i=0;
                    if ($questions["has_residual"]) $has_residual = 1;

                    echo "<td align=\"center\" width=\"3%\" valign=\"TOP\"><font size=\"-1\">".$questions["summe_antworten"]."</font></td><td align=\"center\" width=\"3%\" valign=\"TOP\"><font size=\"-1\">".$questions["antwort_durchschnitt"].($questions["has_residual"]?"<b>*</b>":"")."</font></td><td align=\"center\" width=\"6%\" valign=\"TOP\"><font size=\"-1\">".$questions["anzahl_teilnehmer"]."</font></td>";

                    echo "</tr>";
                }
                if ($has_residual) echo "<tr><td><font size=\"-1\"><b>*</b>"._("Werte ohne Enthaltungen").".</font></td></tr>";
            }

            echo "</table>\n";
            echo "</td></tr>\n";
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
  $query = "SELECT t.*
            FROM eval_templates AS t
            JOIN eval_templates_eval AS te USING (template_id)
            WHERE te.eval_id = ?";
  $statement = DBManager::get()->prepare($query);
  $statement->execute([$eval_id]);
  $eval_templates = $statement->fetch(PDO::FETCH_ASSOC);

  $has_template = !empty($eval_templates);

  $db_owner = User::find($evaluation['author_id']);

  $global_counter = 0;
  $local_counter  = 0;

  $query = "SELECT COUNT(DISTINCT user_id) FROM eval_user WHERE eval_id = ?";
  $statement = DBManager::get()->prepare($query);
  $statement->execute([$eval_id]);
  $number_of_votes = $statement->fetchColumn();

  $eval_ranges_names = [];

  $query = "SELECT range_id FROM eval_range WHERE eval_id = ?";
  $statement = DBManager::get()->prepare($query);
  $statement->execute([$eval_id]);
  $eval_ranges = $statement->fetchAll(PDO::FETCH_COLUMN);

  foreach ($eval_ranges as $eval_range) {
      $o_type = get_object_type($eval_range, ['studip','user','sem','inst']);
      switch($o_type) {
      case 'global':
          $name = _("Systemweite Evaluationen");
          break;
      case 'sem':
          $name = _("Veranstaltung:");
          $seminar = Seminar::getInstance($eval_range);
          $name .= ' ' . $seminar->getName();
          $name .= ' (' . Semester::findByTimestamp($seminar->semester_start_time)->name;
          if ($seminar->semester_duration_time == -1) {
              $name .= ' - ' . _("unbegrenzt");
          }
          if ($seminar->semester_duration_time > 0) {
              $name .= ' - ' . Semester::findByTimestamp($seminar->semester_start_time + $seminar->semester_duration_time)->name;
          }
          $name .= ')';
          $dozenten = array_map(function($v){return $v['Nachname'];}, $seminar->getMembers('dozent'));
          $name .= ' (' . join(', ' , $dozenten) . ')';
          break;
      case 'user':
          $name = _("Profil:");
          $name .= ' ' . get_fullname($eval_range);
          break;
      case 'inst':
      case 'fak':
          $name = _("Einrichtung:");
          $name .= ' ' . Institute::find($eval_range)->name;
          break;
      default:
          $name = _("unbekannt");
      }
      $eval_ranges_names[] = $name;
  }
  sort($eval_ranges_names);

  // Evaluation existiert auch...
  echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\n";
  echo "<tr><td class=\"table_header_bold\" align=\"left\"><font color=\"".($ausgabeformat==1 ? "white" : "black")."\">";
  echo ($ausgabeformat==1 ? Icon::create('test', 'info_alt')->asImg() : "" );
  echo "<b>"._("Evaluations-Auswertung")."</b></font></td>\n";
  echo "<td class=\"".($ausgabeformat==1 ? "table_header_bold" : "blank" )."\" align=\"RIGHT\">".($ausgabeformat==1 ? "<a href=\"eval_summary_export.php?eval_id=".$eval_id."\" TARGET=\"_blank\"><font color=\"WHITE\">"._("PDF-Export")."</font></a><b>&nbsp;|&nbsp;</b><a href=\"".URLHelper::getLink('?eval_id='.$eval_id.'&ausgabeformat=2')."\" TARGET=\"_blank\"><font color=\"WHITE\">"._("Druckansicht")."</font></a>&nbsp;&nbsp;<a href=\"eval_config.php?eval_id=".$eval_id."\">" . Icon::create('arr_2right', 'info_alt', ['title' => _('Auswertung konfigurieren')])->asImg() . "</a>" : "" ) ."&nbsp;</td>\n";
  echo "</tr>\n";
  echo "<tr><td class=\"blank\" colspan=\"2\" align=\"left\">&nbsp;</td></tr>\n";
  echo "<tr><td class=\"blank\" colspan=\"2\" align=\"left\"><font size=\"+1\"><b>&nbsp;&nbsp;".formatReady($evaluation['title'])."</b></font></td>\n";
  echo "<tr><td class=\"blank\" colspan=\"2\" align=\"left\">&nbsp;&nbsp;";
  echo _("Diese Evaluation ist folgenden Bereichen zugeordnet:");
  echo '<ul>';
  echo '<li>' . join('</li><li>', array_map('htmlready', $eval_ranges_names)) . '</li>';
  echo '</ul>';
  echo "</td></tr>\n";

  echo "</tr>\n";

  echo "<tr><td class=\"blank\" colspan=\"2\" align=\"left\">&nbsp;</font></td></tr>\n";

  // Gesamtstatistik
  if (do_template("show_total_stats")) {
    echo "  <tr>\n";
    echo "    <td colspan=\"2\" class=\"blank\"><font size=\"-1\">\n";
    echo "      &nbsp;&nbsp;".$number_of_votes." "._("Teilnehmende insgesamt").".&nbsp;";
    echo "      ". ($evaluation['anonymous'] == 0 ? _('Die Teilnahme war nicht anonym.') : _('Die Teilnahme war anonym.')) . ' ';
    echo "      "._("Eigentümer").": ".($db_owner ? htmlReady($db_owner->getFullName('no_title')) : _('Unbekannter Nutzer')).". ".("Erzeugt am").": ".date('d.m.Y H:i:s');
    echo "    </font></td>\n";
    echo "  </tr>\n";
  }

  echo "  <tr><td colspan=\"2\">\n";
  echo "    <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\">\n";

  groups($evaluation['eval_id']);

  echo "    </table>\n";
  echo "  </td></tr>\n";
  echo "</table>\n";
}


PageLayout::setHelpKeyword("Basis.Evaluationen");
Navigation::activateItem('/tools/evaluation');
PageLayout::setTitle(_("Evaluations-Auswertung"));

if ($ausgabeformat == 2) {
    PageLayout::removeStylesheet('studip-base.css');
    PageLayout::addStylesheet('print.css');
}
$layout = $GLOBALS['template_factory']->open('layouts/base.php');

$layout->content_for_layout = ob_get_clean();

echo $layout->render();
page_close();
