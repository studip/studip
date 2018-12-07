<?php
/**
 * Privacy.php - Privacy policy of Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class Privacy
{
    /**
     * Names of classes containing user data.
     */
    private static $privacy_classes = [
        'User',
        'BlubberPosting',
        'CalendarEvent',
        'EventData',
        'CourseDate',
        'CourseExDate',
        'DataField',
        'DatafieldEntryModel',
        'FileRef',
        'ForumEntry',
        'StudipNews',
        'StudipComment',
        'Message',
        'MessageUser',
        'Course',
        'CourseMember',
        'AdmissionApplication',
        'ArchivedCourse',
        'ArchivedCourseMember',
        'Statusgruppen',
        'StatusgruppeUser',
        'UserConfig',
        'InstituteMember',
        'UserStudyCourse',
        'Fach',
        'Abschluss',
        'WikiPage',
        'Evaluation',
        'Questionnaire',
        'QuestionnaireAnswer',
        'QuestionnaireAssignment',
        'eTask\Attempt',
        'eTask\Response',
        'eTask\Task',
        'eTask\Test',
        'HelpTourUser',
        'StudipLitList',
        'LogEvent'
    ];

    /**
     * Returns the tables containing user data.
     * the array consists of the tables containing user data
     * the expected format for each table is:
     * $array[ table display name ] = [ 'table_name' => name of the table, 'table_content' => array of db rows containing userdata]
     *
     * @param string $user_id
     * @return array
     */
    public static function getUserdataInformation($user_id)
    {
        //workaround make Forum Model available
        PluginManager::getInstance()->getPlugin('CoreForum');
        $core_data = [];
        $user = User::find($user_id);

        foreach (self::$privacy_classes as $privacy_class) {
            if (class_exists($privacy_class) && in_array('PrivacyObject', class_implements($privacy_class))) {
                $class_storage = $privacy_class::getUserdata($user);
                if ($class_storage && $class_storage->hasData()) {
                    foreach ($class_storage->getTabularData() as $meta) {
                        $core_data[$meta['name']] = [
                            'table_name'    => $meta['key'],
                            'table_content' => $meta['value'],
                        ];
                    }
                }
            }
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM object_user_visits WHERE user_id =?", [$user->user_id]);
        if ($field_data) {
            $core_data['Objekt Aufrufe'] = [
                'table_name'    => 'object_user_visits',
                'table_content' => $field_data,
            ];
        }

        foreach (PluginEngine::getPlugins('PrivacyPlugin') as $plugin) {
            $plugin_data = $plugin->getUserData($user_id);
            if ($plugin_data && $plugin_data->hasData()) {
                foreach ($plugin_data->getTabularData() as $meta) {
                    $core_data[$meta['name']] = [
                        'table_name'    => $meta['key'],
                        'table_content' => $meta['value']
                    ];
                }
            }
        }

        return $core_data;
    }

    /**
     * Checks if current user is privileged to see the data of given user
     *
     * @param string $user_id
     * @return boolean
     */
    public static function isVisible($user_id)
    {
        $needed_perm = Config::get()->PRIVACY_PERM ?: 'root';
        $allowed_person = true;
        if (!in_array($needed_perm, ['root', 'admin'])) {
            if (!$GLOBALS['perm']->have_perm('admin')) {
                $allowed_person = $GLOBALS['user']->user_id === $user_id;
            }
        }

        return $GLOBALS['perm']->have_perm($needed_perm)
            && $allowed_person;
    }

}
