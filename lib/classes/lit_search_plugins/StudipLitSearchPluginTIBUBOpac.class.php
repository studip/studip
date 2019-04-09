<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginRkgoe.class.php
//
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

require_once 'StudipLitSearchPluginGvk.class.php';

/**
* Plugin for retrieval using Z39.50
*
*
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
**/
class StudipLitSearchPluginTIBUBOpac extends StudipLitSearchPluginGvk
{


    function __construct()
    {
        parent::__construct();
        $this->description = "Technische Informationsbibliothek / Universitätsbibliothek Hannover";
        $this->z_host = "sru.gbv.de/opac-de-89";
        $this->z_profile = ['1016' => _("alle Wörter [ALL]"),
                     '1004' => _("Person, Autor [PER]"),
                     '4' => _("Titelstichwörter [TIT]"),
                     '5' => _("Stichwörter Serie/Zeitschrift [SER]"),
                     '1005' => _("Stichwörter Körperschaft [KOR]"),
                     '46' => _("Schlagwörter [SWW]"),
                     '54' => _("Signatur [SGN]"),
                     '1007' => _("alle Nummern (ISBN, ISSN ...) [NUM]")
        ]; /* '4' => _("Titelanfänge [TAF]"),
           herausgenommen, da keine Unterscheidung anhand von Wort oder Phrase durchgeführt wird.
           */
    }
}
?>