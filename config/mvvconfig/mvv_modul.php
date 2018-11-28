<?php

/**
 * mvv_modul.php
 * Configures the permissions for Module (table mvv_modul)
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


// Tabelle mvv_modul
$privileges = array(
    'lock_status' => array(
        'ausgelaufen'
    ),
    'default_table' => array(
        'MVVEntwickler' => 7,
        'MVVRedakteur'  => 3,
        'MVVTranslator' => 1,
        'MVVFreigabe'   => 1
    ),
    'table' => array(
        'planung' => array(
            'MVVEntwickler' => 7,
            'MVVRedakteur'  => 3,
            'MVVTranslator' => 3,
            'MVVFreigabe'   => 7
        ),
        'genehmigt' => array(
            'MVVEntwickler' => 7,
            'MVVRedakteur'  => 3,
            'MVVTranslator' => 3,
            'MVVFreigabe'   => 7
        ),
        'ausgelaufen' => array(
            'MVVEntwickler' => 1,
            'MVVRedakteur'  => 1,
            'MVVTranslator' => 1,
            'MVVFreigabe'   => 3
        )
    ),
    'default_fields' => array(
        'MVVEntwickler' => 1,
        'MVVRedakteur'  => 1,
        'MVVTranslator' => 1,
        'MVVFreigabe'   => 1
    ),
    'fields' => array(
        'quelle' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 1
            )
        ),
        'variante' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'flexnow_modul' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'code' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'start' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'end' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'beschlussdatum' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'fassung_nr' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'fassung_typ' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'version' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'aktiv' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        // wird beim Erstellen vorgegeben
        'sprache' => array(
            'default' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 1
            )
        ),
        'verantwortlich' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'dauer' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'kapazitaet' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'kp' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'wl_selbst' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'wl_pruef' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'pruef_ebene' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'faktor_note' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'stat' => array(
            'default' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
        ),
        'kommentar_status' => array(
            'default' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
        ),
        // verknüpfte Objekte
        'responsible_institute' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'assigned_institutes' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'languages' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'assigned_users' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'modulteile' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'deskriptoren' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'abschnitte_modul' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'abschnitte' => array(
            'planung' => array(
                'MVVEntwickler' => 7,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 7
            )
        ),
        'modul_quelle' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 1
            )
        ),
        'modul_variante' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        // folgende Konfigurationen gelten für bestimmte Funktionen,
        // die auf das Objekt angewendet werden dürfen (oder eben nicht).
        
        // Kopieren von Modulen
        'copy_module' => [
            'planung' => [
                    'MVVEntwickler' => 0,
                    'MVVRedakteur'  => 0,
                    'MVVTranslator' => 0,
                    'MVVFreigabe'   => 0
            ],
            'genehmigt' => [
                    'MVVEntwickler' => 7,
                    'MVVRedakteur'  => 0,
                    'MVVTranslator' => 0,
                    'MVVFreigabe'   => 0
            ],
            'ausgelaufen' => [
                    'MVVEntwickler' => 0,
                    'MVVRedakteur'  => 0,
                    'MVVTranslator' => 0,
                    'MVVFreigabe'   => 0
            ]
        ]
    )
);
