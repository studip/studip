<?php
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


# Define constants ========================================================== #
# ===================================================== end: define constants #


# Include all required files ================================================ #
require_once 'lib/evaluation/evaluation.config.php';
require_once HTML;
# ====================================================== end: including files #


/**
 * Library with common static functions for the evaluation module
 *
 * @author      Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 *
 */
class EvalCommon
{
    /**
     * Creates this funny blue title bar
     * @param string $title The title
     * @param string $iconURL The URL for the icon
     */
    public static function createTitle($title, $iconURL = "", $padding = 0)
    {
        $table = new HTML("table");
        $table->addAttr("border", "0");
        $table->addAttr("class", "blank");
        $table->addAttr("align", "center");
        $table->addAttr("cellspacing", "0");
        $table->addAttr("cellpadding", $padding);
        $table->addAttr("width", "100%");

        $trTitle = new HTML("tr");
        $trTitle->AddAttr("valign", "top");
        $trTitle->AddAttr("align", "center");

        $tdTitle = new HTML("td");
        if ($iconURL) {
            $tdTitle->addAttr("class", "table_header_bold");
        } else {
            $tdTitle->addAttr("class", "content_body");
        }
        $tdTitle->addAttr("colspan", "2");
        $tdTitle->addAttr("align", "left");
        $tdTitle->addAttr("valign", "middle");

        if ($iconURL) {
            $imgTitle = new HTMLempty ("img");
            $imgTitle->addAttr("src", $iconURL);
            $imgTitle->addAttr("alt", $title);
            $imgTitle->addAttr("align", "bottom");
            $tdTitle->addContent($imgTitle);
        }

        $bTitle = new HTML ("b");
        $bTitle->addContent($title);
        $tdTitle->addContent($bTitle);

        $trTitle->addContent($tdTitle);
        $table->addContent($trTitle);

        return $table;
    }

    /**
     * Creates a simple image for the normal top of an modulepage
     * @param string $imgURL The URL for the icon
     * @param string $imgALT The description for the icon
     */
    public static function createImage($imgURL, $imgALT, $extra = "")
    {
        $img = new HTMLempty ("img");
        $img->addAttr("border", "0");
        $img->addAttr("valign", "middle");
        $img->addAttr("src", $imgURL);
        if (empty($extra)) {
            $img->addAttr("alt", $imgALT);
            $img->addAttr("title", $imgALT);
        } else {
            $img->addString($extra);
        }

        return $img;
    }

    /**
     * Creates the Javascript static function, which will open an evaluation popup
     */
    static function createEvalShowJS($isPreview = NO, $as_object = YES)
    {
        $html = "";
        $html .=
            "<script type=\"text/javascript\" language=\"JavaScript\">" .
            "  function openEval( evalID ) {" .
            "    evalwin = window.open(STUDIP.URLHelper.getURL('show_evaluation.php?evalID=' + evalID + '&isPreview=" . $isPreview . "'), " .
            "                          evalID, 'width=790,height=500,scrollbars=yes,resizable=yes');" .
            "    evalwin.focus();" .
            "  }\n" .
            "</script>\n";

        $div = new HTML ("div");
        $div->addHTMLContent($html);

        if ($as_object) {
            return $div;
        }
        return $html;
    }

    /**
     * Creates a link, which will open an evaluation popup
     */
    static function createEvalShowLink($evalID, $content, $isPreview = NO, $as_object = YES)
    {
        $html = "";

        $html .=
            "<a " .
            "href=\"" . URLHelper::getLink('show_evaluation.php?evalID=' . $evalID . '&isPreview=' . $isPreview) . "\" " .
            "target=\"" . $evalID . "\" " .
            "onClick=\"openEval('" . $evalID . "'); return false;\">" .
            (is_object($content) ? str_replace("\n", "", $content->createContent()) : $content) .
            "</a>";

        $div = new HTML ("div");
        $div->addHTMLContent($html);

        if ($as_object) {
            return $div;
        }
        return $html;
    }


    /**
     * Creates an errormessage from an object
     * @param object StudipObejct   $object   A Stud.IP-object
     */
    public static function showErsrorReport(&$object, $errortitle = "")
    {
        $errors = $object->getErrors();
        if (empty ($errortitle)) {
            if ($errors && count($errors) > 1) {
                $errortitle = _("Es sind Fehler aufgetreten.");
            } else {
                $errortitle = _("Es ist ein Fehler aufgetreten.");
            }
        }

        if (!$object->isError()) {
            return MessageBox::success(_("Es ist kein Fehler aufgetreten"));
        } else {
            $details = [];
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $string = $error['string'];
                    if ($error["type"] == ERROR_CRITICAL) {
                        $string .= _("Datei: ") . $error["file"] . '<br>';
                        $string .= _("Zeile: ") . $error["line"] . '<br>';

                    }
                    $details[] = $string;
                }
            }
            return MessageBox::error($errortitle, $details);
        }
    }

    /**
     * @param $object
     * @param string $errortitle
     * @return MessageBox|object
     * @deprecated
     */
    public static function createErrorReport(&$object, $errortitle = "")
    {
        return MessageBox::error($errortitle);
    }

    /**
     * Returns the rangeID
     */
    public static function getRangeID()
    {
        $rangeID = Request::option('range_id') ?: Context::getId();

        if (empty ($rangeID) || ($rangeID == get_username($GLOBALS['user']->id)))
            $rangeID = $GLOBALS['user']->id;

        return $rangeID;
    }


    /**
     * Checks and transforms a date into a UNIX (r)(tm) timestamp
     * @access public
     * @static
     * @param integer $day The day
     * @param integer $month The month
     * @param integer $year The year
     * @param integer $hour The hour (optional)
     * @param integer $minute The minute (optional)
     * @param integer $second The second (optional)
     * @return  integer If an error occurs -> -1. Otherwise the UNIX-timestamp
     */
    public static function date2timestamp(
        $day,
        $month,
        $year,
        $hour = 0,
        $minute = 0,
        $second = 0
    )
    {
        if (!checkdate((int)$month, (int)$day, (int)$year) ||
            $hour < 0 || $hour > 24 ||
            $minute < 0 || $minute > 59 ||
            $second < 0 || $second > 59) {
            return -1;
        }

        // windows cant count that mutch
        if ($year < 1971) {
            $year = 1971;
        } elseif ($year > 2037) {
            $year = 2037;
        }

        return mktime($hour, $minute, $second, $month, $day, $year);
    }
}

?>
