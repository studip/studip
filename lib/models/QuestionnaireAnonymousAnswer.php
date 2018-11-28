<?php

class QuestionnaireAnonymousAnswer extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'questionnaire_anonymous_answers';
        $config['belongs_to']['questionnaire'] = array(
            'class_name' => 'Questionnaire'
        );
        parent::configure($config);
    }
}