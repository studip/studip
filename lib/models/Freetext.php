<?php

require_once 'lib/classes/QuestionType.interface.php';

use eTask\Task;

class Freetext extends QuestionnaireQuestion implements QuestionType
{
    static public function getIcon($active = false, $add = false)
    {
        return Icon::create(($add ?  "add/" : "")."guestbook", $active ? "clickable" : "info");
    }

    static public function getName()
    {
        return _("Freitextfrage");
    }

    public function getEditingTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open("questionnaire/question_types/freetext/freetext_edit.php");
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createDataFromRequest()
    {
        $questions = Request::getArray("questions");
        $data = $questions[$this->getId()];

        if (!$this->etask) {
            $this->etask = Task::create([
                'type' => "freetext",
                'user_id' => $GLOBALS['user']->id,
            ]);
        }

        $this->etask->description = Studip\Markup::purifyHtml($data['description']);
        $this->etask->task = [];

        $this->etask->store();
    }

    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open("questionnaire/question_types/freetext/freetext_answer.php");
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createAnswer()
    {
        $answer = $this->getMyAnswer();
        $answers = Request::getArray("answers");
        $userAnswerText = $answers[$this->getId()]['answerdata']['text'];
        $answer->setData(['answerData' => ['text' => $userAnswerText]]);
        return $answer;
    }

    public function getResultTemplate($only_user_ids = null)
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open("questionnaire/question_types/freetext/freetext_evaluation.php");
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function getResultArray()
    {
        $output = array();

        foreach ($this->answers as $answer) {
            $text = $answer['answerdata']['text'];
            if (isset($output[$text])) {
                $output[$text][$answer['user_id']]++;
            } else {
                $output[$text] = array($answer['user_id'] => 1);
            }
        }

        return $output;
    }

    public function onEnding()
    {
        //Nothing to do here.
    }
}
