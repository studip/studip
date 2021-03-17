<?php
# Lifter010: TODO
/**
 * StandardSearch.class.php - Class of type SearchType used for searches with QuickSearch
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
 * Class of type SearchType used for searches with QuickSearch
 * (lib/classes/QuickSearch.class.php). You can search with a sql-syntax in the
 * database. You just need to give in a query like for a PDB-prepare statement
 * and at least the variable ":input" in the query (the :input will be replaced
 * with the input of the QuickSearch userinput.
 *  [code]
 *  $search = new SQLSearch("username");
 *  [/code]
 *
 * @author Rasmus Fuhse
 *
 */

class StandardSearch extends SQLSearch
{

    public $search;
    public $search_settings;

    /**
     *
     * @param string $search The search type.
     *
     * @param Array $search_settings Settings for the selected seach type.
     *     Depending on the search type different settings are possible
     *     which can change the output or the display of the output
     *     of the search. The array must be an associative array
     *     with the setting as array key.
     *     The following settings are implemented:
     *     Search type 'room':
     *     - display_seats: If set to true, the seats will be displayed
     *       after the name of the room.
     *
     * @return void
     */
    public function __construct($search, $search_settings = [])
    {
        if (is_array($search_settings)) {
            $this->search_settings = $search_settings;
        }

        $this->avatarLike = $this->search = $search;
        $this->sql = $this->getSQL();
    }


    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        switch ($this->search) {
            case "username":
            case "user_id":
                return _("Nutzer suchen");
            case "Seminar_id":
            case "AnySeminar_id":
                return _("Veranstaltung suchen");
            case "Arbeitsgruppe_id":
                return _("Arbeitsgruppe suchen");
            case "Institut_id":
                return _("Einrichtung suchen");
        }
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL()
    {
        switch ($this->search) {
            case "username":
                $this->extendedLayout = true;
                return "SELECT DISTINCT auth_user_md5.username, CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname, ' (', auth_user_md5.username, ')'), auth_user_md5.perms " .
                        "FROM auth_user_md5 LEFT JOIN user_info ON (user_info.user_id = auth_user_md5.user_id) " .
                        "LEFT JOIN user_visibility ON (user_visibility.user_id = auth_user_md5.user_id) " .
                        "WHERE (CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input) AND " . get_vis_query('auth_user_md5', 'search') .
                        " ORDER BY Nachname ASC, Vorname ASC";
            case "user_id":
                $this->extendedLayout = true;
                return "SELECT DISTINCT auth_user_md5.user_id, CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname, ' (', auth_user_md5.username, ')'), auth_user_md5.perms " .
                        "FROM auth_user_md5 LEFT JOIN user_info ON (user_info.user_id = auth_user_md5.user_id) " .
                    "LEFT JOIN user_visibility ON (user_visibility.user_id = auth_user_md5.user_id) " .
                    "WHERE (CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input) AND " . get_vis_query('auth_user_md5', 'search') .
                        " ORDER BY Nachname ASC, Vorname ASC";
            case "Seminar_id":
                $semester = "CONCAT(' (',
                    IF(semester_courses.semester_id IS NULL, '" . _('unbegrenzt') . "',
                        IF(COUNT(DISTINCT semester_courses.semester_id) > 1, CONCAT_WS(' - ', (SELECT start_semester.name FROM `semester_data` AS start_semester WHERE start_semester.semester_id = semester_courses.semester_id ORDER BY `beginn` ASC LIMIT 1), (SELECT end_semester.name FROM `semester_data` AS end_semester WHERE end_semester.semester_id = semester_courses.semester_id ORDER BY `beginn` DESC LIMIT 1)), sem1.name)),
                    ')')";
                return "SELECT DISTINCT seminare.Seminar_id, CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.Name,  ".$semester.") " .
                    "FROM seminare " .
                    "LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id) " .
                    "JOIN `semester_data` sem1 ON (seminare.`start_time` = sem1.`beginn`) " .
                    "LEFT JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id AND seminar_user.status = 'dozent') " .
                    "LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = seminar_user.user_id) " .
                    "WHERE (seminare.Name LIKE :input " .
                    "OR CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE :input " .
                    "OR seminare.VeranstaltungsNummer LIKE :input " .
                    "OR seminare.Untertitel LIKE :input " .
                    "OR seminare.Beschreibung LIKE :input " .
                    "OR seminare.Ort LIKE :input " .
                    "OR seminare.Sonstiges LIKE :input) " .
                    "AND seminare.visible = 1 " .
                    "AND seminare.status NOT IN ('".implode("', '", studygroup_sem_types())."') " .
                    " ORDER BY sem1.`beginn` DESC, " .
                    (Config::get()->IMPORTANT_SEMNUMBER ? "seminare.`VeranstaltungsNummer`, " : "") .
                    "seminare.`Name`";
            case "AnySeminar_id":
                $semester = "CONCAT(' (',
                    IF(semester_courses.semester_id IS NULL, '" . _('unbegrenzt') . "',
                        IF(COUNT(DISTINCT semester_courses.semester_id) > 1, CONCAT_WS(' - ', (SELECT start_semester.name FROM `semester_data` AS start_semester WHERE start_semester.semester_id = semester_courses.semester_id ORDER BY `beginn` ASC LIMIT 1), (SELECT end_semester.name FROM `semester_data` AS end_semester WHERE end_semester.semester_id = semester_courses.semester_id ORDER BY `beginn` DESC LIMIT 1)), sem1.name)),
                    ')')";
                return "SELECT DISTINCT seminare.Seminar_id, CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.Name,  ".$semester.") " .
                    "FROM seminare " .
                    "LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id) " .
                    "JOIN `semester_data` sem1 ON (seminare.`start_time` = sem1.`beginn`) " .
                    "LEFT JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id AND seminar_user.status = 'dozent') " .
                    "LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = seminar_user.user_id) " .
                    "WHERE (seminare.Name LIKE :input " .
                    "OR CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ') " .
                    "OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ') " .
                    "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                    "OR seminare.VeranstaltungsNummer LIKE :input " .
                    "OR seminare.Untertitel LIKE :input " .
                    "OR seminare.Beschreibung LIKE :input " .
                    "OR seminare.Ort LIKE :input " .
                    "OR seminare.Sonstiges LIKE :input) " .
                    " ORDER BY sem1.`beginn` DESC, " .
                    (Config::get()->IMPORTANT_SEMNUMBER ? "seminare.`VeranstaltungsNummer`, " : "") .
                    "seminare.`Name`";
            case "Arbeitsgruppe_id":
                return "SELECT DISTINCT seminare.Seminar_id, seminare.Name " .
                        "FROM seminare " .
                            "LEFT JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id AND seminar_user.status = 'dozent') " .
                            "LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = seminar_user.user_id) " .
                        "WHERE (seminare.Name LIKE :input " .
                            "OR CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ') " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR seminare.VeranstaltungsNummer LIKE :input " .
                            "OR seminare.Untertitel LIKE :input " .
                            "OR seminare.Beschreibung LIKE :input " .
                            "OR seminare.Ort LIKE :input " .
                            "OR seminare.Sonstiges LIKE :input) " .
                            "AND seminare.visible = 1 " .
                            "AND seminare.status IN ('".implode("', '", studygroup_sem_types())."') " .
                        "ORDER BY seminare.Name";
            case "Institut_id":
                return "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                        "FROM Institute " .
                            "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                        "WHERE Institute.Name LIKE :input " .
                            "OR Institute.Strasse LIKE :input " .
                            "OR Institute.email LIKE :input " .
                            "OR range_tree.name LIKE :input " .
                        "ORDER BY Institute.Name";
        }
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
