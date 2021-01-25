<?php

require_once 'lib/classes/QuestionType.interface.php';

use eTask\Task;

class Freetext extends QuestionnaireQuestion implements QuestionType
{
    /**
     * Returns the Icon-object to this QuestionType.
     * @param bool $active: true if Icon should be clickable, false for black info-icon.
     * @param bool $add : true if the add-appendix shoudl be added to the icon.
     * @return Icon : guestbook-icon.
     */
    public static function getIcon($active = false, $add = false)
    {
        return Icon::create(
            ($add ?  'add/' : '') . 'guestbook',
            $active ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO
        );
    }

    /**
     * Returns the name of this QuestionType "Freitextfrage".
     * @return string
     */
    public static function getName()
    {
        return _('Freitextfrage');
    }

    /**
     * Returns the template to edit this question
     * @return Flexi_Template
     * @throws Flexi_TemplateNotFoundException if there is no template.
     */
    public function getEditingTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__ . '/../../app/views'));
        $template = $factory->open('questionnaire/question_types/freetext/freetext_edit.php');
        $template->vote = $this;
        return $template;
    }

    /**
     * Processes the request and stores the given values into the etask-object.
     * Called when the question is saved by the user.
     */
    public function createDataFromRequest()
    {
        $questions = Request::getArray('questions');
        $data = $questions[$this->getId()];

        if (!$this->etask) {
            $this->etask = Task::create([
                'type'    => 'freetext',
                'user_id' => $GLOBALS['user']->id,
            ]);
        }

        $this->etask->description = Studip\Markup::purifyHtml($data['description']);
        $this->etask->task = [];

        // update mandatory option
        if (isset($data['options']['mandatory'])) {
            $options = $this->etask->options;
            $options['mandatory'] = (bool) $data['options']['mandatory'];
            $this->etask->options = $options;
        }

        $this->etask->store();
    }

    /**
     * Returns the template of this question to answer the question.
     * @return Flexi_Template
     * @throws Flexi_TemplateNotFoundException if there is no template.
     */
    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__ . '/../../app/views'));
        $template = $factory->open('questionnaire/question_types/freetext/freetext_answer.php');
        $template->vote = $this;
        return $template;
    }

    /**
     * Creates an answer by the parameters of the request. Called when a user clicked to answer
     * the questionnaire.
     * @return QuestionnaireAnswer
     */
    public function createAnswer()
    {
        $answer = $this->getMyAnswer();
        $answers = Request::getArray('answers');
        $userAnswerText = $answers[$this->getId()]['answerdata']['text'];
        $answer->setData(['answerData' => ['text' => $userAnswerText]]);
        return $answer;
    }

    /**
     * Returns the template with the answers of the question so far.
     * @param null $only_user_ids : array of user_ids
     * @return Flexi_Template
     * @throws Flexi_TemplateNotFoundException if there is no template.
     */
    public function getResultTemplate($only_user_ids = null)
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__ . '/../../app/views'));
        $template = $factory->open('questionnaire/question_types/freetext/freetext_evaluation.php');
        $template->vote = $this;
        return $template;
    }

    /**
     * Returns an array of the answers to be put into a CSV-file.
     * @return array
     */
    public function getResultArray()
    {
        $output = [];
        $countNobodys = 0;

        $question = trim(strip_tags($this->etask->description));
        foreach ($this->answers as $answer) {
            if ($answer['user_id'] && $answer['user_id'] != 'nobody') {
                $userId = $answer['user_id'];
            } else {
                $countNobodys++;
                $userId = _('unbekannt').' '.$countNobodys;
            }

            $output[$question][$userId] = $answer['answerdata']['text'];
        }

        return $output;
    }

    /**
     * Called after the questionnaire gets closed. Does nothing for this QuestionType Freetext.
     */
    public function onEnding()
    {
        //Nothing to do here.
    }
}
