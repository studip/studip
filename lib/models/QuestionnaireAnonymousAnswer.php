<?php

class QuestionnaireAnonymousAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_anonymous_answers';
        $config['belongs_to']['questionnaire'] = [
            'class_name' => 'Questionnaire'
        ];
        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return StoredUserData object
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
                $storage->addTabularData(_('Fragebögen anonyme Antworten'),'questionnaire_anonymous_answers', $field_data, $user);
            }
        }
        return $storage;
    }
}
