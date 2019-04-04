<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Objects of this class represent the state of the study area selection form.
 *
 * Note: Many of the methods return "$this" to let you easily cascade method
 * calls: $selection->toggleShowAll()->setSelected("012345etc.");
 *
 * @package   studip
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class StudipStudyAreaSelection {

    private $selected;
    private $showAll;

    private $areas;

    private $searchKey;
    private $searchResult;

    /**
     * This constructor can be called with or without a course ID. If a course ID
     * has been sent, the selected areas are populated by that course's already
     * chosen study areas. If no course ID is given, it is assumed that you are
     * creating a new course at the moment.
     *
     * @param  string     optional; the ID of the course to prepopulate the form
     *                    with
     *
     * @return void
     */
    function __construct($course_id = NULL) {
        $this->selected = StudipStudyArea::getRootArea();
        $this->showAll = FALSE;

        $this->areas = array();

        $this->searchKey = '';
        $this->clearSearchResult();

        if (isset($course_id)) {
            $this->populateAreasForCourse($course_id);
        }
    }


    /**
     * This method populates this instance with the already chosen study areas.
     *
     * @param  string     the course's ID
     *
     * @return void
     */
    private function populateAreasForCourse($id) {
        $areas = StudipStudyArea::getStudyAreasForCourse($id);
        $this->setAreas($areas);
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
        uasort($this->areas, function ($a, $b) {
            return strcoll($a->getPath(' · '), $b->getPath(' · '));
        });
    }


    /**
     * @return string     the current search term
     */
    function getSearchKey() {
        return $this->searchKey;
    }


    /**
     * @param  string     a search term
     *
     * @return object     this instance
     */
    function setSearchKey($searchKey) {
        $this->searchKey = (string) $searchKey;

        $this->clearSearchResult();
        return $this;
    }


    /**
     * @return bool       returns TRUE if the search key was set meaning that
     *                    we are currently searching; returns FALSE otherwise
     */
    function searched() {
        return $this->searchKey !== '';
    }


    /**
     * Clears the current search result.
     *
     * @return object     this instance
     */
    function clearSearchResult() {
        $this->searchResult = NULL;
        return $this;
    }


    /**
     * Returns an array of search results. This result is memoized for
     * performance though setting the search key anew clears the memo again.
     *
     * @return array      an array of search results
     */
    function getSearchResult() {

        # no search key -> return empty array
        if ($this->searchKey === '') {
            return array();
        }

        # not memoized yet, do so now
        if (is_null($this->searchResult)) {
            $this->searchResult = StudipStudyArea::search($this->searchKey);
            usort($this->searchResult, array(__CLASS__, 'sortSearchResult'));
        }

        return $this->searchResult;
    }

    static function sortSearchResult($a, $b) {
        return strcmp($a->getPath('·'), $b->getPath('·'));
    }


    /**
     * @return object     the currently selected study area
     */
    function getSelected() {
        return $this->selected;
    }


    /**
     * @param  mixed      either an MD5ish ID of the study area to select or the
     *                    area object itself
     *
     * @return object     this instance
     */
    function setSelected($selected) {
      if (!is_object($selected)) {
        $this->selected = StudipStudyArea::find($selected);
      }
      else {
            $this->selected = $selected;
        }
        return $this;
    }


    /**
     * @return bool       returns TRUE if the subtrees should be expanded
     *                    completely or FALSE otherwise
     */
    function getShowAll() {
        return $this->showAll;
    }


    /**
     * @param  bool       the new state of the expansion of subtrees
     *
     * @return object     this instance
     */
    function setShowAll($showAll) {
        $this->showAll = $showAll;
        return $this;
    }


    /**
     * Toggles the state of the expansion of subtrees.
     *
     * @return object     this instance
     */
    function toggleShowAll() {
        $this->showAll = !$this->showAll;
        return $this;
    }


    /**
     * Returns all the IDs of the selected study areas.
     *
     * @return array      an array of MD5ish strings representing the IDs of the
     *                    selected study areas
     */
    function getAreaIDs() {
        return array_keys($this->areas);
    }


    /**
     * Returns all the selected study areas.
     *
     * @return array      an array of StudipStudyArea representing the selected
     *                    study areas
     */
    function getAreas() {
        return $this->areas;
    }


    /**
     * Sets the study areas of this selection. One can provide either MD5ish ID
     * strings or instances of StudipStudyArea.
     *
     * @param  array      an array of either MD5ish ID strings or StudipStudyAreas
     *
     * @return object     the called instance itself
     */
    function setAreas($areas) {
        $this->areas = array();
        foreach ($areas as $area) {
            $this->add($area);
        }
        return $this;
    }


    /**
     * @param  mixed      the MD5ish ID of a study area or the area object itself
     *
     * @return bool       returns TRUE if this area is selected, FALSE otherwise
     */
    function includes($area) {
        $id = is_object($area) ? $area->getID() : $area;
        return isset($this->areas[$id]);
    }


    /**
     * @return integer    returns the number of the selected study areas
     */
    function size() {
        return sizeof($this->areas);
    }


    /**
     * This method adds an area to the selected study areas.
     *
     * @param  string     the MD5ish ID of the study area to add
     *
     * @return object     this instance
     */
    function add($area) {
        # convert to an object
        if (!is_object($area)) {
            $area = StudipStudyArea::find($area);
        }
        $id = $area->getID();
        if (!isset($this->areas[$id])) {
            $this->areas[$id] = $area;
        }
        $this->sortAreas();
        return $this;
    }


    /**
     * This method removes an area from the selected study areas.
     *
     * @param  string     the MD5ish ID of the study area to add
     *
     * @return object     this instance
     */
    function remove($area) {
        if (is_object($area)) {
            $area = $area->getID();
        }
        if (isset($this->areas[(string) $area])) {
            unset($this->areas[$area]);
        }
        return $this;
    }


    /**
     * Returns the trail -- the path from the root of the tree of study areas down
     * to the currently selected area.
     *
     * # TODO (mlunzena) this has to be refactored as well
     *
     * @return array      an array of study areas; currently each item is an
     *                    hashmap containing the ID of each area using the key
     *                    'id' and the name of the study area using the key 'name'
     */
    function getTrail() {
        $area = $this->selected;
        $trail = array($area->getID() => $area);
        while ($parent = $area->getParent()) {
            $trail[$parent->getID()] = $parent;
            $area = $parent;
        }
        $trail[StudipStudyArea::ROOT] = StudipStudyArea::getRootArea();
        return array_reverse($trail, TRUE);
    }
}
