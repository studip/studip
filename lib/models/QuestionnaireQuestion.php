<?php

use eTask\Task;

class QuestionnaireQuestion extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_questions';
        $config['belongs_to']['questionnaire'] = [
            'class_name' => 'Questionnaire',
            'foreign_key' => 'questionnaire_id'
        ];
        $config['has_many']['answers'] = [
            'class_name' => 'QuestionnaireAnswer',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['belongs_to']['etask'] = [
            'class_name' => '\\eTask\\Task',
            'foreign_key' => 'etask_task_id'
        ];
        parent::configure($config);
    }

    public static function findByQuestionnaire_id($questionnaire_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM questionnaire_questions
            WHERE questionnaire_id = ?
            ORDER BY position ASC
        ");
        $statement->execute([$questionnaire_id]);
        $data = $statement->fetchAll();
        $questions = [];
        foreach ($data as $questionnaire_data) {

            if (!$task = Task::find($questionnaire_data['etask_task_id'])) {
                continue;
            }

            $class = $task->type;

            if ($class === 'multiple-choice') {
                $totalScore = array_reduce(
                    isset($task->task['answers']) ? $task->task['answers']->getArrayCopy() : [],
                    function ($totalScore, $answer) {
                        return $totalScore + intval($answer['score'] ?: 0);
                    },
                    0
                );
                $class = $totalScore === 0 ? 'Vote' : 'Test';
            }

            if (class_exists($class)) {
                $questions[] = $class::buildExisting($questionnaire_data);
            }
        }
        return $questions;
    }

    public function getMyAnswer($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if (!$user_id || $user_id === "nobody") {
            $answer = new QuestionnaireAnswer();
            $answer['user_id'] = $user_id;
            $answer['question_id'] = $this->getId();
            return $answer;
        }
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM questionnaire_answers
            WHERE question_id = :question_id
                AND user_id = :me
        ");
        $statement->execute([
            'question_id' => $this->getId(),
            'me' => $user_id
        ]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return QuestionnaireAnswer::buildExisting($data);
        } else {
            $answer = new QuestionnaireAnswer();
            $answer['user_id'] = $user_id;
            $answer['question_id'] = $this->getId();
            return $answer;
        }
    }

    public function onBeginning()
    {
    }

    public function onEnding()
    {
    }
}