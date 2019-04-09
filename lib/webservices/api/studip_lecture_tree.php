<?php
/*
 * studip_lecture_tree.php - base class for lecture tree
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class StudipLectureTreeHelper
{
    function get_seminars_by_sem_tree_id($sem_tree_id, $term_id)
    {
        $db = DBManager::get();

        $semester = SemesterData::getSemesterData($term_id);

        $stmt = $db->prepare('SELECT s.Seminar_id AS seminar_id, s.Name AS name
                              FROM seminar_sem_tree st
                              JOIN seminare s ON (st.seminar_id = s.Seminar_id)
                              WHERE st.sem_tree_id = ?  AND s.start_time <= ?
                              AND (s.start_time + s.duration_time >= ? OR duration_time = -1)');
        $stmt->execute([$sem_tree_id, $semester['beginn'], $semester['beginn']]);

        return $stmt->fetchAll();
    }

    function get_sem_path($sem_tree_id)
    {
        $stack = (array) $sem_tree_id;
        $info = StudipLectureTreeHelper::get_info_for_sem_tree_id($sem_tree_id);

        $name_parts = [];

        while(($current = array_pop($stack))) {
            $info = StudipLectureTreeHelper::get_info_for_sem_tree_id($current);
            array_push($stack, $info['parent_id']);
            $name_parts = array_merge((array) $info['name'], $name_parts);
            $last = $current;
        }

        return implode (" > ", $name_parts);
    }

    function get_info_for_sem_tree_id($sem_tree_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare("SELECT st.sem_tree_id AS id, st.parent_id,
                              IF (st.name IS NULL OR st.name = '', i.Name, st.name) AS name
                              FROM sem_tree st
                              LEFT JOIN Institute i ON (st.studip_object_id = i.Institut_id)
                              WHERE st.sem_tree_id = ?
                              GROUP BY st.sem_tree_id");
        $stmt->execute([$sem_tree_id]);

        return $stmt->fetchAll();
    }

    function get_subtree($sem_tree_id)
    {
        $stack = $collected = [$sem_tree_id];

        while ($current = array_pop($stack)) {
            $local_tree = StudipLectureTreeHelper::get_local_tree($current);
            $collected = array_merge($collected, $local_tree);
            $stack = array_merge($local_tree, $stack); // depth first
        }

        return $collected;
    }

    function get_subtree_seminar_count($sem_tree_id, $only_visible = true)
    {
        $db = DBManager::get();

        $subtree_entries = StudipLectureTreeHelper::get_subtree($sem_tree_id);
        $subtree_entries = array_map([$db, 'quote'], $subtree_entries);

        $stmt = $db->prepare('SELECT COUNT(sst.seminar_id) AS seminar_count
                              FROM seminar_sem_tree sst
                              JOIN seminare s ON sst.seminar_id = s.Seminar_id
                              WHERE sst.sem_tree_id IN (' . join(',', $subtree_entries) . ')' .
                              ($only_visible ? ' AND s.visible = 1' : ''));
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    function get_local_tree($sem_tree_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT sem_tree_id FROM sem_tree WHERE parent_id = ? ORDER BY priority');
        $stmt->execute([$sem_tree_id]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
