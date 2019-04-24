<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * Databaseclass for all evaluationgroups
 *
 * @author      Alexander Willner <mail@AlexanderWillner.de>
 * @license     GPL2 or any later version
 */

require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_OBJECTDB;
require_once EVAL_FILE_ANSWERDB;

/**
 * @const INSTANCEOF_EVALGROUPDB Instance of an evaluationGroupDB object
 * @access public
 */
define('INSTANCEOF_EVALGROUPDB', 'EvalGroupDB');

class EvaluationGroupDB extends EvaluationObjectDB
{
    public function __construct()
    {
        parent::__construct();
        $this->instanceof = INSTANCEOF_EVALGROUPDB;
    }
    
    /**
     * Loads an evaluationgroup from DB into an object
     *
     * @param object  EvaluationGroup &$groupObject  The group to load
     * @throws error
     */
    public function load(&$groupObject)
    {
        $row = DBManager::get()->fetchOne("
            SELECT * FROM evalgroup WHERE evalgroup_id = ? ORDER BY position ",
            [$groupObject->getObjectID()]
        );
        
        if (count($row) === 0) {
            return $this->throwError(1, _('Keine Gruppe mit dieser ID gefunden.'));
        }
        
        $groupObject->setParentID($row['parent_id']);
        $groupObject->setTitle($row['title']);
        $groupObject->setText($row['text']);
        $groupObject->setPosition($row['position']);
        $groupObject->setChildType($row['child_type']);
        $groupObject->setMandatory($row['mandatory']);
        $groupObject->setTemplateID($row['template_id']);
        
        if ($groupObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
            if ($groupObject->loadChildren == EVAL_LOAD_ONLY_EVALGROUP) {
                EvaluationGroupDB::addChildren($groupObject);
            } else {
                EvaluationGroupDB::addChildren($groupObject);
                EvaluationQuestionDB::addChildren($groupObject);
            }
        }
    }
    
    /**
     * Saves a group
     * @param object   EvaluationGroup  &$groupObject  The group to save
     * @throws  error
     */
    public function save(&$groupObject)
    {
        if ($this->exists($groupObject->getObjectID())) {
            DBManager::get()->execute("
            UPDATE evalgroup SET
                title           = ?,
                text            = ?,
                child_type      = ?,
                position        = ?,
                template_id     = ?,
                mandatory       = ?
            WHERE
                evalgroup_id    = ?
            ", [
                    (string)$groupObject->getTitle(),
                    (string)$groupObject->getText(),
                    (string)$groupObject->getChildType(),
                    (int)$groupObject->getPosition(),
                    (string)$groupObject->getTemplateID(),
                    (int)$groupObject->isMandatory(),
                    (string)$groupObject->getObjectID()
                ]
            );
        } else {
            DBManager::get()->execute("
            INSERT INTO evalgroup SET
                evalgroup_id    = ?,
                parent_id       = ?,
                title           = ?,
                text            = ?,
                child_type      = ?,
                mandatory       = ?,
                template_id     = ?,
                position        = ?
            ", [
                    (string)$groupObject->getObjectID(),
                    (string)$groupObject->getParentID(),
                    (string)$groupObject->getTitle(),
                    (string)$groupObject->getText(),
                    (string)$groupObject->getChildType(),
                    (int)$groupObject->isMandatory(),
                    (string)$groupObject->getTemplateID(),
                    (int)$groupObject->getPosition()
                ]
            );
        }
    }
    
    /**
     * Deletes a group
     * @param object   EvaluationGroup  &$groupObject  The group to delete
     * @throws  error
     */
    public function delete(&$groupObject)
    {
        DBManager::get()->execute("DELETE FROM evalgroup WHERE evalgroup_id = ?", [$groupObject->getObjectID()]);
    }
    
    /**
     * Checks if group with this ID exists
     * @param string $groupID The groupID
     * @return  bool     YES if exists
     */
    public function exists($groupID)
    {
        $result = DBManager::get()->fetchColumn("SELECT 1 FROM evalgroup WHERE evalgroup_id = ?", [$groupID]);
        return (bool)$result;
    }
    
    /**
     * Adds the children to a parent object
     * @param EvaluationObject  &$parentObject The parent object
     */
    public static function addChildren(&$parentObject)
    {
        $result = DBManager::get()->fetchFirst("
            SELECT evalgroup_id FROM evalgroup
            WHERE parent_id = ?
            ORDER BY position",
            [$parentObject->getObjectID()]
        );
        
        if (($loadChildren = $parentObject->loadChildren) == EVAL_LOAD_NO_CHILDREN) {
            $loadChildren = EVAL_LOAD_NO_CHILDREN;
        }
        
        foreach ($result as $groupID) {
            $parentObject->addChild(
                new EvaluationGroup ($groupID, $parentObject, $loadChildren)
            );
        }
    }
    
    /**
     * Returns the type of an objectID
     * @param string $objectID The objectID
     * @return string  INSTANCEOF_x, else NO
     */
    public function getType($objectID)
    {
        if ($this->exists($objectID)) {
            return INSTANCEOF_EVALGROUP;
        } else {
            $dbObject = new EvaluationQuestionDB ();
            return $dbObject->getType($objectID);
        }
    }
    
    
    /**
     * Returns whether the childs are groups or questions
     * @param string $objectID The object id
     */
    public function getChildType($objectID)
    {
        $result = DBManager::get()->fetchColumn("
            SELECT child_type FROM evalgroup WHERE evalgroup_id = ?", [$objectID]
        );
        if ($result) {
            return $result;
        }
        return null;
    }
    
    /**
     * Returns the id from the parent object
     * @param string $objectID The object id
     * @return string  The id from the parent object
     */
    public function getParentID($objectID)
    {
        return DBManager::get()->fetchColumn("
            SELECT parent_id FROM evalgroup WHERE evalgroup_id = ?", [$objectID]
        );
    }
}
