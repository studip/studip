<?php
/**
 * LockRule.class.php
 * model class for table lock_rule
 * this class represents one record of the lock_rules table
 * the column attributes is converted to an ArrayObject and vice-versa,
 * to allow array-like access
 *
 * e.g.
 * @code
 * $lockrule = LockRule::find($id);
 * $lockrule['attributes']['name'] = 1;
 * $lockrule->store();
 * @endcode
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2011 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string lock_id database column
 * @property string id alias column for lock_id
 * @property string permission database column
 * @property string name database column
 * @property string description database column
 * @property string attributes database column
 * @property string object_type database column
 * @property string user_id database column
 */

class LockRule extends SimpleORMap
{

    /**
     * returns the lockrule for a course
     *
     * @param string $seminar_id id of a course
     * @return LockRule
     */
    static function findBySeminar($seminar_id)
    {
        $db = DBManager::get();
        $lock_rule_id = $db->query("SELECT lock_rule FROM seminare WHERE seminar_id = " . $db->quote($seminar_id))
                           ->fetchColumn();
        return self::find($lock_rule_id);
    }

    /**
     * returns the lockrule for an institute
     *
     * @param string $institute_id id of an institute
     * @return LockRule
     */
    static function findByInstitute($institute_id)
    {
        $db = DBManager::get();
        $lock_rule_id = $db->query("SELECT lock_rule FROM Institute WHERE Institut_id = " . $db->quote($institute_id))
                           ->fetchColumn();
        return self::find($lock_rule_id);
    }

    /**
     * returns the lockrule for a user
     *
     * @param string $user_id id of a user
     * @return LockRule
     */
    static function findByUser($user_id)
    {
        $db = DBManager::get();
        $lock_rule_id = $db->query("SELECT lock_rule FROM user_info WHERE user_id = " . $db->quote($user_id))
                           ->fetchColumn();
        return self::find($lock_rule_id);
    }

    /**
     * returns all exisiting lockrules for a given entity type
     *
     * @param string $type entity type, one of [sem,inst,user]
     * @return array of LockRule objects
     */
    static function findAllByType($type)
    {
        return self::findByObject_type($type, " ORDER BY name");
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'lock_rules';
        $config['serialized_fields']['attributes'] = 'JSONArrayObject';
        parent::configure($config);
    }

    /**
     * @see SimpleORMap::delete()
     */
    function delete()
    {
        $id = $this->getId();
        $object_type = $this->object_type;
        $ret = parent::delete();

        $db = DBManager::get();
        $tables['sem'] = 'seminare';
        $tables['inst'] = 'Institute';
        $tables['user'] = 'user_info';
        $db->exec("UPDATE " . $tables[$object_type] . " SET lock_rule='' WHERE lock_rule = " . $db->quote($id));
        return $ret;
    }

    /**
     * returns the number of uses for this lockrule
     *
     * @return integer
     */
    function getUsage()
    {
        if (!$this->isNew()) {
            $db = DBManager::get();
            $tables['sem'] = 'seminare';
            $tables['inst'] = 'Institute';
            $tables['user'] = 'user_info';
            return $db->query("SELECT COUNT(*) FROM " . $tables[$this->object_type] . " WHERE lock_rule = " . $db->quote($this->getId()))->fetchColumn();
        } else {
            return 0;
        }
    }
}
