<?php
/**
 * Evaluation.php
 * model class for table Evaluation
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 * @property string eval_id database column
 * @property string id alias column for eval_id
 * @property string author_id database column
 * @property string title database column
 * @property string text database column
 * @property string startdate database column
 * @property string stopdate database column
 * @property string timespan database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string anonymous database column
 * @property string visible database column
 * @property string shared database column
 * @property string enddate computed column
 * @property User author belongs_to User
 * @property SimpleORMapCollection participants has_and_belongs_to_many User
 */
class StudipEvaluation extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'eval';
        $config['belongs_to']['author'] = array(
            'class_name' => 'User',
            'foreign_key' => 'author_id'
        );
        $config['has_and_belongs_to_many']['participants'] = array(
            'class_name' => 'User',
            'thru_table' => 'eval_user'
        );
        $config['additional_fields']['enddate'] = true;
        parent::configure($config);
    }
    
    /**
     * Fetches all evaluations for a specific range_id
     * 
     * @param String $range_id the range id
     * @return Array All evaluations for that range
     */
    public static function findByRange_id($range_id) {
        return self::findThru($range_id, array(
            'thru_table'        => 'eval_range',
            'thru_key'          => 'range_id',
            'thru_assoc_key'    => 'eval_id',
            'assoc_foreign_key' => 'eval_id'
        ));
    }
    
    /**
     * Returns the enddate of a evaluation. Returns null if stop is manual
     * 
     * @return stopdate or null
     */
    public function getEnddate() {
        if ($this->stopdate) {
            return $this->stopdate;
        }
        if ($this->timespan) {
            return $this->startdate + $this->timespan;
        }
        return null;
    }

    function getNumberOfVotes () {
        return DBManager::get()->fetchColumn("SELECT count(DISTINCT user_id) FROM eval_user WHERE eval_id = ?", array($this->id));
}
}
