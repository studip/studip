<?php
# Lifter002: TEST
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// DatabaseObject.class.php
//
// Class to provide basic properties of an DatabseObject in Stud.IP
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
define("INSTANCEOF_DATABASEOBJECT", "DatabaseObject");
# =========================================================================== #


/**
 * DatabaseObject.class.php
 *
 * Class to provide basic properties of an DatabaseObject in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     studip_core
 * @modulegroup core
 */
class DatabaseObject extends AuthorObject
{

# Define constructor and destructor ========================================= #
    /**
     * Constructor
     *
     * @access   public
     */
    public function __construct()
    {
        /* For good OOP: Call constructor ------------------------------------- */
        parent::__construct();
        $this->instanceof = INSTANCEOF_DATABASEOBJECT;
        /* -------------------------------------------------------------------- */
    }
# =========================================================================== #


# Define public functions =================================================== #
    /**
     * Gets the objectID
     *
     * @access  public
     * @return  string  The objectID
     */
    public function getObjectID()
    {
        return $this->objectID;
    }

    /**
     * Sets the objectID
     *
     * @access  public
     *
     * @param   String $objectID The object ID
     */
    public function setObjectID($objectID)
    {
        if (empty ($objectID))
            throwError(1, _("Die ObjectID darf nicht leer sein."));
        else
            $this->objectID = $objectID;
    }

    /**
     * Gets the authorID
     *
     * @access  public
     * @return  string  The authorID
     */
    public function getAuthorID()
    {
        return $this->authorID;
    }

    /**
     * Sets the authorID
     *
     * @access  public
     *
     * @param   String $authorID The author ID
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
     *
     * @access  public
     * @return  string  The rangeID
     */
    public function getRangeID()
    {
        return $this->objectID;
    }

    /**
     * Sets the rangeID
     *
     * @access  public
     *
     * @param   String $rangeID The range ID
     */
    public function setRangeID($rangeID)
    {
        if (empty ($rangeID))
            throwError(1, _("Die RangeID darf nicht leer sein."));
        else
            $this->rangeID = $rangeID;
    }

}

?>
