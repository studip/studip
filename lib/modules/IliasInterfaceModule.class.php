<?php
/**
 * Ilias Interface - navigation and meta data
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2019 Stud.IP Core-Group
 * @license   GPL version 2 or any later version
 * @since     4.3
 */

class IliasInterfaceModule extends StudIPPlugin implements StandardPlugin, SystemPlugin
{
    public function __construct()
    {
        parent::__construct();
        if (Config::get()->ILIAS_INTERFACE_ENABLE) {
            if (Seminar_Perm::get()->have_perm('root')) {
                Navigation::addItem('/admin/config/ilias_interface',
                    new Navigation(_('ILIAS-Schnittstelle'), 'dispatch.php/admin/ilias_interface'));
            }
            if (Seminar_Perm::get()->have_perm('tutor')) {
                Navigation::addItem('/tools/my_ilias_accounts',
                    new Navigation(_('ILIAS'), 'dispatch.php/my_ilias_accounts'));
            }
        }
    }

    public function isActivatableForContext(Range $context)
    {
        return Config::get()->ILIAS_INTERFACE_ENABLE;
    }

    public function getInfoTemplate($course_id)
    {
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        if (!Config::get()->ILIAS_INTERFACE_ENABLE) {
            return;
        }
        $sql = "SELECT a.object_id, COUNT(IF(a.module_type != 'crs', module_id, NULL)) as count_modules, COUNT(IF(a.module_type = 'crs', module_id, NULL)) as count_courses,
                COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), module_id, NULL)) AS neue,
                MAX(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), chdate, 0)) AS last_modified
                FROM
                object_contentmodules a
                LEFT JOIN object_user_visits b ON (b.object_id = a.object_id AND b.user_id = :user_id AND b.type ='ilias_interface')
                WHERE a.object_id = :course_id
                GROUP BY a.object_id";
        
        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            $title = CourseConfig::get($course_id)->getValue('ILIAS_INTERFACE_MODULETITLE');
            $nav = new Navigation($title, 'dispatch.php/course/ilias_interface/index');
            if ((int)$result['neue']) {
                $nav->setImage(
                    Icon::create(
                        'learnmodule+new',
                        'attention',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%1$d Lernobjekt, %2$d neues',
                                    '%1$d Lernobjekte, %2$d neue',
                                    $result['count_modules']
                                ),
                                $result['count_modules'],
                                $result['neue']
                            )
                        ]
                    )
                );
            } elseif ((int)$result['count_modules']) {
                $nav->setImage(
                    Icon::create(
                        'learnmodule',
                        'inactive',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%d Lernobjekt',
                                    '%d Lernobjekte',
                                    $result['count_modules']
                                ),
                                $result['count_modules']
                            )
                        ]
                    )
                );
            } elseif ((int)$result['count_courses']) {
                $nav->setImage(
                    Icon::create(
                        'learnmodule',
                        'inactive',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%d ILIAS-Kurs',
                                    '%d ILIAS-Kurse',
                                    $result['count_courses']
                                ),
                                $result['count_courses']
                            )
                        ]
                    )
                );
            }
            return $nav;
        }
    }

    public function getTabNavigation($course_id)
    {
        if (!Config::get()->ILIAS_INTERFACE_ENABLE) {
            return;
        }
        $ilias_interface_config = Config::get()->ILIAS_INTERFACE_BASIC_SETTINGS;
        if (count($ilias_interface_config)) {
            $moduletitle = Config::get()->getValue('ILIAS_INTERFACE_MODULETITLE');
            if ($ilias_interface_config['edit_moduletitle']) {
                $moduletitle = CourseConfig::get($course_id)->getValue('ILIAS_INTERFACE_MODULETITLE');
            }

            $navigation = new Navigation($moduletitle);
            $navigation->setImage(Icon::create('learnmodule', 'info_alt'));
            $navigation->setActiveImage(Icon::create('learnmodule', 'info'));
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id) || ($GLOBALS['perm']->have_studip_perm('autor', $course_id) && IliasObjectConnections::isCourseConnected($course_id))) {
                if (get_object_type($course_id, ['inst'])) {
                    $navigation->addSubNavigation('view', new Navigation(_('Lernobjekte dieser Einrichtung'), 'dispatch.php/course/ilias_interface/index/' . $course_id));
                } else {
                    $navigation->addSubNavigation('view', new Navigation(_('Lernobjekte dieser Veranstaltung'), 'dispatch.php/course/ilias_interface/index/' . $course_id));
                }
            }

            return ['ilias_interface' => $navigation];
        } else {
            return null;
        }
    }

    /**
     * @see StudipModule::getMetadata()
     */
    public function getMetadata()
    {
        return [
            'summary'          => _('Zugang zu extern erstellten ILIAS-Lernobjekten'),
            'description'      => _('Über diese Schnittstelle ist es möglich, Lernobjekte aus ' .
                'einer ILIAS-Installation (ILIAS-Version >= 5.3.8) in Stud.IP zur Verfügung ' .
                'zu stellen. Lehrende haben die Möglichkeit, in ' .
                'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP bereit zu stellen.'),
            'displayname'      => _('ILIAS-Schnittstelle'),
            'category'         => _('Inhalte und Aufgabenstellungen'),
            'keywords'         => _('Einbindung von ILIAS-Lernobjekten;
                            Zugang zu ILIAS;
                            Aufgaben- und Test-Erstellung'),
            'icon'             => Icon::create('learnmodule', 'info'),
            'descriptionshort' => _('Zugang zu extern erstellten ILIAS-Lernobjekten'),
            'descriptionlong'  => _('Über diese Schnittstelle ist es möglich, Lernobjekte aus ' .
                'einer ILIAS-Installation (> 5.3.8) in Stud.IP zur Verfügung ' .
                'zu stellen. Lehrende haben die Möglichkeit, in ' .
                'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP bereit zu stellen.'),
        ];
    }
}
