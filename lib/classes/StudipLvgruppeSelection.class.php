<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * Copyright (C) 2013 - Peter Thienel <thienel@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Objects of this class represent the state of the LV-Gruppe selection form.
 */
class StudipLvgruppeSelection {

    private $selected;
    private $showAll;
    private $areas;
    private $searchKey;
    private $searchResult;

    /**
     * This constructor can be called with or without a course ID. If a course ID
     * has been sent, the selected lvgruppen are populated by that course's already
     * chosen lvgruppen. If no course ID is given, it is assumed that you are
     * creating a new course at the moment.
     *
     * @param  string     optional; the ID of the course to prepopulate the form
     *                    with
     *
     * @return void
     */
    public function __construct($course_id = null)
    {
        $this->selected = self::getRootItem();
        $this->showAll = FALSE;

        $this->areas = array();

        $this->searchKey = '';
        $this->clearSearchResult();

        if (isset($course_id)) {
            $this->populateAreasForCourse($course_id);
        }
    }

    /**
      * Returns the not really existing root of the tree.
      *
      * @return object     the root tree object
      */
     public static function getRootItem()
     {
        $root = new MvvTreeRoot();
        return $root;
     }

    /**
     * This method populates this instance with the already chosen LV-Gruppen.
     *
     * @param  string     the course's ID
     *
     * @return void
     */
    private function populateAreasForCourse($id)
    {
        $lvgruppen = Lvgruppe::findBySeminar($id);
        $this->setLvgruppen($lvgruppen);
        $this->sortAreas();
    }


    /**
     * Sorts the internal representation of the areas by their paths according to
     * the current locale.
     *
     * @return void
     */
    private function sortAreas()
    {
        // MVV: sort by name of LvGruppe
        uasort($this->areas, function ($a, $b) {
            return strcoll($a->getDisplayName(), $b->getDisplayName());
        });
    }


    /**
     * @return string     the current search term
     */
    public function getSearchKey()
    {
        return $this->searchKey;
    }


    /**
     * @param  string     a search term
     *
     * @return object     this instance
     */
    public function setSearchKey($searchKey)
    {
        $this->searchKey = (string) $searchKey;

        $this->clearSearchResult();
        return $this;
    }


    /**
     * @return bool       returns TRUE if the search key was set meaning that
     *                    we are currently searching; returns FALSE otherwise
     */
    public function searched()
    {
        return $this->searchKey !== '';
    }


    /**
     * Clears the current search result.
     *
     * @return object     this instance
     */
    public function clearSearchResult()
    {
        $this->searchResult = null;
        return $this;
    }


    /**
     * Returns an array of search results.
     *
     * @return array      an array of search results
     */
    public function getSearchResult()
    {

        # no search key -> return empty array
        if ($this->searchKey === '') {
            return array();
        }

        if (is_null($this->searchResult)) {
            $lvgruppen = Lvgruppe::findBySearchTerm($this->searchKey);
            foreach ($lvgruppen as $lvgruppe) {
                $this->searchResult[$lvgruppe->id] = $lvgruppe;
            }
            usort($this->searchResult, array(__CLASS__, 'sortSearchResult'));
        }

        return $this->searchResult;
    }

    public static function sortSearchResult($a, $b)
    {
        // sort by display name
        return strcmp($a->getDisplayName(), $b->getDisplayName());
    }


    /**
     * @return object     the currently selected lvgruppe
     */
    public function getSelected()
    {
        return $this->selected;
    }


    /**
     * Sets the selected tree item.
     *
     * @param  mixed $selected Either the id of a tree item to select or the
     * tree item object itself
     * @return object this instance
     */
    public function setSelected($selected, $type = null)
    {
        if (!is_object($selected) && !is_null($type)) {
            $reflection = new ReflectionClass($type);
            if (!$reflection->implementsInterface('MvvTreeItem')) {
                throw new InvalidArgumentException('Wrong type of tree element.');
            }
            if ($type != 'MvvTreeRoot') {
                $this->selected = $type::find(explode('_', $selected));
            }
        } else {
            $this->selected = $selected;
        }
        return $this;
    }


    /**
     * @return bool       returns TRUE if the subtrees should be expanded
     *                    completely or FALSE otherwise
     */
    public function getShowAll()
    {
        return $this->showAll;
    }


    /**
     * @param  bool       the new state of the expansion of subtrees
     *
     * @return object     this instance
     */
    public function setShowAll($showAll)
    {
        $this->showAll = $showAll;
     //   $this->selected = new MvvTreeRoot();
        return $this;
    }


    /**
     * Toggles the state of the expansion of subtrees.
     *
     * @return object     this instance
     */
    public function toggleShowAll()
    {
        $this->showAll = !$this->showAll;
        return $this;
    }


    /**
     * Returns all the IDs of the already selected LV-Gruppen.
     *
     * @return array      an array with IDs of the selected LV-Gruppen
     */
    public function getLvGruppenIDs()
    {
        return array_keys($this->areas);
    }


    /**
     * Returns all the selected LV-Gruppen.
     *
     * @return array      an array of LV-Gruppen representing the selected
     *                    LV-Gruppen
     */
    public function getAreas()
    {
        return $this->areas;
    }


    /**
     * Sets the LV-Gruppen of this selection. One can provide either MD5ish ID
     * strings or instances of Lvgruppe.
     *
     * @param  array      an array of either MD5ish ID strings or Lvgruppe
     *
     * @return object     the called instance itself
     */
    public function setLvgruppen($areas)
    {
        $this->areas = array();
        foreach ($areas as $area) {
            $this->add($area);
        }
        return $this;
    }


    /**
     * Returns true if this LV-Gruppe is selected, false otherwise.
     *
     * @param  mixed      the id of a LV-Gruppe or the LV-Gruppe object itself
     * @return bool       returns true if selected, false otherwise
     */
    public function includes($area)
    {
        $id = is_object($area) ? $area->getId() : $area;
        return isset($this->areas[$id]);
    }


    /**
     * @return integer    returns the number of the selected LV-Gruppen
     */
    public function size()
    {
        return count($this->areas);
    }


    /**
     * This method adds an area to the selected LV-Gruppen.
     *
     * @param  mixed     the ID of the LV-Gruppe to add or a LV-Gruppe object
     *
     * @return object     this instance
     */
    public function add($area)
    {
        # convert to an object
        if (!is_object($area)) {
            $area = Lvgruppe::find($area);
        }
        $id = $area->getId();
        if (!isset($this->areas[$id])) {
            $this->areas[$id] = $area;
        }
        $this->sortAreas();
        return $this;
    }


    /**
     * This method removes given LV-Gruppe from the already selected LV-Gruppen.
     *
     * @param  mixed     the ID of the LV-Gruppe to add or a LV-Gruppe object
     *
     * @return object     this instance
     */
    public function remove($area)
    {
        if (is_object($area)) {
            $area = $area->getId();
        }
        if (isset($this->areas[(string) $area])) {
            unset($this->areas[$area]);
        }
        return $this;
    }


    /**
     * Returns the trail -- the path from the root of the tree of MVV objects down
     * to the currently selected LV-Gruppe.
     *
     * @return array an array of MVV objects
     */
    public function getTrail()
    {
        $area = $this->selected;
        $trail = array(implode('_', (array) $area->getId()) => $area);
        while ($parent = $area->getTrailParent()) {
            $trail[implode('_', (array) $parent->getId())] = $parent;
            $area = $parent;
        }
        $trail['root'] = new MvvTreeRoot();
        return array_reverse($trail, true);
    }
}
