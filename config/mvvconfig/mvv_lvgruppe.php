<?php

/**
 * mvv_lvgruppe.php
 * Configures the permissions for Lehrveranstaltungsgruppen (table mvv_lvgruppe)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */


/**
 * Permissions
 * ============
 * 
 * read: MVVPlugin::PERM_READ | 1
 * read && write: MVVPlugin::PERM_WRITE | 3
 * read && write && create && delete: MVVPlugin::PERM_CREATE | 7
 * 
 * Structure
 * ==========
 * 
 * ['default_table' => [name_of_role => permission]]
 * Permissions for the object itself regardless of its status.
 * Every tuple defines the permission for a different role (the role of a user
 * who wants to handle this object).
 * 
 * ['default_fields' => [name_of_role => permission]]
 * Default permissions for all fields of this object regardless of its status.
 * Maybe overwritten by an entry for a single field.
 * Every tuple defines the permission for a different role (the role of a user
 * who wants to handle this object).
 * 
 * ['fields' => ... ]
 * Permissions for a single field of this object (db_fields and relations of
 * the SORM-object). Overwites above declaration for this field.
 * 
 * ['fields' => name_of_field ['default' => [name_of_role => permission]]]
 * Default permission for one field for every given role regardless of
 * object's status.
 * 
 * ['fields' => name_of_field [name_of_status => [name_of_role => permission]]]
 * Permission for one field of the object with indicated status for every
 * given role. Overwrites above declaration.
 *  
 */

$privileges = [
    /**
     * This object has no different status. So only the default declarations are
     * required.
     */
    'default_table' => [
        'MVVEntwickler'     => 7,
        'MVVRedakteur'      => 1,
        'MVVTranslator'     => 3,
        'MVVFreigabe'       => 7,
        'MVVLvGruppenAdmin' => 7
    ],
    'default_fields' => [
        'MVVEntwickler'     => 1,
        'MVVRedakteur'      => 1,
        'MVVTranslator'     => 1,
        'MVVFreigabe'       => 1,
        'MVVLvGruppenAdmin' => 7
    ],
    'fields' => [
        'name' => [
            'default' => [
                'MVVEntwickler'     => 3,
                'MVVRedakteur'      => 1,
                'MVVTranslator'     => 1,
                'MVVFreigabe'       => 3,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        'name_i18n[en_GB]' => [
            'default' => [
                'MVVEntwickler'     => 3,
                'MVVRedakteur'      => 1,
                'MVVTranslator'     => 3,
                'MVVFreigabe'       => 3,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        'alttext' => [
            'default' => [
                'MVVEntwickler'     => 3,
                'MVVRedakteur'      => 3,
                'MVVTranslator'     => 1,
                'MVVFreigabe'       => 3,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        'alttext_i18n[en_GB]' => [
            'default' => [
                'MVVEntwickler'     => 3,
                'MVVRedakteur'      => 3,
                'MVVTranslator'     => 3,
                'MVVFreigabe'       => 3,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        // relations
        'modulteile' => [
            'default' => [
                'MVVEntwickler'     => 1,
                'MVVRedakteur'      => 1,
                'MVVTranslator'     => 1,
                'MVVFreigabe'       => 1,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        'courses' => [
            'default' => [
                'MVVEntwickler'     => 1,
                'MVVRedakteur'      => 1,
                'MVVTranslator'     => 1,
                'MVVFreigabe'       => 1,
                'MVVLvGruppenAdmin' => 7
            ]
        ],
        'archived_courses' => [
            'default' => [
                'MVVEntwickler'     => 1,
                'MVVRedakteur'      => 1,
                'MVVTranslator'     => 1,
                'MVVFreigabe'       => 1,
                'MVVLvGruppenAdmin' => 7
            ]
        ]
    ]
];