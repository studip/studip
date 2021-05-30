<?php

use Courseware\Instance;
use Courseware\StructuralElement;

class CoursewareModule extends CorePlugin implements SystemPlugin, StudipModule, PrivacyPlugin
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        NotificationCenter::on('UserDidDelete', function ($event, $user) {
            Instance::deleteForRange($user);
        });
        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            Instance::deleteForRange($course);
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
     */
    public function getTabNavigation($courseId)
    {
        $navigation = new Navigation(
            _('Courseware'),
            URLHelper::getURL('dispatch.php/course/courseware/?cid='.$courseId)
        );
        $navigation->addSubNavigation(
            'content',
            new Navigation(_('Inhalt'), 'dispatch.php/course/courseware/?cid='.$courseId)
        );
        $navigation->addSubNavigation(
            'dashboard',
            new Navigation(_('Übersicht'), 'dispatch.php/course/courseware/dashboard?cid='.$courseId)
        );

        if ($GLOBALS['perm']->have_studip_perm('dozent', $courseId)) {
            $navigation->addSubNavigation(
                'manager',
                new Navigation(_('Verwaltung'), 'dispatch.php/course/courseware/manager?cid='.$courseId)
            );
        } else {
            $element = StructuralElement::getCoursewareCourse($courseId);
            if ($element !== null) {
                $instance = new Instance($element);
                if ($GLOBALS['perm']->have_studip_perm($instance->getEditingPermissionLevel(), $courseId)) {
                    $navigation->addSubNavigation(
                        'manager',
                        new Navigation(_('Verwaltung'), 'dispatch.php/course/courseware/manager?cid='.$courseId)
                    );
                }
            }
        }

        return ['courseware' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($courseId, $last_visit, $user_id)
    {
        $new = 0;
        $course_elements = Courseware\StructuralElement::findBySQL(
            'range_type = ? AND range_id = ?',
            ['course', $courseId]
        );
        foreach($course_elements as $element) {
            $has_new_blocks = false;
            $containers = $element->containers->getArrayCopy();
            foreach($containers as $container) {
                $blocks = $container->blocks->getArrayCopy();
                foreach($blocks as $block) {
                    if (
                        $block->editor_id !== $user_id
                        &&
                        $block->chdate >= $last_visit
                        &&
                        $block->payload !== ''
                    ) {
                        $has_new_blocks = true;
                    }
                }
            }
            if($has_new_blocks) {
                $new++;
            }
        }
        $nav = new Navigation(_('Courseware'), 'dispatch.php/course/courseware');
        $nav->setImage(Icon::create('courseware', Icon::ROLE_INACTIVE), [
            'title' => _('Courseware'),
        ]);

        if ($new > 0) {
            if ($new === 1) {
                $text =  _('neue Seite');

            } else {
                $text =  _('neue Seiten');
            }
            $nav->setImage(Icon::create('courseware', Icon::ROLE_ATTENTION), [
                'title' => $new . ' ' . $text,
            ]);
            $nav->setBadgeNumber("$new");
        }

        return $nav;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Interaktive, multimediale Lernmodule erstellen und bereitstellen.'),
            'description' => _('Mit der Courseware können Sie interaktive, multimediale Lernmodule erstellen und nutzen.
                Die Module sind in Kapitel, Unterkapitel und Abschnitte unterteilt und können aus Textblöcken, Videosequenzen,
                Aufgaben (benötigt das Vips-Plugin) und Kommunkationselementen bestehen. Fertige Module können exportiert und
                in andere Kurse oder andere Installationen importiert werden.'),
            'displayname' => _('Courseware'),
            'category' => _('Lehr- und Lernorganisation'),
            // 'keywords' => '',
            'descriptionshort' => _('Interaktive, multimediale Lernmodule erstellen und bereitstellen.'),
            'descriptionlong' => _('Mit der Courseware können Sie interaktive, multimediale Lernmodule erstellen und nutzen.
                Die Module sind in Kapitel, Unterkapitel und Abschnitte unterteilt und können aus Textblöcken, Videosequenzen,
                Aufgaben (benötigt das Vips-Plugin) und Kommunkationselementen bestehen. Fertige Module können exportiert und
                in andere Kurse oder andere Installationen importiert werden.'),
            'icon' => Icon::create('courseware', 'info'),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Courseware',
                'pictures' => [
                    0 => ['source' => 'preview.png', 'title' => _('Überssichtsseite der Courseware')],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function exportUserData(StoredUserData $storage)
    {
        $db = DBManager::get();

        $structuralElements = $db->fetchAll(
            'SELECT * FROM cw_structural_elements WHERE owner_id = ? OR editor_id = ? OR range_id = ?',
            [$storage->user_id, $storage->user_id, $storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Strukturelemente-Ergebnisse'), 'cw_structural_elements', $structuralElements);

        $containers = $db->fetchAll(
            'SELECT * FROM cw_containers WHERE owner_id = ? OR editor_id = ?',
            [$storage->user_id, $storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Container-Ergebnisse'), 'cw_containers', $containers);

        $blocks = $db->fetchAll(
            'SELECT * FROM cw_blocks WHERE owner_id = ? OR editor_id = ?',
            [$storage->user_id, $storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Blöcke-Ergebnisse'), 'cw_blocks', $blocks);

        $comments = $db->fetchAll(
            'SELECT * FROM cw_block_comments WHERE user_id = ?',
            [$storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Kommentare-Ergebnisse'), 'cw_block_comments', $comments);

        $userData = $db->fetchAll(
            'SELECT * FROM cw_user_data_fields WHERE user_id = ?',
            [$storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Nutzer-Daten-Ergebnisse'), 'cw_user_data_fields', $userData);

        $userProgresses = $db->fetchAll(
            'SELECT * FROM cw_user_progresses WHERE user_id = ?',
            [$storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Nutzer-Fortschritt-Ergebnisse'), 'cw_user_progresses', $userProgresses);

        $bookmarks = $db->fetchAll(
            'SELECT * FROM cw_bookmarks WHERE user_id = ?',
            [$storage->user_id]
        );
        $storage->addTabularData(_('Courseware-Lesezeichen-Ergebnisse'), 'cw_bookmarks', $bookmarks);


    }

    public function isActivatableForContext(Range $context)
    {
        return $context->getRangeType() === 'course';
    }
}
