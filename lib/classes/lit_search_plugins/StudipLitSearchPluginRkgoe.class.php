<?php
# Lifter002: TODO
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
class StudipLitSearchPluginRkgoe extends StudipLitSearchPluginGvk
{


    function __construct()
    {
        parent::__construct();
        $this->description = "Göttinger Gesamtkatalog (GGK)
Online-Katalog der folgenden Göttinger Bibliotheken:
- SUB Göttingen mit Bereichsbibliotheken
- Teilbibliotheken (Institutsbibliotheken) der Georg-August-Universität Göttingen
- Otto-Hahn-Bibliothek - Max-Planck-Institut für biophysikalische Chemie
- Max-Planck-Institut für experimentelle Medizin
- Max-Planck-Institut zur Erforschung multireligiöser und multiethnischer Gesellschaften
- Max-Planck-Institut für Strömungsforschung
- Bibliotheken der FH Hildesheim-Holzminden in Göttingen
- Bibliothek der FH im Deutschen Roten Kreuz in Göttingen
- Stadtbibliothek Göttingen
";
        $this->z_host = "sru.gbv.de/rk-goe";
        $this->z_profile = ['1016' => _("Basisindex [ALL]"), '2' => _("Körperschaftsname [KOS]"),
                                '3' => _("Kongress [KNS]"),'4' => _("Titelstichwörter [TIT]"),
                                '5' => _("Serienstichwörter [SER]"), '7' => _("ISBN [ISB]"),
                                '8' => _("ISSN [ISS]"), '12' => _("PICA Prod.-Nr [PPN]"),
                                '21' => _("alle Klassifikationen [SYS]"), '1004' => _("Person, Author [PER]"),
                                '1005' => _("Körperschaften [KOR]"), '1006' => _("Kongresse [KON]"),
                                '1007' => _("alle Nummern [NUM]")];
    }
}
?>
