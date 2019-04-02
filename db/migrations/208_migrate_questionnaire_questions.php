<?php

use eTask\Task;

/**
 * Migration for Ticket #7059
 *
 * @author  <mlunzena@uos.de>
 */
class MigrateQuestionnaireQuestions extends Migration
{

    public function description()
    {
        return 'Migrates questions of the questionnaires to eTask compatible tasks.';
    }

    public function up()
    {
        $this->db = DBManager::get();
        $this->addEtaskIDToQuestionsTable();

        foreach ($this->fetchQuestions() as $question) {
            $this->migrateQuestion($question);
        }

        $this->migrateMCAnswers();

        $this->removeQuestionTypeAndDataFromQuestionsTable();
    }

    public function down()
    {
        $this->db = DBManager::get();
        $this->addQuestionTypeAndDataFromQuestionsTable();

        foreach ($this->fetchQuestions() as $question) {
            $this->unmigrateQuestion($question);
        }

        $this->removeEtaskIDToQuestionsTable();
    }

    // ***** PRIVATES *****

    private function addEtaskIDToQuestionsTable()
    {
        $this->db->exec("
            ALTER TABLE  `questionnaire_questions`
            ADD  `etask_task_id` INT NOT NULL
            AFTER  `questionnaire_id`
        ");
    }

    private function addQuestionTypeAndDataFromQuestionsTable()
    {
        $this->db->exec("
            ALTER TABLE  `questionnaire_questions`
            ADD  `questiondata` text NOT NULL AFTER  `questionnaire_id`,
            ADD  `questiontype` varchar(64) NOT NULL AFTER  `questionnaire_id`
        ");
    }

    private function connectTaskToQuestion($taskID, $questionID)
    {
        $stmt = $this->db->prepare("
            UPDATE questionnaire_questions
            SET  etask_task_id =  ?
            WHERE  question_id = ?
        ");

        $stmt->execute([ $taskID, $questionID ]);
    }

    private function fetchTask($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM  `etask_tasks` WHERE id = ?
        ");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchQuestions()
    {
        $stmt = $this->db->prepare('SELECT * FROM  `questionnaire_questions`');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt;
    }

    private function findOwner($questionID)
    {
        $stmt = $this->db->prepare("
            SELECT user_id
            FROM  `questionnaire_questions`
            INNER JOIN questionnaires
            USING ( questionnaire_id )
            WHERE question_id = ? LIMIT 1
        ");

        $stmt->execute([ $questionID ]);

        return $stmt->fetchColumn();
    }

    private function fetchMCAnswers()
    {
        $stmt = $this->db->prepare("
            SELECT answer_id, answerdata
            FROM  `questionnaire_answers`
            WHERE answerdata LIKE  '%{\"answers\":%'
        ");
        $stmt->execute([]);
        $stmt->setFetchMode(PDO::FETCH_NUM);

        return $stmt;
    }

    private function migrateMCAnswers()
    {
        $updateStmt = $this->db->prepare("
            UPDATE questionnaire_answers SET answerdata = ? WHERE answer_id = ?
        ");

        $decr = function ($item) {
            return  intval($item) - 1;
        };

        foreach ($this->fetchMCAnswers() as $answer) {
            list($answerID, $answerData) = $answer;

            $data = (array)json_decode($answerData, true);
            if (is_array($data['answers'])) {
                $dataAnswers = array_map($decr, $data['answers']);
            } else {
                $dataAnswers = $decr($data['answers']);
            }

            $data['answers'] = $dataAnswers;

            $updateStmt->execute(
                [
                    json_encode($data),
                    $answerID
                ]
            );
        }
    }

    private function migrateQuestion($questionAry)
    {
        $task = null;

        switch ($type = $questionAry['questiontype']) {
        case 'Test':
        case 'Vote':
            $task = $this->migrateTypeVoteOrTest($questionAry);
            break;

        case 'Datefinder':
            $task = $this->migrateTypeDatefinder($questionAry);
            break;

        default:
            throw new RuntimeException("Unknown Type: " . $type);
        }

        $this->connectTaskToQuestion($task->id, $questionAry['question_id']);
    }

    private function migrateTypeDatefinder($questionAry)
    {
        $oldData = (array)json_decode($questionAry['questiondata'], true);

        $description = $oldData['question'] ?: '';
        $userID = $this->findOwner($questionAry['question_id']);

        $task = [
            'automatic' => $oldData['automatic'] ? true : false,
            'dates' => $oldData['dates'] ?: [],
            'duration' => (int) $oldData['duration'],
            'founddate' => $oldData['founddate'],
            'status' => $oldData['status']
        ];

        $options = [
            'questionnaire_question_id' => $questionAry['question_id'],
            'questionnaire_questiontype' => $questionAry['questiontype'],
            'questionnaire_questiondata' => $questionAry['questiondata']
        ];

        return Task::create(
            [
                'type' => 'datefinder',
                'title' => '',
                'description' => $description,
                'task' => $task,
                'user_id' => $userID,
                'mkdate' => $questionAry['mkdate'],
                'chdate' => $questionAry['chdate'],
                'options' => $options
            ]
        );
    }

    private function migrateTypeVoteOrTest($questionAry)
    {
        $oldData = (array)json_decode($questionAry['questiondata'], true);

        $description = $oldData['question'] ?: '';
        $userID = $this->findOwner($questionAry['question_id']);

        $correctAnswer = array_key_exists('correctanswer', $oldData) && is_array($oldData['correctanswer'])
                       ? $oldData['correctanswer']
                       : [];
        $answers = [];
        foreach ($oldData['options'] as $index => $answer) {
            $isCorrect = in_array((string)($index + 1), $correctAnswer);
            $answers[] = [
                'text' => $answer,
                'score' => $isCorrect ? 1 : 0,
                'feedback' => ''
            ];
        }

        $task = [
            'type' => $oldData['multiplechoice'] ? 'multiple' : 'single',
            'answers' => $answers
        ];

        $options = [
            'randomize' => $oldData['randomize'] ? true : false,
            'questionnaire_question_id' => $questionAry['question_id'],
            'questionnaire_questiontype' => $questionAry['questiontype'],
            'questionnaire_questiondata' => $questionAry['questiondata']
        ];

        return Task::create(
            [
                'type' => 'multiple-choice',
                'title' => '',
                'description' => $description,
                'task' => $task,
                'user_id' => $userID,
                'mkdate' => $questionAry['mkdate'],
                'chdate' => $questionAry['chdate'],
                'options' => $options
            ]
        );
    }

    private function removeEtaskIDToQuestionsTable()
    {
        $this->db->exec("
            ALTER TABLE `questionnaire_questions`
            DROP `etask_task_id`
        ");
    }

    private function removeQuestionTypeAndDataFromQuestionsTable()
    {
        $this->db->exec("
            ALTER TABLE `questionnaire_questions`
            DROP `questiontype`,
            DROP `questiondata`
        ");
    }

    private function unmigrateQuestion($questionAry)
    {
        $taskID = $questionAry['etask_task_id'];
        $task = $this->fetchTask($taskID);

        $options = (array)json_decode($task['options'], true);
        $questiontype = $options['questionnaire_questiontype'];
        $questiondata = $options['questionnaire_questiondata'];

        if (!strlen($questiontype) || !strlen($questiondata)) {
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE questionnaire_questions
            SET  questiontype = ?,
                 questiondata = ?
            WHERE question_id = ?
        ");

        $stmt->execute([$questiontype, $questiondata, $questionAry['question_id']]);
    }
}
