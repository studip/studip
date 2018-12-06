<?php

class QuestionnaireAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'questionnaire_answers';
        $config['belongs_to']['question'] = array(
            'class_name' => 'QuestionnaireQuestion'
        );
        $config['serialized_fields']['answerdata'] = "JSONArrayObject";
        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return array of StoredUserData objects
     */
    public static function getUserdata(User $user)
    {
        $storage = new StoredUserData($user);
        $sorm = self::findBySQL("user_id = ?", [$user->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Fragebögen Antworten'), 'questionnaire_answers', $field_data, $user);
            }
        }
        return $storage;
    }
}
