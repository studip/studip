<?php
# Lifter010: TODO
/**
 * SQLSearch.class.php - A class-structure for alle search-objects in Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * A class-structure for alle search-objects in Stud.IP.
 * It is (mainly?) used in QuickSearch to display searchresults and the
 * layout of them.
 * 
 * @author Rasmus Fuhse
 * 
 */
abstract class SearchType
{

    /**
     * title of the search like "search for courses" or just "courses"
     *
     * @return string
     */
    public function getTitle() 
    {
        return "";
    }
    
    /**
     * Returns an URL to a picture of that type. Return "" for nothing found.
     * For example: "return CourseAvatar::getAvatar($id)->getURL(Avatar::SMALL)".
     *
     * @param string $id
     *
     * @return: string URL to a picture
     */
    public function getAvatar($id) 
    {
        return "";
    }
    
    /**
     * Returns an HTML-Tag of a picture of that type. Return "" for nothing found.
     * For example: "return CourseAvatar::getAvatar($id)->getImageTag(Avatar::SMALL)".
     *
     * @param string $id
     *
     * @return string HTML of a picture
     */
    public function getAvatarImageTag($id) 
    {
        return "";
    }
    
    /**
     * Returns the results to a given keyword. To get the results is the
     * job of this routine and it does not even need to come from a database.
     * The results should be an array in the form
     * array (
     *   array($key, $name),
     *   array($key, $name),
     *   ...
     * )
     * where $key is an identifier like user_id and $name is a displayed text
     * that should appear to represent that ID.
     *
     * @param string $keyword
     * @param string $contextual_data
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     *
     * @return array
     */
    public function getResults($keyword, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0) 
    {
        return [["", _("Die Suchklasse, die Sie verwenden, enthält keine Methode getResults.")]];
    }
    
    /**
     * Returns the path to this file, so that this class can be autoloaded and is 
     * always available when necessary.
     * Should be: "return __file__;"
     * 
     * @return string path to this file
     */
    abstract public function includePath();
}

