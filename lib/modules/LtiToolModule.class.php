<?php
/**
 * LtiToolModule.class.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class LtiToolModule extends CorePlugin implements StudipModule, SystemPlugin, PrivacyPlugin
{
    /**
     * Initialize the LtiToolModule.
     */
    public function __construct()
    {
        parent::__construct();

        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::addItem('/admin/config/lti', new Navigation(_('LTI-Tools'), 'dispatch.php/admin/lti'));
        }

        NotificationCenter::on('UserDidDelete', function ($event, $user) {
            LtiGrade::deleteBySQL('user_id = ?', [$user->id]);
        });
        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            LtiData::deleteBySQL('course_id = ?', [$course->id]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $title = CourseConfig::get($course_id)->LTI_TOOL_TITLE;
        $changed = LtiData::countBySQL('course_id = ? AND chdate > ?', [$course_id, $last_visit]);

        $icon = $changed
              ? Icon::create('link-extern+new', Icon::ROLE_NEW)
              : Icon::create('link-extern', Icon::ROLE_INACTIVE);

        $navigation = new Navigation($title, 'dispatch.php/course/lti');
        $navigation->setImage($icon, ['title' => $title]);

        return $navigation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        $title = CourseConfig::get($course_id)->LTI_TOOL_TITLE;
        $grades = LtiData::countBySQL('course_id = ?', [$course_id]);

        $navigation = new Navigation($title);
        $navigation->setImage(Icon::create('link-extern', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('link-extern', Icon::ROLE_INFO));
        $navigation->addSubNavigation('index', new Navigation($title, 'dispatch.php/course/lti'));

        if ($grades) {
            $navigation->addSubNavigation('grades', new Navigation(_('Ergebnisse'), 'dispatch.php/course/lti/grades'));
        }

        if ($GLOBALS['user']->id !== 'nobody') {
            return ['lti' => $navigation];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function exportUserData(StoredUserData $storage)
    {
        $db = DBManager::get();

        $data = $db->fetchAll('SELECT * FROM lti_grade WHERE user_id = ?', [$storage->user_id]);
        $storage->addTabularData(_('LTI-Ergebnisse'), 'lti_grade', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Verlinkung auf Inhalte in externen Anwendungen (LTI-Tool)'),
            'description' => _('Dieses Modul bietet eine Möglichkeit zur Einbindung von externen Tools, '.
                               'sofern diese den LTI-Standard unterstützen. Ähnlich wie bei der Seite '.
                               '"Informationen" kann ein Titel sowie ein freier Text angegeben werden, der '.
                               'den Nutzern zur Erläuterung angezeigt wird. Zur Einbindung von Inhalten aus '.
                               'Fremdsystemen wird die LTI-Schnittstelle in der Version 1.x unterstützt.'),
            'category' => _('Kommunikation und Zusammenarbeit'),
            'keywords' => _('Einbindung von LTI-Tools (Version 1.x)'),
            'icon' => Icon::create('link-extern', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Lti',
                'pictures' => [
                    ['source' => 'Lti_tool_demo.jpg', 'title' => 'Beispiel für Wordpress-Einbindung']
                ]
            ]
        ];
    }
}
