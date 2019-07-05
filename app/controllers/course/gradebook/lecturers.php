<?php

require_once __DIR__.'/template_helpers.php';

use Grading\Definition;
use Grading\Instance;

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Course_Gradebook_LecturersController extends AuthenticatedController
{
    use GradebookTemplateHelpers;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$this->viewerIsLecturer()) {
            throw new AccessDeniedException();
        }
        $this->setDefaultPageTitle();
        $this->setupLecturerSidebar();
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function index_action()
    {
        if (Navigation::hasItem('/course/gradebook/index')) {
            Navigation::activateItem('/course/gradebook/index');
        }

        $course = \Context::get();
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->students = $course->getMembersWithStatus('autor', true)->pluck('user');
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->groupedInstances = $this->groupedInstances($course);
        $this->sumOfWeights = $this->getSumOfWeights($gradingDefinitions);
        $this->totalSums = $this->sumOfWeights ? $this->getTotalSums($gradingDefinitions) : 0;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function export_action()
    {
        $filename = preg_replace(
            '/[^a-zA-Z0-9-_.]+/',
            '-',
            sprintf(
                'gradebook-%s.csv',
                \Context::getHeaderLine()
            )
        );

        $course = \Context::get();
        $this->students = $course->getMembersWithStatus('autor', true)->pluck('user');

        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->groupedInstances = $this->groupedInstances($course);

        $headerLine = ['Name'];
        foreach ($this->categories as $category) {
            $categoryName = Definition::CUSTOM_DEFINITIONS_CATEGORY === $category ? _('Manuell eingetragen') : $category;
            foreach ($this->groupedDefinitions[$category] as $definition) {
                $headerLine[] = $categoryName.': '.$definition->name;
            }
        }
        $studentLines = [];
        foreach ($this->students as $user) {
            $studentLine = [$user->getFullName('no_title_rev')];
            foreach ($this->categories as $category) {
                foreach ($this->groupedDefinitions[$category] as $definition) {
                    $studentLine[] = isset($this->groupedInstances[$user->id][$definition->id])
                                   ? $this->groupedInstances[$user->id][$definition->id]->rawgrade
                                   : 0;
                }
            }
            $studentLines[] = $studentLine;
        }

        $data = array_merge([$headerLine], $studentLines);
        $this->render_csv($data, $filename);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function weights_action()
    {
        if (Navigation::hasItem('/course/gradebook/weights')) {
            Navigation::activateItem('/course/gradebook/weights');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->sumOfWeights = $this->getSumOfWeights($gradingDefinitions);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_weights_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $weights = \Request::intArray('definitions');
        $gradingDefinitions = \SimpleCollection::createFromArray(
            Definition::findByCourse(\Context::get())
        );

        foreach ($gradingDefinitions as $def) {
            if (!isset($weights[$def->id])) {
                continue;
            }
            $newWeight = (int) $weights[$def->id];
            if ($newWeight < 0) {
                continue;
            }
            $def->weight = $newWeight;
        }

        $changedDefinitions = array_filter($gradingDefinitions->store());
        if (count($changedDefinitions)) {
            \PageLayout::postSuccess(_('Gewichtungen erfolgreich verändert.'));
        }
        $this->redirect('course/gradebook/lecturers/weights');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function custom_definitions_action()
    {
        if (Navigation::hasItem('/course/gradebook/custom_definitions')) {
            Navigation::activateItem('/course/gradebook/custom_definitions');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->customDefinitions = isset($this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY])
                                 ? $this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY]
                                 : [];

        $this->students = $course->getMembersWithStatus('autor', true)->pluck('user');
        $this->groupedInstances = $this->groupedInstances($course);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_grades_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $course = \Context::get();
        $studentIds = $course->getMembersWithStatus('autor', true)->pluck('user_id');
        $definitionIds = \SimpleCollection::createFromArray(
            Definition::findByCourse($course)
        )->pluck('id');

        $grades = \Request::getArray('grades');
        foreach ($grades as $studentId => $studentGrades) {
            if (!in_array($studentId, $studentIds)) {
                continue;
            }
            foreach ($studentGrades as $definitionId => $strGrade) {
                if (!in_array($definitionId, $definitionIds)) {
                    continue;
                }

                $instance = new Instance([$definitionId, $studentId]);
                $instance->rawgrade = ((int) $strGrade) / 100.0;
                $instance->store();
            }
        }

        \PageLayout::postSuccess(_('Die Noten wurden gespeichert.'));
        $this->redirect('course/gradebook/lecturers/custom_definitions');
    }

    public function edit_custom_definitions_action()
    {
        if (Navigation::hasItem('/course/gradebook/custom_definitions')) {
            Navigation::activateItem('/course/gradebook/edit_custom_definitions');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->customDefinitions = isset($this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY])
                                 ? $this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY]
                                 : [];
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function new_custom_definition_action()
    {
        // show template
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function create_custom_definition_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $name = trim(\Request::get('name', ''));
        if (!mb_strlen($name)) {
            \PageLayout::postError(_('Der Name einer Leistung darf nicht leer sein.'));
        } else {
            $definition = Definition::create(
                [
                    'course_id' => \Context::getId(),
                    'item' => 'manual',
                    'name' => $name,
                    'tool' => 'manual',
                    'category' => Definition::CUSTOM_DEFINITIONS_CATEGORY,
                    'position' => 0,
                    'weight' => 1.0,
                ]
            );

            if (!$definition) {
                \PageLayout::postError(_('Die Leistung konnte nicht definiert werden.'));
            } else {
                \PageLayout::postSuccess(_('Die Leistung wurde erfolgreich definiert.'));
            }
        }
        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function edit_custom_definition_action($definitionId)
    {
        if (!$this->definition = Definition::findOneBySQL('id = ? AND course_id = ?', [$definitionId, \Context::getId()])) {
            throw new \Trails_Exception(404);
        }

        // show template
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function update_custom_definition_action($definitionId)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$definition = Definition::findOneBySQL('id = ? AND course_id = ?', [$definitionId, \Context::getId()])) {
            throw new \Trails_Exception(404);
        }

        $name = trim(\Request::get('name', ''));
        if (!mb_strlen($name)) {
            \PageLayout::postError(_('Der Name einer Leistung darf nicht leer sein.'));
        } else {
            $definition->name = $name;
            if (!$definition->store()) {
                \PageLayout::postError(_('Die Leistung konnte nicht geändert werden.'));
            }
        }

        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function delete_custom_definition_action($definitionId)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$definition = Definition::findOneBySQL(
                'id = ? AND course_id = ?',
                [$definitionId, \Context::getId()]
            )
        ) {
            \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
        } else {
            if (Definition::deleteBySQL('id = ?', [$definition->id])) {
                \PageLayout::postSuccess(_('Die Leistung wurde gelöscht.'));
            } else {
                \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
            }
        }

        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    public function getInstanceForUser(Definition $definition, \User $user)
    {
        if (!isset($this->groupedInstances[$user->id])) {
            return null;
        }
        if (!isset($this->groupedInstances[$user->id][$definition->id])) {
            return null;
        }

        return $this->groupedInstances[$user->id][$definition->id];
    }

    private function groupedInstances($course)
    {
        $gradingInstances = Instance::findByCourse($course);
        $groupedInstances = [];
        foreach ($gradingInstances as $instance) {
            if (!isset($groupedInstances[$instance->user_id])) {
                $groupedInstances[$instance->user_id] = [];
            }
            $groupedInstances[$instance->user_id][$instance->definition_id] = $instance;
        }

        return $groupedInstances;
    }

    private function getTotalSums($gradingDefinitions)
    {
        $gradingDefinitions = \SimpleCollection::createFromArray($gradingDefinitions);
        $totalSums = [];
        foreach ($this->students as $student) {
            if (!isset($totalSums[$student->id])) {
                $totalSums[$student->id] = 0;
            }

            if (!isset($this->groupedInstances[$student->id])) {
                continue;
            }

            foreach ($this->groupedInstances[$student->id] as $definitionId => $instance) {
                if ($definition = $gradingDefinitions->findOneBy('id', $definitionId)) {
                    $totalSums[$student->id] += $instance->rawgrade * ($definition->weight / $this->sumOfWeights);
                }
            }
        }

        return $totalSums;
    }
}
