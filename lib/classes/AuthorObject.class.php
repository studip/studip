<?php
# Lifter002: TEST
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// AuthorObject.class.php
//
// Class to provide basic properties of an object in Stud.IP
//
// Copyright (c) 2003 Alexander Willner <mail@AlexanderWillner.de>
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


define("ERROR_NORMAL", "1");
define("ERROR_CRITICAL", "8");


/**
 * AuthorObject.class.php
 *
 * Class to provide basic properties of an object in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     studip_core
 * @modulegroup core
 */
class AuthorObject
{

    /**
     * Holds the code and description of an internal error
     * @access   private
     * @var      array $errorArray
     */
    public $errorArray;

    /**
     * Holds the emailadress of the author
     * @access   private
     * @var      array $authorEmail
     */
    public $authorEmail;

    /**
     * Holds the name of the author
     * @access   private
     * @var      array $authorName
     */
    public $authorName;

    /**
     * Holds the type of object. See INSTANCEOF_*
     * @access   private
     * @var      string $instanceof
     */
    public $instanceof;

    /**
     * Constructor
     * @access   public
     */
    public function __construct()
    {
        $this->instanceof = 'AuthorObject';
        $this->errorArray = [];
    }


    /**
     * Destructor. Should be used every time after an object is not longer
     * usefull!
     * @access   public
     */
    public function finalize()
    {
    }

    /**
     * Sets the emailaddress of the author
     * @access  public
     * @param string $email The emailaddress
     */
    public function setAuthorEmail($email)
    {
        $this->authorEmail = $email;
    }

    /**
     * Gets the emailaddress of the author
     * @access  public
     * @return  string The emailaddress
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * Sets the name of the author
     * @access  public
     * @param string $name The name
     */
    public function setAuthorName($name)
    {
        $this->authorNmae = $name;
    }

    /**
     * Gets the name of the author
     * @access  public
     * @return  string The name
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Gets the type of object
     * @access  public
     * @return  string The type of object. See INSTANCEOF_*
     */
    public function x_instanceof()
    {
        return $this->instanceof;
    }

    /**
     * Gives the internal errorcode
     * @access public
     * @return boolean True if an error exists
     */
    public function isError()
    {
        return (count($this->errorArray) != 0);
    }

    /**
     * Gives the codes and descriptions of the internal errors
     * @access  public
     * @return  array  The errors as an Array like "1" => "Could not open DB"
     */
    public function getErrors()
    {
        return $this->errorArray;
    }

    /**
     * Resets the errorcodes and descriptions
     * @access public
     */
    public function resetErrors()
    {
        $this->errorArray = [];
    }

    /**
     * Sets the errorcode (internal)
     * @access  public
     * @param integer $errcode The code of the error
     * @param string $errstring The description of the error
     * @param integer $errline The line
     * @param string $errfile The file
     * @param integer $errtype Defines wheter the error is critical
     */
    public function throwError($errcode, $errstring, $errline = 0, $errfile = 0, $errtype = ERROR_NORMAL)
    {
        if (!is_array($this->errorArray)) {
            $this->errorArray = [];
        }

        array_push($this->errorArray,
            ["code" => $errcode,
                "string" => $errstring,
                "file" => $errfile,
                "line" => $errline,
                "type" => $errtype]
        );
        if ($errtype == ERROR_CRITICAL) {
            @mail($this->getAuthorEmail(),
                "Critical error in Stud.IP",
                "Hello " . $this->getAuthorName() . "\n\n" .
                "there is an error in file " . $errfile . " " .
                "in line " . $errline . ". \n" .
                "The code is " . $errcode . "\n" .
                "Description: " . $errstring . ".\n\n\n" .
                "regards, *an AuthorObject*\n\n");
        }
    }

    /**
     * Sets the errorcode from other classes (internal)
     * @access  private
     * @param object $class The class with the error
     */
    public function throwErrorFromClass(&$class)
    {
        $this->errorArray = $class->getErrors();
        $class->resetErrors();
    }

    /**
     * An errorHandler for PHP-errors
     * @access  private
     * @param int $no Errornumber
     * @param string $str Errordescription
     * @param string $file Filename
     * @param int $line Linenumber
     * @param array $ctx All variables
     */
    public function errorHandler($no, $str, $file, $line, $ctx)
    {
        if (!($no & error_reporting())) {
            return;
        }
        $this->throwError($no, $str, $line, $file, ERROR_CRITICAL);
        echo createErrorReport($this, "Schwerer PHP-Laufzeitfehler");
    }
}

