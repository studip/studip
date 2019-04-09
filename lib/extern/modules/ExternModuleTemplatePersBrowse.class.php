<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplatePersBrowser.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplatePersBrowser
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplatePersBrowser.class.php
//
// Copyright (C) 2009 Peter Thienel <thienel@data-quest.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once 'lib/extern/views/extern_html_templates.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/dates.inc.php';

class ExternModuleTemplatePersBrowse extends ExternModule {

    public $markers = [];
    private $approved_params = [];
    private $range_tree;
    private $global_markers = [];

    /**
    *
    */
    public function __construct ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->data_fields = [
                'Nachname', 'Telefon', 'raum', 'Email', 'sprechzeiten'
        ];
        $this->registered_elements = [
                'SelectInstitutes',
                'LinkInternListCharacters' => 'LinkInternTemplate',
                'LinkInternListInstitutes' => 'LinkInternTemplate',
                'LinkInternPersondetails' => 'LinkInternTemplate',
                'TemplateListCharacters' => 'TemplateGeneric',
                'TemplateListInstitutes' => 'TemplateGeneric',
                'TemplateListPersons' => 'TemplateGeneric',
                'TemplateMain' => 'TemplateGeneric'
        ];

        $this->field_names = 
        [
                _("Name"),
                _("Telefon"),
                _("Raum"),
                _("Email"),
                _("Sprechzeiten")
        ];

        $this->approved_params = ['item_id', 'initiale'];

        $this->range_tree = TreeAbstract::GetInstance('StudipRangeTree');

        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    public function setup () {
        $this->elements['LinkInternListCharacters']->real_name = _("Verlinkung der alpabetischen Liste zur Personenliste");
        $this->elements['LinkInternListCharacters']->link_module_type = [16];
        $this->elements['LinkInternListInstitutes']->real_name = _("Verlinkung der Einrichtungsliste zur Personenliste");
        $this->elements['LinkInternListInstitutes']->link_module_type = [16];
        $this->elements['LinkInternPersondetails']->real_name = _("Verlinkung der Personenliste zum Modul MitarbeiterInnendetails");
        $this->elements['LinkInternPersondetails']->link_module_type = [2, 14];
        $this->elements['TemplateMain']->real_name = _("Haupttemplate");
        $this->elements['TemplateListInstitutes']->real_name = _("Einrichtungsliste");
        $this->elements['TemplateListPersons']->real_name = _("Personenliste");
        $this->elements['TemplateListCharacters']->real_name = _("Liste mit Anfangsbuchstaben der Nachnamen");

    }

