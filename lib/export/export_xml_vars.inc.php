<?php
/**
* Config file for xml-export
*
* This file contains several arrays, that define which names will be used for specific xml-tags in the output-file.
* Changes in this file may cause export-problems if the XSLT-Scripts are not changed either!
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_xml_vars
* @package      Export
*/

// Variablen mit den XML-Bezeichnern
// "TABELLENSPALTE" =>      "XML-BEZEICHNER"
$xml_groupnames_fak = [
    'group'  => 'fakultaeten',
    'object' => 'fakultaet'
];

$xml_names_fak = [
    'Name' => 'name'
];

$xml_groupnames_inst = [
    'object'       => 'institut',
    'childobject'  => 'fakultaet',
    'childgroup2'  => 'datenfelder',
    'childobject2' => 'datenfeld'
];

$xml_names_inst = [
    'type'    => 'type',
    'Name'    => 'name',
    'Strasse' => 'strasse',
    'Plz'     => 'plz',
    'url'     => 'homepage',
    'telefon' => 'telefon',
    'email'   => 'email',
    'fax'     => 'fax'
];

$xml_groupnames_lecture = [
    'group'        => 'seminare',
    'subgroup1'    => 'gruppe',
    'subgroup2'    => 'untergruppe',
    'object'       => 'seminar',
    'childgroup1'  => 'termine',
    'childgroup2'  => 'dozenten',
    'childobject2' => 'dozent',
    'childgroup3'  => 'bereiche',
    'childgroup3a' => 'lvgruppen',
    'childgroup4'  => 'datenfelder',
    'childobject4' => 'datenfeld'
];

$xml_names_lecture = [
    'Name'                      => 'titel',
    'Untertitel'                => 'untertitel',
    'status'                    => 'status',
    'Beschreibung'              => 'beschreibung',
    'ort'                       => 'raum',
    'Sonstiges'                 => 'sonstiges',
    'art'                       => 'art',
    'teilnehmer'                => 'teilnehmer',
    'admission_turnout'         => 'teilnehmerzahl',
    'teilnehmer_anzahl_aktuell' => 'teilnehmer_anzahl_aktuell',
    'vorrausetzungen'           => 'voraussetzung',
    'lernorga'                  => 'lernorga',
    'leistungsnachweis'         => 'schein',
    'VeranstaltungsNummer'      => 'veranstaltungsnummer',
    'ects'                      => 'ects',
    'bereich'                   => 'bereich',
    'lvgruppe'                  => 'lvgruppe',
    'metadata_dates'            => ['vorbesprechung', 'erstertermin', 'termin'],
    'Institut_id'               => 'heimateinrichtung'
];

$xml_groupnames_person = [
    'group'        => 'personen',
    'subgroup1'    => 'gruppe',
    'object'       => 'person',
    'childgroup1'  => 'datenfelder',
    'childobject1' => 'datenfeld'
];

$xml_names_person = [
    'title_front'          => 'titel',
    'Vorname'              => 'vorname',
    'Nachname'             => 'nachname',
    'title_rear'           => 'titel2',
    'username'             => 'username',
    'geschlecht'           => 'geschlecht',
    'sprechzeiten'         => 'sprechzeiten',
    'raum'                 => 'raum',
    'Telefon'              => 'telefon',
    'Fax'                  => 'fax',
    'Email'                => 'email',
    'Home'                 => 'homepage',
    'name'                 => 'statusgruppe',
    'privadr'              => 'adresse',
    'privatnr'             => 'privatnummer',
    'comment'              => 'bemerkung',
    'admission_position'   => 'position_warteliste',
    'registration_date'    => 'datum_anmeldung',
    'nutzer_studiengaenge' => 'nutzer_studiengaenge'
];

$xml_groupnames_studiengaenge = [
    'group'  => 'studiengaenge',
    'object' => 'studiengang'
];

$xml_names_studiengaenge = [
    'name'  => 'name',
    'count' => 'anzahl'
];
