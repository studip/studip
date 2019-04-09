<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginFH_Nordhausen.class.php
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
class StudipLitSearchPluginFH_Nordhausen extends StudipLitSearchPluginGvk
{

    function __construct()
    {
        parent::__construct();
        $this->description = 'Bibliothek der Fachhochschule Nordhausen';
        $this->z_host = "sru.gbv.de/opac-de-564";
        $this->z_profile = ['1016' => _("Basisindex [ALL]"),
                    '4' => _("Titelstichwörter [TIT]"),
                    '5' => _("Serienstichwörter [SER]"),
                    '21' => _("alle Klassifikationen [SYS]"),
                    '54' => _("Signatur [SGN]"),
                    '1004' => _("Person, Author [PER]"),
                    '1005' => _("Körperschaften [KOR]"),
                    '1006' => _("Kongresse [KON]"),
                    '1007' => _("alle Nummern [NUM]"),
                    '5040' => _("Schlagwörter [SLW]"),
                    '8062' => _("alle Titelanfänge [TAF]"),
                    '8580' => _("Verlagsort, Verlag [PUB]")
                    ];
    }
}
?>