    public function toStringEdit ($open_elements = '', $post_vars = '', $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateListPersons', 'user');
        $this->elements['TemplateMain']->markers = $this->getMarkerDescription('TemplateMain');
        $this->elements['TemplateListInstitutes']->markers = $this->getMarkerDescription('TemplateListInstitutes');
        $this->elements['TemplateListPersons']->markers = $this->getMarkerDescription('TemplateListPersons');
        $this->elements['TemplateListCharacters']->markers = $this->getMarkerDescription('TemplateListCharacters');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    public function getMarkerDescription ($element_name) {

        $markers['TemplateMain'] = [
            ['__GLOBAL__', _("Globale Variablen (gültig im gesamten Template).")],
            ['###CHARACTER###', ''],
            ['###INSTNAME###', ''],
            ['<!-- BEGIN PERS_BROWSER -->', ''],
            ['###LISTCHARACTERS###', _("Auflistung der Anfangsbuchstaben")],
            ['###LISTINSTITUTES###', _("Auflistung der Einrichtungen")],
            ['###LISTPERSONS###', _("Auflistung der gefundenen Personen")],
            ['<!-- END PERS_BROWSER -->', '']
        ];

        $markers['TemplateListInstitutes'] = [
            ['<!-- BEGIN LIST_INSTITUTES -->', ''],
            ['<!-- BEGIN INSTITUTE -->', ''],
            ['###INSTITUTE_NAME###', _("Name der Einrichtung (erster Level im Einrichtungsbaum)")],
            ['###INSTITUTE_COUNT_USER###', _("Anzahl der Personen innerhalb der Einrichtung (und untergeordneten Einrichtungen)")],
            ['###URL_LIST_PERSONS###', _("URL zur Personenlist mit diesem Anfangsbuchstaben")],
            ['<!-- END INSTITUTE -->', ''],
            ['<!-- END LIST_INSTITUTES -->', '']
        ];

        $markers['TemplateListCharacters'] = [
            ['<!-- BEGIN LIST_CHARACTERS -->', ''],
            ['<!-- BEGIN CHARACTER -->', ''],
            ['###CHARACTER_USER###', _("Anfangsbuchstabe der Namen zur Verlinkung nach alpabetische Übersicht")],
            ['###CHARACTER_COUNT_USER###', _("Anzahl der Personennamen mit diesem Anfangsbuchstaben")],
            ['###URL_LIST_PERSONS###', _("URL zur Personenlist mit diesem Anfangsbuchstaben")],
            ['<!-- END CHARACTER -->', ''],
            ['<!-- END LIST_CHARACTERS -->', '']
        ];

        $markers['TemplateListPersons'] = [
            ['<!-- BEGIN LIST_PERSONS -->', ''],
            ['<!-- BEGIN NO-PERSONS -->', ''],
            ['<!-- END NO-PERSONS -->', ''],
            ['<!-- BEGIN PERSONS -->', ''],
            ['<!-- BEGIN PERSON -->', ''],
            ['###FULLNAME###', ''],
            ['###LASTNAME###', ''],
            ['###FIRSTNAME###', ''],
            ['###TITLEFRONT###', ''],
            ['###TITLEREAR###', ''],
            ['###PERSONDETAIL-HREF###', ''],
            ['###USERNAME###', ''],
            ['###INSTNAME###', ''],
            ['###PHONE###', ''],
            ['###ROOM###', ''],
            ['###EMAIL###', ''],
            ['###EMAIL-LOCAL###', _("Der local-part der E-Mail-Adresse (vor dem @-Zeichen)")],
            ['###EMAIL-DOMAIN###', _("Der domain-part der E-Mail-Adresse (nach dem @-Zeichen)")],
            ['###OFFICEHOURS###', ''],
            ['###PERSON-NO###', ''],
            $this->insertDatafieldMarkers('user', $markers, 'TemplateList'),
            ['<!-- END PERSON -->', ''],
            ['<!-- END PERSONS -->', ''],
            ['<!-- END LIST_PERSONS -->', '']
        ];

        return $markers[$element_name];
    }

    private function getContent ($args = null, $raw = false) {
        if ($raw) {
            self::SetRawOutput();
        }

        if (trim($this->config->getValue('TemplateListInstitutes', 'template'))) {
            $content['PERS_BROWSER']['LISTINSTITUTES'] = $this->elements['TemplateListInstitutes']->toString(['content' => $this->getContentListInstitutes(), 'subpart' => 'LIST_INSTITUTES']);
        }
        if (trim($this->config->getValue('TemplateListCharacters', 'template'))) {
            $content['PERS_BROWSER']['LISTCHARACTERS'] = $this->elements['TemplateListCharacters']->toString(['content' => $this->getContentListCharacters(), 'subpart' => 'LIST_CHARACTERS']);
        }
        if (trim($this->config->getValue('TemplateListPersons', 'template'))) {
            $content['PERS_BROWSER']['LISTPERSONS'] = $this->elements['TemplateListPersons']->toString(['content' => $this->getContentListPersons(), 'subpart' => 'LIST_PERSONS']);
        }
        // set super global markers
        $content['__GLOBAL__'] = $this->global_markers;
        return $content;
    }

    private function getContentListPersons () {
        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'full_rev';
        }

        $selected_item_ids = $this->config->getValue('SelectInstitutes', 'institutesselected');
        // at least one institute has to be selected in the configuration
        if (!is_array($selected_item_ids)) {
            return [];
        }

        $sort = $this->config->getValue('Main', 'sort');
        $query_order = '';
        foreach ($sort as $key => $position) {
            if ($position > 0) {
                $query_order[$position] = $this->data_fields[$key];
            }
        }
        if ($query_order) {
            ksort($query_order, SORT_NUMERIC);
            $query_order = ' ORDER BY ' . implode(',', $query_order);
        }

        $module_params = $this->getModuleParams($this->approved_params);

        $dbv = DbView::getView('sem_tree');
        if ($module_params['initiale']) {
            if ($this->config->getValue('Main', 'onlylecturers')) {
                $current_semester = get_sem_num(time());
                $query = sprintf("SELECT ui.Institut_id, su.user_id "
                . "FROM seminar_user su "
                . "LEFT JOIN seminare s USING (seminar_id) "
                . "LEFT JOIN auth_user_md5 aum USING(user_id) "
                . "LEFT JOIN user_inst ui USING(user_id) "
                . "WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER('%s') "
                . "AND su.status = 'dozent' "
                . "AND s.visible = 1 "
                . "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) "
                . "AND ui.Institut_id IN ('%s') "
                . "AND ui.inst_perms = 'dozent' "
                . "AND ui.externdefault = 1 "
                . "AND " . get_ext_vis_query(),
                mb_substr($module_params['initiale'], 0, 1),
                $dbv->sem_number_sql,
                $current_semester,
                $dbv->sem_number_sql,
                $current_semester,
                $dbv->sem_number_end_sql,
                $current_semester,
                $dbv->sem_number_end_sql,
                implode("','", $selected_item_ids));
            } else {
                    // get only users with the given status
                $query = sprintf("SELECT ui.Institut_id, ui.user_id "
                    . "FROM user_inst ui "
                    . "LEFT JOIN auth_user_md5 aum USING(user_id) "
                    . "WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER('%s') "
                    . "AND ui.inst_perms IN('%s') "
                    . "AND ui.Institut_id IN ('%s') "
                    . "AND ui.externdefault = 1 "
                    . "AND " . get_ext_vis_query(),
                    mb_substr($module_params['initiale'], 0, 1),
                    implode("','", $this->config->getValue('Main', 'instperms')),
                    implode("','", $selected_item_ids));
            }
        // item_id is given and it is in the list of item_ids selected in the configuration
        } else if ($module_params['item_id'] && in_array($module_params['item_id'], $selected_item_ids)) {
            if ($this->config->getValue('Main', 'onlylecturers')) {
                $current_semester = get_sem_num(time());
                // get only users with status dozent in an visible seminar in the current semester
                $query = sprintf("SELECT ui.Institut_id, ui.user_id "
                    . "FROM user_inst ui "
                    . "INNER JOIN auth_user_md5 aum USING (user_id) "
                    . "LEFT JOIN seminar_user su USING(user_id) "
                    . "LEFT JOIN seminare s USING (seminar_id) "
                    . "WHERE ui.Institut_id = '%s' "
                    . "AND ui.inst_perms = 'dozent' "
                    . "AND ui.externdefault = 1 "
                    . "AND " . get_ext_vis_query()
                    . "AND su.status = 'dozent' "
                    . "AND s.visible = 1 "
                    . "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) ",
                    $module_params['item_id'],
                    $dbv->sem_number_sql,
                    $current_semester,
                    $dbv->sem_number_sql,
                    $current_semester,
                    $dbv->sem_number_end_sql,
                    $current_semester,
                    $dbv->sem_number_end_sql);
            } else {
                // get only users with the given status
                $query = sprintf("SELECT ui.Institut_id, ui.user_id "
                    . "FROM user_inst ui "
                    . "INNER JOIN auth_user_md5 aum USING (user_id) "
                    . "WHERE ui.Institut_id = '%s' "
                    . "AND ui.inst_perms IN('%s') "
                    . "AND ui.externdefault = 1 "
                    . "AND " . get_ext_vis_query(),
                    $module_params['item_id'],
                    implode("','", $this->config->getValue('Main', 'instperms')));
            }
        } else {
            return [];
        }

        $rows = DBManager::get()->fetchAll($query);

        $user_list = [];
        foreach ($rows as $row) {
            if (!isset($user_list[$row['user_id']])) {
                $user_list[$row['user_id']] = $row['user_id'] . $row['Institut_id'];
            }
        }

        if (count($user_list) === 0) {
            return [];
        }

        $query = sprintf(
            "SELECT ui.Institut_id, ui.raum, ui.sprechzeiten, ui.Telefon, "
            . "inst_perms,  i.Name, aum.Email, aum.user_id, username, "
            . "%s AS fullname, aum.Nachname, aum.Vorname "
            . "FROM user_inst ui "
            . "LEFT JOIN Institute i USING(Institut_id) "
            . "LEFT JOIN auth_user_md5 aum USING(user_id) "
            . "LEFT JOIN user_info uin USING(user_id) "
            . "WHERE CONCAT(ui.user_id, ui.Institut_id) IN ('%s') "
            . "AND " . get_ext_vis_query()
            . "ORDER BY aum.Nachname, aum.Vorname ",
            $GLOBALS['_fullname_sql'][$nameformat],
            implode("','", $user_list));

        $rows = DBManager::get()->fetchAll($query);

        $j = 0;
        foreach ($rows as $row) {
            $content['PERSONS']['PERSON'][$j]['FULLNAME'] = ExternModule::ExtHtmlReady($row['fullname']);
            $content['PERSONS']['PERSON'][$j]['LASTNAME'] = ExternModule::ExtHtmlReady($row['Nachname']);
            $content['PERSONS']['PERSON'][$j]['FIRSTNAME'] = ExternModule::ExtHtmlReady($row['Vorname']);
            $content['PERSONS']['PERSON'][$j]['TITLEFRONT'] = ExternModule::ExtHtmlReady($row['title_front']);
            $content['PERSONS']['PERSON'][$j]['TITLEREAR'] = ExternModule::ExtHtmlReady($row['title_rear']);
            $content['PERSONS']['PERSON'][$j]['PERSONDETAIL-HREF'] = $this->elements['LinkInternPersondetails']->createUrl(['link_args' => 'username=' . $row['username']]);
            $content['PERSONS']['PERSON'][$j]['USERNAME'] = $row['username'];
            $content['PERSONS']['PERSON'][$j]['INSTNAME'] = ExternModule::ExtHtmlReady($row['Name']);
            $content['PERSONS']['PERSON'][$j]['PHONE'] = ExternModule::ExtHtmlReady($row['Telefon']);
            $content['PERSONS']['PERSON'][$j]['ROOM'] = ExternModule::ExtHtmlReady($row['raum']);
            $content['PERSONS']['PERSON'][$j]['EMAIL'] = ExternModule::ExtHtmlReady(get_visible_email($row['user_id']));
            $content['PERSONS']['PERSON'][$j]['EMAIL-LOCAL'] = array_shift(explode('@', $content['PERSONS']['PERSON'][$j]['EMAIL']));
            $content['PERSONS']['PERSON'][$j]['EMAIL-DOMAIN'] = array_pop(explode('@', $content['PERSONS']['PERSON'][$j]['EMAIL']));
            $content['PERSONS']['PERSON'][$j]['OFFICEHOURS'] = ExternModule::ExtHtmlReady($row['sprechzeiten']);
            $content['PERSONS']['PERSON'][$j]['PERSON-NO'] = $j + 1;

            // generic data fields
            if (is_array($generic_datafields)) {
                $localEntries = DataFieldEntry::getDataFieldEntries($row['user_id'], 'user');
                $k = 1;
                foreach ($generic_datafields as $datafield) {
                    if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
                        if ($localEntries[$datafield]->getType() == 'link') {
                            $localEntry = ExternModule::extHtmlReady($localEntries[$datafield]->getValue());
                        } else {
                            $localEntry = $localEntries[$datafield]->getDisplayValue();
                        }
                        if ($localEntry) {
                            $content['PERSONS']['PERSON'][$j]['DATAFIELD_' . $k] = $localEntry;
                        }
                    }
                    $k++;
                }
            }
            $j++;
        }
        if (!$module_params['initiale']) {
            $this->global_markers['INSTNAME'] = $content['PERSONS']['PERSON'][0]['INSTNAME'];
        } else {
            $this->global_markers['CHARACTER'] = mb_substr($module_params['initiale'], 0, 1);
        }

