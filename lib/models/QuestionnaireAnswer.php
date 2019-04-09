<?php

class QuestionnaireAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_answers';
        $config['belongs_to']['question'] = [
            'class_name' => 'QuestionnaireQuestion'
        ];
        $config['serialized_fields']['answerdata'] = "JSONArrayObject";
        parent::configure($config);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Fragebögen Antworten'), 'questionnaire_answers', $field_data);
            }
        }
    }
}
