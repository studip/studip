<?php

class QuestionnaireAssignment extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'questionnaire_assignments';
        $config['belongs_to']['questionnaire'] = array(
            'class_name'        => 'Questionnaire',
            'foreign_key'       => 'questionnaire_id',
            'assoc_foreign_key' => 'questionnaire_id',
        );

        parent::configure($config);
    }

    public static function findBySeminarAndQuestionnaire($seminar_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'course'", array($questionnaire_id, $seminar_id));
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
                $storage->addTabularData(_('Fragebögen Zuweisungen'), 'questionnaire_assignments', $field_data, $user);
            }
        }
        return $storage;
    }
}
