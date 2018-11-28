<?php

/**
 * Abstract class GlobalSearchModule
 *
 * Module for global search extensions, e.g. forum, files or users
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
abstract class GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    abstract public static function getName();

    /**
     * Has to return a SQL Query that discovers all objects. All retrieved data is passed row by row to getGlobalSearchFilter
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    abstract public static function getSQL($search);

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $data One row returned from getSQL SQL Query
     * @param $search The searchstring (Use for markup e.g. self::mark)
     * @return mixed Information Array
     */
    abstract public static function filter($data, $search);

    /**
     * Returns the URL that can be called for a full search.
     *
     * This could become obsolete when we have a real global search page.
     * Overwrite this method in your subclass to provide the category
     * specific search page.
     *
     * @param string $searchterm what to search for?
     */
    public static function getSearchURL($searchterm)
    {
        return '';
    }

    /**
     * Function to mark a querystring in a resultstring
     *
     * @param $string
     * @param $query
     * @param bool|true $filename
     * @return mixed
     */
    public static function mark($string, $query, $longtext = false, $filename = true)
    {
        // Secure
        $string = strip_tags($string);

        // Maximum length for an unshortened string.
        $maxlength = 100;

        if (mb_strpos($query, '/') !== false) {
            $args = explode('/', $query);
            if ($filename) {
                return self::mark($string, trim($args[1]));
            }
            return self::mark($string, trim($args[0]));
        }

        $query = trim($query);

        // Replace direct string
        $result = preg_replace("/{$query}/i", "<mark>$0</mark>", $string, -1, $found);

        if ($found) {
            // Check for overlength
            if ($longtext && mb_strlen($result) > $maxlength) {
                $start = max(array(0, mb_stripos($result, '<mark>') - 20));
                return '[...]' . mb_substr($result, $start, $maxlength) . '[...]';
            }

            return $result;
        }

        // Replace camelcase
        $i = 1;
        $replacement = "${$i}";
        foreach (preg_split('//u', mb_strtoupper($query), -1, PREG_SPLIT_NO_EMPTY) as $letter) {
            $queryletter[] = "({$letter})";
            $replacement .= '<mark>$' . ++$i . '</mark>$' . ++$i;
        }

        $pattern = '/([\w\W]*)' . implode('([\w\W]*)', $queryletter) . '/';
        $result = preg_replace($pattern, $replacement, $string, -1, $found);

        if ($found) {
            // Check for overlength
            if ($longtext && mb_strlen($result) > $maxlength) {
                $start = max(array(0, mb_stripos($result, '<mark>') - 20));
                $space = mb_stripos($result, ' ', $start);
                $start = $space < $start + 20 ? $space : $start;
                return '[...]' . mb_substr($result, $start, $maxlength) . '[...]';
            }

            return $result;
        }

        // Check for overlength
        if ($longtext && mb_strlen($result) > $maxlength) {
            return '[...]' . mb_substr($string, 0, $maxlength) . '[...]';
        }

        if (mb_strlen($string) > $maxlength) {
            return mb_substr($string, 0, $maxlength) . '[...]';
        }

        return $string;
    }
}
