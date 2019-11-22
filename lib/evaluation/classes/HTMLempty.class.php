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

class HTMLempty
{

# Define all required variables ============================================= #
  /**
   * Holds the name of the element.
   *
   * @access   private
   * @var      string $_name
   */
  var $_name = "";

  /**
   * Holds the attributes of the element.
   *
   * @access   private
   * @var      array $_attribute
   */
  var $_attribute = [];

  /**
   * Holds additional attributes (strings generated from studip functions)
   *
   * @access   private
   * @var      array $_string
   */
  var $_string = "";
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  public function __construct($name)
  {
    if (preg_match('/^[a-zA-Z.:][\w\-_\.:]*$/i', $name)) {
      $this->_name = $name;
    } else {
      trigger_error("Unerlaubter Name für ein HTML-Element : '" .
                    $name . "'", E_USER_ERROR);
    }
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #
  /**
   *
   */
  function addAttr($name, $wert = null)
  {
    if (isset ($wert)) {
      $name = (string)$name;
      if (preg_match('/^[a-zA-Z.:][\w\-_\.:]*$/i', $name)) {
        $this->_attribute[$name] = $wert;
      } else {
        trigger_error("Unerlaubter Name für ein HTML-Attribut : '" . $name . "'",
                      E_USER_ERROR);
      }
    } else {
      if (is_scalar($name)) {
        // Dies braucht man, falls man Attribute hinzufügen
        // will, die keinen Wert haben, wie man es bei
        // <option selected> kennt
        if (preg_match('/^[a-zA-Z\.:][\w\-_\.:]*$/i', $name)) {
          $this->_attribute[$name] = $name;
          // Da wir gültiges HTML bzw XML schreiben
          // muss jedes Attribut auch einen Wert haben
          // selected wird dann zu selected="selected"
        } else {
          trigger_error("Unerlaubter Name für ein HTML-Attribut : '" . $name . "'",
                        E_USER_ERROR);
        }
      } elseif (is_array($name)) {
        // Jedes Arrayelement durchgehen
        foreach ($name as $key => $wert) {
          if (is_int($key)) {
            // Arrayelement wurde mit $foo[] hinzugefügt
            // also ohne Schlüssel. Ich nehme dann an
            // das es sich um ein Attribut wie
            // 'selected' oder 'readonly' handelt
            if (preg_match('/^[a-zA-Z\.:][\w\-_\.:]*$/i', $wert)) {
              $this->_attribute[$wert] = $wert;
            } else {
              trigger_error("Unerlaubter Name für ein HTML-Attribut : '" .
                            $wert . "'", E_USER_ERROR);
            }
          } else {
            $key = (string)$key;
            if (preg_match('/^[a-zA-Z\.:][\w\-_\.:]*$/i', $key)) {
              $this->_attribute[$key] = $wert;
            } else {
              trigger_error("Unerlaubter Name für ein HTML-Attribut : '" .
                            $key . "'", E_USER_ERROR);
            }
          }
        }
      } else {
        trigger_error("Erster Parameter muss ein Scalar oder ein Array sein",
                      E_USER_ERROR);
      }
    }
  }

  /**
   * to support Stud.IP legacy functions like makeButton...
   */
  function addString($string)
  {
    $this->_string .= " " . $string;
  }

  /**
   *
   */
  function getName()
  {
    return $this->_name;
  }

  /**
   *
   */
  function getAttr()
  {
    return $this->_attribute;
  }

  /**
   *
   */
  function printContent($indent = 0)
  {
    echo $this->createContent($str);
  }

  /**
   *
   */
  function createContent($indent = 0)
  {
    $str = str_repeat(' ', $indent);
    $str .= "<" . $this->getName();
    $attrib = $this->getAttr();
    foreach ($attrib as $name => $value) {
      $str .= ' ' . $name . '="' . htmlReady($value) . '"';
    }
    $str .= $this->_string;
    $str .= ">\n";

    return ($str);
  }
# ===================================================== end: public functions #


# Define private functions ================================================== #

# ==================================================== end: private functions #
}

?>
