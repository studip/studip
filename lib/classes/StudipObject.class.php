<?php
# Lifter002: TEST
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipObject.class.php
//
// Class to provide basic properties of an StudipObject in Stud.IP
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


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_STUDIPOBJECT Is instance of a studip object
 * @access public
 */
define("INSTANCEOF_STUDIPOBJECT", "StudipObject");
# =========================================================================== #


/**
 * StudipObject.class.php
 *
 * Class to provide basic properties of an StudipObject in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     studip_core
 * @modulegroup core
 */
class StudipObject extends AuthorObject
{

# Define all required variables ============================================= #
    /**
     * The unique ID
     * @access   private
     * @var      integer $id
     */
    var $objectID;
    
    /**
     * The unique ID of the author
     * @access   private
     * @var      string $authorID
     */
    var $authorID;
    
    /**
     * The unique range ID
     * @access   private
     * @var      string $rangeID
     */
    var $rangeID;

    /**
     * Constructor
     * @access   public
     * @param string $objectID The ID of an existing object
     */
    public function __construct($objectID = "")
    {
        
        /* For good OOP: Call constructor ------------------------------------- */
        parent::__construct();
        $this->instanceof = INSTANCEOF_STUDIPOBJECT;
        /* -------------------------------------------------------------------- */
        
        /* Set default values ------------------------------------------------- */
        $this->objectID = $objectID;
        $this->authorID = "";
        $this->rangeID  = "";
        /* -------------------------------------------------------------------- */
    }

    /**
     * Creates a new ID
     * @return  string  The new ID
     */
    public static function createNewID()
    {
        srand((double)microtime() * 1000000);
        return md5(uniqid(rand()));
    }
    
    /**
     * Gets the objectID
     * @return  string  The objectID
     */
    public function getObjectID()
    {
        return $this->objectID;
    }
    
    /**
     * Sets the objectID
     * @param String $objectID The object ID
     */
    public function setObjectID($objectID)
    {
        if (empty ($objectID))
            $this->throwError(1, _("Die ObjectID darf nicht leer sein."));
        else
            $this->objectID = $objectID;
    }
    
    /**
     * Gets the authorID
     * @return  string  The authorID
     */
    public function getAuthorID()
    {
        return $this->authorID;
    }
    
    /**
     * Gets the authorname
     * @return  string  The authorID
     */
    public function getAuthor()
    {
        return get_username($this->authorID);
    }
    
    /**
     * Gets the full name of the author
     * @return  string  The authorID
     */
    public function getFullname()
    {
        return get_fullname($this->authorID);
    }
    
    /**
     * Sets the authorID
     * @param String $authorID The author ID
     */
    public function setAuthorID($authorID)
    {
        if (empty ($authorID))
            throwError(1, _("Die AuthorID darf nicht leer sein."));
        else
            $this->authorID = $authorID;
    }
    
    /**
     * Gets the rangeID
     * @return  string  The rangeID
     */
    public function getRangeID()
    {
        return $this->rangeID;
    }
    
    /**
     * Sets the rangeID
     * @param String $rangeID The range ID
     */
    public function setRangeID($rangeID)
    {
        $this->rangeID = $rangeID;
    }
}
