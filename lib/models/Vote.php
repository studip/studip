<?php
require_once 'lib/classes/QuestionType.interface.php';

use eTask\Task;

class Vote extends QuestionnaireQuestion implements QuestionType
{
    public static function getIcon($active = false, $add = false)
    {
        return Icon::create($add ?  'vote+add' : 'vote', $active ? 'clickable' : 'info');
    }

    public static function getName()
    {
        return _('Frage');
    }

    public function getEditingTemplate()
    {
        $tf = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $tf->open('questionnaire/question_types/vote/vote_edit');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createDataFromRequest()
    {
        $questions = Request::getArray('questions');
        $data = $questions[$this->getId()];

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
        $this->etask->description = Studip\Markup::purifyHtml($data['description']);

        // update task's type (single|multiple)
        $task = [
            'type' => $data['task']['type'] === 'multiple' ? 'multiple' : 'single',
            'answers' => []
        ];

        // update task's answers
        foreach ($data['task']['answers'] as $index => $text) {
            $trimmedText = trim($text);
            if ($trimmedText === '') {
                continue;
            }

            $task['answers'][] = [
                'text' => $trimmedText,
                'score' => 0,
                'feedback' => ''
            ];
        }

        $this->etask->task = $task;

        // update randomize option
        if (isset($data['options']['randomize'])) {
            $options = $this->etask->options;
            $options['randomize'] = (bool) $data['options']['randomize'];
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
        $template = $factory->open('questionnaire/question_types/vote/vote_evaluation');
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
}
