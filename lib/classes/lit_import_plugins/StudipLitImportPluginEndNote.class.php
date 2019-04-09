<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitImportPluginEndNote.class.php
//
//
// Copyright (c) 2006 Jan Kulmann <jankul@zmml.uni-bremen.de>
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

require_once 'StudipLitImportPluginAbstract.class.php';

/**
*
*
*
*
* @access   public
* @author   Jan Kulmann <jankul@zmml.uni-bremen.de>
* @package
**/
class StudipLitImportPluginEndNote extends StudipLitImportPluginAbstract {

    function __construct(){
        // immer erst den parent-contructor aufrufen!
        parent::__construct();
    }

    function parse($data){
        // Disable entity load
        $this->loadEntities = libxml_disable_entity_loader(true);
        $suche =  ["'<style[^>]*?>'si","'</style>'si"];
            $ersetze =  ["",""];
        if ($suche && $ersetze && $data)
                        $data = preg_replace($suche, $ersetze, $data);

        if (!$domTree = DomDocument::loadXML($data)) {
            // parent::addError("error","Error 5: while parsing the document");
            libxml_disable_entity_loader($this->loadEntities);
            $this->addError("error","Error 5: while parsing the document");
            return FALSE;
        }
        return $domTree;
    }

    function import($domTree) {
        global $auth, $_msg;
            $msg = &$_msg;
            if ($domTree) {
                    $records = $domTree->getElementsByTagname("record");
                    if (count($records)==0) $records = $domTree->getElementsByTagname("RECORD");

            $fields_arr = [];

                    foreach ($records as $record) {
                            $fields = [];
                            $fields["catalog_id"] = "new_entry";
                            $fields["user_id"] = $auth->auth["uid"];
                            $dates = "";

                            $child = $record;
                            $ref_type = $child->getElementsByTagName("ref-type");
                            if (count($ref_type)==0) $ref_type = $child->getElementsByTagName("REF-TYPE");
                            foreach ($ref_type as $r)
                                    $fields["dc_type"] = $r->getAttribute("name");

                            $titles = $child->getElementsByTagName("title");
                            if (count($titles)==0) $titles = $child->getElementsByTagName("TITLE");
                            foreach ($titles as $t)
                                    $fields["dc_title"] .= $t->textContent.",";

                            $authors = $child->getElementsByTagName("author");
                            if (count($authors)==0) $authors = $child->getElementsByTagName("AUTHOR");
                            foreach ($authors as $a)
                                    $fields["dc_creator"] .= $a->textContent.",";

                            $keywords = $child->getElementsByTagName("keyword");
                            if (count($keywords)==0) $keywords = $child->getElementsByTagName("KEYWORD");
                            foreach ($keywords as $k)
                                    $fields["dc_subject"] .= $k->textContent.",";
                            $notes = $child->getElementsByTagName("notes");
                            if (count($notes)==0) $notes = $child->getElementsByTagName("NOTES");
                            foreach ($notes as $n)
                                    $fields["dc_subject"] .= $n->textContent.",";

                            $publisher = $child->getElementsByTagName("publisher");
                            if (count($publisher)==0) $publisher = $child->getElementsByTagName("PUBLISHER");
                            foreach ($publisher as $p)
                                    $fields["dc_publisher"] .= $p->textContent.",";

                            $pub_loc = $child->getElementsByTagName("pub-location");
                            if (count($pub_loc)==0) $pub_loc = $child->getElementsByTagName("PUB-LOCATION");
                            foreach ($pub_loc as $p)
                                    $fields["dc_publisher"] .= " ".$p->textContent.",";

                            $isbn = $child->getElementsByTagName("isbn");
                            if (count($isbn)==0) $isbn = $child->getElementsByTagName("ISBN");
                            foreach ($isbn as $i)
                                    $fields["dc_identifier"] .= " ISBN: ".$i->textContent.",";
                            $issn = $child->getElementsByTagName("issn");
                            if (count($issn)==0) $issn = $child->getElementsByTagName("ISSN");
                            foreach ($issn as $i)
                                    $fields["dc_identifier"] .= " ISSN: ".$i->textContent.",";

                            $years = $child->getElementsByTagName("year");
                            if (count($years)==0) $years = $child->getElementsByTagName("YEAR");
                            foreach ($years as $y) {
                                    // $fields["dc_date"] = mktime(0, 0, 0, 1, 1, date("Y",$y->textContent));
                                    $fields["dc_date"] = $y->textContent."-01-01";
                                    $dates .= $y->textContent.",";
                            }

                            if ($fields["dc_identifier"]) $fields["dc_identifier"] = mb_substr($fields["dc_identifier"],0,-1);
                            if ($fields["dc_publisher"]) $fields["dc_publisher"] = mb_substr($fields["dc_publisher"],0,-1);
                            if ($fields["dc_title"]) $fields["dc_title"] = mb_substr($fields["dc_title"],0,-1);
                            if ($fields["dc_creator"]) $fields["dc_creator"] = mb_substr($fields["dc_creator"],0,-1);
                            if ($fields["dc_subject"]) $fields["dc_subject"] = mb_substr($fields["dc_subject"],0,-1);

                            if (!trim($fields["dc_creator"])) $fields["dc_creator"] = "Unbekannt";
                            if (!trim($fields["dc_title"])) $fields["dc_title"] = "";

                if ( $fields["dc_title"] != "") array_push($fields_arr, $fields);

                    }
                    libxml_disable_entity_loader($this->loadEntities);
            return (count($fields_arr)>0 ? $fields_arr : FALSE);
            }

    }
}
