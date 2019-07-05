<?php

use Grading\Definition;

trait GradebookTemplateHelpers
{
    public function formatAsPercent($value)
    {
        return (float) (round($value * 1000) / 10);
    }

    public function formatCategory($category)
    {
        return htmlReady(Definition::CUSTOM_DEFINITIONS_CATEGORY === $category ? _('Manuell eingetragen') : $category);
    }

    public function getNormalizedWeight(Definition $definition)
    {
        return $this->sumOfWeights ? $definition->weight / $this->sumOfWeights : 0;
    }

    protected function getSumOfWeights($gradingDefinitions)
    {
        $sumOfWeights = 0;
        foreach ($gradingDefinitions as $def) {
            $sumOfWeights += $def->weight;
        }

        return $sumOfWeights;
    }

    protected function getGroupedDefinitions($gradingDefinitions)
    {
        $groupedDefinitions = [];
        foreach ($gradingDefinitions as $def) {
            if (!isset($groupedDefinitions[$def->category])) {
                $groupedDefinitions[$def->category] = [];
            }
            $groupedDefinitions[$def->category][] = $def;
        }

        return $groupedDefinitions;
    }

    protected function setupLecturerSidebar()
    {
        $export = new \ExportWidget();
        $export->addLink(
            _('Leistungen als CSV exportieren'),
            $this->url_for('course/gradebook/lecturers/export'),
            Icon::create('export')
        );
        \Sidebar::Get()->addWidget($export);
    }

    protected function setupStudentsSidebar()
    {
        $export = new \ExportWidget();
        $export->addLink(
            _('Leistungen exportieren'),
            $this->url_for('course/gradebook/students/export'),
            Icon::create('export')
        );
        \Sidebar::Get()->addWidget($export);
    }

    protected function viewerIsStudent()
    {
        return $this->viewerHasPerm('autor') && !$this->viewerHasPerm('dozent');
    }

    protected function viewerIsLecturer()
    {
        return $this->viewerHasPerm('dozent');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function viewerHasPerm($perm)
    {
        return $GLOBALS['perm']->have_studip_perm($perm, \Context::getId());
    }

    protected function setDefaultPageTitle()
    {
        \PageLayout::setTitle(Context::getHeaderLine().' - Gradebook');
    }
}
