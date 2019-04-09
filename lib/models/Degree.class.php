<?php
/**
 * Degree.class.php
 * model class for table studiengang
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string abschluss_id database column
 * @property string id alias column for abschluss_id
 * @property string name database column
 * @property string beschreibung database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class Degree extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'abschluss';
        
        $config['has_and_belongs_to_many']['professions'] = [
            'class_name' => 'StudyCourse',
            'thru_table' => 'user_studiengang',
            'thru_key' => 'abschluss_id',
            'thru_assoc_key' => 'fach_id',
            'order_by' => 'GROUP BY fach_id ORDER BY name'
        ];
        
        $config['additional_fields']['count_user']['get'] = 'countUser';
        
        parent::configure($config);
    }
    
    public function countUser()
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang WHERE abschluss_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchColumn();
    }
    
    public function countUserByStudycourse($studycourse_id)
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang '
                . 'WHERE fach_id = ? AND abschluss_id = ?');
        $stmt->execute([$studycourse_id, $this->id]);
        return $stmt->fetchColumn();
    }
    
    public function store()
    {
        if ($this->isNew() || $this->isDirty()) {
            $this->editor_id = $GLOBALS['user']->id;
            if (!$this->getPristineValue('author_id')) {
                $this->author_id = $GLOBALS['user']->id;
            }
        }
        
        return parent::store();
    }
    
}
