<?php

/**
 * Interface QuestionnaireAssignmentPlugin
 * Implement this interface if you want to relate Stud.IP-questionnaires to your plugin-contents.
 * By storing an assignment you should set the range_type to something unique that is
 * related to your plugin like "mytaskplugin". In all methods you should first check if the
 * assignment is related to your plugin, because other QuestionnaireAssignmentPlugins might
 * be installed as well.
 */
interface QuestionnaireAssignmentPlugin
{
    /**
     * Returns if the questionnaire is viewable dependend on the assignment. Check for the range_type
     * and range_id to see if the the assignment has to do with your plugin (and not with
     * somebody else's plugin) and if type and id fit.
     * @param QuestionnaireAssignment $questionnaire
     * @return boolean
     */
    public function isQuestionnaireViewable(QuestionnaireAssignment $questionnaire);

    /**
     * Returns if the questionnaire is editable dependend on the assignment. Check for the range_type
     * and range_id to see if the the assignment has to do with your plugin (and not with
     * somebody else's plugin) and if type and id fit.
     * @param QuestionnaireAssignment $questionnaire
     * @return boolean
     */
    public function isQuestionnaireEditable(QuestionnaireAssignment $questionnaire);

    /**
     * The display name of the assignment.
     * @param QuestionnaireAssignment $questionnaire
     * @return string
     */
    public function getQuestionnaireAssignmentName(QuestionnaireAssignment $questionnaire);

    /**
     * This template will get displayed when someone at tools -> questionnaires
     * wants to edit the contexts of the questionnaire. Maybe you don't want to provide a
     * template here, so return null or just a readonly html-snippet.
     * @param Questionnaire $questionnaire
     * @return null|Flexi_Template
     */
    public function getQuestionnaireAssignmentEditTemplate(Questionnaire $questionnaire);

    /**
     * When the context of the questionnaire is stored at tools -> questionnaires (where
     * the template from getQuestionnaireAssignmentEditTemplate was displayed) you should
     * use this method to store your assignments as well.
     * @param Questionnaire $questionnaire
     * @return null
     */
    public function storeQuestionnaireAssignments(Questionnaire $questionnaire);
}
