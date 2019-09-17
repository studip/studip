<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplatePersons.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplatePersons
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplatePersons.class.php
//
// Copyright (C) 2007 Peter Thienel <pthienel@web.de>,
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

class ExternModuleTemplatePersons extends ExternModule {

    var $markers = [];

    /**
    *
    */
    function __construct ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->data_fields = [
                'Nachname', 'Telefon', 'raum', 'Email', 'sprechzeiten'
        ];
        $this->registered_elements = [
                'LinkInternTemplate',
                'TemplateGeneric'
        ];

        $this->field_names =
        [
                _("Name"),
                _("Telefon"),
                _("Raum"),
                _("E-Mail"),
                _("Sprechzeiten")
        ];

        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        $this->elements['TemplateGeneric']->real_name = _("Template");
        // Set internal link to module 'staff details'
        $this->elements['LinkInternTemplate']->link_module_type = [2, 14];
        $this->elements['LinkInternTemplate']->real_name = _("Verlinkung zum Modul MitarbeiterInnendetails");

    }

    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateGeneric', 'user');
        $this->updateGenericDatafields('TemplateGeneric', 'userinstrole');
        $this->elements['TemplateGeneric']->markers = $this->getMarkerDescription('TemplateGeneric');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    function getMarkerDescription ($element_name) {
        $markers['TemplateGeneric'][] = ['<!-- BEGIN PERSONS -->', ''];

        $markers['TemplateGeneric'][] = ['<!-- BEGIN NO-PERSONS -->', ''];
        $markers['TemplateGeneric'][] = ['###NO-LECTURES-TEXT###', ''];
        $markers['TemplateGeneric'][] = ['<!-- END NO-PERSONS -->', ''];

        $markers['TemplateGeneric'][] = ['<!-- BEGIN GROUP -->', ''];
        $markers['TemplateGeneric'][] = ['###GROUPTITLE###', ''];
        $markers['TemplateGeneric'][] = ['###GROUPTITLE-SUBSTITUTE###', ''];
        $markers['TemplateGeneric'][] = ['###GROUP-NO###', ''];

        $markers['TemplateGeneric'][] = ['<!-- BEGIN PERSON -->', ''];
        $markers['TemplateGeneric'][] = ['###FULLNAME###', ''];
        $markers['TemplateGeneric'][] = ['###LASTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###FIRSTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###TITLEFRONT###', ''];
        $markers['TemplateGeneric'][] = ['###TITLEREAR###', ''];
        $markers['TemplateGeneric'][] = ['###PERSONDETAIL-HREF###', ''];
        $markers['TemplateGeneric'][] = ['###USERNAME###', ''];
        $markers['TemplateGeneric'][] = ['###IMAGE-URL-NORMAL###', _('Nutzerbild (groß)')];
        $markers['TemplateGeneric'][] = ['###IMAGE-URL-MEDIUM###', _('Nutzerbild (mittel)')];
        $markers['TemplateGeneric'][] = ['###IMAGE-URL-SMALL###', _('Nutzerbild (klein)')];
        $markers['TemplateGeneric'][] = ['###PHONE###', ''];
        $markers['TemplateGeneric'][] = ['###ROOM###', ''];
        $markers['TemplateGeneric'][] = ['###EMAIL###', ''];
        $markers['TemplateGeneric'][] = ['###EMAIL-LOCAL###', _("Der local-part der E-Mail-Adresse (vor dem @-Zeichen)")];
        $markers['TemplateGeneric'][] = ['###EMAIL-DOMAIN###', _("Der domain-part der E-Mail-Adresse (nach dem @-Zeichen)")];
        $markers['TemplateGeneric'][] = ['###HOMEPAGE-HREF###', ''];
        $markers['TemplateGeneric'][] = ['###OFFICEHOURS###', ''];
        $markers['TemplateGeneric'][] = ['###PERSON-NO###', ''];
        $this->insertDatafieldMarkers('user', $markers, 'TemplateGeneric');
        $this->insertDatafieldMarkers('userinstrole', $markers, 'TemplateGeneric');
        $markers['TemplateGeneric'][] = ['<!-- END PERSON -->', ''];

        $markers['TemplateGeneric'][] = ['<!-- END GROUP -->', ''];
        $markers['TemplateGeneric'][] = ['<!-- END PERSONS -->', ''];

        return $markers[$element_name];
    }

    function getContent ($args = NULL, $raw = FALSE) {
        if ($raw) {
            self::SetRawOutput();
        }

        if (!$all_groups = get_all_statusgruppen($this->config->range_id)) {
            die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
        } else {
            $all_groups = array_keys($all_groups);
        }

        if (!$group_ids = $this->config->getValue('Main', 'groupsvisible')) {
            die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
        } else {
            $group_ids = array_intersect($all_groups, $group_ids);
        }

        if (!is_array($group_ids)) {
            die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
        }

        if (!$visible_groups = get_statusgruppen_by_id($this->config->range_id, $group_ids)) {
            die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
        }

        $sort = $this->config->getValue('Main', 'sort');
        $query_order = [];
        foreach ($sort as $key => $position) {
            if ($position > 0) {
                $query_order[$position] = $this->data_fields[$key];
            }
        }
        if (count($query_order)) {
            ksort($query_order, SORT_NUMERIC);
            $query_order = ' ORDER BY ' . implode(',', $query_order);
        } else {
            $query_order = '';
        }

        $grouping = $this->config->getValue("Main", "grouping");
        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'full_rev';
        }

        if(!$grouping) {
            $query = "SELECT DISTINCT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms, Email, aum.user_id, ";
            $query .= 'username, aum.Vorname, title_front, title_rear, Home, ';
            $query .= $GLOBALS['_fullname_sql'][$nameformat] . " AS fullname, aum.Nachname ";
            if ($query_order) {
                $query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) ";
                $query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
                $query .= "WHERE statusgruppe_id IN (?) AND Institut_id = ? AND ".get_ext_vis_query()."$query_order";
            } else {
                $query .= "FROM statusgruppen s LEFT JOIN statusgruppe_user su USING(statusgruppe_id) ";
                $query .= "LEFT JOIN auth_user_md5 aum USING(user_id) ";
                $query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
                $query .= "WHERE su.statusgruppe_id IN (?) AND Institut_id = ? ";
                $query .= " AND ".get_ext_vis_query()." ORDER BY ";
                $query .= "s.position ASC, su.position ASC";
            }
            $parameters = [$this->config->getValue('Main', 'groupsvisible'), $this->config->range_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $visible_groups = [''];
        }

        // generic data fields
        $generic_datafields = $this->config->getValue('TemplateGeneric', 'genericdatafields');

        $data['data_fields'] = $this->data_fields;
        $defaultaddress = $this->config->getValue('Main', 'defaultadr');
        if (! $defaultaddress) {
           $db_out =& $row;
        }

        $content = null;
        $i = 0;
        $aliases_groups = $this->config->getValue('Main', 'groupsalias');
        foreach ($visible_groups as $group_id => $group) {
            if ($grouping) {
                if (!$query_order) {
                    $query_order = ' ORDER BY su.position';
                }
                $query = 'SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms, Email, aum.user_id, ';
                $query .= 'username, aum.Vorname, title_front, title_rear, Home, ';
                $query .= $GLOBALS['_fullname_sql'][$nameformat] . " AS fullname, aum.Nachname ";
                $query .= 'FROM statusgruppe_user su LEFT JOIN auth_user_md5 aum USING(user_id) ';
                $query .= 'LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ';
                $query .= "WHERE su.statusgruppe_id = ? AND ".get_ext_vis_query()." AND Institut_id = ? $query_order";

                $parameters = [$group_id, $this->config->range_id ];
                $statement = DBManager::get()->prepare($query);
                $statement->execute($parameters);
                $row = $statement->fetch(PDO::FETCH_ASSOC);

                if($aliases_groups[$group_id]) {
                    $group = $aliases_groups[$group_id];
                }
            }


            if ($row !== false) {
                if($aliases_groups[$group_id]) {
                    $content['PERSONS']['GROUP'][$i]['GROUPTITLE-SUBSTITUTE'] = ExternModule::ExtHtmlReady($aliases_groups[$group_id]);
                }
                $content['PERSONS']['GROUP'][$i]['GROUPTITLE'] = ExternModule::ExtHtmlReady($group);
                $content['PERSONS']['GROUP'][$i]['GROUP-NO'] = $i + 1;

                $j = 0;
                do{
                    $visibilities = get_local_visibility_by_id($row['user_id'], 'homepage', true);
                    $user_perm = $visibilities['perms'];
                    $visibilities = json_decode($visibilities['homepage'], true);
                    $instituts_id = $this->config->range_id;

                    if ($defaultaddress) {
                        $query = 'SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,  Email, ';
                        $query .= 'title_front, title_rear, Home, Institut_id, ';
                        $query .= 'aum.user_id, username, ' . $GLOBALS['_fullname_sql'][$nameformat];
                        $query .= ' AS fullname, aum.Nachname, aum.Vorname FROM auth_user_md5 aum LEFT JOIN ';
                        $query .= 'user_info USING(user_id) LEFT JOIN ';
                        $query .= "user_inst ui USING(user_id) WHERE aum.user_id = '" . $row['user_id'];
                        $query .= "' AND ".get_ext_vis_query().' AND externdefault = 1';

                        $statement2 = DBManager::get()->prepare($query);
                        $statement2->execute();
                        $db_out = $statement2->fetch(PDO::FETCH_ASSOC);
                        //no default
                        if ($db_out === false) {
                            $query = 'SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,  Email, ';
                            $query .= 'title_front, title_rear, Home, ';
                            $query .= 'aum.user_id, username, ' . $GLOBALS['_fullname_sql'][$nameformat];
                            $query .= ' AS fullname, aum.Nachname, aum.Vorname FROM auth_user_md5 aum LEFT JOIN ';
                            $query .= 'user_info USING(user_id) LEFT JOIN ';
                            $query .= "user_inst ui USING(user_id) WHERE aum.user_id = '" . $row['user_id'];
                            $query .= "' AND ".get_ext_vis_query()." AND Institut_id = ? " ;
                            $statement2 = DBManager::get()->prepare($query);
                            $params = [$this->config->range_id];
                            $statement2->execute($params);
                            $db_out = $statement2->fetch(PDO::FETCH_ASSOC);
                        } else {
                            $instituts_id = $db_out['Institut_id'];
                        }
                    }
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['FULLNAME'] = ExternModule::ExtHtmlReady($db_out['fullname']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['LASTNAME'] = ExternModule::ExtHtmlReady($db_out['Nachname']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['FIRSTNAME'] = ExternModule::ExtHtmlReady($db_out['Vorname']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['TITLEFRONT'] = ExternModule::ExtHtmlReady($db_out['title_front']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['TITLEREAR'] = ExternModule::ExtHtmlReady($db_out['title_rear']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['PERSONDETAIL-HREF'] =
                        $this->elements['LinkInternTemplate']->createUrl(['link_args' => 'username=' . $db_out['username'] . ($grouping ? '&group_id=' . $group_id : '')]);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['USERNAME'] = $db_out['username'];

                    if (Visibility::verify('picture', $row['user_id']) == 5) {
                        $avatar = Avatar::getAvatar($db_out['user_id']);
                    } else {
                        $avatar = Avatar::getNobody();
                    }
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['IMAGE-URL-SMALL'] = $avatar->getURL(Avatar::SMALL);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['IMAGE-URL-MEDIUM'] = $avatar->getURL(Avatar::MEDIUM);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['IMAGE-URL-NORMAL'] = $avatar->getURL(Avatar::NORMAL);

                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['PHONE'] = ExternModule::ExtHtmlReady($db_out['Telefon']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['ROOM'] = ExternModule::ExtHtmlReady($db_out['raum']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['EMAIL'] = get_visible_email($row['user_id']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['EMAIL-LOCAL'] = array_shift(explode('@', $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['EMAIL']));
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['EMAIL-DOMAIN'] = array_pop(explode('@', $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['EMAIL']));
                    if ($row['Home'] && Visibility::verify('homepage', $row['user_id'])) {
                        $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['HOMEPAGE-HREF'] = ExternModule::ExtHtmlReady(trim($row['Home']));
                    }
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['OFFICEHOURS'] = ExternModule::ExtHtmlReady($db_out['sprechzeiten']);
                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['PERSON-NO'] = $j + 1;

                    // generic data fields
                    if (is_array($generic_datafields)) {
                        $localEntries = DataFieldEntry::getDataFieldEntries($db_out['user_id'], 'user');
                        #$datafields = $datafields_obj->getLocalFields($db_out->f('user_id'));
                        $k = 1;
                        foreach ($generic_datafields as $datafield) {
                            if (isset($localEntries[$datafield]) &&
                                    is_object($localEntries[$datafield] &&
                                    is_element_visible_externally($db_out['user_id'],
                                        $user_perm, $localEntries[$datafield]->getId(),
                                        $visibilities[$localEntries[$datafield]->getId()]))) {
                                if ($localEntries[$datafield]->getType() == 'link') {
                                    $localEntry = ExternModule::extHtmlReady($localEntries[$datafield]->getValue());
                                } else {
                                    $localEntry = $localEntries[$datafield]->getDisplayValue();
                                }
                                if ($localEntry) {
                                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['DATAFIELD_' . $k] = $localEntry;
                                }
                            }
                            $k++;
                        }

                        $localEntries = DataFieldEntry::getDataFieldEntries([$db_out['user_id'], $instituts_id], 'userinstrole');
                        if ($grouping) {
                            $roleEntries = DataFieldEntry::getDataFieldEntries([$db_out['user_id'], $group_id], 'userinstrole');
                            $roleEntries = array_filter($roleEntries, function($val) { return $val->getValue() !== 'default_value'; });
                            $localEntries = $roleEntries + $localEntries;
                        }
                        $k = 1;
                        foreach ($generic_datafields as $datafield) {
                            if (isset($localEntries[$datafield]) &&
                                    is_object($localEntries[$datafield])) {
                                $localEntry = $localEntries[$datafield]->getDisplayValue();
                                if ($localEntry) {
                                    $content['PERSONS']['GROUP'][$i]['PERSON'][$j]['DATAFIELD_' . $k] = $localEntry;
                                }
                            }
                            $k++;
                        }
                    }
                    $j++;
                }while ($row = $statement->fetch(PDO::FETCH_ASSOC));
            }
            $i++;
        }

        return $content;
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        echo $this->elements['TemplateGeneric']->toString(['content' => $this->getContent($args), 'subpart' => 'PERSONS']);

    }

    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(['content' => $this->getContent(), 'subpart' => 'PERSONS', 'hide_markers' => FALSE]);

    }

}

?>
