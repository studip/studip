<?php

require_once 'lib/classes/QuestionType.interface.php';

use eTask\Task;

class Test extends QuestionnaireQuestion implements QuestionType
{
    public static function getIcon($active = false, $add = false)
    {
        return Icon::create($add ?  'test+add' : 'test', $active ? 'clickable' : 'info');
    }

    public static function getName()
    {
        return _('Test');
    }

    public function getEditingTemplate()
    {
        $tf = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $tf->open('questionnaire/question_types/test/test_edit');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createDataFromRequest()
    {
        $questions = Request::getArray('questions');
        $requestData = $questions[$this->getId()];

        // create a new eTask if this is a new question
        if (!$this->etask) {
            $this->etask = Task::create(
                [
                    'type' => 'multiple-choice',
                    'user_id' => $GLOBALS['user']->id,
                ]
            );
        }

        // update description
        $this->etask->description = Studip\Markup::purifyHtml($requestData['description']);

        // update task's type (single|multiple)
        $task = [
            'type' => $requestData['task']['type'] === 'multiple' ? 'multiple' : 'single',
            'answers' => []
        ];

        // update task's answers
        $correct = isset($requestData['task']['correct']) ? $requestData['task']['correct'] : [];
        foreach ($requestData['task']['answers'] as $index => $text) {
            $trimmedText = trim($text);
            if ($trimmedText === '') {
                continue;
            }

            $task['answers'][] = [
                'text' => $trimmedText,
                'score' => in_array($index + 1, $correct) ? 1 : 0,
                'feedback' => ''
            ];
        }

        $this->etask->task = $task;

        // update randomize option
        if (isset($requestData['options']['randomize'])) {
            $options = $this->etask->options;
            $options['randomize'] = (bool) $requestData['options']['randomize'];
            $this->etask->options = $options;
        }

        // store the eTask instance
        $this->etask->store();
    }

    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/vote/vote_answer');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createAnswer()
    {
        $answer = $this->getMyAnswer();

        $answers = Request::getArray('answers');
        if (array_key_exists($this->getId(), $answers)) {
            $userAnswer = $answers[$this->getId()]['answerdata']['answers'];
            if (is_array($userAnswer)) {
                $userAnswer = array_map('intval', $userAnswer);
            }
            else {
                $userAnswer = (int) $userAnswer;
            }
        }
        $answer->setData(['answerData' => ['answers' => $userAnswer ] ]);
        return $answer;
    }

    public function getResultTemplate($only_user_ids = null)
    {
        $answers = $this->answers;
        if ($only_user_ids !== null) {
            foreach ($answers as $key => $answer) {
                if (!in_array($answer['user_id'], $only_user_ids)) {
                    unset($answers[$key]);
                }
            }
        }
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/test/test_evaluation');
        $template->set_attribute('vote', $this);
        $template->set_attribute('answers', $answers);
        return $template;
    }

    public function getResultArray()
    {
        $output = [];

        $taskAnswers = $this->etask->task['answers'];

        foreach ($taskAnswers as $key => $option) {
            $answerOption = [];
            $countNobodys = 0;

            foreach ($this->answers as $answer) {
                $answerData = $answer['answerdata']->getArrayCopy();

                if ($answer['user_id'] && $answer['user_id'] != 'nobody') {
                    $userId = $answer['user_id'];
                } else {
                    $countNobodys++;
                    $userId = _('unbekannt').' '.$countNobodys;
                }

                if (in_array($key, (array) $answerData['answers'])) {
                    $answerOption[$userId] = 1;
                } else {
                    $answerOption[$userId] = 0;
                }
            }
            $output[$option['text']] = $answerOption;
        }
        return $output;
    }

    public function correctAnswered($userId = null,  $answersToCheck = null)
    {
        $userId = $userId ?: $GLOBALS['user']->id;
        $correctAnswered = true;
        $task = $this->etask->task;
        $numTaskAnswers = count($task['answers']);
        $resultsUsers = array_fill(0, $numTaskAnswers, []);
        if ($answersToCheck && !is_array($answersToCheck)) {
            $answersToCheck = [$answersToCheck];
        }
        $answersToCheck = is_array($answersToCheck) ? $answersToCheck : $this->answers->findBy('user_id', $userId);

        foreach ($answersToCheck as $answer) {
            if ($task['type'] === 'multiple') {
                foreach ($answer['answerdata']['answers'] as $a) {
                    $resultsUsers[(int) $a][] = $answer['user_id'];
                }
            } else {
                $resultsUsers[(int) $answer['answerdata']['answers']][] = $answer['user_id'];
            }
        }
        foreach ($task['answers'] as $index => $option) {
            if ($option['score']) {
                if (!in_array($userId, $resultsUsers[$index])) {
                    $correctAnswered = false;
                    break;
                }
            } else {
                if (in_array($userId, $resultsUsers[$index])) {
                    $correctAnswered = false;
                    break;
                }
            }
        }
        return $correctAnswered;
    }
}
