<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModulePersondetail.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModulePersondetail
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModulePersondetail.class.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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
require_once 'lib/dates.inc.php';

class ExternModulePersondetails extends ExternModule {

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->data_fields = [
                'contact' => [
                    'raum', 'Telefon', 'Fax', 'Email',
                    'Home', 'sprechzeiten'],
                'content' => [
                    'head', 'lebenslauf', 'schwerp', 'lehre',
                    'news', 'termine', 'publi', 'kategorien'/*, 'literature' */]
        ];
        $this->registered_elements = [
                'Body', 'TableHeader', 'PersondetailsHeader', 'Contact',
                'PersondetailsLectures', 'TableParagraph', 'TableParagraphHeadline',
                'TableParagraphSubHeadline', 'TableParagraphText', 'List',/* 'LitList',*/
                'LinkIntern', 'StudipLink'
        ];
        $this->args = ['username', 'seminar_id'];
        $this->field_names =
        [
            "contact" =>
            [
                _("Raum"),
                _("Telefon"),
                _("Fax"),
                _("E-Mail"),
                _("Homepage"),
                _("Sprechzeiten")
            ],
            "content" =>
            [
                _("Name, Anschrift, Kontakt"),
                _("Lebenslauf"),
                _("Schwerpunkte"),
                _("Lehrveranstaltungen"),
                _("News"),
                _("Termine"),
                _("Publikationen"),
                _("eigene Kategorien")/*,
                _("Literaturlisten")*/
            ]
        ];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        // extend $data_fields if generic datafields are set
        $config_datafields = $this->config->getValue("Main", "genericdatafields");
        $this->data_fields["content"] = array_merge((array)$this->data_fields['content'], (array)$config_datafields);

        // setup module properties
        $this->elements["LinkIntern"]->link_module_type = 4;
        $this->elements["LinkIntern"]->real_name = _("Link zum Modul Veranstaltungsdetails");
        $this->elements["TableHeader"]->real_name = _("Umschließende Tabelle");
    }

    function printout ($args) {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);

        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        include "lib/extern/modules/views/persondetails.inc.php";

        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }

    function printoutPreview () {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);

        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        include "lib/extern/modules/views/persondetails_preview.inc.php";

        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }

}

?>
