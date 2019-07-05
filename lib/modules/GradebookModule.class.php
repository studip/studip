<?php

use Grading\Definition;
use Grading\Instance;

/**
 * GradebookModule.class.php - Gradebook API for Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class GradebookModule extends StudIPPlugin implements SystemPlugin, StandardPlugin
{
    public function __construct()
    {
        parent::__construct();

        NotificationCenter::on('UserDidDelete', function ($event, $user) {
            Instance::deleteBySQL('user_id = ?', [$user->id]);
        });
        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            Definition::deleteBySQL('course_id = ?', [$course->id]);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getIconNavigation($courseId, $lastVisit, $userId)
    {
        $title = _('Gradebook');
        if ($GLOBALS['perm']->have_studip_perm('dozent', $courseId, $userId)) {
            $changed = Instance::countBySQL(
                'INNER JOIN grading_definitions gd ON(gd.id = definition_id) '.
                'WHERE gd.course_id = ? AND grading_instances.chdate > ?',
                [$courseId, $lastVisit]
            );
        } else {
            $changed = Instance::countBySQL(
                'INNER JOIN grading_definitions gd ON(gd.id = definition_id) '.
                'WHERE gd.course_id = ? AND grading_instances.chdate > ? AND user_id = ?',
                [$courseId, $lastVisit, $userId]
            );
        }

        $icon = Icon::create('assessment', $changed ? Icon::ROLE_NEW : Icon::ROLE_INACTIVE);

        $navigation = new Navigation($title, 'dispatch.php/course/gradebook/overview');
        $navigation->setImage($icon, ['title' => $title]);

        return $navigation;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getTabNavigation($cid)
    {
        if ('nobody' === $GLOBALS['user']->id) {
            return [];
        }

        $gradebook = new Navigation('Gradebook');
        $gradebook->addSubNavigation('index', new Navigation(_('Erbrachte Leistungen'), 'dispatch.php/course/gradebook/overview'));

        if ($GLOBALS['perm']->have_studip_perm('dozent', $cid)) {
            $this->addTabNavigationOfLecturers($gradebook, $cid);
        }

        return compact('gradebook');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function addTabNavigationOfLecturers(\Navigation $navigation, $cid)
    {
        $navigation->addSubNavigation(
            'weights',
            new Navigation(_('Gewichtungen'), 'dispatch.php/course/gradebook/lecturers/weights')
        );
        $navigation->addSubNavigation(
            'edit_custom_definitions',
            new Navigation(_('Manuelle Leistungen definieren'), 'dispatch.php/course/gradebook/lecturers/edit_custom_definitions')
        );
        $navigation->addSubNavigation(
            'custom_definitions',
            new Navigation(_('Noten manuell erfassen'), 'dispatch.php/course/gradebook/lecturers/custom_definitions')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function exportUserData(StoredUserData $storage)
    {
        if ($instances = Grading\Instance::findBySql('user_id = ?', [$storage->user_id])) {
            $fieldData = array_map(
                function ($instance) {
                    return
                        array_merge(
                            $instance->definition->toRawArray('course_id item name tool category weight'),
                            $instance->toRawArray('rawgrade feedback mkdate chdate')
                        );
                },
                $instances
            );
            if ($fieldData) {
                $storage->addTabularData(_('Leistungen'), 'fach', $fieldData);
            }
        }
    }

    /**
     * Provides metadata like a descriptional text for this module that
     * is shown on the course "+" page to inform users about what the
     * module acutally does. Additionally, a URL can be specified.
     *
     * @return array metadata containg description and/or url
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Noten- und Fortschrittserfassung (Gradebook)'),
            'description' => _('Dieses Modul ermöglicht die manuelle und automatische Erfassung von Noten und Leistungen.'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('automatische und manuelle Erfassung von gewichteten Leistungen;Export von Leistungen;persönliche Fortschrittskontrolle'),
            'icon' => Icon::create('assessment', 'info'),
            'screenshots' => [
                'path' => '../../assets/images/plus/screenshots/Gradebook',
                'pictures' => [
                    [
                        'source' => 'Lehrendensicht.png',
                        'title' => 'Beispiel für das Gradebook aus der Sicht der Lehrenden',
                    ],
                    [
                        'source' => 'Studierendensicht.png',
                        'title' => 'Beispiel für das Gradebook aus der Sicht der Studierenden',
                    ],
                ],
            ],
        ];
    }
}
