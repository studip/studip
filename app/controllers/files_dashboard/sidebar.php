<?php

namespace FilesDashboard;

use Institute;
use ViewsWidget;
use LinkElement;
use FilesSearch\Filter;

trait Sidebar
{
    /**
     * Adds the Sidebar for the dashboard.
     */
    private function addIndexSidebar()
    {
        $sidebar = \Sidebar::get();

        $actions = $sidebar->hasWidget('actions')
                 ? $sidebar->getWidget('actions')
                 : new \ActionsWidget();

        $actions->addLink(
            _('Datei hinzufügen'),
            $this->url_for('file/choose_destination/upload'),
            \Icon::create('file+add', 'clickable'),
            ['data-dialog' => 'size=auto']
        );

        if (!$sidebar->hasWidget('actions')) {
            $sidebar->addWidget($actions);
        }
    }

    /**
     * Adds the Sidebar containing the categories and their optional filters.
     */
    private function addSearchSidebar()
    {
        $sidebar = \Sidebar::get();

        $categoryWidget = $this->getCategoryWidget();
        $sidebar->addWidget($categoryWidget);

        $sidebar->addWidget($this->getSemesterFilterWidget($this->query));
    }

    /**
     * Build a NavigationWidget for the sidebar to filter out a specific category from your search results.
     * There can only be one category selected at a time.
     *
     * @return NavigationWidget containing all categories included in the search result
     */
    private function getCategoryWidget()
    {
        $filter = $this->query->getFilter();
        $query = $this->query->getQuery();

        $categoryWidget = new ViewsWidget();
        $categoryWidget->setTitle(_('Ansicht'));

        $addElement = function ($label, $url, $type = null) use ($categoryWidget, $filter) {
            $element = new LinkElement(
                $label,
                $url
            );
            $element->setActive(isset($type)
                                ? $filter->getCategory() === $type
                                : empty($filter->getCategory()));

            $categoryWidget->addElement($element);
        };

        $addElement(
            _('Alle Ergebnisse'),
            \URLHelper::getURL(
                'dispatch.php/files_dashboard/search',
                [
                    'q' => $query,
                    'filter' => array_merge($filter->toArray(), ['category' => '']),
                ]
            )
        );

        // list all possible categories as Links
        foreach (Filter::getCategories() as $type => $label) {
            $addElement(
                $label,
                \URLHelper::getURL(
                    'dispatch.php/files_dashboard/search',
                    [
                        'q' => $query,
                        'filter' => array_merge($filter->toArray(), ['category' => $type]),
                    ]
                ),
                $type
            );
        }

        return $categoryWidget;
    }

    private function getSemesterFilterWidget(\FilesSearch\Query $query)
    {
        $sem = '';
        if (($filter = $query->getFilter()) && ($semester = $filter->getSemester())) {
            $sem = $semester->id;
        }

        $semesters = new \SimpleCollection(\Semester::getAll());
        $semesters = $semesters->orderBy('beginn desc');

        $omitSemester = function ($array) {
            unset($array['semester']);
            return $array;
        };

        $widget = new \SelectWidget(
            _('Semesterfilter'),
            $this->url_for(
                'files_dashboard/search',
                [
                    'q' => $query->getQuery(),
                    'filter' => $filter ? $omitSemester($filter->toArray()) : ''
                ]
            ),
            'filter[semester]'
        );

        $widget->addElement(new \SelectElement('', _('Alle Semester'), $sem == ''));

        if (!empty($semesters)) {
            $group = new \SelectGroupElement(_('Semester auswählen'));
            foreach ($semesters as $semester) {
                $group->addElement(new \SelectElement($semester->id, $semester->name, $sem == $semester->id));
            }
            $widget->addElement($group);
        }

        return $widget;
    }
}
