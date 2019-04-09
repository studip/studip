<?php
# Lifter010: TODO
/**
 * SeminarSearch.class.php
 * class to adapt StudipSemSearch to Quicksearch
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class SeminarSearch extends SearchType
{

    /**
     * title of the search like "search for courses" or just "courses"
     * @return string
     */
    public function getTitle() {
        return _("Veranstaltungen suchen");
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
     * @param keyword: string
     * @param array $contextual_data an associative array with more variables
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     * @return array
     */
     public function getResults($keyword, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0) {
         $search_helper = new StudipSemSearchHelper();
         $search_helper->setParams(
             [
                 'quick_search' => $keyword,
                 'qs_choose' => $contextual_data['search_sem_qs_choose'] ? $contextual_data['search_sem_qs_choose'] : 'all',
                 'sem' => isset($contextual_data['search_sem_sem']) ? $contextual_data['search_sem_sem'] : 'all',
                 'category' => $contextual_data['search_sem_category'],
                 'scope_choose' => $contextual_data['search_sem_scope_choose'],
                 'range_choose' => $contextual_data['search_sem_range_choose']],
             !(is_object($GLOBALS['perm'])
                 && $GLOBALS['perm']->have_perm(
                     Config::Get()->SEM_VISIBILITY_PERM)));
         $search_helper->doSearch();
         $result = $search_helper->getSearchResultAsArray();

         if (empty($result)) {
             return [];
         }

         $query = "SELECT s.Seminar_id, CONCAT_WS(' ', s.VeranstaltungsNummer, s.name, CONCAT(' (', 
            IF(s.duration_time = -1, CONCAT_WS(' - ', sem1.name, '" . _('unbegrenzt') . "'),
                IF(s.duration_time != 0, CONCAT_WS(' - ', sem1.name, sem2.name), sem1.name)), ')')) AS Name
                   FROM seminare AS s
                   JOIN `semester_data` sem1 ON (s.`start_time` = sem1.`beginn`)
                        LEFT JOIN `semester_data` sem2 ON (s.`start_time` + s.`duration_time` = sem2.`beginn`)
                   LEFT JOIN seminar_user AS su ON (su.Seminar_id = s.Seminar_id AND su.status='dozent')
                   LEFT JOIN auth_user_md5 USING (user_id)
                   WHERE s.Seminar_id IN (?)
                   GROUP BY s.Seminar_id";
         if (Config::get()->IMPORTANT_SEMNUMBER) {
             $query .= " ORDER BY IFNULL(sem2.beginn, sem1.beginn) DESC, s.VeranstaltungsNummer, s.Name";
         } else {
             $query .= " ORDER BY IFNULL(sem2.beginn, sem1.beginn) DESC, s.Name";
         }
         $statement = DBManager::get()->prepare($query);
         $statement->execute([
            array_slice($result, $offset, $limit) ?: ''
         ]);
         return $statement->fetchAll(PDO::FETCH_NUM);
     }


    /**
     * Returns the path to this file, so that this class can be autoloaded and is
     * always available when necessary.
     * Should be: "return __file__;"
     *
     * @return string   path to this file
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