        return $content;
    }


    private function getContentListCharacters () {
        $selected_item_ids = $this->config->getValue('SelectInstitutes', 'institutesselected');
        // at least one institute has to be selected in the configuration
        if (!is_array($selected_item_ids)) {
            return [];
        }
        $content = [];

        // at least one institute has to be selected in the configuration
        if (!is_array($selected_item_ids)) {
            return [];
        }
        $dbv = DbView::getView('sem_tree');
        if ($this->config->getValue('Main', 'onlylecturers')) {
            $current_semester = get_sem_num(time());
                $query = sprintf("SELECT COUNT(DISTINCT aum.user_id) as count_user, "
                . "UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale "
                . "FROM user_inst ui "
                . "LEFT JOIN seminar_user su ON ui.user_id = su.user_id "
                . "LEFT JOIN seminare s ON su.Seminar_id = s.Seminar_id "
                . "LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id "
                . "WHERE su.status = 'dozent' AND s.visible = 1 "
                . "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1))) "
                . "AND TRIM(aum.Nachname) != '' "
                . "AND ui.Institut_id IN ('%s') "
                . "AND ui.externdefault = 1 "
                . "AND " . get_ext_vis_query()
                . "GROUP BY initiale",
                $dbv->sem_number_sql,
                $current_semester,
                $dbv->sem_number_sql,
                $current_semester,
                $dbv->sem_number_end_sql,
                $current_semester,
                $dbv->sem_number_end_sql,
                implode("','", $selected_item_ids));
        } else {
            $query = sprintf("SELECT COUNT(DISTINCT ui.user_id) as count_user, "
                . "UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale "
                . "FROM user_inst ui "
                . "LEFT JOIN auth_user_md5 aum USING (user_id) "
                . "WHERE ui.inst_perms IN ('%s') "
                . "AND ui.Institut_id IN ('%s') "
                . "AND ui.externdefault = 1 "
                . "AND TRIM(aum.Nachname) != '' "
                . "GROUP BY initiale",
                implode("','", $this->config->getValue('Main', 'instperms')),
                implode("','", $selected_item_ids));
        }

        $rows = DBManager::get()->fetchAll($query);
        foreach ($rows as $row) {
            $content['LIST_CHARACTERS']['CHARACTER'][] = [
                'CHARACTER_USER'       => ExternModule::ExtHtmlReady($row['initiale']),
                'CHARACTER_COUNT_USER' => ExternModule::ExtHtmlReady($row['count_user']),
                'URL_LIST_PERSONS'     => $this->getLinkToModule('LinkInternListCharacters', ['initiale' => $row['initiale']]),
            ];
        }
        return $content;
    }

    private function getContentListInstitutes () {
        $selected_item_ids = $this->config->getValue('SelectInstitutes', 'institutesselected');
        // at least one institute has to be selected in the configuration
        if (!is_array($selected_item_ids)) {
            return [];
        }
        $content = [];

        $first_levels = $this->range_tree->getKids('root');
    //  var_dump($first_levels);
        $current_semester = get_sem_num(time());

        $dbv = DbView::getView('sem_tree');
        $mrks = str_repeat('?,', count($selected_item_ids) - 1) . '?';
        $query = "SELECT Institut_id, Name "
            . "FROM Institute "
            . "WHERE Institut_id IN ($mrks) "
            . "AND fakultaets_id != Institut_id "
            . "ORDER BY Name ASC";
        $parameters = $selected_item_ids;

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($this->config->getValue('Main', 'onlylecturers')) {
                // get only users with status dozent in an visible seminar in the current semester
                $query = sprintf("SELECT COUNT(DISTINCT(su.user_id)) AS count_user "
                    . "FROM user_inst ui "
                    . "LEFT JOIN seminar_user su USING(user_id) "
                    . "LEFT JOIN seminare s USING (seminar_id) "
                    . "LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id "
                    . "WHERE ui.Institut_id = '%s' "
                    . "AND su.status = 'dozent' "
                    . "AND ui.externdefault = 1 "
                    . "AND " . get_ext_vis_query()
                    . "AND ui.inst_perms = 'dozent' "
                    . "AND ((%s) = %s OR ((%s) <= %s  AND ((%s) >= %s OR (%s) = -1)))",
                    $row['Institut_id'],
                    $dbv->sem_number_sql,
                    $current_semester,
                    $dbv->sem_number_sql,
                    $current_semester,
                    $dbv->sem_number_end_sql,
                    $current_semester,
                    $dbv->sem_number_end_sql);
            } else {
                // get only users with the given status
                $query = sprintf("SELECT COUNT(DISTINCT(ui.user_id)) AS count_user "
                    . "FROM user_inst ui "
                    . "INNER JOIN auth_user_md5 aum USING (user_id) "
                    . "WHERE ui.Institut_id = '%s' "
                    . "AND ui.inst_perms IN('%s') "
                    . "AND ui.externdefault = 1 "
                    . "AND " . get_ext_vis_query(),
                    $row['Institut_id'],
                    implode("','", $this->config->getValue('Main', 'instperms')));
            }


            $state = DBManager::get()->prepare($query);
            $state->execute($parameters);
            while ($row_count = $state->fetch(PDO::FETCH_ASSOC)) {

                if ($row_count['count_user'] > 0) {
                    $content['LIST_INSTITUTES']['INSTITUTE'][] = [
                        'INSTITUTE_NAME' => ExternModule::ExtHtmlReady($row['Name']),
                        'INSTITUTE_COUNT_USER' => $row_count['count_user'],
                        'URL_LIST_PERSONS' => $this->getLinkToModule('LinkInternListInstitutes', ['item_id' => $row['Institut_id']])];
                }
            }
        }

        return $content;
    }

    public function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateMain']->toString(['content' => $this->getContent($args), 'subpart' => 'PERS_BROWSE']);

    }

    public function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateMain']->toString(['content' => $this->getContent(), 'subpart' => 'PERS_BROWSE', 'hide_markers' => FALSE]);

    }

}

?>
