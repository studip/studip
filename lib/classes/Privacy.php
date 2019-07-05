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
        'core' => [
            'User',
            'DataField',
            'DatafieldEntryModel',
            'UserConfig',
            'HelpTourUser',
            'Grading\Instance',
            'LogEvent',
        ],
        'date' => [
            'CalendarEvent',
            'EventData',
            'CourseDate',
            'CourseExDate',
        ],
        'message' => [
            'BlubberPosting',
            'StudipNews',
            'StudipComment',
            'Message',
            'MessageUser',
        ],
        'content' => [
            'FileRef',
            'ForumEntry',
            'WikiPage',
            'StudipLitList',
        ],
        'quest' => [
            'Evaluation',
            'Questionnaire',
            'QuestionnaireAnswer',
            'QuestionnaireAnonymousAnswer',
            'QuestionnaireAssignment',
            'eTask\Attempt',
            'eTask\Response',
            'eTask\Task',
            'eTask\Test',
        ],
        'membership' => [
            'Course',
            'CourseMember',
            'AdmissionApplication',
            'ArchivedCourse',
            'ArchivedCourseMember',
            'Statusgruppen',
            'StatusgruppeUser',
            'InstituteMember',
            'UserStudyCourse',
            'Fach',
            'Abschluss',
        ],
        'plugins' => [
        ],
    ];

    /**
     * Returns the tables containing user data.
     * the array consists of the tables containing user data
     * the expected format for each table is:
     * $array[ table display name ] = [ 'table_name' => name of the table, 'table_content' => array of db rows containing userdata]
     *
     * @param string $user_id
     * @param string $section
     * @return array
     */
    public static function getUserdataInformation($user_id, $section = null)
    {
        //workaround make Forum Model available
        PluginEngine::getPlugin('CoreForum');
        $storage = new StoredUserData($user_id);

        if ($section && !isset(self::$privacy_classes[$section])) {
            throw new Exception("Invalid privacy section '{$section}'");
        }

        $privacy_classes = $section
                         ? self::$privacy_classes[$section]
                         : array_flatten(array_values(self::$privacy_classes));

        foreach ($privacy_classes as $privacy_class) {
            if (is_a($privacy_class, 'PrivacyObject', true)) {
                $privacy_class::exportUserData($storage);
            }
        }

        if (!$section || $section === 'plugins') {
            foreach (PluginEngine::getPlugins('PrivacyPlugin') as $plugin) {
                $plugin->exportUserData($storage);
            }
        }

        $user_data = [];
        foreach ($storage->getTabularData() as $meta) {
            $user_data[$meta['name']] = [
                'table_name'    => $meta['key'],
                'table_content' => $meta['value'],
            ];
        }

        return $user_data;
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
        if (!in_array($needed_perm, ['root', 'admin']) && !$GLOBALS['perm']->have_perm('admin')) {
            $allowed_person = $GLOBALS['user']->user_id === $user_id;
        }

        return $GLOBALS['perm']->have_perm($needed_perm)
            && $allowed_person;
    }
}
