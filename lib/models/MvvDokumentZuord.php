<?php
/**
 * DokumentZuord.php
 * Model class for assignments of Documents to different MVV-Objects
 * (table mvv_dokument_zuord)
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

class MvvDokumentZuord extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_dokument_zuord';

        $config['belongs_to']['document'] = [
            'class_name' => 'MvvDokument',
            'foreign_key' => 'dokument_id'
        ];

        $config['i18n_fields']['kommentar'] = true;
        
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * Inherits the status of the related object.
     *
     * @return string the status of the related object
     */
    public function getStatus()
    {
        if (!$this->isNew()) {
            $mvv_object_type = $this->object_type;
            $mvv_object = new $mvv_object_type($this->range_id);
            return $mvv_object->getStatus();
        }
        return parent::getStatus();
    }

    /**
     * Retrieves all assignments of Dokumente for the given MVV Object)
     *
     * @param SimpleORMap $object
     * @return type
     */
    public static function findByObject(SimpleORMap $object)
    {
        if (!($object instanceof ModuleManagementModel)) {
            throw new UnexpectedValueException();
        }

        $sql = 'range_id = ? AND object_type = ? ORDER BY position,mkdate';
        return self::findBySQL($sql, [$object->getId(), get_class($object)]);
    }

}