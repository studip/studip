<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// Universität Trier  -  Jörg Röpke  -  <roepke@uni-trier.de>
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginZ3950Abstract_Aleph.class.php
//
//
// Copyright (c) 2005 Jörg Röpke  -  <roepke@uni-trier.de>
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

require_once 'StudipLitSearchPluginZ3950Abstract.class.php';

class StudipLitSearchPluginZ3950Abstract_Aleph extends StudipLitSearchPluginZ3950Abstract {

    var $convert_umlaute = true;
    var $z_record_encoding = 'utf-8';

    var $superTitle = "";
    var $superAutor = "";
    var $superCity = "";
    var $superPublisher = "";

    function __construct() {
        parent::__construct();
        $mapping =
                                   ['001' => ['field' => 'accession_number', 'callback' => 'idMap', 'cb_args' => FALSE],
                                         // übergeordneter Band
                                         '760' => ['field' => 'dc_title', 'callback' => 'search_superbook', 'cb_args' => '$o'],
                                         // Titel
                                         '245' =>  [['field' => 'dc_title', 'callback' => 'simpleMap', 'cb_args' => '$a - $b'],
                                                ['field' => 'dc_contributor', 'callback' => 'simpleMap', 'cb_args' => '$c']],
                                         // Autor
                                         '100' => ['field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a'],
                                         // alle weitere Autoren
                                         '700' => ['field' => 'dc_creator', 'callback' => 'simpleMap', 'cb_args' => '$a'],

                                         '433' => ['field' => 'dc_format', 'callback' => 'simpleMap', 'cb_args' => '$a'],

                                         '260' =>  [['field' => 'dc_publisher', 'callback' => 'simpleMap', 'cb_args' => '$a $b, '],
                                                 ['field' => 'dc_date', 'callback' => 'notEmptyMap', 'cb_args' => ['$c-01-01','dummy','dummy']]],
                                         '020' => ['field' => 'dc_identifier', 'callback' => 'simpleMap', 'cb_args' => 'ISBN: $a'],
                                         '907' => ['field' => 'dc_subject', 'callback' => 'simpleMap', 'cb_args' => '$s;']
                                        ];
        foreach ($mapping as $k => $v) {
            $this->mapping['USMARC'][$k] = $v;
        }
    }




    // suche übergeortnetem Band
    function search_superbook($cat_element, $data, $field, $args)
    {
        $result = $data['o'];
        if(!$zid = $this->doZConnect()){
            return false;
        }
        //var_dump($cat_element);
        $tempcreator = $cat_element->getValue('dc_contributor');
        $temptitle = $cat_element->getValue('dc_title');
        if(mb_substr($temptitle, -1) == "-")
            $temptitle = mb_substr($temptitle, 0, -1);
        $ok = $this->doZsearch($zid, "@attr 1=12 \"".$result."\"", 1, 1);
        if($ok){
            $super = $this->getZRecord($zid, 1);
            $cat_element->setValue('dc_title', $super['dc_title'] . " - " . $temptitle);
            $cat_element->setValue('dc_creator', $super['dc_creator']);
            $cat_element->setValue('dc_publisher', $super['dc_publisher']);
            $cat_element->super_book = $super;
        }
    }


    // ID Mapping für Hyperlink zum Bibliothekskatalog
    function idMap($cat_element, $data, $field, $args)
    {
        // NEU
        //$cat_element->setValue($field, "IDN=".mb_substr($data,3));
        $cat_element->setValue($field, "IDN=".$data);

        return;
    }


    // Titel
    function titleMap($cat_element, $data, $field, $args)
    {
        $result = $data['a'];

        $result = str_replace(['<','>'],'',$result);
        $result = trim($result);

        // Untergeordneter Band -> Supertitel hinzufügen
        if($cat_element->super_book['dc_title'] != "")
            $cat_element->setValue($field, $cat_element->super_book['dc_title']." - ".$result);
        // Haupt- bzw. Übergeordneter Band
        else
            $cat_element->setValue($field, $result);
        return;
    }

    function simpleMap($cat_element, $data, $field, $args){
        if (is_array($data)) {
            foreach($data as $key => $value){
                $data1[$key] = str_replace(['<','>'],'',$value);
            }
        }
        parent::simpleMap($cat_element, $data1, $field, $args);

        if($field == 'dc_title') {
            $temptitle = $cat_element->getValue('dc_title');
            if(mb_substr($temptitle, -1) == "-") {
                $temptitle = mb_substr($temptitle, 0, -1);
                $cat_element->setValue('dc_title', $temptitle);
            }
        }
    }
}
?>
