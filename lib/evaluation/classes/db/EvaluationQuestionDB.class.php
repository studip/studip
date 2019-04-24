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
 * @const INSTANCEOF_EVALQUESTIONDB Instance of an evaluationQuestionDB object
 * @access public
 */
define('INSTANCEOF_EVALQUESTIONDB', 'EvalQuestionDB');

# =========================================================================== #


class EvaluationQuestionDB extends EvaluationObjectDB
{
    public function __construct()
    {
        parent::__construct();
        $this->instanceof = INSTANCEOF_EVALGROUPDB;
    }
    
    /**
     * Loads a question from the DB
     * @param EvaluationQuestion   &$questionObject The question object
     */
    public function load(&$questionObject)
    {
        $query = "
            SELECT *
            FROM evalquestion
            WHERE evalquestion_id = ?
            ORDER BY position ";
        $row   = DBManager::get()->fetchOne($query, [$questionObject->getObjectID()]);
        
        if (!count($row)) {
            return $this->throwError(1, _('Keine Frage mit dieser ID gefunden.'));
        }
        
        $questionObject->setParentID($row['parent_id']);
        $questionObject->setType($row['type']);
        $questionObject->setPosition($row['position']);
        $questionObject->setText($row['text']);
        $questionObject->setMultiplechoice($row['multiplechoice']);
        
        if ($questionObject->loadChildren != EVAL_LOAD_NO_CHILDREN) {
            EvaluationAnswerDB::addChildren($questionObject);
        }
    }
    
    /**
     * Writes or updates a question into the DB
     * @param EvaluationQuestion   &$questionObject The question object
     */
    public function save(&$questionObject)
    {
        if ($this->exists($questionObject->getObjectID())) {
            $sql = "
                UPDATE evalquestion
                SET
                 parent_id = ?, type = ?, position = ?, text = ?, multiplechoice = ?
                WHERE
                 evalquestion_id = ?";
        } else {
            $sql = "
            INSERT INTO evalquestion  SET parent_id = ?, type = ?, position = ?, text = ?, multiplechoice = ?, evalquestion_id = ?";
        }
        
        DBManager::get()->execute($sql, [
                (string)$questionObject->getParentID(),
                (string)$questionObject->getType(),
                (int)$questionObject->getPosition(),
                (string)$questionObject->getText(),
                (int)$questionObject->isMultiplechoice(),
                $questionObject->getObjectID()
            ]
        );
    }
    
    /**
     * Deletes a question
     * @param object EvaluationQuestion &$questionObject The question to delete
     * @throws  error
     */
    public function delete(&$questionObject)
    {
        DBManager::get()->execute(
            "DELETE FROM evalquestion WHERE evalquestion_id = ?",
            [$questionObject->getObjectID()]
        );
    }
    
    /**
     * Checks if question with this ID exists
     * @param string $questionID The questionID
     * @return  bool     YES if exists
     */
    public function exists($questionID)
    {
        return (bool)DBManager::get()->fetchColumn(
            "SELECT 1 FROM evalquestion WHERE evalquestion_id = ?",
            [$questionID]
        );
    }
    
    /**
     * Checks if a template exists with this title
     * @param string $questionTitle The title of the question
     * @param string $userID The user id
     * @return  bool     YES if exists
     */
    public function titleExists($questionTitle, $userID)
    {
        return (bool)DBManager::get()->fetchColumn(
            "SELECT 1 FROM evalquestion WHERE text = ? AND parent_id = ?",
            [$questionTitle, $userID]
        );
    }
    
    /**
     * Adds the children to a parent object
     * @param EvaluationObject  &$parentObject The parent object
     */
    public static function addChildren(&$parentObject)
    {
        $result = DBManager::get()->fetchFirst(
            "SELECT evalquestion_id FROM evalquestion WHERE parent_id = ? ORDER BY position",
            [$parentObject->getObjectID()]
        );
        
        $loadChildren = $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN
            ? EVAL_LOAD_ALL_CHILDREN
            : EVAL_LOAD_NO_CHILDREN;
        
        foreach ($result as $evalquestion_id) {
            $parentObject->addChild(
                new EvaluationQuestion($evalquestion_id, $parentObject, $loadChildren)
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
            return INSTANCEOF_EVALQUESTION;
        } else {
            $dbObject = new EvaluationAnswerDB ();
            return $dbObject->getType($objectID);
        }
    }
    
    /**
     * Returns the id from the parent object
     * @param string $objectID The object id
     * @return string  The id from the parent object
     */
    public function getParentID($objectID)
    {
        return DBManager::get()->fetchColumn(
            "SELECT parent_id FROM evalquestion WHERE evalquestion_id = ?",
            [$objectID]
        );
    }
    
    /**
     * Returns the ids of the Answertemplates of a user
     * @param string $userID The user id
     * @return array  The ids of the answertemplates
     */
    public function getTemplateID($userID)
    {
        $db = DBManager::get();
        
        if (EvaluationObjectDB::getGlobalPerm() === 'root') {
            return $db->fetchFirst("SELECT evalquestion_id FROM evalquestion WHERE parent_id = '0' ORDER BY text");
        } else {
            return $db->fetchFirst("SELECT evalquestion_id FROM evalquestion WHERE parent_id = ? OR parent_id = '0' ORDER BY text", [$userID]);
        }
    }
}