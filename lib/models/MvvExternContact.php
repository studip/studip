<?php
/**
 * MvvExternContact.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */

class MvvExternContact extends ModuleManagementModel
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_extern_contacts';

        $config['belongs_to']['MvvContact'] = array(
            'class_name' => 'MvvContact',
            'foreign_key' => 'extern_contact_id',
            'assoc_func' => 'findCached',
        );

        $config['i18n_fields']['name']     = true;
        $config['i18n_fields']['homepage'] = true;

        parent::configure($config);
    }
}
