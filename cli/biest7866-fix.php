#!/usr/bin/env php
<?php
/**
 * This script sets folder range_ids to the range_ids of their parent folder.
 *
 * @author Thomas Hackl <thomas.hackl@uni-passau.de>
 * @see    https://develop.studip.de/trac/ticket/7866
 */

require_once __DIR__ . '/studip_cli_env.inc.php';

/**
 * Sets the range_id of all child folders to the given range_id.
 * @param $parent_folder
 * @param $range_id
 */
function setFolderRangeId($parent_folder, $range_id) {
    // Update all child folder range_ids.
    DBManager::get()->execute(
        "UPDATE `folders` SET `range_id` = :range WHERE `parent_id` = :parent",
        [
            'range' => $range_id,
            'parent' => $parent_folder
        ]
    );

    // Recursion: set correct range_id for child folders with wrong range_id.
    $children = DBManager::get()->fetchAll(
        "SELECT `id`, `range_id` FROM `folders` WHERE `parent_id` = :parent",
        [
            'parent' => $parent_folder
        ]
    );
    foreach ($children as $child) {
        if ($child['range_id'] != $range_id) {
            echo sprintf("Folder %s -> range_id %s.\n", $child['id'], $range_id);
        }
        setFolderRangeId($child['id'], $range_id);
    }
}

// Fetch all root folders and process their children recursively.
$root_folders = DBManager::get()->fetchAll("SELECT `id`, `range_id` FROM `folders` WHERE `parent_id` = ''");

foreach ($root_folders as $r) {
    setFolderRangeId($r['id'], $r['range_id']);
}
