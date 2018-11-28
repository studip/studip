<?php

/**
 * Interface QuestionType
 * This is a question-object with a specific type. Note that an object of class QuestionType
 * is also always derived from SORM class 'QuestionnairQuestion'. So additionally to all methods
 * here, the QuestionType object also knows about the data-attribute from QuestionnaireQuestion
 * and what the Questionnaire-object is by calling $this->questionnaire.
 */
interface QuestionType {

    /**
     * Returns a specific icon for this type of question. Note this is not bound to the
     * object but called staticly.
     * @return Icon the specific icon for this type of question
     */
    static public function getIcon($active = false, $add = false);

    /**
     * Returns the name of the type of question like "Frage" or "Test" or "Dateiablage"
     * This name is not showed to the participant of the questionnaire, but to the editor.
     * It might get displayed like "add another Frage to questionnaire", where 'Frage'
     * is the name of the type.
     * @return string : the name of this type of question.
     */
    static public function getName();

    /**
     * Returns a template that is used to edit or create the question. Note that
     * $this['data'] might already be filled with data, when the user is editing an
     * existing question.
     * @return Flexi_Template
     */
    public function getEditingTemplate();

    /**
     * Called right before the questionnaire and the question is stored or when the user
     * needs to refresh the editing-window, This method is called to store the
     * request-values into $this['data']. You get them from the Request-class as usual.
     */
    public function createDataFromRequest();

    /**
     * Display the question to the user. This template will be embedded into a
     * html <form>-tag. Maybe more questions will appear in that form and maybe
     * more questions of the same type. This is important to know, so that you
     * prefix all the input-fields.
     *
     * Wrong: <input type="checkbox" name="anser_a" value="1" <?= Request::get("anser_a") ? " checked" : "" ?>">
     *
     * Right: <input type="checkbox" name="answer[<?= $this->getId() ?>][a]" value="1" <?= Request::getArray("answer")[$this->getId()]['a'] ? " checked" : "" ?>">
     *
     * Try to prefix all your input variables at least with the id of the question,
     * so that they will never conflict with other variables.
     * @return Flexi_Template
     */
    public function getDisplayTemplate();

    /**
     * Uses current user and Request-variables to create an answer for the question.
     * Return this answer. It does not necessarily be stored to the database!
     * @return QuestionnaireAnswer or derived
     */
    public function createAnswer();

    /**
     * Returns a template with the results of this question.
     * @param $only_user_ids : array\null array of user_ids that the results should be restricted to.
     *                         this is used to show only a subset of results to the user for
     *                         visible evaluation of the results. If the questionnaire is anonymous
     *                         just do nothing.
     * @return Flexi_Template
     */
    public function getResultTemplate($only_user_ids = null);

    /**
     * This method is called to generate a csv-export from a whole questionnaire.
     * The returned array looks like this:
     *     array(
     *         'Answer 1' => array('e7a0a84b161f3e8c09b4a0a2e8a58147' => "1", '7e81ec247c151c02ffd479511e24cc03' => "0"),
     *         'Answer 2' => array('e7a0a84b161f3e8c09b4a0a2e8a58147' => "1", '7e81ec247c151c02ffd479511e24cc03' => "1")
     *     )
     * This is a two-dimensional array. The first array provides a set of answers. The values of
     * this array are themselves arrays with the user_ids as indexes and the user's
     * answers as the values. With this construction you can evaluate single-choice tests
     * as well as multiple-choice tests.
     * @return array : indexed with user_id and valued with the value of the answer.
     *                 If your QuestionType allows more than one value (i.e. multiple-choice)
     *                 you might need to serialize it.
     */
    public function getResultArray();

    /**
     * A method to be called after the questionnaire has ended.
     * @return void
     */
    public function onEnding();
}