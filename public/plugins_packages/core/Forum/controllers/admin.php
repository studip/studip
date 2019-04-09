<?php

/*
 * Copyright (C) 2011 - Till Glöggler     <tgloeggl@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
class AdminController extends ForumController
{
    /* * * * * * * * * * * * * * * * * * * * */
    /* * *   A D M I N   M E T H O D S   * * */
    /* * * * * * * * * * * * * * * * * * * * */

    /**
     * show the administration page for mass-editing forum-entries
     */
    function index_action()
    {
        ForumPerm::check('admin', $this->getId());
        $nav = Navigation::getItem('course/forum2');
        $nav->setImage(Icon::create('forum', 'info'));
        Navigation::activateItem('course/forum2/admin');

        $list = ForumEntry::getList('flat', $this->getId());

        // sort by cat
        $new_list = [];
        // iterate over all categories and add the belonging areas to them
        foreach ($categories = ForumCat::getListWithAreas($this->getId(), false) as $category) {
            if ($category['topic_id']) {
                $new_list[$category['category_id']][$category['topic_id']] = $list['list'][$category['topic_id']];
                unset($list['list'][$category['topic_id']]);
            } else if (ForumPerm::has('add_area', $this->seminar_id)) {
                $new_list[$category['category_id']] = [];
            }
            $this->categories[$category['category_id']] = $category['entry_name'];
        }

        if (!empty($list['list'])) {
            // append the remaining entries to the standard category
            $new_list[$this->getId()] = array_merge((array)$new_list[$this->getId()], $list['list']);
        }

        $this->list = $new_list;

    }
    
    /**
     * show child entries for the passed entry
     * 
     * @param string $parent_id id of entry to get the childs for
     */
    function childs_action($parent_id)
    {
        $this->set_layout(null);

        // if the parent-id is a category-id, get all areas for this category
        if ($cat = ForumCat::get($parent_id)) {
            ForumPerm::check('admin', $cat['seminar_id']);  // check the perms in the categories seminar
            $this->entries = ForumEntry::parseEntries(ForumCat::getAreas($parent_id));
        } else {
            ForumPerm::check('admin', $this->getId(), $parent_id);
            $entries = ForumEntry::getList('flat', $parent_id);
            $this->entries = $entries['list'];
        }
    }

    /**
     * move the submitted topics[] to the passed destination
     * 
     * @param string $destination id of seminar to move topics to
     */
    function move_action($destination)
    {
        // check if destination is a category_id. if yes, use seminar_id instead
        if (ForumCat::get($destination)) {
            $category_id = $destination;
            $destination = $this->getId();
        }
        
        ForumPerm::check('admin', $this->getId(), $destination);

        foreach (Request::getArray('topics') as $topic_id) {
            // make sure every passed topic_id is checked against the current seminar
            ForumPerm::check('admin', $this->getId(), $topic_id);

            // if the source is an area and the target a category, just move this area to the category
            $entry = ForumEntry::getEntry($topic_id);
            if ($entry['depth'] == 1 && $category_id) {
                ForumCat::removeArea($topic_id);
                ForumCat::addArea($category_id, $topic_id);
            } else {
                // first step: move the whole topic with all childs
                ForumEntry::move($topic_id, $destination);

                // if the current topic id is an area, remove it from any categories
                ForumCat::removeArea($topic_id);

                // second step: move all to deep childs a level up (depth > 3)
                $data = ForumEntry::getList('depth_to_large', $topic_id);
                foreach ($data['list'] as $entry) {
                    $path = ForumEntry::getPathToPosting($entry['topic_id']);
                    array_shift($path); // Category
                    array_shift($path); // Area

                    $thread = array_shift($path); // Thread

                    ForumEntry::move($entry['topic_id'], $thread['id']);
                }
                
                // add entry to passed category when moving to the top
                if ($category_id) {
                    ForumCat::addArea($category_id, $topic_id);
                }                    
            }
        }

        $this->render_nothing();
    }
}
