<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * @author      Alexander Willner <mail@AlexanderWillner.de>
 * @license     GPL2 or any later version
 */


require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_OBJECTDB;

/**
 * @const INSTANCEOF_EVALANSWERDB Instance of an evaluationanswerDB object
 * @access public
 */
define('INSTANCEOF_EVALANSWERDB', 'EvalANSWERDB');


class EvaluationAnswerDB extends EvaluationObjectDB
{
    public function __construct()
    {
        parent::__construct();
        $this->instanceof = INSTANCEOF_EVALANSWERDB;
    }
    
    /**
     * Loads answers of a group from the DB
     * @param EvaluationAnswer   &&$answerObject The answer object
     */
    public function load(&$answerObject)
    {
        /* load answer --------------------------------------------------------- */
        $row = DBManager::get()->fetchOne(
            "SELECT * FROM evalanswer WHERE evalanswer_id = ?",
            [$answerObject->getObjectID()]
        );
        if (!count($row)) {
            return $this->throwError(2, _('Keine Antwort mit dieser ID gefunden.'));
        }
        
        $answerObject->setObjectID($row['evalanswer_id']);
        $answerObject->setParentID($row['parent_id']);
        $answerObject->setPosition($row['position']);
        $answerObject->setText($row['text']);
        $answerObject->setValue($row['value']);
        $answerObject->setRows($row['rows']);
        $answerObject->setResidual($row['residual']);
    }
    
    /**
     * Loads the votes from the users for this answer
     * @access   public
     * @param EvaluationAnswer   &$answerObject The answer object
     */
    public function loadVotes(&$answerObject)
    {
        $result = DBManager::get()->fetchFirst(
            "SELECT user_id FROM evalanswer_user WHERE evalanswer_id = ?",
            [$answerObject->getObjectID()]
        );
        foreach ($result as $row) {
            $answerObject->addUserID($row, NO);
        }
    }
    
    /**
     * Writes answers into the DB
     * @param EvaluationAnswer   &$answerObject The answerobject
     * @throws    error
     */
    public function save(&$answerObject)
    {
        DBManager::get()->execute(
            "REPLACE INTO evalanswer SET
                `evalanswer_id`   = ?,
                `parent_id`       = ?,
                `position`        = ?,
                `text`            = ?,
                `value`           = ?,
                `rows`            = ?,
                `residual`        = ?
                ",
            [$answerObject->getObjectID(),
             $answerObject->getParentID(),
             $answerObject->getPosition(),
             $answerObject->getText(),
             $answerObject->getValue(),
             $answerObject->getRows(),
             $answerObject->isResidual()]
        );
        
        while ($userID = $answerObject->getNextUserID()) {
            DBManager::get()->execute(
                "INSERT INTO evalanswer_user SET
                    evalanswer_id   = ?,
                    user_id         = ?,
                    evaldate        = UNIX_TIMESTAMP()",
                [$answerObject->getObjectID(), $userID]
            );
        }
    }
    
    /**
     * Deletes all votes from the users for this answers
     * @param EvaluationAnswer   &$answerObject The answer object
     */
    public function resetVotes(&$answerObject)
    {
        DBManager::get()->execute("
            DELETE FROM evalanswer_user WHERE evalanswer_id = ?",
            [$answerObject->getObjectID()]
        );
    }
    
    /**
     * Deletes a answer
     * @param EvaluationAnswer   &$answerObject The answer to delete
     * @throws  error
     */
    public function delete(&$answerObject)
    {
        DBManager::get()->execute(
            "DELETE FROM evalanswer WHERE evalanswer_id   = ?",
            [$answerObject->getObjectID()]
        );
        $this->resetVotes($answerObject);
    }
    
    /**
     * Checks if answer with this ID exists
     * @param string $answerID The answerID
     * @return  bool     YES if exists
     */
    public function exists($answerID)
    {
        $result = DBManager::get()->fetchOne(
            "SELECT 1 FROM evalanswer WHERE evalanswer_id= ?", [$answerID]
        );
        if (count($result) > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Adds the children to a parent object
     * @param EvaluationObject  &$parentObject The parent object
     */
    public static function addChildren(&$parentObject)
    {
        $result = DBManager::get()->fetchFirst(
            "SELECT evalanswer_id FROM evalanswer WHERE parent_id= ? ORDER by position",
            [$parentObject->getObjectID()]
        );
        
        $loadChildren =
            $parentObject->loadChildren == EVAL_LOAD_ALL_CHILDREN ? EVAL_LOAD_ALL_CHILDREN : EVAL_LOAD_NO_CHILDREN;
        
        foreach ($result as $row) {
            $parentObject->addChild(new EvaluationAnswer
            ($row, $parentObject, $loadChildren));
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
            return INSTANCEOF_EVALANSWER;
        } else {
            return NO;
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
            "SELECT parent_id FROM evalanswer WHERE evalanswer_id = ?", [$objectID()]
        );
    }
    
    /**
     * Give all textanswers for a user and question for the export
     * @param string $questionID The question id
     * @param string $userID The user id
     * @return array $answer_ids
     */
    public function getUserAnwerIDs($questionID, $userID)
    {
        $sql = "SELECT a.evalanswer_id as ttt FROM evalanswer a, evalanswer_user b
                    WHERE a.parent_id = ? AND a.evalanswer_id = b.evalanswer_id";
        if (empty ($userID)) {
            $answer_ids = DBManager::get()->fetchFirst($sql, [$questionID]);
        } else {
            $answer_ids = DBManager::get()->fetchFirst($sql . " AND b.user_id = ?", [$questionID, $userID]);
        }
        return $answer_ids;
    }
    
    /**
     * Checks whether a user has voted for an answer
     * @param string $answerID The answer id
     * @param string $userID The user id
     * @return   boolean  YES if user has voted for the answer
     */
    public function hasVoted($answerID, $userID)
    {
        $result = DBManager::get()->fetchOne(
            "SELECT 1 FROM evalanswer_user WHERE evalanswer_id= ? AND user_id",
            [$answerID, $userID]
        );
        if (count($result) > 0) {
            return true;
        }
        return false;
    }
    
    public function getAllAnswers($question_id, $userID, $only_user_answered = false)
    {
        if ($only_user_answered) {
            return DBManager::get()->fetchAll("
                SELECT evalanswer.*, COUNT(IF(user_id=?,1,NULL)) AS has_voted
                FROM evalanswer LEFT JOIN evalanswer_user USING(evalanswer_id)
                WHERE parent_id = ? AND user_id = ?
                GROUP BY evalanswer.evalanswer_id ORDER BY position",
                [$userID, $question_id, $userID]
            );
        } else {
            return DBManager::get()->fetchAll("
                SELECT evalanswer.*, COUNT(IF(user_id=?,1,NULL)) AS has_voted
                FROM evalanswer LEFT JOIN evalanswer_user USING(evalanswer_id)
                WHERE parent_id = ?
                GROUP BY evalanswer.evalanswer_id ORDER BY position",
                [$userID, $question_id]
            );
        }
    }
}
