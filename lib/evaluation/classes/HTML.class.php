<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * HTML-class for the Stud.IP-project.
 * Based on scripts from "http://tut.php-q.net/".
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

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
require_once 'HTMLempty.class.php';

class HTML extends HTMLempty
{
    /**
     * Holds the content.
     *
     * @access   private
     * @var      object $_content
     */
    var $_content;

    /**
     */
    var $has_textarea = false;

    public function addHTMLContent($_content)
    {
        if (is_object($_content)) {
            $classname = mb_strtolower(get_class($_content));
            $valid_classes = ['htmlempty', 'html', 'htm', 'htmpty', 'studip\button', 'studip\linkbutton', 'messagebox'];
            if (in_array($classname, $valid_classes)) {
                $this->_content[] = $_content;
            } else {
                trigger_error('Ungültiges Objekt: "' . $classname . '"', E_USER_ERROR);
            }
        } elseif (is_scalar($_content)) {
            $this->_content[] = (string)$_content;
        } else {
            echo "Fehler in HTML.class.php: Es fehlt ein addHTMLContent-Element für ein Element des Typs \"&lt;" . $this->getName() . "&gt;\"<br>";
        }
    }

    public function addContent($_content)
    {
        if (is_object($_content)) {
            $this->addHTMLContent($_content);
        } elseif (is_scalar($_content)) {
            $this->addHTMLContent(htmlReady(((string)$_content)));
        } else {
            $this->addHTMLContent("");
        }
    }

    /**
     *
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * avoid indentation of <textarea>...
     */
    public function setTextareaCheck()
    {
        $this->has_textarea = true;
    }

    /**
     *
     */
    public function printContent($indent = 0)
    {
        echo $this->createContent($indent);
    }

    /**
     *
     */
    public function createContent($indent = 0)
    {
        $output = "";

        $str_indent = str_repeat(' ', $indent);

        $_content = $this->getContent();
        $output .= ($str_indent . "<" . $this->getName());

        $attribute = $this->getAttr();
        foreach ($attribute as $name => $value) {
            $output .= (' ' . $name . '="' . $value . '"');
        }

        $output .= $this->_string;
        $output .= (">\n");
        if (!is_array($_content)) {
            $attributes = "";
            foreach ($attribute as $name => $value) {
                $attributes .= ($name . '=&gt;"' . $value . '"; ');
            }
            print "Fehler in HTML.class.php: Es fehlt ein Content-Element für ein Element des Typs \"&lt;" . $this->getName() . "&gt;\" (Attribute: $attributes).";

            return;
        }

        foreach ($_content as $content) {
            if (is_object($content)) {
                // der aktuelle Content ist ein Object
                // also ein HTML-Element. Also geben
                // wir es aus
                $classname = mb_strtolower(get_class($content));
                $valid_classes = ['studip\button', 'studip\linkbutton', 'messagebox'];
                if (in_array($classname, $valid_classes)) {
                    $output .= $content;
                } else {
                    $output .= $content->createContent($indent + 4);
                }
                // Rekursion lässt grüßen ...
            } else {
                // Content ist ein String. Jeden Zeile
                // geben wir getrennt aus
                $zeilen = explode("\n", $content);
                $echo = "";

                if ($this->has_textarea) {

                    // look for textarea in content
                    $text_area = false;
                    foreach ($zeilen as $zeile) {

                        if (mb_strstr($zeile, "<textarea"))
                            $text_area = true;

                        if ($text_area)
                            $echo .= $zeile . "\n";
                        else
                            $echo .= $str_indent . "    " . $zeile . "\n";

                        if (mb_strstr($zeile, "</textarea"))
                            $text_area = false;
                    }
                } else {
                    // standard
                    foreach ($zeilen as $zeile) {
                        $echo .= $str_indent . "    " . $zeile . "\n";
                    }
                }
                $output .= $echo;
            }
        }
        $output .= ($str_indent . "</" . $this->getName() . ">\n");

        return $output;
    }
}

include_once("LazyHTML.class.php");

