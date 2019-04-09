<?php

class QuestionnaireAssignment extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_assignments';
        $config['belongs_to']['questionnaire'] = [
            'class_name'        => 'Questionnaire',
            'foreign_key'       => 'questionnaire_id',
            'assoc_foreign_key' => 'questionnaire_id',
        ];

        parent::configure($config);
    }

    public static function findBySeminarAndQuestionnaire($seminar_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'course'", [$questionnaire_id, $seminar_id]);
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
                $storage->addTabularData(_('Fragebögen Zuweisungen'), 'questionnaire_assignments', $field_data);
            }
        }
    }
}
