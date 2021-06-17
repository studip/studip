<?php

use Courseware\StructuralElement;
use Courseware\Instance;

/**
 * @property ?string $entry_element_id
 * @property int $last_visitdate
 * @property mixed $courseware_progress_data
 * @property mixed $courseware_chapter_counter
 */
class Course_CoursewareController extends AuthenticatedController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Courseware'));
        PageLayout::setHelpKeyword('Basis.Courseware');

        checkObject();
        if (!Context::isCourse()) {
            throw new CheckObjectException(_('Es wurde keine passende Veranstaltung gefunden.'));
        }
        $this->studip_module = checkObjectModule('CoursewareModule', true);
        object_set_visit_module($this->studip_module->getPluginId());
        $this->last_visitdate = object_get_visit(Context::getId(), $this->studip_module->getPluginId());
    }

    public function index_action()
    {
        /** @var array<mixed> $last */
        $last = UserConfig::get($GLOBALS['user']->id)->getValue('COURSEWARE_LAST_ELEMENT');
        if (isset($last[Context::getId()])) {
            $this->entry_element_id = $last[Context::getId()];
            /** @var ?StructuralElement $struct */
            $struct = StructuralElement::findOneBySQL("id = ? AND range_id = ? AND range_type = 'course'", [
                $this->entry_element_id,
                Context::getId(),
            ]);
        }

        // load courseware for seminar
        if (!$this->entry_element_id || !$struct || !$struct->canRead($GLOBALS['user'])) {
            $course = Course::find(Context::getId());

            if (!$course->courseware) {
                // create initial courseware dataset
                StructuralElement::createEmptyCourseware(Context::getId(), 'course');
            }

            $this->entry_element_id = $course->courseware->id;
        }

        $last[Context::getId()] = $this->entry_element_id;
        UserConfig::get($GLOBALS['user']->id)->store('COURSEWARE_LAST_ELEMENT', $last);

        Navigation::activateItem('course/courseware/content');
        $this->setIndexSidebar();
        $this->licenses = array();
        $sorm_licenses = License::findBySQL("1 ORDER BY name ASC");
        foreach($sorm_licenses as $license) {
            array_push($this->licenses, $license->toArray());
        }
        $this->licenses = json_encode($this->licenses);
    }

    public function dashboard_action(): void
    {
        global $perm, $user;
        $course_progress = $perm->have_studip_perm('dozent', Context::getId(), $user->id);
        $this->courseware_progress_data = $this->getProgressData($course_progress);
        $this->courseware_chapter_counter = $this->getChapterCounter($this->courseware_progress_data);
        Navigation::activateItem('course/courseware/dashboard');
    }

    public function manager_action(): void
    {
        $courseId = Context::getId();
        $element = StructuralElement::getCoursewareCourse($courseId);
        $instance = new Instance($element);
        if (!$GLOBALS['perm']->have_studip_perm($instance->getEditingPermissionLevel(), $courseId)) {
            $this->redirect('course/courseware/index');
        } else {
            Navigation::activateItem('course/courseware/manager');
        }

    }

    private function setIndexSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $actions = new TemplateWidget(
            _('Aktionen'),
            $this->get_template_factory()->open('course/courseware/action_widget')
        );
        $sidebar->addWidget($actions)->addLayoutCSSClass('courseware-action-widget');

        $views = new TemplateWidget(
            _('Ansichten'),
            $this->get_template_factory()->open('course/courseware/view_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-view-widget');
    }

    private function getProgressData(bool $course_progress = false): array
    {
        $data = [];

        $cid = Context::getId();

        $elements = StructuralElement::findBySQL('range_id = ?', [$cid]);

        foreach ($elements as $element) {
            $el = [
                'id' => $element->id,
                'name' => $element->title,
                'parent_id' => $element->parent->id,
                'parent_name' => $element->parent->title,
                'children' => $this->getChildren($element->children),
            ];
            $el['progress'] = $this->getProgress($element, $course_progress);

            array_push($data, $el);
        }

        //update children progress
        foreach ($data as &$element) {
            if (count($element['children'])) {
                foreach ($element['children'] as &$child) {
                    foreach ($data as $el) {
                        if ($el['id'] == $child['id']) {
                            $child['progress'] = $el['progress'];
                        }
                    }
                }
            }
        }

        return $data;
    }

    private function getChildren($children): array
    {
        $data = [];
        foreach ($children as $child) {
            $el = [
                'id' => $child->id,
                'name' => $child->title,
            ];
            array_push($data, $el);
        }

        return $data;
    }

    private function getProgress(StructuralElement $element, bool $course_progress = false): array
    {
        $descendants = $element->findDescendants();
        $count = count($descendants);
        $progress = 0;
        $own_progress = 0;
        $course = Seminar::GetInstance(Context::getId());

        foreach ($descendants as $el) {
            $block = $this->getBlocks($el->id, $course_progress, $course);
            if ($block['counter'] > 0) {
                $progress += $block['progress'] / $block['counter'];
            } else {
                $progress += 1;
            }
        }

        $own_blocks = $this->getBlocks($element->id, $course_progress, $course);

        if ($own_blocks['counter'] > 0) {
            $own_progress = $own_blocks['progress'] / $own_blocks['counter'];
        } else {
            $own_progress = 1;
        }

        $count = count($descendants);
        if ($count > 0) {
            $progress = ($progress + $own_progress) / ($count + 1);
        } else {
            $progress = $own_progress;
        }

        return ['total' => round($progress, 2) * 100, 'current' => round($own_progress, 2) * 100];
    }

    private function getBlocks(string $element_id, bool $course_progress = false, $course): array
    {
        $containers = Courseware\Container::findBySQL('structural_element_id = ?', [intval($element_id)]);
        $blocks = [];
        $blocks['counter'] = 0;
        $blocks['progress'] = 0;
        $users_counter = count($course->getMembersWithStatus('autor'));

        foreach ($containers as $container) {
            $counter = $container->countBlocks();
            $blocks['counter'] += $counter;
            if ($counter > 0) {
                $blks = Courseware\Block::findBySQL('container_id = ?', [$container->id]);
                foreach ($blks as $item) {
                    if ($course_progress) {
                        if ($users_counter > 0) {
                            $progresses = Courseware\UserProgress::findBySQL('block_id = ?', [$item->id]);
                            $users_progress = 0;
                            foreach ($progresses as $prog) {
                                if (array_key_exists($prog->user_id, $course->getMembersWithStatus('autor'))) {
                                    $users_progress += $prog->grade;
                                }
                            }

                            $blocks['progress'] += $users_progress / $users_counter;
                        }
                    } else {
                        $progress = Courseware\UserProgress::findOneBySQL('user_id = ? and block_id = ?', [
                            $GLOBALS['user']->id,
                            $item->id,
                        ]);
                        $blocks['progress'] += $progress->grade;
                    }
                }
            }
        }

        return $blocks;
    }

    private function getChapterCounter(array $chapters): array
    {
        $finished = 0;
        $started = 0;
        $ahead = 0;

        foreach ($chapters as $chapter) {
            if ($chapter['parent_id'] != null) {
                if ($chapter['progress']['current'] == 0) {
                    $ahead += 1;
                }
                if ($chapter['progress']['current'] > 0 && $chapter['progress']['current'] < 100) {
                    $started += 1;
                }
                if ($chapter['progress']['current'] == 100) {
                    $finished += 1;
                }
            }
        }

        return [
            'started' => $started,
            'finished' => $finished,
            'ahead' => $ahead,
        ];
    }
}
